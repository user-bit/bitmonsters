<?php class Validate
{
	static function check($str,$translation,$type='text')
	{
		$message='';
		if(is_array($str)){
			foreach($str as $row){
				if($type=='email')
					if(!preg_match('|\b([a-z0-9_\.\-]{1,20})@\b([a-z0-9\.\-]{1,20}\b)\.([a-z]{2,5})|is',$row))$message="<div class='alert alert-danger'>".$translation['wrong_email']."</div>";
					elseif($type=='text')
						if(!isset($row{1}))$message="<div class='alert alert-danger'>".$translation['required']."</div>";
						elseif($type=='password')if(!isset($row{5}))$message="<div class='alert alert-danger'>".$translation['required']."</div>";
			}
		}else{
			if($type=='email'&&!preg_match('|\b([a-z0-9_\.\-]{1,20})@\b([a-z0-9\.\-]{1,20}\b)\.([a-z]{2,5})|is',$str))$message="<div class='alert alert-danger'>".$translation['wrong_email']."</div>";
			if($type=='phone'&&!preg_match('/^\+?[0-9]{1,3}\s?\s?\(?\s?[0-9]{1,5}\s?\)?\s?[0-9\s-]{3,10}$/',$str))$message="<div class='alert alert-danger'>".$translation['wrong_phone']."</div>";
			elseif($type=='text'&&!isset($str{3}))$message="<div class='alert alert-danger'>".$translation['required']."</div>";
		}
		return $message;
	}

	static function checkPass($pass,$pass2)
	{
		$text='';
		if($pass != $pass2)$text.='Пароли не совпадают<br>';
		if(!isset($pass{6}))$text.='Пароль не менее 7 символов!<br>';
		return $text;
	}
}