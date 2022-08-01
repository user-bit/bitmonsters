<?php class FeedbackController extends BaseController
{
    protected $feedback;

    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->tb = Feedback::$table;
        $this->feedback = new Feedback($this->sets);
    }

    function feedbackAction()
    {
        if (isset($_POST['mail'])) {
            $error = "";
            if ($error == "") {
                $this->feedback->insert(array(
                    'date' => date('Y-m-d H:i:s'),
                    'from_p' => $_POST['from'],
                    'mail' => $_POST['mail'],
                    'status' => 'новая'
                ));
                $messageTelegram = "<b>Обратная связь Finch:</b> " . PHP_EOL;
                $messageTelegram .= "<b>Какая форма:</b> " . $_POST['from'] . PHP_EOL;
                $messageTelegram .= "Страница : " . $_POST['page'] . PHP_EOL;
                $messageTelegram .= "Почта: " . $_POST['mail'] . PHP_EOL;
//                Mail::senTelegram($messageTelegram);
                Mail::send($_POST['name'], // имя отправителя
                    $this->settings['email'], // email отправителя
                    $this->settings['name'], // имя получателя
                    $this->settings['email'], // email получателя
                    "utf-8", // кодировка переданных данных
                    "windows-1251", // кодировка письма
                    "Feedback: " . $_SERVER['HTTP_HOST'], // тема письма
                    $messageTelegram);
                $this->translation = $this->feedback->get_lang("tb.modules_id=46");
                $message['title'] = '<span>' . $this->translation['modal_feedback_title'] . '</span>';
                $message['desc'] = '<span>' . $this->translation['modal_feedback_desc'] . '</span>';
            } else {
                $message = $error;
            }
        }
        return json_encode($message);
    }
}