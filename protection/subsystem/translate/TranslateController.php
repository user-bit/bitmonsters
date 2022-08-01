<?php class TranslateController extends BaseController
{
    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->tb = "translate";
        $this->name = "Интерфейс";
        $this->tb_lang = $this->key_lang_admin . '_' . $this->tb;
        $this->registry = $registry;
        $row = $this->db->row("SELECT id FROM `subsystem` WHERE `name`=?", [$this->tb]);
        $this->subsystem_id = $row['id'];
        $row = $this->db->row("SELECT id FROM `modules` WHERE `controller`=?", [$this->tb]);
        $this->modules_id = $row['id'];
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
            $template = 'admin/view.phtml';
            $template2 = 'admin/list.phtml';
            $vars['menu'] = $this->db->rows("SELECT m.*, mp.*, COUNT(DISTINCT translate.id) as cnt FROM modules m LEFT JOIN `moderators_permission` mp ON mp.module_id=m.id LEFT JOIN translate ON translate.modules_id=m.id GROUP BY m.id ORDER BY cnt DESC, name ASC", [$_SESSION['admin']['type']]);
        } else {
            $vars['menu'] = $this->db->rows("SELECT m.*, mp.*, COUNT(DISTINCT translate.id) as cnt FROM modules m LEFT JOIN `moderators_permission` mp ON mp.module_id=m.id LEFT JOIN translate ON translate.modules_id=m.id WHERE mp.moderators_type_id=? AND subsystem_id='" . $this->subsystem_id . "' AND permission!='000' GROUP BY m.id ORDER BY cnt DESC, name ASC", [$_SESSION['admin']['type']]);
        }
        $data['right_menu'] = $this->view->Render('right_menu.phtml', $vars);
        $vars['list'] = $this->view->Render($template, $this->listView($where));
        $data['content'] = $this->view->Render($template2, $vars);
        return $this->Index($data);
    }

    public function addAction()
    {
        $vars['message'] = '';
        if (isset($_POST['add'])) $vars['message'] = $this->add();
        $vars['this_modules'] = $this->params['modules'];
        $vars['path'] = '';
        if (isset($this->params['modules'])) $vars['path'] = '/modules/' . $this->params['modules'];
        $vars['list'] = $this->listView();
        $this->view = new View($this->registry);
        $data['breadcrumb'] = '<a class="back-link" href="/'.PathToTemplateAdmin.'/' . $this->tb . $vars['path'] . '">« Назад в: ' . $this->name . '</a>';
        $data['content'] = $this->view->Render('add.phtml', $vars);
        return $this->Index($data);
    }

    public function add($modules_id = '')
    {
        $message = '';
        if (isset($_POST['key'], $_POST['value'], $_POST['comment'])) {
            $row = $this->db->row("SELECT id FROM `" . $this->tb . "` WHERE `key`=?", [$_POST['key']]);
            if (!$row) {
                if (empty($_POST['modules_id']))
                    $_POST['modules_id'] = 0;
                $param = array($_POST['key'], $_POST['comment'], $_POST['modules_id']);

                $insert_id = $this->db->insert_id("INSERT INTO `" . $this->tb . "` SET `key`=?, `comment`=?, `modules_id`=?", $param);
                foreach ($this->language as $lang) {
                    $tb = $lang['language'] . "_" . $this->tb;
                    $param = array($_POST['value'], $insert_id);
                    $this->db->query("INSERT INTO `$tb` SET `value`=?, `" . $this->tb . "_id`=?", $param);
                }
                $message .= messageAdmin('Данные успешно добавлены');
            } else $message .= messageAdmin('Данный ключ занят!', 'error');
        } elseif (isset($this->params['addsubsystem'])) {

            $insert_id = $this->db->insert_id("INSERT INTO `" . $this->tb . "` SET modules_id='$modules_id'");

            foreach ($this->language as $lang) {
                $tb = $lang['language'] . "_" . $this->tb;
                $param = array('', $insert_id);
                $this->db->query("INSERT INTO `$tb` SET `value`=?, `" . $this->tb . "_id`=?", $param);
            }
            $message .= messageAdmin('Данные успешно добавлены');
        } //else $message.= messageAdmin('При добавление произошли ошибки', 'error');
        return $message;
    }

    public function save()
    {
        $message = '';
        if (isset($this->registry['access'])) $message = $this->registry['access'];
        else {
            if (isset($_POST['save_id']) && is_array($_POST['save_id'])) {
                if ($_SESSION['admin']['type'] == 1) {
                    if (isset($_POST['save_id'], $_POST['key'], $_POST['value'], $_POST['comment'])) {
                        for ($i = 0; $i <= count($_POST['save_id']) - 1; $i++) {
                            if(empty($_POST['modules_id'][$i]))
                                $_POST['modules_id'][$i] = 0;
                            $this->db->query("UPDATE `" . $this->tb_lang . "` SET `value`=? WHERE " . $this->tb . "_id=?", [$_POST['value'][$i], $_POST['save_id'][$i]]);
                            $this->db->query("UPDATE `" . $this->tb . "` SET `key`=?, `comment`=?, modules_id=? WHERE id=?", [$_POST['key'][$i], $_POST['comment'][$i], $_POST['modules_id'][$i], $_POST['save_id'][$i]]);
                        }
                        if ($message == '') $message .= messageAdmin('Данные успешно сохранены');
                    } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
                } else {
                    if (isset($_POST['save_id'], $_POST['value'], $_POST['comment'])) {

                        for ($i = 0; $i <= count($_POST['save_id']) - 1; $i++) {
                            if ($this->model->check_for_update($_POST['save_id'][$i], $this->tb, $_SESSION['admin']['type'])) {
                                $this->db->query("UPDATE `" . $this->tb_lang . "` SET `value`=? WHERE " . $this->tb . "_id=?", [$_POST['value'][$i], $_POST['save_id'][$i]]);
                                $this->db->query("UPDATE `" . $this->tb . "` SET `comment`=? WHERE id=?", [$_POST['comment'][$i], $_POST['save_id'][$i]]);
                            } else $message = messageAdmin('Ошибка в правах доступа!', 'error');
                        }
                        if ($message == '') $message .= messageAdmin('Данные успешно сохранены');
                    } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
                }
            }
        }
        return $message;
    }

    public function delete()
    {
        $message = '';
        if (isset($this->registry['access'])) $message = $this->registry['access'];
        elseif ($_SESSION['admin']['type'] == 1) {
            if (isset($this->params['delsubsystem']) || isset($this->params['delete'])) {
                if (isset($this->params['delsubsystem']))
                    $id = $this->params['delsubsystem'];
                elseif (isset($this->params['delete']))
                    $id = $this->params['delete'];
                $this->db->query("DELETE FROM `" . $this->tb . "` WHERE `id`=?", [$id]);
                if ($message == '') $message = messageAdmin('Запись успешно удалена');
            } elseif (isset($this->params['delete']) && $this->params['delete'] != '') $message = $this->model->check_for_delete($this->params['delete'], $this->tb, $_SESSION['admin']['type']);
            elseif (isset($this->params['delsubsystem']) && $this->params['delsubsystem'] != '') $message = $this->model->check_for_delete($this->params['delsubsystem'], $this->tb, $_SESSION['admin']['type']);
        }
        return $message;
    }

	/**
	 * @return StringClass
	 */
	public function searchAction()
	{
		$this->registry->set(PathToTemplateAdmin, $this->tb);
		$status = null;
		$content = null;
		$template = 'view.phtml';
		if ($_SESSION['admin']['type'] == 1) {
			$template = 'admin/view.phtml';
		}
		$where = 'tb.comment LIKE "%' . $_POST['message'] . '%" 
				OR tb_lang.value LIKE "%' . $_POST['message'] . '%" 
				OR tb.value_et LIKE "%' . $_POST['message'] . '%" 
				OR tb.key LIKE "%' . $_POST['message'] . '%"';
		if (empty($_POST['message'])) $where = '1';
		$list = $this->model->find([
				'table'=>$this->tb,
				'select' => '*',
				'order' => 'tb.`id` DESC',
				'type' => 'rows',
				'where' => $where,
				'action' => 'meta',
				'paging' => 100
			]
		);
		if (!$list) {
			$status = false;
			$content = "Ничего не найдено";
		} else {
			$status = true;
			$content = $this->view->Render($template, array('list' => $list['list'], 'action' => 'meta', 'paging' => $list['paging']));
		}
		return json_encode(array("status" => $status, "content" => $content, "action" => "meta"));
	}


    function listView($where = '')
    {
        $vars['path'] = '';
        if (isset($this->params['modules'])) {
            $vars['path'] = '/modules/' . $this->params['modules'];
        }
        $join = '';
        $where = str_replace('WHERE', 'AND', $where);
        if ($_SESSION['admin']['type'] != 1) {
            $join = "LEFT JOIN `moderators_permission` mp ON mp.module_id=tb.modules_id";
            $where .= " AND ((mp.moderators_type_id='{$_SESSION['admin']['type']}' AND subsystem_id='" . $this->subsystem_id . "' AND permission!='000') OR tb.modules_id='0')";
        }
        $vars['list'] = $this->db->rows("SELECT tb.*, tb2.* FROM " . $this->tb . " tb LEFT JOIN " . $this->tb_lang . " tb2 ON tb.id=tb2." . $this->tb . "_id $join WHERE tb.id!=0 $where ORDER BY tb.modules_id ASC, tb.`id` DESC");
        $vars['modules'] = $this->db->rows("SELECT id, name FROM modules ORDER BY sub ASC, name ASC");
        return $vars;
    }


    public function subcontent($vars = array())
    {
        $vars['modules'] = $this->db->rows("SELECT id, name FROM modules ORDER BY sub ASC, name ASC");
        $arr = $this->listView($vars['where']);
        $vars['list'] = $arr['list'];
        $template = $this->tb . '.phtml';
        if ($_SESSION['admin']['type'] == 1) $template = 'admin/'.$this->tb . '.phtml';
        return $this->view->Render($template, $vars);
    }
}