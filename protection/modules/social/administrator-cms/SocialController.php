<?php class SocialController extends BaseController
{

	function  __construct($registry,$params)
	{
        parent::__construct($registry, $params);
        $this->name = Social::$name;
        $this->tb = Social::$table;
        $this->social = new Social($this->sets);
	}

	public function indexAction()
	{
		$vars['name']=$this->name;
        if (isset($this->params['act'])) {
            $act = $this->params['act'] . 'Action';
            return $this->Index($this->$act());
        }
		if (isset($this->params['subsystem'])) return $this->Index($this->social->subsystemAction());
		$vars['message']='';
		if (isset($this->registry['access'])) $vars['message']=$this->registry['access'];
		if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message']=$this->social->delete('social');
		elseif (isset($_POST['update'])) $vars['message']=$this->social->save();
		elseif (isset($_POST['update_close'])) $vars['message']=$this->social->save();
		elseif (isset($_POST['add_close'])) $vars['message']=$this->social->add();

		$vars['link']='/'.PathToTemplateAdmin.'/' . $this->tb;

        $vars['list'] = $this->view->Render('view.phtml',
            array('list' => $this->social->find(array('type' => 'rows'))));

        $data['right_menu'] = $this->model->right_menu_admin(array('action' => $this->tb, 'name' => $this->name));
		$data['content']=$this->view->Render('list.phtml',$vars);
		$data['scripts']=array('libs/jquery.treeview.js');
		//передаем название модуля (для открывание подраздела главного меню)
		$data['position']=$this->tb;
		return $this->Index($data);
	}

	public function addAction()
	{
		$vars['message']='';
		if (isset($_POST['add'])) $vars['message']=$this->social->add();

        $data['content']=$this->view->Render('add.phtml',$vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
		return $this->Index($data);
	}

	public function editAction()
	{
        $vars['message'] = isset($_POST['update']) ? $this->social->save() : '';
		$vars['edit']=$this->social->find((int)$this->params['edit']);

    	$data['content']=$this->view->Render('edit.phtml',$vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
		return $this->Index($data);
	}
}
