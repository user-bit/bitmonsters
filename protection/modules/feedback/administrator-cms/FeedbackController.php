<?php class FeedbackController extends BaseController
{
	function  __construct($registry,$params)
	{
		parent::__construct($registry,$params);
        $this->name = Feedback::$name;
		$this->tb = Feedback::$table;
        $this->feedback = new Feedback($this->sets);
	}

	public function indexAction()
	{
		$vars['message']='';
		$vars['name']=$this->name;
		if (isset($this->params['subsystem']))return $this->Index($this->feedback->subsystemAction());
		if (isset($this->registry['access']))$vars['message']=$this->registry['access'];
		if (isset($this->params['delete'])||isset($_POST['delete']))$vars['message']=$this->feedback->delete($this->tb);
        elseif (isset($_POST['update_close'])) $vars['message']=$this->feedback->save();
		$vars['list']=$this->view->Render('view.phtml',$this->feedback->find(['paging'=>true,'order'=>'tb.id DESC']));
        $data['right_menu']=$this->model->right_menu_admin(['action' => $this->tb, 'name' => $this->name]);
        $data['content']=$this->view->Render('list.phtml',$vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
		return $this->Index($data);
	}

	public function editAction()
	{
        $vars['message'] = isset($_POST['update']) ? $this->feedback->save() : '';
		$vars['edit']=$this->feedback->find(array(
		        'select' => 'tb.*',
                'where' => 'tb.id='.(int)$this->params['edit']
        ));
        $vars['status'] = array(
            0 => 'новая',
            1 => 'обработанная'
        );
        $data['content']=$this->view->Render('edit.phtml',$vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
		return $this->Index($data);
	}
}