<?php class Faq extends Model
{
    static $table='faq';
    static $name='FAQ';

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
        if(isset($_POST['active'],$_POST['question'],$_POST['answer'])){

            $id = $this->insert(array(
                'active' => $_POST['active']
            ));
            foreach ($this->language as $lang) {
                $this->insert(array(
                    'question' => $_POST['question'],
                    'answer' => $_POST['answer'],
                    self::$table . '_id' => $id
                ), $lang['language'] . "_" . self::$table);
            }
            $this->images->savePhoto($id, $_POST['link'], $_POST['tmp_image'], self::$table);
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
                if(isset($_POST['save_id'],$_POST['question'])){
                    for ($i=0; $i <= count($_POST['save_id']) - 1; $i++){
                        $this->update(array(
                            'sort' => $_POST['sort'][$i]),
                            [['id', '=', $_POST['save_id'][$i]]]);
                        $this->update(array(
                            'question' => $_POST['question'][$i]),
                            [[self::$table . '_id', '=', $_POST['save_id'][$i]]],
                            $this->registry['key_lang_admin'] . "_" . self::$table);
                    }
                    $message.=messageAdmin('Данные успешно сохранены');
                } else $message.=messageAdmin('При сохранение произошли ошибки','error');
            } else {
                if(isset($_POST['active'],$_POST['id'],$_POST['question'],$_POST['answer'])){
                    // Save to database
                    $this->update(array('active' => $_POST['active']),
                        [['id', '=', $_POST['id']]]);
                    $this->update(array(
                        'question' => $_POST['question'],
                        'answer' => $_POST['answer']),
                        [[self::$table . '_id', '=', $_POST['id']]],
                        $this->registry['key_lang_admin'] . "_" . self::$table);
                    $message.=messageAdmin('Данные успешно сохранены');
                } else $message.=messageAdmin('При сохранение произошли ошибки','error');
            }
        }
        return $message;
    }
}