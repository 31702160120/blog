<?php
namespace phpmailer;
use think\Exception;
class Email{
    public static function send($Host, $emailUrl, $token, $name, $receiver, $receiverName, $title, $news){
        try{
        date_default_timezone_set('PRC');//set time   
        $mail = new PHPMailer;
        $mail->isSMTP();
        // $mail->SMTPDebug = 2;   //用的时候这个应该注释  不然会输出代码
        $mail->Debugoutput = 'html';
        $mail->Host = $Host;                   //发送者使用的邮件服务器
        $mail->Port = 25;
        $mail->SMTPAuth = true;
        $mail->Username = $emailUrl;           //发送者邮箱
        $mail->Password = $token;             //客户端授权密码
        $mail->setFrom($emailUrl, $name);  //发送者邮箱
        $mail->addAddress($receiver, $receiverName);   //邮件接收者邮箱
        $mail->Subject = $title;                            //发送邮件标题
        $mail->msgHTML($news);                             //发送邮件正文
        if (!$mail->send()) {
            return false;
        } else {
            return true;
        }
        }catch(phpmailerException $e){
            return false;
        }
    }
}