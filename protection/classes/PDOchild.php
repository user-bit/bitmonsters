<?php class PDOchild Extends PDO
{
	private $registry;

	public function __construct($registry)
	{
		$this->registry=$registry;
		$db_setts=$this->registry['db_settings'];
		$db_setts['host']='mysql:host='.$db_setts['host'].';dbname='.$db_setts['name'];
		$db_setts=[$db_setts['host'],$db_setts['user'],$db_setts['password'],[PDO::MYSQL_ATTR_INIT_COMMAND=> "SET NAMES '".$db_setts['charset']."'"]];
		if(($num_args=func_num_args()) > 0){
			$args=func_get_args();
			for($i=1;$i<$num_args;$i++)if($db_setts[$i]!=NULL)$db_setts[$i]=$args[$i];
		}
		try{
			$dbh=call_user_func_array(['PDO','__construct'],$db_setts);
			$this->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
			return $dbh;
		}catch(Exception $e){
			Log::echoLog("Cannot connect to database!<br>\nCaught exception:\n".$e->getMessage()." in ".$e->getFile()." on line ".$e->getLine());
			return;
		}
	}

	public function query($pattern,$vars=null)
	{
		try{
			$sth=$this->prepare($pattern);
			$sth->execute($vars);
		}catch(PDOException $e){
			$info=debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $sth->rowCount();
	}

	public function insert_id($pattern,$vars=null)
	{
		try{
			$sth=$this->prepare($pattern);
			$sth->execute($vars);
		}catch(PDOException $e){
			$info=debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $this->lastInsertId();
	}

	public function rows($pattern,$vars=null)
	{
		try{
			$sth=$this->prepare($pattern);
			$sth->execute($vars);
		}catch(PDOException $e){
			$info=debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $sth->fetchAll(PDO::FETCH_ASSOC);
	}

	public function row($pattern,$vars=null)
	{
		try{
			$sth=$this->prepare($pattern);
			$sth->execute($vars);
		}catch(PDOException $e){
			$info=debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $sth->fetch(PDO::FETCH_ASSOC);
	}

	public function cell($pattern,$vars=null)
	{
		try{
			$sth=$this->prepare($pattern);
			$sth->execute($vars);
		}catch(PDOException $e){
			$info=debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $sth->fetch(PDO::FETCH_COLUMN,PDO::FETCH_ORI_FIRST);
	}

	public function error($e,$pattern,$info,$vars)
	{
		$msg='Catched error 406'.$e->getCode().': '.$e->getMessage()."\nin ".$info[0]['file'].' on line '.$info[0]['line'].".\nQuery: '$pattern'";
		if($vars='')$msg=":'".implode(",",$vars)."'";
		Log::echoLog($msg);
	}

	public function rows_key($pattern,$vars=null)
	{
		try{
			$sth=$this->prepare($pattern);
			$sth->execute($vars);
		}catch(PDOException $e){
			$info=debug_backtrace();
			$this->error($e,$pattern,$info,$vars);
			return false;
		}
		return $sth->fetchAll(PDO::FETCH_KEY_PAIR);
	}

}