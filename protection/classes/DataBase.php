<?php class DataBase
{
	public $sql = '';
	public $params_db = array();

	public function __construct()
	{
		return false;
	}

	private function __clone()
	{
		return false;
	}

	public function getInstance($registry)
	{
        $this->registry = $registry['registry'];
        $this->settings = $registry['settings'];
        $this->params = $registry['params'];
        $this->db = $registry['db'];
        $model = str_replace('Controller', '', get_called_class());
        $this->table = $model::$table;
        $this->name = $model::$name;
        $this->language = $this->db->rows("SELECT * FROM language ORDER BY `id` ASC");
        $this->translation = $registry['translation'];
        $this->sets = ['settings' => $this->settings, 'registry' => $this->registry, 'params' => $this->params, 'db' => $this->db, 'translation' => $this->translation];
        if (isset($model::$lang_table)) $this->lang_table = $this->registry['key_lang'] . $model::$lang_table;
	}

	/*
	 * Генерируем SELECT sql
	 * select- поля которые нужно выбрать в виде массива
	 * where- условие выбора в виде массива array(поле=>array(знак=,значение))
	 * limit-лимит записей в виде массива array(начальная запись, количество)
	 * order- сортировка array (поле=>направление)
	 * join- массив join
	 * debug- если true то в свойство класса sql записывается текущий sql запрос и в свойство params записываются параметры
	 *
	 * */
	protected function select($sql)
	{
		if (isset($sql['table'])) $this->table = $sql['table'];
		$join = '';
		if ($this->db->row("SHOW TABLES LIKE '" . $this->registry['key_lang'] . "_" . $this->table . "'")) {

		    if (isset($this->registry[PathToTemplateAdmin]) && $this->registry[PathToTemplateAdmin] != '') $lang = $this->registry['key_lang_admin'];
			else $lang = $this->registry['key_lang'];

			$join = "LEFT JOIN `" . $lang . "_" . $this->table . "` tb_lang ON tb.id=tb_lang." . $this->table . "_id ";
		}
		$sql = $this->checkSql($sql);
		$where = $this->checkWhere($sql['where']);
		$join .= $sql['join'];
		$query = "SELECT " . $sql['select'] . " FROM `" . $this->table . "` `tb` " . $join . " " . $where['query'] . " " . $sql['group'] . " " . $sql['having'] . " " . $sql['order'] . " " . $sql['limit'];
		if ($sql['debug']) {
			$this->sql = $query;
			$this->params_db = $where['params'];
		}
		if (isset($sql['type']) && $sql['type'] == 'count') return $this->db->query($query, $where['params']);
		elseif ((isset($sql['type']) && $sql['type'] == 'rows') || isset($sql['paging']) && is_array($sql)) return $this->db->rows($query, $where['params']);
		elseif (isset($sql['type']) && $sql['type'] == 'rows_keys') return $this->db->rows($query, $where['params']);
		else return $this->db->row($query, $where['params']);
	}

	protected function query($sql, $insert_id = false)
	{
		if (isset($sql['table'])) $this->table = $sql['table'];
		$where = $this->checkWhere($sql);
		$join = $sql['join'];
		$query = $where['query'];
		if ($sql['debug']) {
			$this->sql = $query;
			$this->params_db = $where['params'];
		}
		if ($insert_id) return $this->db->insert_id($query, $where['params']);
		else return $this->db->query($query, $where['params']);
	}

	/*
	* Добавляем join к запросу.
	* type - тип нужного join
	* tables - массив таблиц которые будут связываться
	* pseudoName - псевдонимы для таблиц
	* row - поля по которым производится связть
	* */
	protected function addJoin($type = ' INNER ', $tables, $pseudoName, $rows)
	{
		if ($type !== '' && is_array($tables) && is_array($rows)) {
			$t0 = $tables[0];
			$t1 = $tables[1];
			if (is_array($pseudoName) && count($pseudoName) > 0) {
				$t0 = $pseudoName[0];
				$t1 = $pseudoName[1];
			}
			return $type . " JOIN `" . $tables[1] . "` `" . $pseudoName[1] . "` ON `" . $t0 . "`.`" . $rows[0] . "`=`" . $t1 . "`.`" . $rows[1] . "`";
		} else return false;
	}

	/*
	 * Добавляем несколько join к запросу
	 * join - массив массивов join array(join,join)
	 * */
	protected function addJoinArray($join)
	{
		$res = [];
		if (is_array($join)) foreach ($join as $j) $res[] = $this->addJoin($j[0], $j[1], $j[2], $j[3]);
		return $res;
	}

	protected function checkWhere($query)
	{
		if ($query != '') {
			$sep = '__';
			$change = '=?' . $sep;
			$out = preg_replace('/\:.+' . $sep . '/Uis', $change, $query); // перебиваем все на знаки равно и вопроса
			$out = str_replace($sep, '', $out);
			// это для удобства, может тебе понадобится
			preg_match_all('/' . $sep . '(.*)' . $sep . '/Uis', $query, $array); // регулярка на полный шаблон от @@ до @@;
			$full_matches = $array[1]; // массив с найдеными значениями
			$x = [];
			foreach ($full_matches as $key => $value) $x[] = explode(':=', $value); // я не помню нужны тебе были эти значения (названия перед двоеточием) на всякий случай массив с ними.
			$params = [];
			foreach ($x as $key => $value) array_push($params, $value[1]);
			//у тебя как-то так было там, короче в строку string собрались все значения после двоеточия, ты их уже там по-свойски)) сам разберешься))
			// это для удобства, может тебе понадобится
			return ['query' => $out, 'params' => $params];
		} else return ['query' => NULL, 'params' => NULL];
	}

	protected function checkSql($sql)
	{
		if (!isset($sql['select'])) $sql['select'] = '*';
		if (!isset($sql['where']) || (isset($sql['where']) && $sql['where'] == '')) $sql['where'] = '';
		else $sql['where'] = 'WHERE ' . $sql['where'];
		if (!isset($sql['order'])) $sql['order'] = '';
		else $sql['order'] = 'ORDER BY ' . $sql['order'];
		if (!isset($sql['group'])) $sql['group'] = '';
		else $sql['group'] = 'GROUP BY ' . $sql['group'];
		if (!isset($sql['limit'])) $sql['limit'] = '';
		elseif ($sql['limit'] != '') $sql['limit'] = 'LIMIT ' . $sql['limit'];
		if (!isset($sql['having'])) $sql['having'] = '';
		elseif ($sql['having'] != '') $sql['having'] = 'HAVING ' . $sql['having'];
		if (!isset($sql['join'])) $sql['join'] = '';
		if (!isset($sql['debug'])) $sql['debug'] = true;
		return $sql;
	}
}