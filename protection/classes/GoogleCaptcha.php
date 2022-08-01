<?php class GoogleCaptcha
{
    public static function check(){
    $secretkey = "6LeaxJkUAAAAAKN1ye8sLmGXolFg9GgDiyf-252l";
      $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secretkey."&response=".$_POST['g-recaptcha-response']."&remoteip=".$_SERVER['REMOTE_ADDR']);
      if(($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
        $responseData = json_decode($response);
        if(!$responseData->success) return 0; // 0
      } else return 0; // 0
      return 1;
    }
}