<?php class BaseController
{
    protected $registry;
    protected $params;
    protected $key_lang = LANG;

    function __construct($registry, $params)
    {
        header('Cache-control: no-cache');
        $this->params = $params;
        $this->registry = $registry;
        $this->db = new PDOchild($registry);
        $this->key_lang = $this->registry['key_lang'];
        $this->key_lang_admin = $this->registry['key_lang_admin'];
        $this->language = $this->db->rows("SELECT * FROM language ORDER BY `id` ASC");
        $const = $this->db->rows_key("SELECT name,value FROM config");
        Registry::set('user_settings', $const);
        $this->settings = Registry::get('user_settings');
        $this->view = new View($this->registry);
        $this->sets = ['settings' => $this->settings, 'registry' => $registry, 'params' => $params, 'db' => $this->db];
        $this->model = new Model($this->sets);
        $this->menu = new Menu($this->sets);
        $this->social = new Social($this->sets);
        $this->meta = new Meta($this->sets);
        if (isset($this->params['topic']) && $this->params['topic'] != PathToTemplateAdmin) $this->meta->check_redirects();
    }

    public function Index($param = [])
    {
        $link = PROTOCOL . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '/';
        $has_en = strpos($link, 'en/');
        if (!empty($has_en)) {
            $link = str_replace('en/', '', $link);
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . $link);
        }
        if (!isset($this->params['topic'])) $param['topic'] = '';
        else $param['topic'] = $this->params['topic'];
        $data = $param;
        $data['lang'] = $this->key_lang;
        $data['languages'] = $this->language;
        if (!isset($param['styles'])) $param['styles'] = [];
        if (!isset($param['scripts'])) $param['scripts'] = [];
        //Back side
        if ($param['topic'] == PathToTemplateAdmin) {

            $data['menu_inc'] = '';
            $data['admin'] = PathToTemplateAdmin;
            $data['meta']['title'] = $this->params['module'] . ' - ';
            if (isset($_SESSION['admin'])) {
                if (!isset($data['breadcrumb'])) $data['breadcrumb'] = $this->model->breadcrumbAdmin();
                $data['current_lang'] = $this->db->row("SELECT * FROM `language` tb WHERE tb.language='" . $_SESSION['key_lang_admin'] . "'");
                if ($_SESSION['admin']['type'] == 1) {
                    $data['top_menu'] = $this->db->rows("SELECT tb.*  FROM `menu_admin` tb");
                    $data['menu'] = containArrayInHisId($this->db->rows("SELECT tb.name, tb.controller, tb.sub, tb.id FROM `modules` tb WHERE tb.hidden='0' AND tb.show_main='1' ORDER BY tb.`sort` ASC"));
                    foreach ($data['menu'] as $id => &$node) {
                        $data['menu'][$node['sub']]['childs'][$id] = &$node;
                    }
                    $data['menu-right'] = $this->db->rows("SELECT tb.name, tb.controller, tb.sub, tb.id FROM `modules` tb WHERE tb.hidden='0' AND tb.sub=6 || tb.sub=5");
                } else {
                    $data['top_menu'] = $this->db->rows("SELECT tb.*  FROM `menu_admin` tb");
                    $data['menu'] = $this->db->rows("SELECT tb.*,subsystem_id FROM `modules` tb RIGHT JOIN moderators_permission tb2 ON tb.id=tb2.module_id AND tb2.moderators_type_id=? AND tb2.permission!=? WHERE tb.hidden='0' GROUP BY tb.id ORDER BY tb.`sort` ASC",
                        [$_SESSION['admin']['type'], '000']);
                    foreach ($data['menu'] as $id => &$node) {
                        $data['menu'][$node['sub']]['childs'][$id] = &$node;
                    }
                }
                $data['key'] = $this->key_lang_admin;
                $data['moderators'] = $this->db->row("SELECT tb.login, tb.id, m_t.comment FROM moderators tb LEFT JOIN moderators_type m_t ON m_t.id=tb.type_moderator WHERE tb.id=" . $_SESSION['admin']['id']);
                $data['menu_inc'] = $this->view->Render('layout/menu_inc.phtml', $data);
            } else {
                $data['login'] = 1;
            }

            $styles = array_merge([
                'libs.css',
                'bundle.css'
            ], $param['styles']);
            $scripts = array_merge([
                'libs.min.js',
                'bundle.js'
            ], $param['scripts']);

            $data['styles'] = $this->view->Load($styles, 'styles', 'administrator-cms');
            $data['scripts'] = $this->view->Load($scripts, 'scripts', 'administrator-cms');

            return ($this->view->Render('index.phtml', $data));
        } else {
            if (!isset($data['meta'])) $data['meta'] = [];
            $data['meta'] = $this->meta->set_meta_data($data['meta'], $param['topic']);
            if ($_SERVER['QUERY_STRING']) {
                $data['meta']['link'] = $data['meta']['link'] . '?' . $_SERVER['QUERY_STRING'];
            }
            if (isset($data['breadcrumbs'])) $data['breadcrumbs'] = $this->model->breadcrumbs($data['breadcrumbs'],
                $this->view, $data['page_n']);

            $page_canonical = 'https://' . $_SERVER['HTTP_HOST'] . LINK . $_SERVER['REQUEST_URI'] . '/';
            if ($this->params['topic'] == 'index') {
                $page_canonical = 'https://' . $_SERVER['HTTP_HOST'] . LINK . '/';
            } else {
                $current_page = array_values($this->params);
                if (empty($this->params['this_page']))
                    $page_canonical = 'https://' . $_SERVER['HTTP_HOST'] . LINK . $_SERVER['REQUEST_URI'] . '/';
                else {
                    $link = $_SERVER['REQUEST_URI'];
                    $page_link = explode('page', $link);
                    $page_link = $page_link[0];
                    $page_canonical = 'https://' . $_SERVER['HTTP_HOST'] . LINK . $page_link;
                }
            }
            if ($_SERVER['QUERY_STRING']) {
                $data['meta']['link'] = PROTOCOL . $_SERVER['HTTP_HOST'] . LINK . $_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING'];
            } else {
                $data['meta']['link'] = PROTOCOL . $_SERVER['HTTP_HOST'] . LINK . $_SERVER['REQUEST_URI'] . '/';
            }
            $menu = $this->menu->find(array('type' => 'rows', 'where' => 'tb.type_id=1'));
            $menu_footer = $this->menu->find(array('type' => 'rows', 'where' => 'tb.type_id=2'));
            $menu_footer_2 = $this->menu->find(array('type' => 'rows', 'where' => 'tb.type_id=3'));
            $menu_footer_3 = $this->menu->find(array('type' => 'rows', 'where' => 'tb.type_id=4'));

            $data['this_page'] = $this->db->row("SELECT * FROM menu WHERE menu.link='".$this->params['pages']."'");

            $main_translate = $this->model->get_lang("tb.modules_id=0");
            $settings = $this->db->rows_key("SELECT tb.name, tb.value FROM config tb WHERE tb.modules_id=0");
            $social = $this->social->find(array('type' => 'rows'));
            $feedback_translate = $this->model->get_lang("tb.modules_id=46");

            $data['head'] = $this->view->Render('layout/head.phtml', array(
                'meta' => $data['meta'],
                'page_canonical' => $page_canonical,
                'lang' => $data['lang'],
                'langs' => $data['languages'],
                'current_page' => $this->params,
                'main_translate' => $main_translate,
            ));
            $data['header'] = $this->view->Render('layout/header.phtml', array(
                'lang' => $data['languages'],
                'main_translate' => $main_translate,
                'settings' => $settings,
                'menu' => $menu
            ));
            $data['footer'] = $this->view->Render('layout/footer.phtml', array(
                'main_translate' => $main_translate,
                'settings' => $settings,
                'social' => $social,
                'menu__footer' => $menu_footer,
                'menu__footer_2' => $menu_footer_2,
                'menu__footer_3' => $menu_footer_3,
            ));
            $data['modals'] = $this->view->Render('layout/modals.phtml', array(
                'feedback_translate' => $feedback_translate,
            ));
            return $this->view->Render("index.phtml", $data);
        }
    }


}