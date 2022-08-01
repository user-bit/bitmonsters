<?php class Language extends Model
{
	static $table='language';
	static $name='Языки';

	public function __construct($registry)
	{
		parent::getInstance($registry);
	}

	public static function getObject($registry)
	{
		return new self::$table($registry);
	}

	public function add()
	{
		$message='';
		if(isset($_POST['comment'],$_POST['language'])&&$_POST['comment'] != ''&&$_POST['language'] != '0') {
			try {
				$this->db->beginTransaction();
				$rollBack='';
				$param=[$_POST['comment'],$_POST['comment_low'],$_POST['language'],'domen'.$_POST['language'],0];
				$insert_id=$this->db->insert_id("INSERT INTO `".self::$table."` SET
				 `comment`=?,`comment_low`=?,`language`=?,`domen`=?,`sort`=?",$param);
				if(!$insert_id)$rollBack='ok';
				$language_default=$this->db->cell("Select `language`  FROM `".self::$table."` WHERE `default`=?",array(1));
				// найдем все языковые таблицы
				$defaultPrefixLang=$language_default.'_';
				$newPrefixLang=$_POST['language'].'_';
				$tables=$this->db->rows("show tables LIKE '%".$defaultPrefixLang."%'");
				foreach ($tables as $key => $table){
					$table=array_values($table)[0];
					$newTable=str_replace($defaultPrefixLang,$newPrefixLang,$table);
					if(false === $this->db->row("SHOW TABLES LIKE '%".$newTable."%' ")){
						$sql="CREATE TABLE IF NOT EXISTS ".$newTable." LIKE ".$table;
						$querySelect="INSERT INTO ".$newTable." SELECT * FROM ".$table;
						$create=$this->db->query($sql);
						if($create === false)$rollBack='ok';
						else $this->db->query($querySelect);
					}
				}
				if($rollBack=='ok') {
					$this->db->rollBack(); // отмена всех add
					$message.=messageAdmin('При добавление произошли ошибки','error');
				} else {
					$this->db->commit();   // save Transaction
					$message.=messageAdmin('Данные успешно добавлены');
				}
			} catch (PDOException $e) {
				$this->db->rollBack();// отмена всех add
				$message.=messageAdmin('При добавление произошли ошибки!','error');
			}
		} else $message.=messageAdmin('Заполнены не все обязательны данные','error');
		return $message;
	}

	public function save()
	{
		$message='';
		if(isset($this->registry['access']))$message=$this->registry['access'];
		else {
			if(isset($_POST['save_id'])&&is_array($_POST['save_id'])) {
				if(isset($_POST['save_id'],$_POST['comment'])) {
					for ($i=0; $i <= count($_POST['save_id']) - 1; $i++) {
						$def=(isset($_POST['default'][0])&&$_POST['default'][0]==$_POST['save_id'][$i]) ? 1 : 0;
						$param=[$_POST['comment'][$i],$_POST['comment_low'][$i],$def,$_POST['save_id'][$i]];
						$this->db->query("UPDATE `".self::$table."` SET `comment`=?,`comment_low`=?,`default`=? WHERE id=?",$param);
					}
					$message.=messageAdmin('Данные успешно сохранены');
				} else $message.=messageAdmin('При сохранение произошли ошибки','error');
			}
		}
		return $message;
	}

	public function delete()
	{
		$message='';
		$id=$this->params['delete'];
		$default=$this->db->cell("Select `default`  FROM `".self::$table."` WHERE `id`=?",[$id]);
		if($default==1)$message.=messageAdmin('При удалении произошли ошибки<br> Нельзя удалить основной язык!','error');
		if(isset($this->registry['access']))$message=$this->registry['access'];
		elseif($default <> 1) {
			if(isset($this->params['delete'])&&$this->params['delete'] > 0) {
				$key=$this->db->cell("Select `language`  FROM `".self::$table."` WHERE `id`=?",[$id]);
				$db_name=$this->registry['db_settings']["name"];
				$tables=$this->db->rows('show tables');
				$mass=[];
				sort($tables);
				$my_value=$key;
				$array=$tables;
				$filtered_array=array_filter($array,function ($element) use ($my_value) {
					$mm=explode('_',$element[key($element)]);
					return ($mm[0]==$my_value);
				});
				foreach ($filtered_array as $ky => $val)$this->db->query("DROP TABLE `".$val["Tables_in_{$db_name}"]."`");
				if($this->db->query("DELETE FROM `".self::$table."` WHERE `id`=?",[$id]))$message=messageAdmin('Запись успешно удалена');
			}
		}
		return $message;
	}
}