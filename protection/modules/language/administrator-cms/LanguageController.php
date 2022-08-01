<?php class LanguageController extends BaseController
{
	protected $params;
	protected $db;

	function  __construct($registry,$params)
	{
		parent::__construct($registry,$params);
		$this->tb=Language::$table;
		$this->name=Language::$name;
		$this->registry=$registry;
		$this->lang=new Language($this->sets);
	}

	public function indexAction()
	{
		$vars['message']='';
		$vars['name']=$this->name;
		if (isset($this->params['subsystem'])) return $this->Index($this->lang->subsystemAction());
		if (isset($this->registry['access'])) $vars['message']=$this->registry['access'];
		if (isset($this->params['delete']) || isset($_POST['delete'])) $vars['message']=$this->lang->delete();
		elseif (isset($_POST['update'])) $vars['message']=$this->lang->save();
		elseif (isset($_POST['update_close'])) $vars['message']=$this->lang->save();
		elseif (isset($_POST['add_close'])) $vars['message']=$this->lang->add();

		$vars['list']=$this->view->Render('view.phtml',['list'=>$this->lang->find(['type'=>'rows','order'=>'tb.id DESC'])]);
		$data['right_menu']=$this->model->right_menu_admin(['action'=>$this->tb,'name'=>$this->name]);
		$data['content']=$this->view->Render('list.phtml',$vars);
		return $this->Index($data);
	}

	public function addAction()
	{
		$vars['message']='';
		if (isset($_POST['add'])) $vars['message']=$this->lang->add();
		$vars['all_lang']=array();
		$dir_lang = getcwd().'/resource/'.PathToTemplateAdmin.'/images/flags/';
		$all_lang = scandir($dir_lang);
		sort($all_lang);
		$default_lang = $this->db->rows_key("Select `language`,`language`  FROM `".$this->tb."`");
		foreach ($all_lang as $lang) {
			$fileParts=pathinfo($dir_lang.$lang);
			if ($lang <> '..' and $lang <> '.' and $fileParts["extension"] == 'png') {
				$index=substr($lang,0,-4);
				if (!in_array($index,array_keys($default_lang)))$vars['Language'][$index]=$index;
			}
		}
		$data['content']=$this->view->Render('add.phtml',$vars);
		return $this->Index($data);
	}
}