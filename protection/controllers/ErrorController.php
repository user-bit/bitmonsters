<?php class errorController extends BaseController
{
	function  __construct($registry,$params)
	{
		$this->registry=$registry;
		parent::__construct($registry,$params);
	}

	function indexAction()
	{
        header("HTTP/1.1 404 Not Found");
		$data['meta']=['title'=>'Page not found','keywords'=>'Page not found','description'=>'Page not found'];
        $data['color'] = 'white';
        $vars['main_translate'] = $this->model->get_lang("tb.modules_id=0");
		$data['content']=$this->view->Render('pages/404.phtml', $vars);
		return $this->Index($data);
	}
}