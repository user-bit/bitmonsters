<?php class Menu extends Model
{
    static $table = 'menu';
    static $name = "Меню";

    public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    public static function getObject($registry)
    {
        return new self::$table($registry);
    }

    public function add()
    {
        $message = '';
        if (isset($_POST['active'], $_POST['link'], $_POST['name'], $_POST['title'], $_POST['description']) && $_POST['name'] != "") {
            if ($_POST['link'] == '') $link = StringClass::translit($_POST['name'], true);
            elseif (!strpos($_POST['link'], '://')) $link = StringClass::translit($_POST['link'], true);
            else $link = $_POST['link'];
            // Save meta data
            $meta = new Meta($this->sets);
            $meta->save_meta(self::$table, $link, $_POST['title'], $_POST['description']);
            if ($_POST['sub'] == 0) $sub = NULL;
            else $sub = $_POST['sub'];
            $id = $this->insert(array(
                'sub' => $sub,
                'blank' => $_POST['blank'],
                'type_id' => $_POST['type'],
                'active' => $_POST['active'],
                'bg' => $_POST['bg'],
                'code' => $_POST['code'],
            ));
            $this->checkUrl(self::$table, $link, $id);
            foreach ($this->language as $lang) {
                $this->insert(array(
                    'name' => $_POST['name'],
                    'body' => $_POST['body'],
                    self::$table . '_id' => $id
                ), $lang['language'] . "_" . self::$table);
            }
            $message .= messageAdmin('Данные успешно добавлены');
        } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
        return $message;
    }

    public function save()
    {
        $message = '';
        if (isset($this->registry['access'])) $message = $this->registry['access'];
        else {
            if (isset($_POST['save_id']) && is_array($_POST['save_id'])) {
                if (isset($_POST['save_id'], $_POST['name'], $_POST['link'])) {
                    for ($i = 0; $i < count($_POST['save_id']); $i++) {
                        $this->db->query("UPDATE `" . self::$table . "` SET `link`=? WHERE id=?", [$_POST['link'][$i], $_POST['save_id'][$i]]);
                        $param = array($_POST['name'][$i], $_POST['save_id'][$i]);
                        $this->db->query("UPDATE `" . $this->registry['key_lang_admin'] . "_" . self::$table . "` SET `name`=? WHERE " . self::$table . "_id=?", $param);
                    }
                    $message .= messageAdmin('Данные успешно сохранены');
                } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            } else {
                if (isset($_POST['sub'], $_POST['active'], $_POST['link'], $_POST['id'], $_POST['name'], $_POST['title'], $_POST['description'])) {
                    $meta = new Meta($this->sets);
                    $meta->save_meta(self::$table, $_POST['link'], $_POST['title'], $_POST['description']);
                    if ($_POST['sub'] == 0) $sub = NULL;
                    else $sub = $_POST['sub'];
                    $this->update(array(
                        'sub' => $sub,
                        'type_id' => $_POST['type'],
                        'blank' => $_POST['blank'],
                        'link' => $_POST['link'],
                        'icon' => $_POST['icon'],
                        'bg' => $_POST['bg'],
                        'color' => $_POST['color'],
                        'active' => $_POST['active']),
                        [['id', '=', $_POST['id']]]);
                    $this->update(array(
                        'name' => $_POST['name']),
                        [[self::$table . '_id', '=', $_POST['id']]],
                        $this->registry['key_lang_admin'] . "_" . self::$table);
                    $message .= messageAdmin('Данные успешно сохранены');
                } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
            }
        }
        return $message;
    }
    public function saveType()
    {
        $message = '';
        if (isset($this->registry['access'])) $message = $this->registry['access'];
        else {
            if (isset($_POST['name'], $_POST['type_id'])) {
                $count = count($_POST['type_id']) - 1;
                for ($i = 0; $i <= $count; $i++) {
                    $this->db->query("UPDATE `menu_type` SET 
                        `name`=?, `color`=? WHERE id=?",
                        [$_POST['name'][$i],$_POST['color'][$i],$_POST['type_id'][$i]]);
                }
                $message .= messageAdmin('Данные успешно сохранены');
            } else $message .= messageAdmin('Заполнены не все обязательные поля', 'error');
        }
        return $message;
    }

    public function addType()
    {
        $message = '';
        if (isset($this->registry['access'])) $message = $this->registry['access'];
        else {
            $this->db->query("INSERT INTO `menu_type` SET `name`='', `color`=''");
            $message .= messageAdmin('Данные успешно сохранены');
        }
        return $message;
    }
}
