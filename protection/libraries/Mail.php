<?php class Mail
{
    static function senTelegram($message) {
        $link = "https://api.telegram.org/bot".BOT_TOKEN."/sendMessage?chat_id=".MAIN_CHAT_ID."&text=".urlencode($message)."&parse_mode=html";

        try {
            $curl = curl_init();
            $options = [
                CURLOPT_URL => $link,
                CURLOPT_RETURNTRANSFER => true
            ];
            curl_setopt_array($curl, $options);
            curl_exec($curl);
            curl_close($curl);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die;
        }
    }
    static function senTelegramMy($message) {
        $link = "https://api.telegram.org/bot".BOT_TOKEN_ALL."/sendMessage?chat_id=".MAIN_CHAT_ID_ALL."&text=".urlencode($message)."&parse_mode=html";

        try {
            $curl = curl_init();
            $options = [
                CURLOPT_URL => $link,
                CURLOPT_RETURNTRANSFER => true
            ];
            curl_setopt_array($curl, $options);
            curl_exec($curl);
            curl_close($curl);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
            die;
        }
    }

    static function SendToAdmin($subject, $letter)
    {
        $settings = Registry::get('user_settings');
        Mail::send(
            $settings['sitename'],
            "info@" . $_SERVER['HTTP_HOST'],
            $settings['sitename'],
            email_admin,
            "utf-8", "utf-8",
            $subject . " - " . $settings['sitename'],
            $letter . "<br/> ---------------------------------------" .
            "<br/>REFERER : " . LINK . $_SERVER['HTTP_REFERER'] .
            "<br/>Страница : " . LINK . $_SERVER['REQUEST_URI']
        );
    }

    static function SendToUser($email, $name, $subject, $letter)
    {
        $settings = Registry::get('user_settings');
        Mail::send(
            $settings['sitename'],
            "info@" . $_SERVER['HTTP_HOST'],
            $name, $email,
            "utf-8", "utf-8",
            $subject . " - " . $settings['sitename'],
            $letter
        );
    }

    static function send($name_from,// имя отправителя
                         $email_from,// email отправителя
                         $name_to,// имя получателя
                         $email_to,// email получателя
                         $data_charset,// кодировка переданных данных
                         $send_charset,// кодировка письма
                         $subject,// тема письма
                         $body // текст письма
    ){
        $email_to=str_replace("&#044;",",",$email_to);
        $email_cnt=explode(",",$email_to);
        $email_to="";
        for($i=0;$i<=count($email_cnt)-1;$i++){
            if($i!=0)$email_to.=",";
            $email_to.="< {$email_cnt[$i]} >";
        }
        $to=Mail::mime_header_encode($name_to,$data_charset,$send_charset).$email_to;
        $subject=Mail::mime_header_encode($subject,$data_charset,$send_charset);
        $from=Mail::mime_header_encode($name_from,$data_charset,$send_charset).' <'.$email_from.'>';
        if($data_charset!=$send_charset)$body=iconv($data_charset,$send_charset,$body);
        $headers="From: $from\r\nReply-To: $from\r\nContent-type: text/html;charset=$send_charset\r\n";
        return mail($to,$subject,$body,$headers,"-f info@".$_SERVER['HTTP_HOST']);
    }

    static function mime_header_encode($str,$data_charset,$send_charset)
    {
        if($data_charset!=$send_charset)$str=iconv($data_charset,$send_charset,$str);
        return '=?'.$send_charset.'?B?'.base64_encode($str).'?=';
    }

    static function errorMail($text)
    {
        $contact_mail=email_error;
        $url=$_SERVER['REQUEST_URI'];
        $refer='';
        if(isset($_SERVER['HTTP_REFERER']))$refer=$_SERVER['HTTP_REFERER'];
        $ip_user=$_SERVER['REMOTE_ADDR'];
        $br_user=$_SERVER['HTTP_USER_AGENT'];
        $header="From: $contact_mail\r\nReply-To: $contact_mail\r\nReturn-Path: $contact_mail\r\nContent-type: text/plain;charset=UTF-8";
        $subject='Ошибки на сайте:'.$_SERVER['SERVER_NAME'];
        $body="SERVER_NAME:".$_SERVER['SERVER_NAME']."страница: $url \nREFER страница: $refer \nIP пользователя: $ip_user \nбраузер пользователя: $br_user \n----------------------------------------- \n$text";
        mail($contact_mail,$subject,$body,$header);
    }
}