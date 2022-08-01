<?php class Filters
	{
		private $registry;
		protected $db;

		public function __construct($sets)
		{
			$this->sets = $sets;
			$this->registry = $sets['registry'];
			$this->db = $sets['db'];
			$this->view = new View($sets['registry']);
			$this->settings = $sets['settings'];
			$this->params = $sets['params'];
			$this->translation = $sets['translation'];
			$this->registry = $sets['registry'];
			$this->tmpTableFilters = 'temp_params_in_catalog';
			$this->paramsModel = new Params($this->sets);
		}

		// Разбор url фильтров и формирование запроса в БД
		public function get_condition($params_url, $paramsByUrl)
		{

			$join = '';
			$where = '';
			$group = [];
			$idParams = [];
			$_SESSION['params'] = [];
			if (!isset($_POST['clear_id'])) $_POST['clear_id'] = '';
			if ($params_url != '') {
				// Разделим фильтра на группы
				$uri = explode(';', $params_url);
				$i = 0;
				foreach ($uri as $row) {
					// Полуим родителя фильтров и его детей
					$uri2 = explode('=', $row);
					if ($uri2[0] != '' && $uri2[1] != '') {
						$group[ $uri2[0] ] = [];
						// Получим отдельно каждого ребенка
						$uri3 = explode(',', $uri2[1]);
						foreach ($uri3 as $row2) {
							if ($_POST['clear_id'] != $row2) {
								$_SESSION['params'][] = $row2;
								$group[ $uri2[0] ][] = $row2;
								$idParams[] = $row2;
							}
						}
					}
					$i++;
				}
			}
			// Если фильтры используют slug вместо id, то заменим id на url
			if ((int)$this->settings['useSlugParams'] === 1) {
				// получим id фильтров по url
				$copyIdParams = $idParams;
				$idParams = [];
				foreach ($copyIdParams as $k => $idParam) {
					// Проверим, не пришел ли нам выключенный параметр фильтра.
					if (key_exists($idParam, $paramsByUrl)) $idParams[ $k ] = $paramsByUrl[ $idParam ]['id'];
				}
				$copyGroup = $group;
				$group = [];
				foreach ($copyGroup as $k => $idParam) {
					foreach ($idParam as $k2 => $childParams) {
						$group[ $paramsByUrl[ $k ]['id'] ][] = $paramsByUrl[ $childParams ]['id'];
					}
				}
			}

			return ['join' => $join, 'where' => $where, 'group' => $group, 'idParams' => $idParams];
		}
		
		/**
		 * @param $categoryId
		 * @return StringClass
		 * Получаем +1 уровень дочерних каталогов, т.к. в текущей категории отображаются товары и дочерних категорий
		 */
		public function getSubCatalogs($categoryId)
		{
			//todo получать список Всех дочерних категорий, а не только на 1 уровень
			$subCatalogs = $this->db->cell("SELECT GROUP_CONCAT(DISTINCT id ) FROM catalog WHERE sub='$categoryId'");
			if ($subCatalogs) $categoryId .= ',' . $subCatalogs;
			return $categoryId;
		}

		/**
		 * @param $group - массив фильтров
		 * @param $cat_id
		 * @param $params_url
		 * @param $current_product
		 * @param $price
		 * @return StringClass|void
		 */
		public function getParams($group, $cat_id, $params_url, $price = '', $idParamsUrl, $priceRange = null)
		{
			$this->idParamsUrl = $idParamsUrl;
			$return = [];
			if ($this->settings['cache'] == 1) {
				$cache_time = $this->settings['lifetime_cache'] * 60 * 60;//Cache lifetime in seconds
				$file = str_replace('=', '--', $params_url);
				$file = str_replace(',', '-', $file);
				$file = str_replace(';', '---', $file);
				if ($params_url == '') $cache_file = 'files/cache/filters_all' . $cat_id . '.html';
				else $cache_file = 'files/cache/' . $file . '-' . $cat_id . '.html';
				if (file_exists($cache_file)) if ((time() - $cache_time) < filemtime($cache_file)) return file_get_contents($cache_file);
			}
			
			$inParams = $this->getSubCatalogs($cat_id);
			// Получаем id фильтров и товаров для данной и дочерних категорий
			$return = $this->getParamsAndProducts($inParams);
			$where = [];
			$cnt = count($group);
			$params = [];
			// Если нужно подсчитывать кол-во товаров для фильтра
			if ((int)$this->settings['filtersCountProducts'] === 1) {
				$filters = $this->db->rows("SELECT params_id FROM params_catalog pc WHERE `catalog_id` IN (" . $inParams . " ) GROUP BY params_id");
				if (count($filters) > 0):
					foreach ($filters as $row) :
						$param = '';
						$join = '';
						$product_q = '';
						foreach ($idParamsUrl as $key2 => $row2) :
							if ($row['params_id'] != $key2) {
								$where[ $row['params_id'] ] = '';
								foreach ($row2 as $row3) $where[ $row['params_id'] ] .= " OR tj" . $key2 . ".params_id='$row3'";
								if ($where[ $row['params_id'] ] != '') $param .= ' AND (' . substr($where[ $row['params_id'] ], 4) . ')';
								$join .= " LEFT JOIN params_product tj" . $key2 . " ON p.id = tj" . $key2 . ".product_id";
							}
						endforeach;
						if ($param != '') {
							if ($cnt == 1 && isset($product['id'], $product_q2)) $product_q = $product_q2;
							else {
								$where_p = '';
								if ($price != '') {
									$join .= " LEFT JOIN `price` tb_price ON `tb_price`.product_id=p.id AND tb_price.price_type_id='" . $_SESSION['price_type_id'] . "'";
									$where_p = " AND (`tb_price`.price>='{$price[0]}' AND `tb_price`.price<='{$price[1]}')";
								}
								$product = $this->db->row("SELECT GROUP_CONCAT(DISTINCT p.id SEPARATOR ',') as id FROM product p $join LEFT JOIN product_catalog pc ON pc.product_id=p.id 
																WHERE p.active='1' AND  `catalog_id` IN (" . $inParams . " ) " . $param . " $where_p");
								if (isset($product['id'])) {
									$products = array_unique(explode(',', $product['id']));
									foreach ($products as $row5) {
										if ($product_q != '') $product_q .= ' OR ';
										$product_q .= "pp.product_id='{$row5}'";
									}
									if ($product_q != '') $product_q = " AND (" . $product_q . ")";
									$product_q2 = $product_q;
								}
							}
						}
						if (count($return['products']) > 0 && $params_url != '') $select_count = '0 as count';
						else $select_count = 'COUNT(DISTINCT pp.product_id) as count';
						$join = "";
						$where_p = '';
						if ($price != '') {
							$join = " LEFT JOIN `price` tb_price ON `tb_price`.product_id=product.id AND tb_price.price_type_id='" . $_SESSION['price_type_id'] . "'";
							$where_p = " AND (`tb_price`.price>='{$price[0]}' AND `tb_price`.price<='{$price[1]}')";
						}
						$cur_param = implode(',', $idParamsUrl);
						if (empty($cur_param)) $cur_param = 0;
						$query = "  SELECT tb1.id, tb1.url, tb1.sub, tb2.name, $select_count, pp.product_id, pp.params_id, GROUP_CONCAT(DISTINCT pp.product_id SEPARATOR ',') as product_id FROM `params` tb1 
								  LEFT JOIN " . $this->registry['key_lang'] . "_params tb2 ON tb1.id=tb2.params_id 
								  LEFT JOIN params_product pp ON pp.params_id = tb1.id $product_q 
								  LEFT JOIN product_catalog pc ON pp.product_id = pc.product_id 
								  LEFT JOIN product ON product.id=pp.product_id 
								  $join 
								  WHERE (tb1.active='1' AND tb1.sub='{$row['params_id']}')
								   AND ((pc.`catalog_id` IN (" . $inParams . " ) AND product.active='1' $where_p) OR tb1.id IN (" . $cur_param . ")) 
								   GROUP BY tb1.id 
								   ORDER BY tb1.`sort` ASC, tb2.`name` ASC, tb1.id DESC";
						$params2 = $this->db->rows($query);
						$params = array_merge($params2, $params);
					endforeach;
				endif;
				//Посчитаем количество товара, доступное за еще не выбранными фильтрами
				// Panasonic (+35)
				if (!empty($params_url) AND $return['products']) {
					$current_product = explode(',', $return['products']);
					foreach ($params as &$row) {
						$count = 0;
						if ($row['product_id'] AND !in_array($row['id'], $_SESSION['params'])) {
							$product_id = explode(',', $row['product_id']);
							foreach ($product_id as $row2) {
								if (!in_array($row2, $current_product)) $count++;
							}
							if ($count) $row['count'] = '+' . $count;
						}
					}
				}
				if ($filters):
					$params2 = $this->paramsModel->find([
						'type'  => 'rows',
						'where' => "tb.active='1' AND tb.sub IS NULL",
						'group' => "tb.id",
						'order' => "tb.`sort` ASC, tb_lang.`name` ASC, tb.id DESC",
					]);
					$params = array_merge($params2, $params);
				endif;
			} else {
				// Если не нужно подсчитывать кол-во товаров для фильтра
				// а затем получаем Параметры по этим id параметров
				if (empty($return['params'])) $return['params'] = 0;
				$params = $this->paramsModel->find([
					'type'   => 'rows',
					'where'  => 'tb.id IN(' . $return['params'] . ') ',
					'orders' => 'tb.sort ASC, tb.id DESC',
				]);
			}
			if (count($params) != 0) {
				$max_price = $this->db->row("SELECT MAX(price) AS `max`, MIN(price) as `min` FROM `price` LEFT JOIN product ON product.id=price.product_id WHERE product.active='1' AND price.price > 0", [1]);
				$price[2] = ceil($max_price['max'] / 5) * 5;
				foreach ($params as &$param) {
					if (in_array($param['id'], $_SESSION['params']) OR in_array($param['url'], $_SESSION['params'])) $param['checked'] = true;
				}
				$price[0] = (!isset($priceRange[0])) ? ceil($max_price['min']) : (int)$priceRange[0];
				$price[1] = (!isset($priceRange[1])) ? ceil($max_price['max']) : (int)$priceRange[1];
				$currency = $this->db->row("SELECT * FROM currency WHERE base='1'");
				$return['content'] = $this->view->Render('cat_filters_ajax.phtml', [
					'params_url' => $params_url,
					'params'     => $params,
					'price'      => $price,
					'currency'   => $currency,
					'translate'  => $this->translation,
					'settings'   => $this->settings,
					'count'      => $return['count'],
				]);
				if ($this->settings['cache'] == 1) {
					$file = $cache_file;
					$fp = fopen($file, "w");
					fwrite($fp, $return);
					fclose($fp);
				}
			}

			return $return;
		}

		/**
		 * @param $inParams
		 * @return mixed
		 * Метод возвращает список id товаров и фильров закрепленных за этим (+дочерними) каталогом
		 */
		public function getParamsAndProducts($inParams)
		{
			$return = [];
			$whereProducts = "";
			// TODO добавить проверку на включенность фильтра
			$this->db->query("CREATE TEMPORARY TABLE `" . $this->tmpTableFilters . "` AS (
												SELECT parp.product_id, parp.params_parent_id , parp.params_id 
												FROM product_catalog prc 
												LEFT JOIN params_product parp ON prc.product_id = parp.product_id
												LEFT JOIN params ON parp.params_id = params.id
												WHERE prc.catalog_id IN (" . $inParams . " ) AND parp.params_id IS NOT NULL AND params.active = '1'
											)");
			$return['params'] = $this->db->cell("SELECT CONCAT( GROUP_CONCAT(DISTINCT params_parent_id ), ',', GROUP_CONCAT(DISTINCT params_id)  )  AS params FROM " . $this->tmpTableFilters);
			if (count($this->idParamsUrl) > 0) $whereProducts = "WHERE params_id IN (" . implode(',', $this->idParamsUrl) . ") ";
			$return['products'] = $this->db->cell("SELECT GROUP_CONCAT(DISTINCT product_id) AS products FROM " . $this->tmpTableFilters . " " . $whereProducts);
			$return['count'] = $this->db->cell("SELECT COUNT(DISTINCT product_id) AS products FROM " . $this->tmpTableFilters . " " . $whereProducts);

			return $return;
		}
	}

















