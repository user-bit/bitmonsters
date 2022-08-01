<?php class EventController extends BaseController
{

    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->name = Event::$name;
        $this->tb = Event::$table;
        $this->event = new Event($this->sets);
    }

    public function indexAction()
    {
        if (isset($this->params['subsystem'])) return $this->Index($this->event->subsystemAction());
        $vars['message']='';
        if (isset($this->params['act'])) {
            $act = $this->params['act'] . 'Action';
            return $this->Index($this->$act());
        }
        $vars['name']=$this->name;
        if (isset($this->registry['access'])) $vars['message']=$this->registry['access'];
        if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message']=$this->event->delete($this->tb);
        elseif (isset($_POST['update'])) $vars['message']=$this->event->save();
        elseif (isset($_POST['update_close'])) $vars['message']=$this->event->save();
        elseif (isset($_POST['add_close'])) $vars['message']=$this->event->add();

        $vars['list']=$this->view->Render('view.phtml', $this->event->find(['paging' => $this->settings['paging_event_admin'], 'order' => 'tb.sort ASC']));
        $data['right_menu'] = $this->model->right_menu_admin(['action' => $this->tb, 'name' => $this->name]);
        $data['content']=$this->view->Render('list.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
        return $this->Index($data);
    }

    public function addAction()
    {
        $vars['message']='';
        if (isset($_POST['add'])) $vars['message']=$this->event->add();
        $data['content']=$this->view->Render('add.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
        return $this->Index($data);
    }

    public function editAction()
    {
        $vars['message']='';
        if (isset($_POST['update'])) $vars['message']=$this->event->save();
        $vars['edit']=$this->event->find((int)$this->params['edit']);
        $row=$this->meta->load_meta($this->tb, $vars['edit']['link']);
        if ($row) {
            $vars['edit']['title']=$row['title'];
            $vars['edit']['description']=$row['description'];
        }
        $vars['action']=$this->tb;
        $vars['path']="files/".$this->tb."/".substr($vars['edit']['id'],-1)."/".$vars['edit']['id']."/";
        $data['content']=$this->view->Render('edit.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
        return $this->Index($data);
    }
}