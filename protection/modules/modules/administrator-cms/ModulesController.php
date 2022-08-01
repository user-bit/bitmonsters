<?php class ModulesController extends BaseController
{
	function __construct($registry,$params)
	{
		parent::__construct($registry,$params);
		$this->tb="modules";
		$this->name="Управление модулями";
		$this->separator="--xxx--";
		$this->separator2="@@";
		$this->registry=$registry;
		$this->modules=new Modules($this->sets);
	}

	public function indexAction()
	{
		$dir=MODULES."product/".PathToTemplateAdmin."/data/db.sql";
		$vars['message']='';
		$vars['name']=$this->name;
		if(isset($this->params['subsystem'])) return $this->Index($this->modules->subsystemAction());
		if(isset($this->registry['access'])) $vars['message']=$this->registry['access'];
		if(isset($this->params['delete']) || isset($_POST['delete'])) $vars['message']=$this->modules->delete();
		elseif(isset($_POST['update'])) $vars['message']=$this->modules->save();
		elseif(isset($_POST['update_close'])) $vars['message']=$this->modules->save();
		elseif(isset($_POST['add_close'])) $vars['message']=$this->modules->add();
		$vars['list']=$this->modules->find(array('select'=>'tb.*,tb2.name as cat','join'=>'LEFT JOIN menu_admin tb2 ON tb.sub=tb2.id','order'=>'tb.sub asc,tb.`sort` ASC','type'=>'rows'));
		$vars['list']=$this->view->Render('view.phtml',$vars);
		$data['right_menu']=$this->model->right_menu_admin(array('action'=>$this->tb,'name'=>$this->name));
		$data['content']=$this->view->Render('list.phtml',$vars);
		return $this->Index($data);
	}

	public function editAction()
	{
		$vars['message']='';
		if(isset($_POST['update'])) $vars['message']=$this->modules->save();
		$vars['edit']=$this->modules->find((int)$this->params['edit']);
		$vars['modules']=$this->db->rows("SELECT *,m.comment as name,m.id as type_id FROM `moderators_type` m LEFT JOIN `moderators_permission` mp ON mp.moderators_type_id=m.id AND mp.module_id=? WHERE m.id!=? GROUP BY m.id ORDER BY m.id ASC",array($this->params['edit'],1));
		$vars['subsystem2']=$this->db->rows("SELECT * FROM `subsystem` m GROUP BY m.id ORDER BY m.id ASC");
		$vars['permission']=$this->db->rows("SELECT * FROM `moderators_permission` mp WHERE module_id=?",
			array($this->params['edit']));
		$vars['menu']=$this->db->rows("SELECT * FROM `menu_admin` ORDER BY id ASC");
		$data['content']=$this->view->Render('edit.phtml',$vars);
		return $this->Index($data);
	}

	public function addAction()
	{
		$vars['message']='';
		if(isset($_POST['add'])) $vars['message']=$this->modules->add();
		$vars['dir']=Dir::get_directory_list(MODULES, ['.']);
		$vars['list']=$this->modules->find(array('type'=>'rows'));
		$vars['modules']=$this->db->rows("SELECT *,m.comment as name FROM `moderators_type` m WHERE m.id!='1' ORDER BY m.id ASC");
		$vars['subsystem2']=$this->db->rows("SELECT * FROM `subsystem` m GROUP BY m.id ORDER BY m.id ASC");
		$vars['menu']=$this->db->rows("SELECT * FROM `menu_admin` ORDER BY id ASC");
		$data['content']=$this->view->Render('add.phtml',$vars);
		return $this->Index($data);
	}

	// Include  modules
	function addModuleAction()
	{
		if($_POST['id']){
			$dir=MODULES.$_POST['id']."/".PathToTemplateAdmin."/data/info.txt";
			if(file_exists($dir)){
				$lines=file($dir);
				$i=0;
				$data=[];
				$data['sub2']=substr($lines[0],3,4);
				$data['name']=$lines[1];
				$data['controller']=$lines[2];
				$data['url']=$lines[3];
				$data['tables']=$lines[4];
				$data['photo']=$lines[5];
				$data['comment']=$lines[6];
				$data['sort2']=$lines[7];
				if(file_exists(MODULES.$_POST['id']."/".PathToTemplateAdmin."/data/translate.txt"))$data['translate']=1;
				if(file_exists(MODULES.$_POST['id']."/".PathToTemplateAdmin."/data/config.txt"))$data['config']=1;
				return json_encode($data);
			}
		}
	}
}