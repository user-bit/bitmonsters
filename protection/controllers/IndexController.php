<?php class IndexController extends BaseController
{
    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->template = new Template($this->sets);
    }

    function indexAction()
    {
        $vars['body'] = $this->model->getPage('/');
        $vars['translate_catalog'] = $this->model->get_lang('tb.modules_id=13');
        $vars['constructor'] = $this->template->getConstructorContentFront(1);
        $data['content'] = $this->view->Render('pages/main.phtml', $vars);
        return $this->Index($data);
    }

}

