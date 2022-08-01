<?php class Roadmap extends Model
{
    static $table='roadmap';
    static $name='Roadmap';

    public function __construct($registry)
    {
        parent::getInstance($registry);
        $this->images = new Images($this->sets);
    }

    public static function getObject($registry)
    {
        return new self::$table($registry);
    }

    public function add()
    {
        $message='';
        if(isset($_POST['active'],$_POST['name'])){
            $id = $this->insert(array(
                'active' => $_POST['active'],
                'sort' => $_POST['sort']
            ));
            foreach ($this->language as $lang) {
                $this->insert(array(
                    'name' => $_POST['name'],
                    self::$table . '_id' => $id
                ), $lang['language'] . "_" . self::$table);
            }
            $message .= messageAdmin('Данные успешно добавлены');
        } else $message.=messageAdmin('При сохранение произошли ошибки','error');
        return $message;
    }

    public function save()
    {
        $message='';
        if(isset($this->registry['access']))$message=$this->registry['access'];
        else {
            if(isset($_POST['save_id']) && is_array($_POST['save_id'])){
                if(isset($_POST['save_id'],$_POST['name'])){
                    for ($i=0; $i <= count($_POST['save_id']) - 1; $i++){
                        $this->update(array(
                            'sort' => $_POST['sort'][$i]),
                            [['id', '=', $_POST['save_id'][$i]]]);
                        $this->update(array(
                            'name' => $_POST['name'][$i]),
                            [[self::$table . '_id', '=', $_POST['save_id'][$i]]],
                            $this->registry['key_lang_admin'] . "_" . self::$table);
                    }
                    $message.=messageAdmin('Данные успешно сохранены');
                } else $message.=messageAdmin('При сохранение произошли ошибки','error');
            } else {
                if(isset($_POST['active'],$_POST['id'],$_POST['name'])){
                    // Save to database
                    $this->update(array('active' => $_POST['active']),
                        [['id', '=', $_POST['id']]]);
                    $this->update(array(
                        'name' => $_POST['name']),
                        [[self::$table . '_id', '=', $_POST['id']]],
                        $this->registry['key_lang_admin'] . "_" . self::$table);
                    $message.=messageAdmin('Данные успешно сохранены');
                } else $message.=messageAdmin('При сохранение произошли ошибки','error');
            }
        }
        return $message;
    }
}