<?php class ChmodController extends BaseController{

	function __construct($registry, $params)
	{
		parent::__construct($registry, $params);
		$this->tb = "moderators_type";
        $this->name = "Права доступа";
		$this->tb_lang = $this->key_lang_admin.'_'.$this->tb;
		$this->registry = $registry;
	}

	public function indexAction()
	{
		$vars['message'] = '';
        $vars['name'] = $this->name;
		if(isset($this->registry['access']))$vars['message'] = $this->registry['access'];
		if(isset($this->params['delete'])||isset($_POST['delete']))$vars['message'] = $this->delete();
		elseif(isset($_POST['update']))$vars['message'] = $this->save();
		$this->view = new View($this->registry);
		$vars['list'] = $this->view->Render('view.phtml', $this->listView());
		$data['content'] = $this->view->Render('list.phtml', $vars);
		return $this->Index($data);
	}
	
	public function editAction()
    {
        $vars['message'] = '';
        if(isset($_POST['update']))$vars['message'] = $this->save();
        $vars['edit'] = $this->db->row("SELECT tb.* FROM ".$this->tb." tb WHERE tb.id=?",[$this->params['edit']]);
        $vars['list'] = $this->listView();
        $this->view = new View($this->registry);
        $vars['modules'] = $this->db->rows("SELECT * FROM `modules` m LEFT JOIN `moderators_permission` mp ON mp.module_id=m.id AND mp.moderators_type_id=? AND subsystem_id='0' ORDER BY m.id ASC",[$this->params['edit']]);
		$vars['subsystem2'] = $this->db->rows("SELECT * FROM `subsystem` m GROUP BY m.id ORDER BY m.id ASC");
		$vars['permission'] = $this->db->rows("SELECT * FROM `moderators_permission` mp WHERE mp.moderators_type_id=? AND subsystem_id!='0'",[$this->params['edit']]);
        $data['content'] = $this->view->Render('edit.phtml', $vars);
        return $this->Index($data);
    }
	
	public function addAction()
	{
		$vars['message'] = '';
		if(isset($_POST['add']))$vars['message'] = $this->add();
		$vars['list'] = $this->listView();
		$this->view = new View($this->registry);
		$data['content'] = $this->view->Render('add.phtml', $vars);
		return $this->Index($data);
	}

	public function add($modules_id='')
	{
		$message='';
		if(isset($_POST['comment'])) {
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET comment=?", [$_POST['comment']]);
			$message.= messageAdmin('Данные успешно добавлены');
		} elseif(isset($this->params['addsubsystem'])) {
            $insert_id = $this->db->insert_id("INSERT INTO `".$this->tb."` SET modules_id='$modules_id'");
			foreach($this->language as $lang) {
				$tb=$lang['language']."_".$this->tb;
				$this->db->query("INSERT INTO `$tb` SET `value`=?, `".$this->tb."_id`=?", ['', $insert_id]);
			}
			$message.= messageAdmin('Данные успешно добавлены');
		} //else $message.= messageAdmin('При добавление произошли ошибки', 'error');
		return $message;
	}
	
	public function save()
	{
		$message='';
        if(isset($this->registry['access']))$message = $this->registry['access'];
        else {
            if(isset($_POST['save_id']) && is_array($_POST['save_id'])) {
                if(isset($_POST['id'], $_POST['comment'])) {
                    $count=count($_POST['id']) - 1;
                    for($i=0; $i<=$count; $i++) $this->db->query("UPDATE `".$this->tb."` SET `comment`=? WHERE id=?", [$_POST['comment'][$i], $_POST['save_id'][$i]]);
                    $message .= messageAdmin('Данные успешно сохранены');
                } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            } else {
                if(isset($_POST['update'])) {
					if (isset($_POST['id']) && (int)$_POST['id'] > 0) $moderator_id = (int)$_POST['id'];
					if(isset($_POST['name'], $moderator_id)) $this->db->query("UPDATE `".$this->tb."` SET `comment`=? WHERE id=?", [$_POST['name'], $moderator_id]);
                    /*  '000'-off;
                        '100'-read;
                        '200'-read/edit;
                        '300'-read/del;
                        '400'-read/add;
                        '500'-read/edit/del;
                        '600'-read/edit/add;
                        '700'-read/del/add;
                        '800'-read/edit/del/add; */
					$types = ['read', 'edit', 'del', 'add'];
                    if(isset($_POST['module_id'])&&count($_POST['module_id'])!=0) {
                        foreach ($_POST['module_id'] as $id => $module_id) {
							$res = "";
							foreach ($types as $type) $res .= ($_POST[$type][$module_id]) ? "1" : "0";
							switch ($res) {
								case ("1111"): $chmod = "800"; break;
								case ("1000"): $chmod = "100"; break;
								case ("1100"): $chmod = "200"; break;
								case ("1010"): $chmod = "300"; break;
								case ("1001"): $chmod = "400"; break;
								case ("1110"): $chmod = "500"; break;
								case ("1101"): $chmod = "600"; break;
								case ("1011"): $chmod = "700"; break;
								default: $chmod = "000"; break;
							}
							$param = (isset($moderator_id)) ? [$moderator_id, $module_id] : [$module_id, $_POST['id2']];
                            $row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=? AND subsystem_id='0'", $param);
							$param = (isset($moderator_id)) ? [$chmod, $moderator_id, $module_id] : [$chmod, $module_id, $_POST['id2']];
							$sql = ($row) ? "UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=? AND subsystem_id='0'" : "INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?, subsystem_id='0'";
                            $this->db->query($sql, $param);
                        }
                    }
					if(isset($_POST['subsystem_id'])&&count($_POST['subsystem_id'])!=0) {
						$chmod=800;
						$this->db->query("UPDATE `moderators_permission` SET `permission`='000' WHERE subsystem_id !='0' AND `moderators_type_id`=?",[$moderator_id]);
						$count=count($_POST['subsystem_id']) - 1;
						for($i=0; $i<=$count; $i++) {
                            $id = explode('-', $_POST['subsystem_id'][$i]);
                            $row = $this->db->row("SELECT moderators_type_id FROM `moderators_permission` WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?",[$moderator_id, $id[0], $id[1]]);
                            $param = [$chmod, $moderator_id, $id[0], $id[1]];
                            if($row)$this->db->query("UPDATE `moderators_permission` SET `permission`=? WHERE moderators_type_id=? AND module_id=? AND subsystem_id=?",$param);
                            else $this->db->query("INSERT INTO `moderators_permission` SET `permission`=?, moderators_type_id=?, module_id=?, subsystem_id=?",$param);
                        }
                    }
                    $message.=messageAdmin('Данные успешно сохранены');
                }
                else $message.=messageAdmin('При сохранение произошли ошибки', 'error');
            }
        }
        return $message;
	}

	public function delete()
	{
		$message='';
		if(isset($this->registry['access']))$message = $this->registry['access'];
		else {
			if(isset($_POST['id'])&&is_array($_POST['id'])) {
				$count=count($_POST['id']) - 1;
				for($i=0; $i<=$count; $i++) {
					if($_POST['id'][$i]!=1)
					$this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?",[$_POST['id'][$i]]);
				}
				$message = messageAdmin('Запись успешно удалена');
			} elseif(isset($this->params['delete'])&& $this->params['delete']!='') {
				$id = $this->params['delete'];
				if($id!=1)
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?",[$id]))$message = messageAdmin('Запись успешно удалена');
			} elseif(isset($this->params['delsubsystem'])&& $this->params['delsubsystem']!='') {
				$id = $this->params['delsubsystem'];
				if($id!=1)
				if($this->db->query("DELETE FROM `".$this->tb."` WHERE `id`=?",[$id]))$message = messageAdmin('Запись успешно удалена');
			}
		}
		return $message;
	}
	
	function listView($where='')
	{
		$vars['list'] = $this->db->rows("SELECT tb.* FROM ".$this->tb." tb WHERE id!='1' ORDER BY tb.`id` DESC");
		return $vars;
	}
	
	public function subcontent($vars=[])
	{
		$vars['modules'] = $this->db->rows("SELECT *, m.comment as name, m.id as type_id FROM `moderators_type` m LEFT JOIN `moderators_permission` mp ON mp.moderators_type_id=m.id AND mp.module_id=? WHERE m.id!=? GROUP BY m.id ORDER BY m.id ASC",[$vars['modules_id'], 1]);
		$this->view = new View($this->registry);
		return $this->view->Render('chmod.phtml', $vars);
	}
}