<?php class Feedback extends Model
{
    static $table = 'feedback';
    static $name = 'Заявки';

    public function __construct($registry)
    {
        parent::getInstance($registry);
    }

    public static function getObject($registry)
    {
        return new self::$table($registry);
    }

    public function save()
    {
        $message = '';
        if (isset($this->registry['access'])) $message = $this->registry['access'];
        else {
            if (isset($_POST['status'])) {
                $this->update(array(
                    'status' => $_POST['status']),
                    [['id', '=', $_POST['id']]]);
                $message .= messageAdmin('Данные успешно сохранены');
            } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
        }
        return $message;
    }
}