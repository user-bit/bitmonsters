<?php class Pages extends Model
{
    static $table = 'pages';
    static $name = 'Содержимое';

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
        if (isset($_POST['active'], $_POST['link'], $_POST['name'], $_POST['template_name'], $_POST['title'], $_POST['description'])
            && $_POST['name'] != "" && $_POST['template_name'] != "") {
            // generate Link
            if ($_POST['link'] == '') $link = StringClass::translit($_POST['name']);
            else $link = StringClass::translit($_POST['link']);
            if ($_POST['no-link'] == 'no-link') $link = '';

            // Save meta data
            $meta = new Meta($this->sets);
            $meta->save_meta(self::$table, $link, $_POST['title'], $_POST['description']);
            $id = $this->insert(array(
                'no-link' => $_POST['no-link'],
                'template_name' => $_POST['template_name'],
                'active' => $_POST['active']
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
            if (isset($_POST['active'], $_POST['link'], $_POST['id'], $_POST['template_name'], $_POST['name'], $_POST['title'], $_POST['description'])) {
                // generate Link
                if ($_POST['link'] == '') $link = StringClass::translit($_POST['name']);
                else $link = StringClass::translit($_POST['link']);
                if ($_POST['no-link'] == 'no-link') $link = '';

                // Save meta data
                $meta = new Meta($this->sets);
                $meta->save_meta(self::$table, $link, $_POST['title'], $_POST['description']);
                $this->checkUrl(self::$table, $link, $_POST['id']);

                // Save to database
                $this->update(array(
                    'no-link' => $_POST['no-link'],
                    'template_name' => $_POST['template_name'],
                    'active' => $_POST['active']),
                    [['id', '=', $_POST['id']]]);
                $this->update(array(
                    'name' => $_POST['name']),
                    [[self::$table . '_id', '=', $_POST['id']]],
                    $this->registry['key_lang_admin'] . "_" . self::$table);

                //Save to construct
                foreach ($_POST['constructor'] as $constructor_resource_id => $constructor_resource_info) {
                    $this->db->query("UPDATE constructor SET sort=? WHERE id=?",
                        [$constructor_resource_info['sort'], $constructor_resource_id]);

                    foreach ($constructor_resource_info['products'] as $key => $products) {
                        if ($key == 0)
                            $constructor_resource_info['products'] = '';
                        $constructor_resource_info['products'] .= $products . ',';
                    }
                    foreach ($constructor_resource_info as $key => $key_value) {
                        if (strpos($key, 'text') !== false) {
                            $this->db->query("UPDATE " . $this->registry['key_lang_admin'] . "_constructor_resource_text SET value=? WHERE constructor_resource_id=? AND key_value=?",
                                [$key_value, $constructor_resource_id, $key]);
                        } else {
                            $this->db->query("UPDATE " . $this->registry['key_lang_admin'] . "_constructor_resource SET value=? WHERE constructor_resource_id=? AND key_value=?",
                                [$key_value, $constructor_resource_id, $key]);
                        }
                    }
                }
                $message .= messageAdmin('Данные успешно сохранены');
            } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
        }
        return $message;
    }

}