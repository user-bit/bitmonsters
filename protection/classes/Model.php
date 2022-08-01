<?php class Model extends DataBase
{
    static $table;
    static $name;

    public function __construct($registry, $table = false)
    {
        parent::getInstance($registry);
        if ($table) $this->table = $table;
    }

    function count($where = '')
    {
        return intval($this->db->cell("SELECT count(*) FROM " . $this->table . (!empty($where) ? (" WHERE " . $where) : "")));
    }

    /**
     * @param $array
     * @param $table
     * @param bool $checkCol
     * @return array
     * Метод подготавливает массив к запросу и выкидывает из массива те ячейки, которых нет в оперируемой таблице
     */
    public function prepareColValues($array, $table, $checkCol = true)
    {
        $return = [];
        if ($checkCol) {
            foreach ($array as $k => $item) {
                if (!$this->checkColumnInTable($k, $table)) unset($array[$k]);
            }
        }
        foreach ($array as $k => &$col) {
            $return['columns'] .= '`' . $k . '`=?';
            $return['values'][] = $col;
            if (!isLast($array, $k)) $return['columns'] .= ',';
        }
        return $return;
    }


    /**
     * @param $array
     * @param bool $table
     * @param bool $checkCol
     * @return mixed
     * Записывает в базу новое значение
     * Метод принимает массив в виде 'имя ячейки' => 'значение'
     */
    public function insert($array, $table = false, $checkCol = true)
    {
        // определим какую таблицу нам нужно использовать
        if (!$table) $table = $this->table;
        $colsValues = $this->prepareColValues($array, $table, $checkCol);

        return $this->db->insert_id("INSERT INTO `" . $table . "` SET " . $colsValues['columns'], $colsValues['values']);
    }

    /**
     * @param $column
     * @param $table
     * @return bool
     * Мктод проверяет наличие ячейки в таблице
     */
    public function checkColumnInTable($column, $table)
    {
        $schema = $this->getSchemaTable($table);
        return isset($schema[$column]);
    }

    public function getSchemaTable($table)
    {
        $database = $this->registry['db_settings']['name'];
        if (isset($this->schema[$table])) return $this->schema[$table];
        $schema = $this->db->rows("SELECT COLUMN_NAME as name FROM  INFORMATION_SCHEMA.COLUMNS  WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? ", [$database, $table]);
        $this->schema[$table] = containArrayInHisId($schema, 'name');
        return $this->schema[$table];
    }

    /**
     * @param $arrWheres
     * @return array|bool
     * Метод подготавливает условие where из массива
     * поскольку условий может быть несколько, структура формирования следующая:
     * Оператором между несколькими аргументами всегда является AND, т.к этот метод не расчитан на сложные операции
     * [
     *      [
     *           'id', //ячейка таблицы
     *           '=', // оператор
     *           '7', // значение
     *      ]
     * ]
     */
    public function prepareWhere($arrWheres)
    {
        if (!$arrWheres) return false;
        $return = [
            'where' => '',
            'values' => []
        ];
        foreach ($arrWheres as $num => $arrWhere) {
            $return['where'] .= '`' . $arrWhere[0] . '` ' . $arrWhere[1] . ' ?';
            if (!isLast($arrWheres, $num)) $return['where'] .= ' AND ';
            $return['values'][] = $arrWhere[2];
        }
        return $return;
    }


    /**
     * @param $array
     * @param $where
     * @param bool $table
     * @param bool $checkCol
     * @return mixed
     *
     * Обвноляет значение поля в базе
     * Метод принимает массив в виде 'имя ячейки' => 'значение'
     * в where указываете уловие обновления, при чем можно передать массив аргуметов, если вы хотите сделать подготовленный запрос.
     */
    public function update($array, $where, $table = false, $checkCol = true)
    {
        // определим какую таблицу нам нужно использовать
        if (!$table) $table = $this->table;
        $colsValues = $this->prepareColValues($array, $table, $checkCol);
        if (is_array($where)) {
            $preparedWhere = $this->prepareWhere($where);
            $where = $preparedWhere['where'];
            $colsValues['values'] = array_merge($colsValues['values'], $preparedWhere['values']);
        }
        return $this->db->query("UPDATE `" . $table . "` SET " . $colsValues['columns'] . " WHERE " . $where, $colsValues['values']);
    }

    public function changestate($table = '')
    {
        if ($table == '') $table = $this->table;
        $message = '';
        if (isset($this->registry['access'])) $message = $this->registry['access'];
        else {
            if (isset($_POST['id']) && is_array($_POST['id'])) {
                $count = count($_POST['id']) - 1;
                for ($i = 0; $i <= $count; $i++) {
                    $sub = $this->db->row("SHOW COLUMNS FROM `{$table}` WHERE `Field`='sub'");
                    if ($sub) $child = $this->db->rows("SELECT * FROM {$table} WHERE `sub`=?", [$_POST['id'][$i]]);
                    else $child = null;
                    if (count($child) < 1) {
                        $row = $this->db->row("SELECT `active` FROM `$table` WHERE `id`=?", [$_POST['id'][$i]]);
                        if ($row['active'] == 1) {
                            $this->db->query("UPDATE `$table` SET `active`=? WHERE `id`=?", [0, $_POST['id'][$i]]);
                            $data['active'] = '<div class="selected-status status-off"><a> Выкл. </a></div>';
                        } else {
                            $this->db->query("UPDATE `$table` SET `active`=? WHERE `id`=?", [1, $_POST['id'][$i]]);
                            $data['active'] = '<div class="selected-status status-onn"><a> Вкл. </a></div>';
                        }
                    } else $message .= 'Некоторые записи содержат непустые дочерние пункты,и не были удалены.<br/>';
                }
                $message .= messageAdmin($message . '<br/>Состояние изменено');
            }
        }
        return $message;
    }

    public function checkModule($module = null)
    {
        if (is_null($module)) $module = $this->table;
        return $this->db->row("SELECT id FROM modules WHERE `controller`=? AND `hidden`=?", [$module, 0]);
    }

    public function delete($table = '')
    {
        $meta = new Meta($this->sets);
        if ($table == '') $table = $this->table;
        $ph = $this->db->cell('select photo from modules where controller=?', [$table]);
        $message = '';
        if (isset($this->registry['access'])) $message = $this->registry['access'];
        else {
            if (isset($_POST['id']) && is_array($_POST['id'])) {

                $count = count($_POST['id']) - 1;
                for ($i = 0; $i <= $count; $i++) {
                    $sub = $this->db->row("SHOW COLUMNS FROM `{$table}` WHERE `Field`='sub'");
                    if ($sub) $child = $this->db->rows("SELECT * FROM {$table} WHERE `sub`=?", [$_POST['id'][$i]]);
                    else $child = NULL;
                    if (count($child) < 1) {
                        // Delete meta data
                        $meta->delete_meta($table, $_POST['id'][$i]);
                        $this->db->query("DELETE FROM `" . $table . "` WHERE `id`=?", [$_POST['id'][$i]]);
                        if ($ph == 1) {
                            $dir = Dir::createDir($_POST['id'][$i], '', $table);

                            if ($dir[0] != '') Dir::removeDir($dir[0]);
                        }
                    } else $message .= 'Некоторые записи содержат непустые дочерние пункты,и не были удалены.<br/>';
                }
                $message .= messageAdmin($message . '<br/>Записи успешно удалены');
            } elseif (isset($this->params['delete']) && $this->params['delete'] != '') {
                $sub = $this->db->row("SHOW COLUMNS FROM `{$table}` WHERE `Field`='sub'");
                if ($sub) $child = $this->db->rows("SELECT * FROM {$table} WHERE `sub`=?", [$this->params['delete']]);
                else $child = NULL;
                if (count($child) < 1) {
                    $id = $this->params['delete'];
                    if ($ph == 1) {
                        $dir = Dir::createDir($id, '', $table);
                        if ($dir[0] != '') Dir::removeDir($dir[0]);
                    }
                    // Delete meta data
                    $meta->delete_meta($table, $id);
                    if ($this->db->query("DELETE FROM `" . $table . "` WHERE `id`=?", [$id])) $message = messageAdmin('Запись успешно удалена');
                } else $message = messageAdmin('Запись содержит непустые дочерние пункты,и не может быть удалена', 'error');
            } elseif (isset($this->params['return']) && $this->params['return'] != '') {
                $sub = $this->db->row("SHOW COLUMNS FROM `{$table}` WHERE `Field`='sub'");
                if ($sub) $child = $this->db->rows("SELECT * FROM {$table} WHERE `sub`=?", [$this->params['return']]);
                else $child = NULL;
                if (count($child) < 1) {
                    $id = $this->params['return'];
                    if ($ph == 1) {
                        $dir = Dir::createDir($id, '', $table);
                        if ($dir[0] != '') Dir::removeDir($dir[0]);
                    }
                    // Delete meta data
                    $meta->delete_meta($table, $id);
                    if ($this->db->query("DELETE FROM `" . $table . "` WHERE `id`=?", [$id])) $message = messageAdmin('Заказ успешно отменен');
                } else $message = messageAdmin('Запись содержит непустые дочерние пункты,и не может быть удалена', 'error');
            }
        }
        return $message;
    }

    public function get_columns($row, $table, $fk = '')
    {
        $query = "";
        $fields = $this->db->rows("SHOW COLUMNS FROM $table");
        foreach ($fields as $row2) if ($row2['Field'] != 'id' && $row2['Field'] != $fk) {
            if ($row2['Field'] == 'link') $row[$row2['Field']] .= "-" . time();
            elseif ($row2['Field'] == 'name') $row[$row2['Field']] .= "-[copy]";
            $query .= "{$row2['Field']}='" . $row[$row2['Field']] . "',";
        }
        return $query = substr($query, 0, strlen($query) - 2);
    }

    public function duplicate($row, $table)
    {
        if (isset($this->registry['access'])) return $this->registry['access'];
        $message = '';
        $fk = $table . "_id";
        $query = $this->get_columns($row, $table);
        if ($query != '') {
            $insert_id = $this->db->insert_id("INSERT INTO `$table` SET " . $query);
            if ($this->db->row("SHOW TABLES LIKE '" . $this->registry['key_lang_admin'] . "_" . $table . "'")) {
                $res = $this->db->rows("SELECT * FROM language");
                foreach ($res as $lang) {
                    $tb = $lang['language'] . "_" . $table;
                    $query = $this->get_columns($row, $tb, $fk);
                    if ($query != '') $this->db->query("INSERT INTO `$tb` SET $fk='$insert_id'," . $query);
                }
            }
            header("Location: /" . PathToTemplateAdmin . "/$table/edit/" . $insert_id);
        }
        return $message;
    }

    public function find($param)
    {
        if (isset($param['paging']) && is_array($param)) {
            if (is_numeric($param['paging'])) $size_page = $param['paging'];
            elseif (isset($this->settings['paging_admin_' . $this->table], $this->registry[PathToTemplateAdmin])) $size_page = $this->settings['paging_admin_' . $this->table];
            elseif (isset($this->settings['paging_' . $this->table])) $size_page = $this->settings['paging_' . $this->table];
            else $size_page = default_paging;
            $start_page = 0;
            $cur_page = 0;
            $paging = '';
            $param2 = $param;
            $param['type'] = 'count';
            $count = $this->select($param);
            //для пагинации - выбираем модуль
            if (($this->params['topic'] == 'news' || $this->params['topic'] == 'catalog' || $this->params['topic'] == 'special-offers')) {
              $this->params['page'] = $this->params['this_page'];
            }

            if (isset($this->params['page'])) {
                $cur_page = $this->params['page'];
                if ($cur_page < 2) {
                    header('Location: ' . StringClass::getUrl2('page'));
                    exit();
                }
                $start_page = ($cur_page - 1) * $size_page; //номер начального элемента
            }
            if ($count > $size_page) {
                $class = new Paging($this->registry, $this->params);
                $paging = $class->MakePaging($cur_page, $count, $size_page); //вызов шаблона для постраничной навигации
            }
            $param2['limit'] = $start_page . ',' . $size_page;
            return ['list' => $this->select($param2), 'paging' => $paging['paging'], 'pagingMeta' => $paging, 'count' => $count];
        }elseif (isset($param['paging_ajax']) && is_array($param)){
            $size_page = $param['paging_ajax'];
            $cur_page = 0;
            $param2 = $param;
            $param['type'] = 'count';
            $count = $this->select($param);
            $start_page = 0;

            if (isset($_POST['this_page'])) {
                $start_page = ($_POST['this_page'] - 1) * $size_page; //номер начального элемента
            }
            if ($count > $size_page) {
                $class = new Paging($this->registry, $this->params);
                $paging = $class->MakePagingAjax($_POST['this_page'], $count, $size_page); //вызов шаблона для постраничной навигации
            }
            $param2['limit'] = $start_page . ',' . $size_page;
            return ['list' => $this->select($param2), 'paging' => $paging['paging'], 'count' => $count];
        }elseif (is_numeric($param)) return $this->select(['where' => '__tb.id:=' . $param . '__']);
        elseif (is_string($param)) return $this->select(['where' => '__tb.link:=' . $param . '__']);
        else return $this->select($param);
    }

    public function select_one_lang($param)
    {
        return $this->db->rows("SELECT * FROM " . $this->table . " tb LEFT JOIN " . $this->table . "_lang tb_lang ON tb_lang." . $this->table . "_id=tb.id WHERE tb_lang.lang='" . $this->registry['key_lang_admin'] . "';");
    }

    public function getPage($id, $index = '*')
    {
        if (is_numeric($id)) $WHERE = 'tb1.id=?';
        else $WHERE = 'tb1.link=?';
        $page = $this->db->row("SELECT " . $index . " FROM `pages` tb1 LEFT JOIN " . $this->registry['key_lang'] . "_pages tb2 ON tb1.id=tb2.pages_id WHERE " . $WHERE . " AND tb1.active=?", [$id, 1]);
        $page['type'] = 'pages';
        return $page;
    }

    public function breadcrumbAdmin()
    {
        if (($this->params['action'] === 'edit' || $this->params['action'] === 'add'))
            return '<a href="' . Links::getAdminURl(PathToTemplateAdmin . '/' . strtolower($this->params['controller'])) . '"><svg><use xlink:href="/resource/administrator-cms/images/svg/svg-sprite.svg#ar-left"></use></svg>' . $this->params['module'] . '</a>';
        return '';
    }

    public function breadcrumbs($links, $view, $page_n)
    {
        if (count($links) > 0) {
            array_unshift($links, ['link' => '/', 'name' => $this->sets['translation']['main']]);
            foreach ($links as $i => $link) {
                if ($link['link'] != '/') $uri = $link['link'];
                else $uri = '/';
                $request = substr($_SERVER['REQUEST_URI'], 1);
                if ($uri == $request) {
                    $links[$i]['link'] = '#';
                    $links[$i]['class'] = 'active';
                } else {
                    $links[$i]['link'] = $uri;
                    $links[$i]['class'] = '';
                }
            }
            $template = 'layout/breadcrumbs.phtml';
            $path = 'resource/' . theme . '/' . $template;
            if (file_exists($path)) {
                return $view->Render($template, compact('links', 'page_n'));
            } else {
                return false;
            }
        }
    }

    /**
     * @param $catrow
     * @param StringClass $product_name
     * @return array
     * Метод для получения цепочки категорий!
     */
    public function getBreadCat($catrow, $product_name = '')
    {
        if (!isset($this->catalog)) $this->catalog = new Catalog($this->sets);
        // Если передали id категории, получим ее всю, нам нужна вся категория
        $chain = [];
        if (is_numeric($catrow)) {
            $catrow = $this->catalog->find((int)$catrow);
        }
        $return = [];

        if ($catrow['id']) {
            $chain = $this->getBreadSubChain($catrow['id'], 'catalog');
        }
        // Добавим в url каталога, url контроллера
        if (!empty($chain)) {
            foreach ($chain as &$item) {
                $item['link'] = 'catalog/' . $item['link'];
            }
        }
        $return = array_merge($return, $chain);

        if ($product_name) {
            $return[] = ['name' => $product_name];
        }

        return $return;
    }

    /**
     * @param $id
     * @param StringClass $table
     * @param array $links
     * @return array
     * получить цепочку вложенных элементов
     */
    public function getBreadSubChain($id, $table = 'menu', $links = [])
    {
        $menu = $this->db->row("SELECT * FROM {$table}
			 LEFT JOIN {$this->registry['key_lang']}_{$table} ON {$this->registry['key_lang']}_{$table}.{$table}_id = {$table}.id
			 WHERE {$table}_id=?", [$id]);
        $links [] = $menu;
        if ($menu['sub'] != null) return $this->getBreadSubChain($menu['sub'], $table, $links);

        $links = array_reverse($links);
        return $this->excludeNonActiveLinks($links);
    }

    /**
     * @param $links метод убират из списка, выключенные каталоги
     * Почему в getBreadSubChain не делает условие на активность сразу?
     * потому что цепочку крошек получаем в обратном порядке, и если где то в конце будет не активный
     * каталог, то он ничего не найдет вообще, поэтому всех выберем, а потом просто уберем из списка выключенные
     */
    public function excludeNonActiveLinks($links = [])
    {
        if (!$links) return $links;
        foreach ($links as $k => $link) {
            if ((int)$link['active'] === 0) unset($links[$k]);
        }
        return $links;
    }

    public function checkAccess($action, $module)
    {
        /*Проверка доступа модулей в админк
          '000'-off;
          '100'-read;
          '200'-read/edit;
          '300'-read/del;
          '400'-read/add;
          '500'-read/edit/del;
          '600'-read/edit/add;
          '700'-read/del/add;
          '800'-read/edit/del/add;
         */
        if ($_SESSION['admin']['id'] == 1) return true;
        $row = $this->db->row("SELECT m.`permission` FROM `moderators_permission` m LEFT JOIN moderators mm ON m.moderators_type_id=mm.type_moderator LEFT JOIN modules mmm ON mmm.id=m.module_id WHERE mmm.controller=? AND mm.id=?", [$module, $_SESSION['admin']['id']]);
        if ($row['permission'] == 000) return false;
        elseif ($action == 'delete' && ($row['permission'] != 500 && $row['permission'] != 300 && $row['permission'] != 700 && $row['permission'] != 800)) return false;
        elseif (($action == 'edit' || $action == 'update') && ($row['permission'] != 200 && $row['permission'] != 500 && $row['permission'] != 600 && $row['permission'] != 800)) return false;
        elseif (($action == 'add' || $action == 'duplicate') && ($row['permission'] != 400 && $row['permission'] != 600 && $row['permission'] != 700 && $row['permission'] != 800)) return false;
        return true;
    }

    public function checkUrl($tb, $link, $id)// Проверка уникальности URL
    {
        if ($this->db->row("SELECT id from `" . $tb . "` WHERE link!='' AND link=? AND id!=?", [$link, $id])) $this->db->query("UPDATE `" . $tb . "` set link=? WHERE id=?", [$link . '-' . $id, $id]);
        else $this->db->query("UPDATE `" . $tb . "` set link=? WHERE id=?", [$link, $id]);
    }

    public function currency()
    {
        return $this->db->row("SELECT * FROM `currency` WHERE  currency.`lang`='" . $_SESSION['key_lang'] . "' AND currency.`current_id`= '" . $_SESSION['currency']['current_id'] . "'");
    }

    function right_menu_admin($vars)
    {
        $this->view = new View($this->registry);
        if ($_SESSION['admin']['type'] == 1) $vars['menu'] = $this->db->rows("SELECT * FROM subsystem tb");
        else $vars['menu'] = $this->db->rows("SELECT tb.* FROM subsystem tb LEFT JOIN moderators_permission tb2 ON tb.id=tb2.subsystem_id LEFT JOIN modules m ON m.id=tb2.module_id WHERE tb2.moderators_type_id=? AND tb2.permission!=? AND m.controller=? GROUP BY tb.id", [$_SESSION['admin']['type'], '000', $vars['action']]);
        foreach ($vars['menu'] as $i => $row) $vars['menu'][$i]['link'] = '/' . PathToTemplateAdmin . '/' . $vars['action'] . '/subsystem/' . $row['name'];
        if (isset($vars['menu2'])) $vars['menu'] = array_merge($vars['menu'], $vars['menu2']);
        return $this->view->Render('layout/right_menu.phtml', $vars);
    }

    public function subsystemAction($right_menu = [])
    {
        $class_name = ucfirst($this->params['subsystem']) . 'Controller';
        $class = new $class_name($this->registry, $this->params);
        $vars['message'] = '';
        $vars['subsystem'] = $this->params['subsystem'];
        $vars['action'] = $this->table;
        $row = $this->db->row("SELECT id,name,controller FROM modules WHERE controller='" . $this->table . "'");
        $modules_id = $row['id'];
        if (isset($this->params['delsubsystem']) || isset($_POST['delete'])) $vars['message'] = $class->delete();
        elseif (isset($_POST['update'])) $vars['message'] = $class->save();
        elseif (isset($_POST['update_close'])) $vars['message'] = $class->save();
        elseif (isset($this->params['addsubsystem'])) $vars['message'] = $class->add($modules_id);
        $vars['where'] = "WHERE `modules_id`='" . $modules_id . "'";
        $vars['modules_id'] = $modules_id;
        $vars['modules_name'] = $row['name'];
        $data['position'] = $row['controller'];
        $vars['path'] = "/subsystem/" . $this->params['subsystem'];
        if (count($right_menu) == 0) $right_menu = ['action' => $this->table, 'name' => $this->name, 'sub' => $this->params['subsystem']];
        else $right_menu = ['action' => $this->table, 'name' => $this->name, 'sub' => $this->params['subsystem'], 'menu2' => $right_menu];
        $data['right_menu'] = $this->right_menu_admin($right_menu);
        $data['content'] = $class->subcontent($vars);
        return $data;
    }

    public function insert_post_form($text)
    {
        $this->db->query("INSERT INTO `feedback` SET `text`=?", [$text]);
    }

    public function active($id, $tb, $tb2, $liqpay = 0)
    {
        $data = [];
        $data['message'] = '';
        if (!$this->checkAccess('edit', $tb)) $data['message'] = messageAdmin('Отказано в доступе', 'error');
        $id = str_replace("active", "", $id);
        if ($tb == 'tasks') {
            $tasks = new Tasks($this->sets);
            $tasks->active($id);
        }
        if ($tb == 'info') $tb = 'infoblocks';
        if ($tb2 != 'undefined') $tb = $tb2;
        if ($data['message'] == '') {
            if ($tb == 'modules') {
                $row = $this->db->row("SELECT `hidden` FROM `$tb` WHERE `id`=?", [$id]);
                if ($row['hidden'] == 0) {
                    $this->db->query("UPDATE `$tb` SET `hidden`=? WHERE `id`=?", [1, $id]);
                    $data['active'] = '<div class="selected-status status-off">Выкл</div>';
                } else {
                    $this->db->query("UPDATE `$tb` SET `hidden`=? WHERE `id`=?", [0, $id]);
                    $data['active'] = '<div class="selected-status status-on">Вкл</div>';
                }
            } else {
                $row = $this->db->row("SELECT `active` FROM `$tb` WHERE `id`=?", [$id]);
                if ($row['active'] == 1) {
                    $this->db->query("UPDATE `$tb` SET `active`=? WHERE `id`=?", [0, $id]);
                    $dopclass = '';
                    if ($tb == 'catalog') $dopclass = "catalog-status";
                    $data['active'] = '<div class="' . $dopclass . ' selected-status status-off">Выкл</div>';
                    if ($tb == 'catalog') {
                        $rows = $this->db->rows("SELECT * FROM `product_catalog` WHERE `catalog_id`=?", [$id]);
                        foreach ($rows as $row) {
                            $multicatalog = $this->db->row("SELECT COUNT(`catalog_id`) as count  FROM `product_catalog` WHERE `product_id`=?", [$row['product_id']]);

                            if ($multicatalog['count'] == 1) $this->db->query("UPDATE `product` SET `active`=? WHERE `id`=?", [0, $row['product_id']]);
                        }
                    }
                } else {
                    $this->db->query("UPDATE `$tb` SET `active`=? WHERE `id`=?", [1, $id]);
                    $dopclass = '';
                    if ($tb == 'catalog') $dopclass = "catalog-status";
                    $data['active'] = '<div class="' . $dopclass . ' selected-status status-on">Вкл</div>';
                    if ($tb == 'catalog') {
                        $rows = $this->db->rows("SELECT * FROM `product_catalog` WHERE `catalog_id`=?", [$id]);
                        foreach ($rows as $row) {
                            $multicatalog = $this->db->row("SELECT COUNT(`catalog_id`) as count  FROM `product_catalog` WHERE `product_id`=?", [$row['product_id']]);
                            if ($multicatalog['count'] == 1) $this->db->query("UPDATE `product` SET `active`=? WHERE `id`=?", [1, $row['product_id']]);
                        }
                    }
                }
            }
            $data['message'] = messageAdmin('Данные успешно сохранены');
        }
        return $data;
    }

    public function sortTable($arr, $tb, $tb2)
    {
        $data = [];
        $data['message'] = '';
        if (!$this->checkAccess('edit', $tb)) $data['message'] = messageAdmin('Отказано в доступе', 'error');
        if ($tb2 != 'undefined') $tb = $tb2;
        if ($data['message'] == '') {
            $arr = str_replace("sort", "", $arr);
            preg_match_all("/=(\d+)/", $arr, $a);
            foreach ($a[1] as $pos => $id) {
                $pos2 = $pos + 1;
                $this->db->query("update `$tb` set `sort`=? WHERE `id`=?", [$pos2, $id]);
            }
            $data['message'] = messageAdmin('Данные успешно сохранены');
        }
        return $data;
    }

    function check_for_delete($subsystem_id, $tb, $group_id)
    {

        if ($this->db->query("DELETE tb.* FROM `" . $tb . "` tb LEFT JOIN `moderators_permission` mp ON mp.module_id=tb.modules_id WHERE mp.moderators_type_id=? AND `id`=? AND (permission='300' OR permission='500' OR permission='700' OR permission='800') AND mp.subsystem_id='0'", [$group_id, $subsystem_id])) return messageAdmin('Запись успешно удалена');
        else return messageAdmin('Ошибка в правах доступа!', 'error');
    }

    function check_for_update($subsystem_id, $tb, $group_id)
    {
        $row = $this->db->row("SELECT tb.* FROM `" . $tb . "` tb LEFT JOIN `moderators_permission` mp ON mp.module_id=tb.modules_id WHERE (mp.moderators_type_id=? AND `id`=? AND (permission='200' OR permission='500' OR permission='600' OR permission='800') AND mp.subsystem_id='0') OR (tb.modules_id='' AND `id`=?)", [$group_id, $subsystem_id, $subsystem_id]);
        return $row;
    }

    /**
     * @param $id
     * @param StringClass $type - favorites | viewed
     */
    function add_fav($id, $type = 0)
    {
        if ($type === 0) {
            $where = "";
            if (isset($_SESSION['user_id'])) $where = ",user_id='{$_SESSION['user_id']}'";
            if (!$this->db->row("SELECT * FROM favorites WHERE type='$type' AND product_id='$id' AND (session_id='" . session_id() . "' " . str_replace(',', ' OR ', $where) . ")")) {
                if ($where == '') $where = ",user_id=NULL";
                $this->db->query("INSERT INTO favorites SET type='$type',product_id='$id',session_id='" . session_id() . "' $where");
            }
        } elseif ($type === 1) {
            if (!isset($_SESSION['products'][$type])) $_SESSION['products'][$type] = [];
            if (!in_array($id, $_SESSION['products'][$type])) $_SESSION['products'][$type][] = $id;
        }
    }

    /**
     * @param StringClass $where
     * @param bool $showHidden
     * @param null $table
     * @return array|bool
     * аргумент $showActive показывать выключенные
     * по умолчанию выключенные не показываются
     */
    public function getAll($select = '', $where = '', $showHidden = false, $table = false, $orderby = '')
    {
        // определим какую таблицу нам нужно использовать
        if (!$table) {
            if (!isset($this->table)) return false;
            $table = $this->table;
        }
        $whereActive = ($showHidden === false and $this->checkColumnInTable('active', $table)) ? "tb.active='1'" : "tb.id != 0";
        $order = ($this->checkColumnInTable('sort', $table)) ? 'tb.sort ASC, tb.id ASC,' : '';
        $where = $whereActive . ' ' . $where;
        if (empty($select)) $select = '*';
        return $this->find([
                'type' => 'rows',
                'table' => $table,
                'select' => $select,
                'where' => $where,
                'group' => 'tb.id',
                'order' => $order . $orderby .' tb.id DESC']
        );
    }

    public function getAllRand($where = '', $showHidden = false, $table = false)
    {
        // определим какую таблицу нам нужно использовать
        if (!$table) {
            if (!isset($this->table)) return false;
            $table = $this->table;
        }
        $whereActive = ($showHidden === false and $this->checkColumnInTable('active', $table)) ? "tb.active='1'" : "tb.id != 0";
        $where = $whereActive . ' ' . $where;

        return $this->find([
                'type' => 'rows',
                'table' => $table,
                'select' => '*',
                'where' => $where,
                'group' => 'tb.id',
                'order' => 'RAND ()']
        );
    }

    /**
     * @return mixed
     * Возвращает список модулей, использующих замену тегов блоками
     */
    public function getReplacingCodes()
    {
        return $this->db->rows("SELECT * FROM `replace_code`");
    }


    /**
     * Метод ищет дочерние элементы каталога
     * @param $id - ID нашего элемента каталога
     * @param $menu - Массив всех элементов каталона
     * @return array Массив найденных элеметов
     */
    public static function all_my_child($id, $menu)
    {
        $result = false;
        foreach ($menu as $row) {
            if ($row['sub'] == $id) {
                $result[] = $row;
            }
        }
        return $result;
    }

    public function get_lang($where = '')
    {
        return $this->db->rows_key("SELECT tb.key, lang_tb.value FROM translate tb LEFT JOIN " . $this->registry['key_lang'] . "_translate lang_tb ON lang_tb.translate_id=tb.id WHERE " . $where);
    }

    public function get_infoblock($where = '')
    {
        return $this->db->rows_key("SELECT tb.key_value, tb.value FROM " . $this->registry['key_lang'] . "_infoblocks tb WHERE infoblocks_id=" . $where);
    }

    public function get_infoblock_text($where = '')
    {
        return $this->db->rows_key("SELECT tb.key_value, tb.value FROM " . $this->registry['key_lang'] . "_infoblocks_text tb WHERE infoblocks_id=" . $where);
    }

    public function err_handler($errmsg, $linenum) {
        $date = date('Y-m-d H:i:s');
        $f = fopen('errors_import.txt', 'a');
        if (!empty($f)) {
            $err  = "$errmsg = $linenum = $date\r\n";
            fwrite($f, $err);
            fclose($f);
        }
    }
}