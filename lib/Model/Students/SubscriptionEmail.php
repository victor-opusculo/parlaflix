<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Students;

use PHPMailer\PHPMailer\PHPMailer;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;

class SubscriptionEmail
{
    public static function sendEmail(string $courseName, string $studentEmail, string $studentName) : void
    {

        $configs = Data::getTransactionalMailConfigs();
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
        $mail->AddAddress($studentEmail, $studentName); // Define qual conta de email receber� a mensagem

        // Defini��o de HTML/codifica��o
        $mail->IsHTML(true); // Define que o e-mail ser� enviado como HTML
        $mail->CharSet = 'utf-8'; // Charset da mensagem (opcional)
        // DEFINI��O DA MENSAGEM
        $mail->Subject  = "Parlaflix - Nova inscrição: {$courseName}"; // Assunto da mensagem

        ob_start();
        $__VIEW = 'message-new-subscription.php';
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