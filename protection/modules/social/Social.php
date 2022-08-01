<?php class Social extends Model
{
    static $table = 'social';
    static $name = "Соц. сети";

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
        if (isset($_POST['link'])) {
            $this->insert(array(
                'name' => $_POST['name'],
                'icon' => $_POST['icon'],
                'link' => $_POST['link'],
            ));
            $message .= messageAdmin('Данные успешно добавлены');
        } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
        return $message;
    }

    public function save()
    {
        $message = '';
        if (isset($this->registry['access'])) $message = $this->registry['access'];
        else {
            if (isset($_POST['link'])) {
                $this->update(array(
                    'name' => $_POST['name'],
                    'icon' => $_POST['icon'],
                    'link' => $_POST['link']),
                    [['id', '=', $_POST['id']]]);

                $message .= messageAdmin('Данные успешно сохранены');
            } else $message .= messageAdmin('При сохранение произошли ошибки', 'error');
        }
        return $message;
    }
}
