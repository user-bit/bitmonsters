<?php class Meta extends Model
{
    static $table = 'meta'; //Главная талица
    static $name = 'Meta-данные'; // primary key

    public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    //для доступа к классу через статичекий метод
    public static function getObject($registry)
    {
        return new self::$table($registry);
    }

    public function load_meta($tb, $link)
    {
        $link = $this->get_link($tb, $link);
        $row = $this->find((string)$link);
        if ($row) return $row;
        return array('title' => '', 'description' => '');

    }

    function get_link($tb, $link)
    {
        $row = $this->db->row("SELECT link FROM `modules` WHERE `controller`=?", array($tb));
        if ($row) {
            if ($link == '/')
                $link = '';
            if ($row['link'] != '')
                $row['link'] .= '/';
            $link = '/' . $row['link'] . $link;
            return $link;
        }
        return false;
    }

    function delete_meta($tb, $id)
    {
        if (isset($this->registry['access'])) return $this->registry['access'];

        $row = $this->db->row("SELECT * FROM `" . $tb . "` WHERE `id`=?", array($id));
        if (isset($row['link'])) {
            $link = $this->get_link($tb, $row['link']);
            if ($link != '')
                $this->db->query("DELETE FROM " . $this->table . " WHERE link=?", array($link));
        }
    }

    public function add()
    {
        if (isset($this->registry['access'])) return $this->registry['access'];
        $message = '';
        if (isset($_POST['active'], $_POST['link'], $_POST['title'], $_POST['description'], $_POST['body']) && $_POST['link'] != "") {
            $id = $this->insert(array(
                'link' => $_POST['link'],
                'type' => $_POST['type'],
                'active' => $_POST['active']
            ));
            foreach ($this->language as $lang) {
                $this->insert(array(
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'body' => $_POST['body'],
                    self::$table . '_id' => $id
                ), $lang['language'] . "_" . self::$table);
            }
            $message .= messageAdmin('Данные успешно добавлены');
        } else $message .= messageAdmin('При добавление произошли ошибки', 'error');
        return $message;
    }

    public function save()
    {
        if (isset($this->registry['access'])) return $this->registry['access'];
        $message = '';
        if (isset($_POST['save_id']) && is_array($_POST['save_id'])) {
            if (isset($_POST['save_id'], $_POST['name'], $_POST['link'])) {
                $count = count($_POST['save_id']) - 1;
                for ($i = 0; $i <= $count; $i++) {
                    $link = $_POST['link'][$i];
                    $this->checkUrl($this->table, $link, $_POST['save_id'][$i]);
                    $this->update(array(
                        'name' => $_POST['name'][$i]),
                        [[self::$table . '_id', '=', $_POST['save_id'][$i]]],
                        $this->registry['key_lang_admin'] . "_" . self::$table);
                }
                $message .= messageAdmin('Данные успешно сохранены');
            } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
        } else {
            if (isset($_POST['active'], $_POST['link'], $_POST['id'], $_POST['title'], $_POST['description'], $_POST['body'])) {
                for ($i = 0; $i < count($_POST['meta_id']); $i++) {
                    $this->db->query("UPDATE `" . $this->registry['key_lang_admin'] . "_meta_faq` SET `quest`=?, `ans`=? WHERE " . self::$table . "_id=?", array($_POST['quest'][$i], $_POST['ans'][$i], $_POST['meta_id'][$i]));
                }
                $this->update(array(
                    'link' => $_POST['link'],
                    'active' => $_POST['active'],
                    'type' => $_POST['type']),
                    [['id', '=', $_POST['id']]]);

                $this->update(array(
                    'title' => $_POST['title'],
                    'description' => $_POST['description'],
                    'body' => $_POST['body'],
                    'excerpt' => $_POST['excerpt']),
                    [[self::$table . '_id', '=', $_POST['id']]],
                    $this->registry['key_lang_admin'] . "_" . self::$table);

                $message .= messageAdmin('Данные успешно сохранены');
            } else
                $message .= messageAdmin('При сохранение произошли ошибки', 'error');
        }

        return $message;
    }

    public function save_meta($tb, $link, $title, $description)
    {
        if (isset($this->registry['access'])) return $this->registry['access'];
        $link = $this->get_link($tb, $link);
        if ($link != '') {
            $row = $this->find(array(
                    'select' => 'id, body',
                    'where' => "tb.link = '" . $link . "'"
                )
            );
            if ($row) {
                $this->db->query("UPDATE `" . $this->registry['key_lang_admin'] . "_meta` SET `title`=?, `description`=? WHERE meta_id=?", array($title, $description, $row['id']));
            } else {
                if ($title == '' && $description == '') {
                    return false;
                } else {
                    $param = array($link, 1);
                    $id = $this->db->insert_id("INSERT INTO `" . $this->table . "` SET `link`=?, `active`=?", $param);
                    $param = array($title, $description, $id);
                    foreach ($this->language as $lang) {
                        $tb = $lang['language'] . "_" . $this->table;
                        $this->db->query("INSERT INTO `$tb` SET `title`=?, `description`=?, `meta_id`=?", $param);
                    }
                }
            }
        }
    }

    public function addredirect()
    {
        if (isset($this->registry['access'])) return $this->registry['access'];

        $message = '';
        $this->db->query("INSERT INTO `redirects` SET `active`='0'");
        $message .= messageAdmin('Данные успешно сохранены');

        return $message;
    }

    public function save_redirects()
    {
        if (isset($this->registry['access'])) return $this->registry['access'];
        $message = '';
        if (isset($_POST['save_id'], $_POST['from'], $_POST['to'])) {
            $count = count($_POST['save_id']) - 1;
            for ($i = 0; $i <= $count; $i++) {
                $param = array($_POST['from'][$i], $_POST['to'][$i], $_POST['type'][$i], $_POST['save_id'][$i]);
                $this->db->query("UPDATE `redirects` SET `from`=?, `to`=?, `type`=? WHERE id=?", $param);
            }
            $message .= messageAdmin('Данные успешно сохранены');
        } else
            $message .= messageAdmin('При сохранение произошли ошибки', 'error');

        return $message;
    }

    function check_redirects()
    {
        $protocol = getProtocol();
        $link = $_SERVER['REQUEST_URI'] . '/';
        $row = $this->db->row("SELECT `to`, `type` FROM `redirects` WHERE `from`=? AND `from`!='' AND `to`!='' AND active='1'", array($link));
        if ($row) {
            if ($row['type'] == '302')
                header("HTTP/1.1 302 Found");
            else
                header("HTTP/1.1 301 Moved Permanently");

            header("Location: " . $protocol . $_SERVER['SERVER_NAME'] . $row['to']);
            exit();
        }
    }

    function save_sitemap()
    {
        $count = count($_POST['save_id']) - 1;
        for ($i = 0; $i <= $count; $i++) {
            $this->update(array(
                'active' => $_POST['active'][$i],
                'module' => $_POST['module'][$i],
                'name_file' => $_POST['name_file'][$i],
                'name_link' => $_POST['name_link'][$i],
                'priority' => $_POST['priority'][$i],
                'exclude' => $_POST['exclude'][$i]),
                [['id', '=', $_POST['save_id'][$i]]],
                'sitemap');
        }

        $message = messageAdmin('Данные успешно сохранены');
        return $message;
    }

    function generate_static_sitemap()
    {
        file_put_contents('sitemap.xml', $this->sitemap_generate());
        return messageAdmin('Данные успешно сохранены');
    }

    function sitemap_generate()
    {
        $this->languages = new Language($this->sets);
        $this->modules = new Modules($this->sets);
        $modules = [
            'menu',
            'pages',
            'catalog',
            'article',
        ];
        $languages = $this->languages->getAll();

        $sitemap = '<?xml version="1.0" encoding="UTF-8"?>
                    <linkset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
        //Put menu links
        foreach ($modules as $module) {
            $sitemap .= 'eat';
        }
        $sitemap .= '
			</linkset>';
        return $sitemap;
    }

    public function set_meta_data($data, $topic)
    {
        //Мета-данные страницы
        if ($_SERVER['REQUEST_URI'] == '')
            $_SERVER['REQUEST_URI'] = '/';
        $protocol = getProtocol();
        $_SERVER['REQUEST_URI'] = current(explode('?', $_SERVER['REQUEST_URI']));
        //echo $topic;
        if ($_SERVER['QUERY_STRING']) {
            $link = $_SERVER['REQUEST_URI'] . '?' . $_SERVER['QUERY_STRING'];
        } else {
            $link = $_SERVER['REQUEST_URI'];
        }
        // Мета данный для урла без пагинации
        if (!empty($this->params['this_page']))
            $link = strstr($link, '/page', true);

        $row = $this->find(array('where' => "__link:={$link}__ AND active='1'"));
        if ($row) {
            $data['id'] = $row['id'];
            $data['title'] = $row['title'];
            $data['description'] = $row['description'];
            $data['text'] = $row['body'];
            $data['excerpt'] = $row['excerpt'];
            $data['name_title'] = $data['name'];
        } elseif (count($data) != 0) {
            // Если массив $data (возвращается контроллером обработчиком) не пустой
            // Для генерации мета данных своего модуля, добавьте новое условие case и создайте свой метод для генерации
            // Давайте осмысленные названия методам

            $active_filter_string = '';
            $product_res = '';
            $product_price_res = '';

            // Если мы находимся в продукте, то выбираем данные параметры для формирования динмаческих заголовков
            $params_product_sex = '';
            $params_product_manufacturer = '';
            $params_product_classification = '';
            if ($this->params['topic'] == 'product') {
                $params_product_sex = $this->db->row("SELECT lang_par.name FROM params_product tb 
                                                LEFT JOIN params par ON par.id = tb.params_id
                                                LEFT JOIN " . $this->registry['key_lang'] . "_params lang_par ON lang_par.params_id = par.id
                                                WHERE par.sub=16 && tb.product_id='" . $data['id'] . "'
                                                ORDER BY par.id");
                $params_product_manufacturer = $this->db->row("SELECT lang_par.name FROM params_product tb 
                                                LEFT JOIN params par ON par.id = tb.params_id
                                                LEFT JOIN " . $this->registry['key_lang'] . "_params lang_par ON lang_par.params_id = par.id
                                                WHERE par.sub=15 && tb.product_id='" . $data['id'] . "'
                                                ORDER BY par.id");
                $params_product_classification = $this->db->row("SELECT lang_par.name FROM params_product tb 
                                                LEFT JOIN params par ON par.id = tb.params_id
                                                LEFT JOIN " . $this->registry['key_lang'] . "_params lang_par ON lang_par.params_id = par.id
                                                WHERE par.sub=17 && tb.product_id='" . $data['id'] . "'
                                                ORDER BY par.sort");
            }
            if ($topic == 'catalog')
                $data = $this->setMetaDataCatalog($data);
            elseif ($topic == 'product' && $data['comments'] != 'comment')
                $data = $this->setMetaDataProduct($data, $params_product_sex, $params_product_manufacturer, $params_product_classification);
            elseif ($data['comments'] == 'comment' && $topic == 'product')
                $data = $this->setMetaDataProductComment($data);
            else
                $data = $this->setMetaDataByName($data);
        }
        $data['title'] = str_replace('{{sitename}}', $this->settings['sitename'], $data['title']);
        $data['description'] = str_replace('{{sitename}}', $this->settings['sitename'], $data['description']);

        if (!empty($data['id']))
            $data = $this->getFaqFromSeo($data);
        return $data;
    }

    function getFaqFromSeo($data)
    {
        $res_faq_meta = $this->db->rows("SELECT tb_lang.quest, tb_lang.ans FROM meta_faq tb
                                                            LEFT JOIN " . $this->registry['key_lang'] . "_meta_faq tb_lang ON tb_lang.meta_id = tb.id
                                                            WHERE tb.meta_id=" . $data['id']);
        foreach ($res_faq_meta as $key => $item_faq) {
            $data['meta_faq'][$key] = $item_faq;
        }
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     * Генерируем мета данные для товара
     */
    public function setMetaDataProduct($data, $params_product_sex, $params_product_manufacturer, $params_product_classification)
    {
        $product = $this->find(['where' => "`type`='2' AND active='1' AND title!=''", 'type' => 'row']);
        if ($product) {
            $data['title'] = str_replace(
                ['{{name}}', '{{catalog}}', '{{sex}}', '{{manufacturer}}', '{{classification}}'],
                [$data['name'], $data['catalog'], $params_product_sex['name'], $params_product_manufacturer['name'], $params_product_classification['name']],
                $product['title']);
            $data['description'] = str_replace(
                ['{{name}}', '{{catalog}}', '{{sex}}', '{{manufacturer}}', '{{classification}}'],
                [$data['name'], $data['catalog'], $params_product_sex['name'], $params_product_manufacturer['name'], $params_product_classification['name']],
                $product['description']);
        }
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     * Генерируем мета данные для товара
     */
    public function setMetaDataProductComment($data)
    {
        $product = $this->find(['where' => "`type`='3' AND active='1' AND title!=''", 'type' => 'row']);
        if ($product) {
            $data['title'] = str_replace(
                ['{{name}}', '{{catalog}}'],
                [$data['name'], $data['catalog']],
                $product['title']);
            $data['description'] = str_replace(
                ['{{name}}', '{{catalog}}'],
                [$data['name'], $data['catalog']],
                $product['description']);
        }
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     * Генерируем мета данные для каталога
     */
    public function setMetaDataCatalog($data)
    {
        // В случае с каталогом, из catalogController передается текущий каталог, с данными о нем
        if (isset($data['sub']) && $data['sub'] != 0) {
            // получим родителей, и добавим в мета
            $name_sub = $this->get_sub_cat($data['sub']);
        }
        $catalog = $this->find(['where' => "`type`='1' AND active='1' AND title!=''", 'type' => 'row']);
        if ($catalog) {
            $data['title'] = str_replace('{{name}}', str_replace('@-@', ' ', $data['name']), $catalog['title']);
            $data['description'] = str_replace('{{name}}', str_replace('@-@', ' ', $data['name']), $catalog['description']);
        }
        return $data;
    }

    /**
     * @param $data
     * @param array $prefix
     * @param array $suffix
     * @return mixed
     * Генерируем мета данные по переданному имени, - статья, новость, страница...
     */
    public function setMetaDataByName($data)
    {
        if (empty($data['title'])) $data['title'] = $data['name'];
        if (empty($data['description'])) $data['description'] = $data['name'];
        return $data;
    }

    function get_sub_cat($sub)
    {
        if (!isset($this->catalog)) $this->catalog = Catalog::getObject($this->sets);

        $row = $this->catalog->find([
            'select' => ' tb_lang.name, tb.sub',
            'where' => "tb.`id` = '" . $sub . "' AND tb.active ='1'"
        ]);

        if ($row) {
            $namecat = $this->get_sub_cat($row['sub']);
            if ($namecat != '')
                $row['name'] = $namecat . '@-@' . $row['name'];
            return $row['name'];
        } else
            return false;
    }
}