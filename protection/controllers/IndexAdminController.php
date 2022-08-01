<?php class IndexAdminController extends BaseController
{
    protected $params;
    protected $db;

    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->registry = $registry;
        $this->feedback = new Feedback($this->sets);
    }

    function indexAction()
    {
        $vars['admin'] = PathToTemplateAdmin;

        $vars['feedback'] = $this->feedback->find(array('type' => 'rows'));
        $vars['count_new'] = $vars['count_processed'] = 0;
        foreach ($vars['feedback'] as $status_feedback) {
            if ($status_feedback['status'] == 'новая')
                $vars['count_new']++;
            if ($status_feedback['status'] == 'обработанная')
                $vars['count_processed']++;
        }
        $vars['feedback_status'] = "'новая - " . $vars['count_new'] . "', 'обработанная - " . $vars['count_processed'] . "'";

        $data['content'] = $this->view->Render('layout/main.phtml', $vars);
        return $this->Index($data);
    }


}