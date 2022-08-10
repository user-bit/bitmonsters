<?php class TeamController extends BaseController
{

    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->name = Team::$name;
        $this->tb = Team::$table;
        $this->team = new Team($this->sets);
    }

    public function indexAction()
    {
        if (isset($this->params['subsystem'])) return $this->Index($this->team->subsystemAction());
        $vars['message']='';
        if (isset($this->params['act'])) {
            $act = $this->params['act'] . 'Action';
            return $this->Index($this->$act());
        }
        $vars['name']=$this->name;
        if (isset($this->registry['access'])) $vars['message']=$this->registry['access'];
        if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message']=$this->team->delete($this->tb);
        elseif (isset($_POST['update'])) $vars['message']=$this->team->save();
        elseif (isset($_POST['update_close'])) $vars['message']=$this->team->save();
        elseif (isset($_POST['add_close'])) $vars['message']=$this->team->add();

        $vars['list']=$this->view->Render('view.phtml', $this->team->find(['paging' => $this->settings['paging_team_admin'],  'order' => 'tb.sort ASC']));
        $data['right_menu'] = $this->model->right_menu_admin(['action' => $this->tb, 'name' => $this->name]);
        $data['content']=$this->view->Render('list.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
        return $this->Index($data);
    }

    public function addAction()
    {
        $vars['message']='';
        if (isset($_POST['add'])) $vars['message']=$this->team->add();
        $data['content']=$this->view->Render('add.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
        return $this->Index($data);
    }

    public function editAction()
    {
        $vars['message']='';
        if (isset($_POST['update'])) $vars['message']=$this->team->save();
        $vars['edit']=$this->team->find((int)$this->params['edit']);
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