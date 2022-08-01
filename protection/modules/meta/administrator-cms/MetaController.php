<?php class MetaController extends BaseController
{
    protected $params;
    protected $db;

    private $left_menu = array(
        array('title' => 'Редирект', 'link' => '/' . PathToTemplateAdmin . '/meta/act/redirects', 'name' => 'redirects'),
        array('title' => 'Sitemap', 'link' => '/' . PathToTemplateAdmin . '/meta/act/sitemap', 'name' => 'Карта сайта')
    );

    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->tb = Meta::$table;
        $this->name = Meta::$name;
    }

    public function indexAction()
    {
        $vars['message'] = '';
        $vars['name'] = $this->name;
        if (isset($this->params['act'])) {
            $act = $this->params['act'] . 'Action';
            return $this->Index($this->$act());
        }
        if (isset($this->params['subsystem'])) return $this->Index($this->meta->subsystemAction($this->left_menu));
        if (isset($this->registry['access'])) $vars['message'] = $this->registry['access'];
        if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message'] = $this->meta->delete($this->tb);
        elseif (isset($_POST['update'])) $vars['message'] = $this->meta->save();
        elseif (isset($_POST['update_close'])) $vars['message'] = $this->meta->save();
        elseif (isset($_POST['add_close'])) $vars['message'] = $this->meta->add();
        $vars['list'] = $this->view->Render('view.phtml', $this->meta->find(array('select' => 'tb.*', 'order' => 'tb.`id` DESC', 'type' => 'rows', 'paging' => true)));
        $data['right_menu'] = $this->model->right_menu_admin(array('action' => $this->tb, 'name' => $this->name, 'menu2' => $this->left_menu));
        $data['content'] = $this->view->Render('list.phtml', $vars);
        return $this->Index($data);
    }

    public function addAction()
    {
        $vars['message'] = '';
        if (isset($_POST['add'])) $vars['message'] = $this->meta->add();
        $data['content'] = $this->view->Render('add.phtml', $vars);
        return $this->Index($data);
    }

    public function editAction()
    {
        $vars['message'] = '';
        if (isset($_POST['update'])) $vars['message'] = $this->meta->save();
        $vars['edit'] = $this->meta->find((int)$this->params['edit']);
        if (isset($this->params['duplicate'])) $vars['message'] = $this->meta->duplicate($vars['edit'], $this->tb);
        $vars['faq_list'] = $this->db->rows("SELECT * FROM meta_faq tb LEFT JOIN ".$this->key_lang_admin."_meta_faq tb_lang ON tb.id = tb_lang.meta_id WHERE tb.meta_id=".$vars['edit']['id']);
        $data['content'] = $this->view->Render('edit.phtml', $vars);
        return $this->Index($data);
    }

    function addFaqAction()
    {
        $id = $_POST['id'];
        $insert_id=$this->db->insert_id("INSERT INTO meta_faq SET meta_id=?", array($id));
        $res=$this->db->rows("SELECT * FROM language");
        foreach ($res as $lang)
            $this->db->query("INSERT INTO `".$lang['language']."_meta_faq` SET `quest`=?,`ans`=?, `meta_id`=?",[$_POST['quest'],$_POST['ans'],$insert_id]);
        $vars['meta_id'] = $insert_id;
        Registry::set(PathToTemplateAdmin, 'meta');
        $data['content'] = $this->view->Render('faq/faq-section.phtml', $vars);
        return json_encode($data);
    }

    function delFaqAction()
    {
        $id = $_POST['id'];
        $this->db->query("DELETE FROM meta_faq WHERE id=?", array($id));
        foreach ($this->language as $lang) {
            $this->db->query("DELETE FROM " . $lang['language'] . "_meta_faq WHERE meta_id=?", array($id));
        }
        return json_encode($id);
    }

    public function redirectsAction()
    {
        $vars['message'] = '';
        if (isset($_POST['update'])) $vars['message'] = $this->meta->save_redirects();
        elseif (isset($this->params['addredirect'])) $vars['message'] = $this->meta->addredirect();
        elseif (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message'] = $this->meta->delete('redirects');
        $vars['name'] = 'Перенаправления';
        $vars['action'] = $this->tb;
        $vars['path'] = '/act/redirects';
        $vars['list'] = $this->db->rows("SELECT * FROM `redirects` ORDER BY id DESC");
        $data['right_menu'] = $this->model->right_menu_admin(array('action' => $this->tb, 'name' => $this->name, 'menu2' => $this->left_menu));
        $data['content'] = $this->view->Render('content/redirects.phtml', $vars);
        return $data;
    }

    //generate sitemap
    public function sitemapAction()
    {
        $vars['message'] = '';
        $vars['name'] = 'Карта сайта';
        if (isset($_POST['save'])) $vars['message'] = $this->meta->save_sitemap();
        if (isset($_POST['update'])) $vars['message'] = $this->meta->generate_static_sitemap();
        $vars['action'] = $this->tb;
        $vars['list'] = $this->db->rows("SELECT * FROM sitemap");
        $vars['priority'] = array('',0.1,0.2,0.3,0.4,0.5,0.6,0.7,0.8,0.9,1);
        $vars['path'] = '/act/sitemap';
        $data['content'] = $this->view->Render('sitemap/sitemap.phtml', $vars);
        return $data;
    }
    public function addSectionSitemapAction()
    {
        $vars['id'] = $this->db->insert_id("INSERT INTO sitemap SET `module`=?",['']);
        Registry::set(PathToTemplateAdmin, 'meta');
        $vars['priority'] = array('',0.1,0.2,0.3,0.4,0.5,0.6,0.7,0.8,0.9,1);
        $data['content'] = $this->view->Render('sitemap/inc/sitemap-content.phtml', $vars);
        return json_encode($data);
    }
    //generate sitemap - END
}
