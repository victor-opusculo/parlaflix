<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Settings;

use mysqli;
use PHPMailer\PHPMailer\PHPMailer;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;

class MainInboxMail extends DataEntity
{
    public function __construct($initialValues = null)
    {
        $this->properties = (object)
        [
            'name' => new DataProperty(null, fn() => 'MAIN_INBOX_MAIL', DataProperty::MYSQL_STRING),
            'value' => new DataProperty(null, fn() => '', DataProperty::MYSQL_STRING)
        ];

        parent::__construct($initialValues);

        $this->properties->name->setValue('MAIN_INBOX_MAIL');

    }

    protected string $databaseTable = 'settings';
    protected string $formFieldPrefixName = 'mainInboxMail';
    protected array $primaryKeys = ['name'];
    protected array $setPrimaryKeysValue = ['name'];

    public static function sendEmail(mysqli $conn, string $userEmail, string $userName) : void
    {
        $sett = (new self)->getSingle($conn);
        $destinationEmail = $sett->value->unwrapOr(null);

        if (!$destinationEmail)
            return;

        $configs = Data::getMailConfigs();
        $mail = new PHPMailer();

        $mail->IsSMTP(); // Define que a mensagem ser� SMTP
        $mail->Host = $configs['host']; // Seu endere�o de host SMTP
        $mail->SMTPAuth = true; // Define que ser� utilizada a autentica��o -  Mantenha o valor "true"
        $mail->Port = $configs['port']; // Porta de comunica��o SMTP - Mantenha o valor "587"
        $mail->SMTPSecure = false; // Define se � utilizado SSL/TLS - Mantenha o valor "false"
        $mail->SMTPAutoTLS = false; // Define se, por padr�o, ser� utilizado TLS - Mantenha o valor "false"
        $mail->Username = $configs['username']; // Conta de email existente e ativa em seu dom�nio
        $mail->Password = $configs['password']; // Senha da sua conta de email
        // DADOS DO REMETENTE
        $mail->Sender = $configs['sender']; // Conta de email existente e ativa em seu dom�nio
        $mail->From = $configs['sender']; // Sua conta de email que ser� remetente da mensagem
        $mail->FromName = "Parlaflix - Ensino à Distância da ABEL"; // Nome da conta de email
        // DADOS DO DESTINAT�RIO
        $mail->AddAddress($destinationEmail, $destinationEmail); // Define qual conta de email receber� a mensagem

        // Defini��o de HTML/codifica��o
        $mail->IsHTML(true); // Define que o e-mail ser� enviado como HTML
        $mail->CharSet = 'utf-8'; // Charset da mensagem (opcional)
        // DEFINI��O DA MENSAGEM
        $mail->Subject  = "Parlaflix - Novo associado cadastrado!"; // Assunto da mensagem

        ob_start();
        $__VIEW = 'message-subscriber-abel-member.php';
        require_once (__DIR__ . '/../../Mail/email-base-body.php');
        $emailBody = ob_get_clean();
        ob_end_clean();

        $mail->Body .= $emailBody;
        
        $sent = $mail->Send();

        $mail->ClearAllRecipients();

        // Exibe uma mensagem de resultado do envio (sucesso/erro)
        if (!$sent)
            throw new \Exception("Não foi possível enviar o e-mail! Detalhes do erro: " . $mail->ErrorInfo);
    } 
}