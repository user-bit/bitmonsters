<?php class InfoblocksController extends BaseController
{
    protected $info;

    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->tb = Infoblocks::$table;
        $this->name = Infoblocks::$name;
        $this->info = new Infoblocks($this->sets);
    }

    public function indexAction()
    {
        $vars['message'] = isset($this->registry['access']) ? $this->registry['access'] : '';
        $vars['name'] = $this->name;
        if (isset($this->params['subsystem'])) return $this->Index($this->info->subsystemAction());
        if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message'] = $this->info->delete($this->tb);
        elseif (isset($_POST['update'])) $vars['message'] = $this->info->save();
        elseif (isset($_POST['update_close'])) $vars['message'] = $this->info->save();
        elseif (isset($_POST['add_close'])) $vars['message'] = $this->info->add();
        $vars['list_info'] = $this->info->find(array('type'=>'rows', 'group' => 'tb.id', 'where' => 'tb.type=0 OR tb.type IS NULL'));
        $vars['list_info_template'] = $this->info->find(array('type'=>'rows', 'group' => 'tb.id', 'where' => 'tb.type=1'));
        $vars['list']=$this->view->Render('view.phtml', $vars);
        $data['right_menu'] = $this->model->right_menu_admin(['action' => 'infoblocks', 'name' => $this->name]);
        $data['content'] = $this->view->Render('list.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position'] = $this->tb;
        return $this->Index($data);
    }

    public function addAction()
    {
        $vars['message'] = isset($_POST['add']) ? $this->info->add() : '';
        $vars['type'] = array(
            array('id' => 0, 'name' => 'Инфо-блок'),
            array('id' => 1, 'name' => 'Инфо-блок для конструктора')
        );
        $vars['template'] = array(
            array('id' => 0, 'name' => 'product-unique-selling-proposition'),
            array('id' => 1, 'name' => 'delivery-pay'),
            array('id' => 2, 'name' => 'return'),
            array('id' => 3, 'name' => 'banner-news'),
            array('id' => 4, 'name' => 'why-me'),
            array('id' => 5, 'name' => 'list-brand'),
            array('id' => 6, 'name' => 'feedback')
        );
        $data['content'] = $this->view->Render('add.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position'] = $this->tb;
        return $this->Index($data);
    }

    public function editAction()
    {
        $vars['message'] = isset($_POST['update']) ? $this->info->save() : '';
        $vars['edit'] = $this->db->row("SELECT * FROM infoblocks WHERE infoblocks.id=".(int)$this->params['edit']);

        $content_edit = $this->db->rows("SELECT * FROM " . $this->registry['key_lang_admin'] . "_infoblocks tb WHERE tb.infoblocks_id=" . $vars['edit']['id']);
        foreach ($content_edit as $info) {
            $vars['content_edit'][$info['key_value']] = array(
                'name_admin' => $info['name_admin'],
                'value' => $info['value'],
                'id' => $info['id'],
                'photo_alt' => $info['photo_alt'],
                'photo_title' => $info['photo_title'],
            );
        }
        $content_edit_text = $this->db->rows("SELECT * FROM " . $this->registry['key_lang_admin'] . "_infoblocks_text tb WHERE tb.infoblocks_id=" . $vars['edit']['id']);
        foreach ($content_edit_text as $info) {
            $vars['content_edit_text'][$info['key_value']] = array(
                'name_admin' => $info['name_admin'],
                'value' => $info['value'],
            );
        }

        $vars['type'] = array(
            array('id' => 0, 'name' => 'Инфо-блок'),
            array('id' => 1, 'name' => 'Инфо-блок для конструктора')
        );
        $data['content'] = $this->view->Render('edit.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position'] = $this->tb;
        return $this->Index($data);
    }

    public function getTemplateAction() {
        Registry::set(PathToTemplateAdmin, 'infoblocks');
        $data['content'] = $this->view->Render($_POST['path'].'/options_add.phtml');
        return json_encode($data['content']);
    }
}