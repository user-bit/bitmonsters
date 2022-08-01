<?php class Router
{
	private $registry;
	private $uri_arr = NULL;
	public $classObj;
	protected $db;

	public function __construct($registry, $db, $uri = NULL)
	{
		$this->registry = $registry;
		$this->db = $db;
		if (!isset($uri)) {
			$uri = $_SERVER['REQUEST_URI'];
			$uri = filter_var($uri, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);
			//удаляем последний пустой елемент если есть
			$rest = mb_substr($uri, -1);
			if ($rest == '/') $uri = mb_substr($uri, 0, strlen($uri) - 1, 'UTF-8');

		}
		$uri = current(explode('?', $uri));
		$_SERVER['REQUEST_URI'] = $uri;// Костыль для карты сайта, не выкидываем ошибку, а направляем в Мета контроллер
        if ($uri === '/sitemap.xml'){
			$uri = '/ajax/meta/sitemap';
		} elseif (!preg_match('/^[-a-zA-Z0-9_\/\=\?\;\,]*$/', $uri)) $err = true;

        $this->uri_arr = explode("/", $uri);
		//удаляем первый пустой елемент
		array_splice($this->uri_arr, 0, 1);

		if (isset($err)) {
			$this->uri_arr[0] = 'Error';
			$this->uri_arr[1] = 'index';
		}
	}

	public function getParams($create = true)
	{

		$url = $this->uri_arr;
		$uri_params = [];
		$params = [];
		$uri_params['action'] = "index";
		//определения контролера и экшина
		if (isset($url[0]) && $url[0] != '') {
		    if ($url[0] == "ajax" && isset($url[1])) {

				if (isset($url[1], $url[2])) {
					$uri_params['controller'] = ucfirst($url[1]);
					$uri_params['action'] = $url[2];
				}
				$params['topic'] = 'ajax';
			} elseif ($url[0] == PathToTemplateAdmin) {
				$params['topic'] = PathToTemplateAdmin;
				if (isset($url[1]) && $url[1] == "logout") {
					unset($_SESSION['admin']);
					if (isset($_COOKIE['login_admin'])) {
						setcookie("login_admin", '', time() + (31566000), '/');
						setcookie("password_admin", '', time() + (31566000), '/');
						unset($_COOKIE['login_admin'], $_COOKIE['password_admin']);
					}
				}
				if ($url[0] == PathToTemplateAdmin &&
                    isset($url[2], $url[1]) && $url[1] == "ajax" && (checkAuthAdmin())) {
				    if (isset($url[2], $url[3]) && $url[2] != 'images') {
						$uri_params['controller'] = ucfirst($url[2]);
						$uri_params['action'] = $url[3];
					}elseif ($url[2] == 'images') {
                        $uri_params['controller'] = 'Images';
                        $uri_params['action'] = $url[3];
                    }else {
                        $uri_params['controller'] = 'AjaxAdmin';
                        $uri_params['action'] = $url[2];
                    }
				} elseif (checkAuthAdmin()) {
					$uri_params['controller'] = 'indexAdmin';
					$uri_params['action'] = 'index';
					if (isset($url[1])) {
						$row2 = $this->db->row("SELECT `id`,`name` FROM `modules` WHERE `controller`=?", [$url[1]]);
						if ($row2) {
							$param = [$row2['id'], $_SESSION['admin']['id']];
							$row = $this->db->row("SELECT mm.`permission`,m.type_moderator FROM `moderators` m LEFT JOIN `moderators_permission` mm ON mm.moderators_type_id=m.type_moderator AND mm.module_id=? WHERE m.id=?", $param);
							if ($row['permission'] != 000 || $row['type_moderator'] == 1) {
								$uri_params['controller'] = ucfirst($url[1]);
								if (isset($url[2]) && ($url[2] == "edit" || $url[2] == "add" || $url[2] == "config" || $url[2] == "subsystem")) {
									if (isset($url[2], $url[3]) && $url[2] == "subsystem" && $url[3] != "") {
										$subsystem = SUBSYSTEM . $url[3] . '/' . ucfirst($url[3]) . 'Controller.php';
										if (!file_exists($subsystem)) return Router::act('error', $this->registry);
										include $subsystem;
										$uri_params['topic'] = 'subsystem';
										$params['subsystem'] = $url[3];
									} else $uri_params['action'] = $url[2];
								}
								Registry::set('topic_value', $url[1]);
								/*'000'-off;
								'100'-read;
								'200'-read/edit;
								'300'-read/del;
								'400'-read/add;
								'500'-read/edit/del;
								'600'-read/edit/add;
								'700'-read/del/add;
								'800'-read/edit/del/add;*/
								if ($row['type_moderator'] != 1) {
									if (isset($url[2]) && ($url[2] == 'delete' || (isset($_POST['delete']) && $url[2] == 'update')) && ($row['permission'] != 500 && $row['permission'] != 300 && $row['permission'] != 700 && $row['permission'] != 800)) {
										$this->registry->set('access', messageAdmin('Отказано в доступе', 'error'));
										$uri_params['action'] = 'index';
									} elseif (isset($url[2]) && ($url[2] == 'edit' || $url[2] == 'update') && ($row['permission'] != 200 && $row['permission'] != 500 && $row['permission'] != 600 && $row['permission'] != 800)) {
										$this->registry->set('access', messageAdmin('Отказано в доступе', 'error'));
										$uri_params['action'] = 'index';
									} elseif (isset($url[2]) && ($url[2] == 'add' || $url[2] == 'duplicate') && ($row['permission'] != 400 && $row['permission'] != 600 && $row['permission'] != 700 && $row['permission'] != 800)) {
										$this->registry->set('access', messageAdmin('Отказано в доступе', 'error'));
										$uri_params['action'] = 'index';
									}
								}
								$this->registry->set(PathToTemplateAdmin, $url[1]);
								$params['action'] = $uri_params['action'];
								$params['controller'] = ucfirst($url[1]);
								$params['module'] = $row2['name'];
							} else return Router::act('error', $this->registry);
						} else return Router::act('error', $this->registry);
					} else {
						$this->registry->set(PathToTemplateAdmin, 'index');
						$uri_params['controller'] = 'IndexAdmin';
						$uri_params['action'] = 'index';
					}
				} else {
					$this->registry->set(PathToTemplateAdmin, 'login');
					$uri_params['controller'] = 'Login';
					if (!empty($url[1]))
					    $params['login'] = $url[1];
					$uri_params['action'] = 'index';
				}
				$params['topic'] = PathToTemplateAdmin;
			} else {
				if (isset($error)) unset($_SESSION['user_info']);
				$row = $this->db->row("SELECT `controller` FROM `modules` WHERE `link`=?", [$url[0]]);
				if ($row) {
					$uri_params['controller'] = ucfirst($row['controller']);
					$uri_params['action'] = "index";
					$params['topic'] = $url[0];
                    if(in_array("page", $url)) {
                        $page_key = array_search("page", $url);
                        $this_page = $url[$page_key + 1];
                    }
					if (isset($url[1])) $params[$row['controller']] = $url[1];
				}else {
                    $uri_params['controller'] = 'Pages';
                    $uri_params['action'] = "index";
                    $params['topic'] = 'pages';
                    $params['pages'] = $url[0];
                    if ($url[0] == 'sitemap') $params['sitemap'] = $url[1];
				}

			}
		} else {
			$uri_params['controller'] = 'Index';
			$uri_params['action'] = 'index';
			$params['topic'] = 'index';
		}
		$url_count = count($url);

		for ($i = 2; $i < $url_count;) {
			if (($url[$i] != 'delete') || (($url[$i] == 'delete') && $i == 2)) {
				if (isset($url[$i + 1])) $val = $url[$i + 1];
				else $val = '';
				$params[$url[$i]] = $val;
			}
			$i += 2;
		}
        $params['this_page'] = $this_page;
		$className = ucfirst($uri_params['controller'] . 'Controller');
		$cs = $this->registry['controllers_settings'];
		$filePath = CONTROLLERS . $className . '.php';
		$method_exists = false;
		if (file_exists($filePath)) {
			include_once $filePath;
			$this->classObj = new $className($this->registry, $params);
			if (method_exists($this->classObj, $uri_params['action'] . 'Action')) {
				$method_exists = true;
				if (!$create) unset($this->classObj);
			}
		} else {
			if ($params['topic'] == PathToTemplateAdmin) $filePath = MODULES . strtolower($uri_params['controller']) . '/'.PathToTemplateAdmin.'/' . $className . '.php';
			else $filePath = MODULES . strtolower($uri_params['controller']) . '/' . $className . '.php';
			if (file_exists($filePath)) {
				include_once $filePath;
				$this->classObj = new $className($this->registry, $params);
				if (method_exists($this->classObj, $uri_params['action'] . 'Action')) {
					$method_exists = true;
					if (!$create) unset($this->classObj);
				}
			} elseif ($params['topic'] == PathToTemplateAdmin) {
				$filePath = SUBSYSTEM . strtolower($uri_params['controller']) . '/' . ucfirst($className) . '.php';
				if (file_exists($filePath)) include_once $filePath;
				$this->classObj = new $className($this->registry, $params);
				if (method_exists($this->classObj, $uri_params['action'] . 'Action')) {
					$method_exists = true;
					if (!$create) unset($this->classObj);
				}
			}
		}
		if (!$method_exists) {
			$uri_params['controller'] = 'Error';
			$uri_params['action'] = 'index';
		}
		if ($create) {
			if (!$method_exists) {
				$className = $uri_params['controller'] . 'Controller';
				$this->classObj = new $className($this->registry, $params);
			}
			return $this->dispatch($uri_params['action'], $this->classObj);
		}
		return $uri_params;
	}

	public function load($controller, $registry, $params = [])
	{
		$className = ucfirst($controller . 'Controller');
		return new $className($registry, $params);
	}

	public static function act($controller, $registry, $action = 'index', $params = [])
	{
		$obj = self::load($controller, $registry, $params);
		$res = self::dispatch($action, $obj);
		return $res;
	}

	public function dispatch($strActionName = 'index', $obj = NULL)
	{
        $results = '';
		$objName = ($obj ? '$obj' : '$this->classObj');
		eval('$results=' . $objName . '->' . $strActionName . 'Action();');
		unset($obj);
        return $results;
	}
}