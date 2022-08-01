<?php class FaqController extends BaseController
{
    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->name = Faq::$name;
        $this->tb = Faq::$table;
        $this->faq = new Faq($this->sets);
    }

    public function indexAction()
    {
        $vars['name']=$this->name;
        if (isset($this->params['subsystem'])) return $this->Index($this->faq->subsystemAction());
        $vars['message']='';
        if (isset($this->registry['access'])) $vars['message']=$this->registry['access'];
        if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message']=$this->faq->delete($this->tb);
        elseif (isset($_POST['update'])) $vars['message']=$this->faq->save();
        elseif (isset($_POST['update_close'])) $vars['message']=$this->faq->save();
        elseif (isset($_POST['add_close'])) $vars['message']=$this->faq->add();
        $vars['list']=$this->view->Render('view.phtml', $this->faq->find(['type'=>'rows', 'paging' => $this->settings['paging_faq_admin'], 'order' => 'tb.sort ASC']));
        $data['right_menu']=$this->model->right_menu_admin(['action' => $this->tb, 'name' => $this->name]);
        $data['content']=$this->view->Render('list.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
        return $this->Index($data);
    }

    public function addAction()
    {
        $vars['message']='';
        if (isset($_POST['add'])) $vars['message']=$this->faq->add();
        $data['content']=$this->view->Render('add.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
        return $this->Index($data);
    }

    public function editAction()
    {
        $vars['message']='';
        if (isset($_POST['update'])) $vars['message']=$this->faq->save();
        $vars['edit']=$this->faq->find((int)$this->params['edit']);
        $row=$this->meta->load_meta($this->tb, $vars['edit']['link']);
        if ($row) {
            $vars['edit']['title']=$row['title'];
            $vars['edit']['keywords']=$row['keywords'];
            $vars['edit']['description']=$row['description'];
        }
        $data['content']=$this->view->Render('edit.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
        return $this->Index($data);
    }
}