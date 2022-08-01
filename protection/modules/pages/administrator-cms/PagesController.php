<?php class PagesController extends BaseController
{
    protected $pages;

    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->tb = Pages::$table;
        $this->name = "Инфо страницы";
        $this->pages = new Pages($this->sets);
        $this->template = new Template($this->sets);
    }

    public function indexAction()
    {
        $vars['message'] = '';
        $vars['name'] = $this->name;
        if (isset($this->params['subsystem'])) return $this->Index($this->pages->subsystemAction());
        if (isset($this->registry['access'])) $vars['message'] = $this->pages->registry['access'];
        if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message'] = $this->pages->delete($this->tb);
        elseif (isset($_POST['update'])) $vars['message'] = $this->pages->save();
        elseif (isset($_POST['update_close'])) $vars['message'] = $this->pages->save();
        elseif (isset($_POST['add_close'])) $vars['message'] = $this->pages->add();
        $vars['list'] = $this->view->Render('view.phtml',
            array('list' => $this->pages->find(array('type' => 'rows', 'order' => 'tb.sort ASC,tb.id DESC'))));

        $data['right_menu'] = $this->model->right_menu_admin(array('action' => $this->tb, 'name' => $this->name));
        $data['content'] = $this->view->Render('list.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position'] = $this->tb;
        return $this->Index($data);
    }

    public function addAction()
    {
        $vars['message'] = '';
        if (isset($_POST['add'])) $vars['message'] = $this->pages->add();
        //шаблоны для страниц
        $vars['template'] = array(
            0 => 'default',
            1 => 'main',
            2 => 'link'
        );
        $data['content'] = $this->view->Render('add.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position'] = $this->tb;
        return $this->Index($data);
    }

    public function editAction()
    {
        $vars['message'] = '';
        if (isset($_POST['update'])) $vars['message'] = $this->pages->save();
        $vars['edit'] = $this->pages->find((int)$this->params['edit']);
        $vars['templates'] = $this->db->rows("SELECT * FROM template");
        $vars['info_block_templates'] = $this->db->rows("SELECT * FROM infoblocks WHERE infoblocks.type=1");
        //шаблоны для страниц
        $vars['template'] = array(
            0 => 'default',
            1 => 'main',
            2 => 'link'
        );

        /**
         * Получаем список шаблонов
         * Вытягиваем ресурсы каждого шаблона (input, description, photo)
         * Создаем массив, с ключами каждого инпута
         */
        $vars['constructor'] = $this->template->getConstructorContentAdmin($vars['edit']['id']);
        // Load meta
        $row = $this->meta->load_meta($this->tb, $vars['edit']['link']);
        if ($row) {
            $vars['edit']['title'] = $row['title'];
            $vars['edit']['description'] = $row['description'];
        }
        $data['content'] = $this->view->Render('edit.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position'] = $this->tb;
        return $this->Index($data);
    }
}