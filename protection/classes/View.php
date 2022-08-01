<?php class View
{
	private $registry;
	public $tplDir=null;
	public $resources=null;
	public $vars=null;

	function __construct($registry,$vars=[])
	{
		$this->vars=$vars;
		$this->registry=$registry;
		$tplSettings=$this->registry['tpl_settings'];
		$this->resources=[];
		$this->resources['image']=$tplSettings['images'];
		$this->resources['flash']=$tplSettings['flash'];
		$this->resources['styles']=$tplSettings['styles'];
		$this->resources['scripts']=$tplSettings['jscripts'];
		$this->tplDir=$tplSettings['source'];
	}

	public function Render($includeFile,$vars=[])
	{
        if (!array_key_exists('settings', $vars)  AND $this->vars['settings'])  $vars['settings']  =  $this->vars['settings'];

        if(isset($vars['subsystem'])&&$vars['subsystem']!=""){

            $pathTpl=SUBSYSTEM.$vars['subsystem']."/".$this->tplDir.$includeFile;

            if(!file_exists($pathTpl)){
                Log::echoLog('Could not found template \''.$pathTpl.'\' !!!');
                return;
            }
        }elseif(isset($vars['module'])&&$vars['module']!=""){
            $pathTpl=MODULES.$vars['module']."/".$this->tplDir.$includeFile;

            if(!file_exists($pathTpl)){
                Log::echoLog('Could not found template \''.$pathTpl.'\' !!!');
                return;
            }
        }elseif(isset($this->registry[PathToTemplateAdmin])){
            $pathTpl=$this->tplDir.PathToTemplateAdmin."/".$includeFile;

            if(!file_exists($pathTpl)){
                $vars['action']=$this->registry[PathToTemplateAdmin];
                $pathTpl=MODULES.strtolower($this->registry[PathToTemplateAdmin])."/".PathToTemplateAdmin."/".$this->tplDir.$includeFile;
                if(!file_exists($pathTpl)){
                    $pathTpl=SUBSYSTEM.strtolower($this->registry[PathToTemplateAdmin])."/".$this->tplDir.$includeFile;
                    if(!file_exists($pathTpl)){
                        Log::echoLog('Could not found template \''.$pathTpl.'\' !!!');
                        return;
                    }
                }
            }
        }else{
            $theme=$this->registry['theme'];
            $pathTpl=$this->tplDir.$theme."/".$includeFile;
            if(!file_exists($pathTpl)){
                Log::echoLog('Could not found template \''.$pathTpl.'\' !!!');
                return;
            }
        }
        ob_start();
        if(isset($theme))require SITE_PATH.'/'.$pathTpl;
        else require $pathTpl;
        $contents=ob_get_contents();
        ob_end_clean();
        return $contents;
	}



	public function LoadResource($id_resource,$fileName,$admin='')
	{
		if($admin==''){
			$path1=$this->tplDir.$this->registry['theme']."/".$this->resources[$id_resource].$fileName;
			$path2=$this->resources[$id_resource].$fileName;
			$path3=$this->tplDir.$this->registry['theme']."/colors/".$this->registry['theme_color']."/".$fileName;
			if(file_exists($path1))return $this->typeResource($id_resource,$path1);
			elseif(file_exists($path2))return $this->typeResource($id_resource,$path2);
			elseif(file_exists($path3))return $this->typeResource($id_resource,$path3);
			else{
				Log::echoLog('Could not found resource \''.$path1.'\' (resource type \''.$id_resource.'\')');
				return false;
			}
		}else{
			$path1=$this->tplDir.PathToTemplateAdmin."/".$this->resources[$id_resource].$fileName;
			$path2=$this->resources[$id_resource].$fileName;
			if(file_exists($path1))return $this->typeResource($id_resource,$path1);
			elseif(file_exists($path2))return $this->typeResource($id_resource,$path2);
			else{
				Log::echoLog('Could not found resource \''.$path1.'\' (resource type \''.$id_resource.'\')');
				return false;
			}
		}
	}

	public function typeResource($type,$path)
	{
		
		$prefix ='?'.filemtime($path);

		if($type=="styles")return '<link rel="stylesheet" type="text/css" href="/'.$path.$prefix.'" />';
		elseif($type=="scripts")return '<script type="text/javascript" src="/'.$path.$prefix.'"></script>';
		elseif($type=="image")return '<link rel="shortcut icon"  href="/'.$path.'" />';
		else return'/'.$path;
	}

	public function Load($array,$type,$admin='')
	{
		$data='';
		if(count($array)>0){
			$data="";
			if($type=="styles")for($i=0;$i<count($array);$i++)$data.=$this->LoadResource('styles',$array[$i],$admin);
			else for($i=0;$i<count($array);$i++)$data.=$this->LoadResource('scripts',$array[$i],$admin);
		}
		return $data;
	}
}