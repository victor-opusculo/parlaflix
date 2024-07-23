<?php
namespace VictorOpusculo\Parlaflix\Api\Administrator\Panel\Subscriptions;

use Exception;
use PHPMailer\PHPMailer\PHPMailer;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VictorOpusculo\Parlaflix\Lib\Helpers\LogEngine;
use VictorOpusculo\PComp\RouteHandler;

require_once __DIR__ . '/../../../../lib/Middlewares/AdminLoginCheck.php';
require_once __DIR__ . '/../../../../lib/Middlewares/JsonBodyParser.php';

final class SendEmail extends RouteHandler
{
    public function __construct()
    {
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\adminLoginCheck';
        $this->middlewares[] = '\VictorOpusculo\Parlaflix\Lib\Middlewares\jsonParser';
    }

    protected function POST(): void
    {
        try
        {
            [ "title" => $title, "message" => $message, "destinations" => $destinations ] = $_POST;

            $configs = Data::getMailConfigs();
            $mail = new PHPMailer();
    
            $mail->IsSMTP();
            $mail->Host = $configs['host'];
            $mail->SMTPAuth = true; 
            $mail->Port = $configs['port'];
            $mail->SMTPSecure = false; 
            $mail->SMTPAutoTLS = false;
            $mail->Username = $configs['username'];
            $mail->Password = $configs['password'];

            // DADOS DO REMETENTE
            $mail->Sender = $configs['sender']; 
            $mail->From = $configs['sender'];
            $mail->FromName = "Parlaflix - Ensino à Distância da ABEL";

            // DADOS DO DESTINAT�RIO
            foreach ($destinations as $dest)
                $mail->AddAddress($dest['email'], $dest['name']);
    
            $mail->IsHTML(true); // Define que o e-mail ser� enviado como HTML
            $mail->CharSet = 'utf-8'; 
            // DEFINI��O DA MENSAGEM
            $mail->Subject  = "Parlaflix - " . $title; // Assunto da mensagem
    
            ob_start();
            $__VIEW = 'message-to-subscriber.php';
            require_once (__DIR__ . '/../../../../lib/Mail/email-base-body.php');
            $emailBody = ob_get_clean();
            ob_end_clean();
    
            $mail->Body .= $emailBody;
            
            $sent = $mail->Send();
    
            $mail->ClearAllRecipients();
    
            if (!$sent)
                throw new \Exception("Não foi possível enviar o e-mail! Detalhes do erro: " . $mail->ErrorInfo);

            LogEngine::writeLog("E-mail enviado para inscrito(s)");
            $this->json([ 'success' => "E-mail enviado com sucesso!" ]);
        }
        catch (Exception $e)
        {
            LogEngine::writeErrorLog($e->getMessage());
            $this->json([ 'error' => $e->getMessage() ], 500);
        }
    } 
}