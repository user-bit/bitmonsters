<?php class MenuController extends BaseController
{
    private $right_menu = array(
        array('title' => 'Позиция меню', 'link' => '/' . PathToTemplateAdmin . '/menu/act/type', 'name' => 'type')
    );

	function  __construct($registry,$params)
	{
        parent::__construct($registry, $params);
        $this->name = Menu::$name;
        $this->tb = Menu::$table;
        $this->menu = new Menu($this->sets);
	}

	public function indexAction()
	{
		$vars['name']=$this->name;
        if (isset($this->params['act'])) {
            $act = $this->params['act'] . 'Action';
            return $this->Index($this->$act());
        }
		if (isset($this->params['subsystem'])) return $this->Index($this->menu->subsystemAction());
		if (isset($_POST['sort_menu'])) $_SESSION['sort_menu']=$_POST['sort_menu'];
		if (!isset($_SESSION['sort_menu'])) $_SESSION['sort_menu']=0;
		$vars['message']='';
		if (isset($this->registry['access'])) $vars['message']=$this->registry['access'];
		if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message']=$this->menu->delete('menu');
		elseif (isset($_POST['update'])) $vars['message']=$this->menu->save();
		elseif (isset($_POST['update_close'])) $vars['message']=$this->menu->save();
		elseif (isset($_POST['add_close'])) $vars['message']=$this->menu->add();
        $vars['type'] = $this->db->rows("SELECT * FROM `menu_type` ORDER BY id ASC");

        $where="tb.sub is NULL";
		$vars['link']='/'.PathToTemplateAdmin.'/' . $this->tb;

		if (isset($this->params['cat'])) {
			$where="tb.sub='{$this->params['cat']}' ";
			$_SESSION['sort_menu']=$this->params['cat'];
			$vars['link'] .= '/cat/' . $this->params['cat'];
		} else $_SESSION['sort_menu']=0;


        if (isset($_POST['getPosition'])) $_SESSION['info_sort_position_id'] = $_POST['getPosition'];
        $where .= empty($_SESSION['info_sort_position_id']) ? '' : (' AND tb.type_id=' . $_SESSION['info_sort_position_id']);

		$vars['menu']=$this->menu->find(array('type'=>'rows','order'=>'tb.sort ASC,tb.id ASC'));
        $vars['list'] = $this->view->Render('view.phtml', $this->menu->find(array(
            'paging'=>true,
            'select' => 'tb.*, tb_lang.*, mt.name as type_name, mt.color as type_color',
            'join' => 'LEFT JOIN menu_type mt ON mt.id = tb.type_id',
            'where' => $where,
            'order' => 'tb.sort ASC,tb.id DESC'
        )));
		$i=0;
		foreach ($vars['menu'] as $key=>$value) {
			$vars['menu'][$i]['link']=$vars['menu'][$i]['id'];
			$i++;
		}
		$settings=array('arr'=>$vars['menu'],'link'=>'/'.PathToTemplateAdmin.'/menu/cat/','id'=>'tree');
        $data['right_menu']=$this->view->Render('cat_menu.phtml',array('cat_menu'=>Arr::treeview($settings)));
		$data['right_menu'] .= $this->model->right_menu_admin(array('action'=>$this->tb,'name'=>$this->name,  'menu2' => $this->right_menu));
		$data['content']=$this->view->Render('list.phtml',$vars);
		$data['scripts']=array('libs/jquery.treeview.js');
		//передаем название модуля (для открывание подраздела главного меню)
		$data['position']=$this->tb;
		return $this->Index($data);
	}

	public function addAction()
	{
		$vars['message']='';
		if (isset($_POST['add'])) $vars['message']=$this->menu->add();
		$vars['catalog']=getTree(containArrayInHisId($this->menu->getAll('tb.id, tb.sub, tb_lang.name')));
        $vars['type'] = $this->db->rows("SELECT * FROM `menu_type` ORDER BY id ASC");

        $data['content']=$this->view->Render('add.phtml',$vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
		return $this->Index($data);
	}

	public function editAction()
	{
        $vars['message'] = isset($_POST['update']) ? $this->menu->save() : '';
		$vars['edit']=$this->menu->find((int)$this->params['edit']);
		$row=$this->meta->load_meta($this->tb,$vars['edit']['link']);
		if ($row) {
            $vars['edit']['title'] = $row['title'];
            $vars['edit']['description'] = $row['description'];
        }
        $vars['type'] = $this->db->rows("SELECT * FROM `menu_type` ORDER BY id ASC");

        $vars['catalog']=getTree(containArrayInHisId($this->menu->getAll('tb.id, tb.sub, tb_lang.name')));
		$data['content']=$this->view->Render('edit.phtml',$vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
		return $this->Index($data);
	}
    public function typeAction()
    {
        $vars['message'] = '';
        if (isset($_POST['update'])) $vars['message'] = $this->menu->saveType();
        elseif (isset($this->params['addposition'])) $vars['message'] = $this->menu->addType();
        elseif (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message'] = $this->menu->delete('menu_type');
        $vars['name'] = 'Позиция меню';
        $vars['action'] = $this->tb;
        $vars['path'] = '/act/type';
        $vars['list'] = $this->db->rows("SELECT * FROM `menu_type` ORDER BY id ASC");
        $data['right_menu'] = $this->model->right_menu_admin(array('action' => $this->tb, 'name' => $this->name, 'sub' => 'pricetype', 'menu2' => $this->right_menu));
        $data['content'] = $this->view->Render('type.phtml', $vars);
        //передаем название модуля (для открывание подраздела главного меню)
        $data['position']=$this->tb;
        return $data;
    }
}
