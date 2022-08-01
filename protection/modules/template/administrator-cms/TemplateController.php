<?php class TemplateController extends BaseController
{
    protected $info;

    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->tb = Template::$table;
        $this->name = Template::$name;
        $this->template = new Template($this->sets);
    }

    public function indexAction()
    {
        $vars['message'] = isset($this->registry['access']) ? $this->registry['access'] : '';
        $vars['name'] = $this->name;
        if (isset($this->params['subsystem'])) return $this->Index($this->template->subsystemAction());
        if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message'] = $this->template->delete($this->tb);
        elseif (isset($_POST['update'])) $vars['message'] = $this->template->save();
        elseif (isset($_POST['update_close'])) $vars['message'] = $this->template->save();
        elseif (isset($_POST['add_close'])) $vars['message'] = $this->template->add();

        $vars['list'] = $this->view->Render('view.phtml', array('list' => $this->template->find(array('paging' => 20))));
        $data['content'] = $this->view->Render('list.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position'] = $this->tb;
        return $this->Index($data);
    }

    public function addAction()
    {
        $vars['message'] = isset($_POST['add']) ? $this->template->add() : '';
        $data['content'] = $this->view->Render('add.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position'] = $this->tb;
        return $this->Index($data);
    }

    public function editAction()
    {
        $vars['message'] = isset($_POST['update']) ? $this->template->save() : '';
        $vars['edit'] = $this->template->find((int)$this->params['edit']);

        $vars['path'] = Dir::createDir($vars['edit']['id'], '', $this->tb);
        $vars['path'] = $vars['path'][0];
        $data['content'] = $this->view->Render('edit.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position'] = $this->tb;
        return $this->Index($data);
    }

    public function saveTemplateAction()
    {
        //запись в базу инфоблока или конструктора
        if (!empty($_POST['info']['infoblock_id'])) {
            $this->db->insert_id("INSERT INTO `constructor` SET `infoblock_id`=?,`pages_id`=?", [$_POST['info']['infoblock_id'], $_POST['info']['page_id']]);
            $vars['template'] = $this->db->row("SELECT tb.id, tb.path FROM infoblocks tb WHERE tb.id=" . $_POST['info']['infoblock_id']);
            Registry::set(PathToTemplateAdmin, 'infoblocks');
        }else {
            $insert_id = $this->db->insert_id("INSERT INTO `constructor` SET `template_id`=?,`pages_id`=?", [$_POST['info']['template_id'], $_POST['info']['page_id']]);
            foreach ($_POST['constructor'] as $key => $constructor_info) {
                foreach ($this->language as $lang) {
                    if (strpos($key, 'text') !== false) {
                        $this->db->query("INSERT INTO " . $lang['language'] . "_constructor_resource_text SET key_value=?, constructor_resource_id=?", [$key, $insert_id]);
                    } else {
                        $this->db->query("INSERT INTO " . $lang['language'] . "_constructor_resource SET key_value=?, constructor_resource_id=?", [$key, $insert_id]);
                    }
                }
            }
            $vars['template'] = $this->db->row("SELECT tb.id, tb.path FROM template tb WHERE tb.id=" . $_POST['info']['template_id']);
            Registry::set(PathToTemplateAdmin, 'template');
        }

        $data['content'] = $this->view->Render($vars['template']['path'] . '/config.phtml', $vars);
        $data['ressult'] = 'Данные успешно сохраненны';
        return json_encode($data);
    }

    public function removeTemplateAction()
    {
        $template_id = $_POST['id'];
        $page_id = $_POST['page'];

        $this->db->query("DELETE FROM `constructor` WHERE `id`=? AND `pages_id`=?", [$template_id, $page_id]);

        $data['result'] = 'Данные успешно удалены';
        return json_encode($data);
    }

    public function addFieldAction()
    {
        foreach ($this->language as $lang) {
            $this->db->query("INSERT INTO " . $lang['language'] . "_" . $_POST['table'] . "_text SET key_value=?, " . $_POST['table'] . "_id=?",
                [$_POST['key_field'], $_POST['id']]);
        }

        $data['result'] = 'Данные успешно удалены';
        return json_encode($data);
    }
}