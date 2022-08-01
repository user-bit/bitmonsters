<?php class Log
{
	public static function echoLog($logText)
	{
		if(DEBUG) echo '<div class="alert alert-danger"><div class="debug_message"><pre>'.$logText.'</pre></div></div>';
		else echo "Mysql Error!";
		Mail::errorMail($logText);
	}

	public static function writeLog($logText, $logType)
	{
		$body=$logType."\n".$logText;
		if(DEBUG)echo '<div class="alert alert-danger">'.$logType.'<br>'.$logText.'</div>';
		else{
			Mail::errorMail($body);
			echo "<div class=\"alert alert-danger\">Mysql Error!</div>";
		}
	}
}