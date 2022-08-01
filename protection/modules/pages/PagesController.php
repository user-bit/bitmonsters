<?php class PagesController extends BaseController
{
    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->template = new Template($this->sets);
        $this->pages = new Pages($this->sets);
    }

    public function indexAction()
    {
        $vars['page'] = $this->model->getPage($this->params['pages']);
        if (!isset($vars['page']['link'])) return Router::act('error', $this->registry);
        $vars['main_translate'] = $this->model->get_lang("tb.modules_id=0");
        if (!empty($vars['page']['id']))
            $vars['constructor'] = $this->template->getConstructorContentFront($vars['page']['id']);

        // шаблон по-умолчанию
        $template = 'pages/default.phtml';
        $data['content'] = $this->view->Render($template, $vars);
        return $this->Index($data);
    }

    function getThemesAction()
    {
        if (!empty($_POST['click']))
            $_SESSION['click'] = $_POST['click'];
        //проверяем - если нажали на изменение темы, то не смотреть время
        if (empty($_SESSION['click'])) {
            //проверяем время и смотрим какую тему ставить
            if ($_POST['time_client'] > 20 || $_POST['time_client'] < 8) {
                $_SESSION['themes_site'] = 'black';
            }
        } else {
            if (!empty($_POST['themes']))
                $_SESSION['themes_site'] = $_POST['themes'];
        }
        return json_encode($_SESSION);
    }
}


