<?php class Infoblocks extends Model
{
    static $table = 'infoblocks';
    static $name = 'Информационные блоки';

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
        if (isset($_POST['name'], $_POST['path']) && $_POST['name'] != "" && $_POST['path'] != "") {
            $id = $this->insert(array(
                'name' => $_POST['name'],
                'path' => $_POST['path'],
                'type' => $_POST['type']
            ));
            foreach ($_POST['info'] as $key => $info) {
                if (strpos($key, 'text') !== false) {
                    foreach ($this->language as $lang) {
                        $this->db->query("INSERT INTO " . $lang['language'] . "_infoblocks_text SET key_value=?, infoblocks_id=?", [$key, $id]);
                    }
                } else {
                    foreach ($this->language as $lang) {
                        $this->db->query("INSERT INTO " . $lang['language'] . "_infoblocks SET key_value=?, infoblocks_id=?", [$key, $id]);
                    }
                }


            }
            return messageAdmin('Данные успешно добавлены');
        }
        return messageAdmin('Заполнены не все обязательные поля', 'error');
    }

    public function save()
    {
        $message = messageAdmin('Заполнены не все обязательные поля', 'error');
        if (isset($_POST['id'], $_POST['name'])) {
            $this->update(array(
                'name' => $_POST['name'],
                'type' => $_POST['type']
            ), "id={$_POST['id']}");
            //Save to construct
            foreach ($_POST['info'] as $info_id => $info_info) {
                if (strpos($info_id, 'text') !== false) {
                    $this->db->query("UPDATE " . $this->registry['key_lang_admin'] . "_infoblocks_text SET value=? WHERE infoblocks_id=? AND key_value=?",
                        [$info_info, $_POST['id'], $info_id]);
                } else {
                    $this->db->query("UPDATE " . $this->registry['key_lang_admin'] . "_infoblocks SET value=? WHERE infoblocks_id=? AND key_value=?",
                        [$info_info, $_POST['id'], $info_id]);
                }
            }
            $message = messageAdmin('Данные успешно сохранены');
        }
        return $message;
    }
}