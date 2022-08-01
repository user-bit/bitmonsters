<?php class ConfigController extends BaseController
{
    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->tb = "config";
        $this->name = "Параметры";
        $this->registry = $registry;
        $row = $this->db->row("SELECT id FROM `subsystem` WHERE `name`=?", [$this->tb]);
        $this->subsystem_id = $row['id'];
        $row = $this->db->row("SELECT id FROM `modules` WHERE `controller`=?", [$this->tb]);
        $this->modules_id = $row['id'];
	    if (isset($_GET['active'])) $this->setActiveTrue();
    }

    public function indexAction()
    {
        $vars['message'] = '';
        $vars['name'] = $this->name;
        if (isset($this->registry['access'])) $vars['message'] = $this->registry['access'];
        if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message'] = $this->delete();
        elseif (isset($_POST['update'])) $vars['message'] = $this->save();
        elseif (isset($_POST['update_close'])) $vars['message'] = $this->save();
        elseif (isset($_POST['add_close'])) $vars['message'] = $this->add();
        $where = "WHERE modules_id='0'";
        $vars['path'] = '';
        if (isset($this->params['modules'])) {
            $vars['sub'] = $this->params['modules'];
            $where = "WHERE modules_id='{$this->params['modules']}'";
            $vars['path'] = '/modules/' . $this->params['modules'];
        }


        $template = 'view.phtml';
        $template2 = 'list.phtml';
        if ($_SESSION['admin']['type'] == 1) {
            $template = 'view_superadmin.phtml';
            $template2 = 'list_superadmin.phtml';
            $vars['menu'] = $this->db->rows("SELECT m.*, mp.*, COUNT(DISTINCT config.id) AS cnt FROM modules m LEFT JOIN `moderators_permission` mp ON mp.module_id=m.id LEFT JOIN config ON config.modules_id=m.id GROUP BY m.id ORDER BY cnt DESC");
        } else $vars['menu'] = $this->db->rows("SELECT m.*, mp.*, COUNT(DISTINCT config.id) as cnt FROM modules m LEFT JOIN `moderators_permission` mp ON mp.module_id=m.id LEFT JOIN config ON config.modules_id=m.id WHERE mp.moderators_type_id=? AND subsystem_id='" . $this->subsystem_id . "' AND permission!='000' GROUP BY m.id ORDER BY cnt DESC ", [$_SESSION['admin']['type']]);
        $data['right_menu'] = $this->view->Render('right_menu2.phtml', $vars);

        $vars['list'] = $this->view->Render($template, $this->listView($where));
        $data['content'] = $this->view->Render($template2, $vars);
        return $this->Index($data);
    }

    public function addAction()
    {
        $vars['message'] = '';
        if (isset($_POST['add'])) $vars['message'] = $this->add();
        $vars['path'] = '';
        if (isset($this->params['modules'])) $vars['path'] = '/modules/' . $this->params['modules'];
        $data['breadcrumb'] = '<a class="back-link" href="/admin/' . $this->tb . $vars['path'] . '">« Назад в: ' . $this->name . '</a>';
        $data['content'] = $this->view->Render('add.phtml', $vars);
        return $this->Index($data);
    }

    public function add($modules_id = '')
    {
        $message = '';
        if (isset($_POST['name'], $_POST['value'], $_POST['comment'])) {
            $row = $this->db->row("SELECT id FROM `" . $this->tb . "` WHERE name=?", array($_POST['name']));
            if (!$row) {
                $where = '';
                if (isset($this->params['modules'])) $modules_id = $this->params['modules'];
                if ($modules_id != '') $where = ", modules_id='$modules_id'";
                $this->db->query("INSERT INTO `" . $this->tb . "` SET `name`=?, `value`=?, comment=? $where", [$_POST['name'], $_POST['value'], $_POST['comment']]);
                $message .= messageAdmin('Данные успешно добавлены');
            } else $message .= messageAdmin('Данный ключ занят!', 'error');
        } elseif (isset($this->params['addsubsystem'])) {
            $this->db->query("INSERT INTO `" . $this->tb . "` SET modules_id='$modules_id'");
            $message .= messageAdmin('Данные успешно добавлены');
        } else $message.= messageAdmin('При добавление произошли ошибки', 'error');
        return $message;
    }

    public function save()
    {
        $message = '';
        if (isset($this->registry['access'])) $message = $this->registry['access'];
        else {
            if (isset($_POST['save_id']) && is_array($_POST['save_id'])) {
                if ($_SESSION['admin']['type'] == 1) {
                    if (isset($_POST['save_id'], $_POST['name'], $_POST['comment'])) {
                        for ($i = 0; $i <= count($_POST['save_id']) - 1; $i++) {
                            $id = $_POST['save_id'][$i];
                            if (isset($_POST['value' . $id])) $value = $_POST['value' . $id];
                            else $value = '';

                            $this->db->query("UPDATE `" . $this->tb . "` tb SET `name`=?, `value`=?, `comment`=? , `modules_id`=? , `type`=? WHERE `id`=?", [$_POST['name'][$i], $value, $_POST['comment'][$i], $_POST['modules_id'][$i], $_POST['type_id'][$i], $_POST['save_id'][$i]]);
                        }
                        if ($message == '') $message = messageAdmin('Данные успешно сохранены');
                    } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
                } else {
                    if (isset($_POST['save_id'], $_POST['comment'])) {
                        for ($i = 0; $i <= count($_POST['save_id']) - 1; $i++) {
                            $id = $_POST['save_id'][$i];
                            if ($this->model->check_for_update($id, $this->tb, $_SESSION['admin']['type'])) {
                                if (isset($_POST['value' . $id])) $value = $_POST['value' . $id];
                                else $value = '';
                                $this->db->query("UPDATE `" . $this->tb . "` tb SET `value`=?, `comment`=? WHERE `id`=?", [$value, $_POST['comment'][$i], $_POST['save_id'][$i]]);
                            } else $message .= messageAdmin('Ошибка в правах доступа!' . $this->tb . $id, 'error');
                        }
                        if ($message == '') $message = messageAdmin('Данные успешно сохранены');
                    } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
                }
            }
        }
        return $message;
    }

    public function delete()
    {
        $message = '';
        if ($_SESSION['admin']['type'] == 1) {

            if (isset($_POST['id']) && is_array($_POST['id'])) {
                for ($i = 0; $i <= count($_POST['id']) - 1; $i++) $message = $this->model->check_for_delete($_POST['id'][$i], $this->tb, $_SESSION['admin']['type']);
                if ($message == '') $message = messageAdmin('Запись успешно удалена');
            } elseif (isset($this->params['delete']) && $this->params['delete'] != '') $message = $this->model->check_for_delete($this->params['delete'], $this->tb, $_SESSION['admin']['type']);
            elseif (isset($this->params['delsubsystem']) && $this->params['delsubsystem'] != '') $message = $this->model->check_for_delete($this->params['delsubsystem'], $this->tb, $_SESSION['admin']['type']);
        }
        return $message;
    }

    public function listView($where = '')
    {

        $vars['path'] = '';
        if (isset($this->params['modules'])) {
            $vars['path'] = '/modules/' . $this->params['modules'];
        }
        $join = '';
        $where = str_replace('WHERE', 'AND', $where);
        if ($_SESSION['admin']['type'] != 1) {
            $join = "LEFT JOIN `moderators_permission` mp ON mp.module_id=tb.modules_id";
            $where .= " AND ((mp.moderators_type_id='{$_SESSION['admin']['type']}' AND subsystem_id='" . $this->subsystem_id . "' AND permission!='000') OR tb.modules_id='0') AND tb.active='1'";
        }
        $vars['list'] = $this->db->rows("SELECT tb.* FROM " . $this->tb . " tb $join WHERE tb.active='1' $where GROUP BY tb.id ORDER BY tb.`sort` ASC, tb.modules_id ASC");
        $vars['modules'] = $this->db->rows("SELECT id, name, controller FROM modules ORDER BY sub ASC, name ASC");
        return $vars;
    }

    public function subcontent($vars = array())
    {
        $vars['modules'] = $this->db->rows("SELECT id, name FROM modules ORDER BY sub ASC, name ASC");
        $arr = $this->listView($vars['where']);
        $vars['list'] = $arr['list'];
        $template = $this->tb . '.phtml';
        if ($_SESSION['admin']['type'] == 1) $template = $this->tb . '_superadmin.phtml';
        return $this->view->Render($template, $vars);
    }


	public function searchAction()
	{
		$this->registry->set(PathToTemplateAdmin, $this->tb);
		$status = null;
		$content = null;
		$template = 'view.phtml';
		if ($_SESSION['admin']['type'] == 1) {
			$template = 'view_superadmin.phtml';
		}
		$where = 'tb.comment LIKE "%' . $_POST['message'] . '%" 
				OR tb.value LIKE "%' . $_POST['message'] . '%" 
				OR tb.name LIKE "%' . $_POST['message'] . '%"';
		if (empty($_POST['message'])) $where = '1';
		$list = $this->model->find([
				'table'=>$this->tb,
				'select' => '*',
				'order' => 'tb.`id` DESC',
				'type' => 'rows',
				'where' => $where,
				'paging' => 100
			]
		);
		$modules = $this->db->rows("SELECT id, name FROM modules ORDER BY sub ASC, name ASC");
		if (!$list) {
			$status = false;
			$content = "Ничего не найдено";
		} else {
			$status = true;
			$content = $this->view->Render($template, array('list' => $list['list'], 'action' => 'config', 'paging' => $list['paging'], 'modules'=> $modules));
		}
		return json_encode(array("status" => $status, "content" => $content, "action" => "meta"));
	}

	public function setActiveTrue()
	{
		$where = '';
		if (isset($this->params['subsystem'])) $moduleId = $this->config = Modules::getObject($this->sets)->find($this->params['controller']);
		if ($moduleId ) $where = " WHERE modules_id='".$moduleId['id'] ."' ";
		$this->db->query("UPDATE ".$this->tb." SET `active` =? ".$where, [1]);
	}
}