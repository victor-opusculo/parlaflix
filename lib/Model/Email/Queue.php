<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Email;

use mysqli;
use PHPMailer\PHPMailer\PHPMailer;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\SqlSelector;
use VOpus\PhpOrm\Option;

/**
 * @property Option<int> id
 * @property Option<string> destination_email
 * @property Option<string> destination_name
 * @property Option<string> subject
 * @property Option<string> message
 */
final class Queue extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty('id', fn() => null, DataProperty::MYSQL_INT),
            'destination_email' => new DataProperty('email', fn() => '', DataProperty::MYSQL_STRING),
            'destination_name' => new DataProperty('name', fn() => '', DataProperty::MYSQL_STRING),
            'subject' => new DataProperty('subject', fn() => '', DataProperty::MYSQL_STRING),
            'message' => new DataProperty('message', fn() => '', DataProperty::MYSQL_STRING)
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'email_queue';
    protected string $formFieldPrefixName = 'email_queue';
    protected string $fileUploadFieldName = 'email_queue';
    protected array $primaryKeys = ['id'];

    public function getAll(?mysqli $conn) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearWhereClauses()
        ->clearValues();

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRowFromDatabase' ], $drs);
    }

    public function clearAll(?mysqli $conn)
    {
        return $conn->query("TRUNCATE TABLE {$this->databaseTable}");
    }

    public function fillMessageFromView(string $view, array $data)
    {
        foreach ($data as $k => $v)
            $$k = $v;

        ob_start();
        $__VIEW = $view;
        require_once (__DIR__ . '/../../Mail/email-base-body.php');
        $emailBody = ob_get_clean();
        ob_end_clean();

        $this->message = $emailBody;
    }

    public function sendEmailCron() : void
    {
        $email = $this->destination_email->unwrapOr(null);
        $name = $this->destination_name->unwrapOr(null);
        $subject = $this->subject->unwrapOr(null);
        $message = $this->message->unwrapOr(null);

        if (!$email || !$name || !$subject || !$message)
        {
            $msglen = strlen($message);
            LogEngine::writeErrorLog("Email de fila com dados incompletos! Email: {$email} | Name: {$name} | Subject: {$subject} | Message Length: {$msglen}");
            return;
        }

        $configs = Data::getTransactionalMailConfigsCron();
        $mail = new PHPMailer();

        $mail->Timeout = 30;
        $mail->IsSMTP(); // Define que a mensagem ser� SMTP
        $mail->Host = $configs['host']; // Seu endere�o de host SMTP
        $mail->SMTPAuth = true; // Define que ser� utilizada a autentica��o -  Mantenha o valor "true"
        $mail->Port = $configs['port']; // Porta de comunica��o SMTP - Mantenha o valor "587"
        $mail->SMTPSecure = 'tls'; // Define se � utilizado SSL/TLS - Mantenha o valor "false"
        //$mail->SMTPAutoTLS = true; // Define se, por padr�o, ser� utilizado TLS - Mantenha o valor "false"
        $mail->Username = $configs['username']; // Conta de email existente e ativa em seu dom�nio
        $mail->Password = $configs['password']; // Senha da sua conta de email
        // DADOS DO REMETENTE
        $mail->Sender = $configs['sender']; // Conta de email existente e ativa em seu dom�nio
        $mail->From = $configs['sender']; // Sua conta de email que ser� remetente da mensagem
        $mail->FromName = "Parlaflix - Ensino à Distância da ABEL"; // Nome da conta de email
        // DADOS DO DESTINAT�RIO
        $mail->AddAddress($email, $name); // Define qual conta de email receber� a mensagem

        // Defini��o de HTML/codifica��o
        $mail->IsHTML(true); // Define que o e-mail ser� enviado como HTML
        $mail->CharSet = 'utf-8'; // Charset da mensagem (opcional)
        // DEFINI��O DA MENSAGEM
        $mail->Subject  = $subject; // Assunto da mensagem

        $mail->Body .= $message;
        
        $sent = $mail->Send();

        $mail->ClearAllRecipients();

        // Exibe uma mensagem de resultado do envio (sucesso/erro)
        if (!$sent)
            throw new \Exception("Não foi possível enviar o e-mail! Detalhes do erro: " . $mail->ErrorInfo);
    } 
}
