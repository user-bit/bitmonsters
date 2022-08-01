<?php class Parser
{
	public $get;
	private $inv_char=['"'=>'&quot;',"'"=>'&#039;','%'=>'&#037;','('=>'&#040;',')'=>'&#041;','<'=>'&lt;','>'=>'&gt;'/*"&"=>'&#038;','"'=>'&#034;',"@"=>"","!"=>'&#033;',"."=>"",","=>"&#044;","/"=>"",'&'=>'&#038;','+' =>'&#043;',*/];
	private $need_to_parse="";

	function __construct(){}

	function email_validation($field)
	{
		return filter_var($field, FILTER_VALIDATE_EMAIL);
	}

	function remove_invalid_characters($field)
	{
		$field=stripslashes($field);
		return strtr($field, $this->inv_char);
	}

	function parse_tree(&$get)
	{
		foreach($get as &$k) if(is_array($k)){
				$this->need_to_parse[]=$k;
				$this->parse_full_tree_remove_invalid($k);
			}else $k="aaa1";
	}

	function parse_full_tree_remove_invalid(&$get)
	{
		foreach($get as &$k) if(is_array($k)) $this->parse_full_tree($k);else $k="aaa2";
	}

	function parse_full_tree(&$get)
	{
		foreach($get as &$k) (is_array($k))?$this->parse_full_tree($k):$k=htmlspecialchars($k);
	}

	function parse_recursive_tree(&$array)
	{
		foreach($array as &$key){
			if(is_array($key)) foreach($key as &$key2) if(is_array($key2)) $this->parse_recursive_tree($key);else $key2=$this->remove_invalid_characters($key2);
			else $key=$this->remove_invalid_characters($key);
		}
	}
}