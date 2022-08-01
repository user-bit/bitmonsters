<?php class Paging extends BaseController
{
    protected $params;
    protected $db;

    function __construct($registry, $params)
    {
        $this->registry = $registry;
        parent::__construct($registry, $params);
    }

    public function MakePaging($page, $itemCount, $perSlade, $dir = "")
    {
        $return = [];
        $vars['link_padding'] = 2;
        $vars['page_var'] = "page";
        $vars['count'] = ceil($itemCount / $perSlade);
        $vars['page'] = $page;
        if ($page == 0) $vars['page'] = 1;
        $vars['page_size'] = $perSlade;
        $return['prev'] = $this->getPrevPage($vars);
        $return['next'] = $this->getNextPage($vars);
        $return['paging'] = $this->view->Render('layout/paging.phtml', $vars);
        return $return;
    }

    public function MakePagingAjax($page, $itemCount, $perSlade, $dir = "")
    {
        $return = [];
        $vars['link_padding'] = 2;
        $vars['page_var'] = "page";
        $vars['count'] = ceil($itemCount / $perSlade);
        $vars['page'] = $page;
        if ($page == 0) $vars['page'] = 1;
        $vars['page_size'] = $perSlade;
        $return['paging'] = $this->view->Render('layout/paging_ajax.phtml', $vars);
        return $return;
    }

    /**
     * @param array $vars
     * @return bool
     * Метод используется для получения url предыдущей страницы и предназначен для добавления в <head> тега
     * <link rel="prev" href="...">
     */
    public function getPrevPage($vars = [])
    {
        $return = false;
        if ($vars['page'] > 1) $return = StringClass::getPageUrl($vars['page'] - 1, $vars['page_var']);
        return $return;
    }

    /**
     * @param array $vars
     * @return bool
     * Метод используется для получения url следующей страницы и предназначен для добавления в <head> тега
     * <link rel="next" href="...">
     */
    public function getNextPage($vars = [])
    {
        $return = false;
        if ($vars['page'] < $vars['count']) $return = StringClass::getPageUrl($vars['page'] + 1, $vars['page_var']);
        return $return;
    }

}