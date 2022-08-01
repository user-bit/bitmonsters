<?php class LoginController extends BaseController
{
    function __construct($registry, $params)
    {
        parent::__construct($registry, $params);
        $this->registry = $registry;
    }

    function indexAction()
    {
        $vars['admin'] = PathToTemplateAdmin;
        $method = strtolower(str_replace('-', '_', $this->params['login'])) . 'Action';

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        if (isset($_COOKIE['login_admin'], $_COOKIE['password_admin']) && $_COOKIE['login_admin'] != '') {
            $sql = "SELECT id,type_moderator,login FROM `moderators` WHERE `login`=? AND `password`=? AND `active`=?";
            $param = [$_COOKIE['login_admin'], $_COOKIE['password_admin'], 1];
            $res = $this->db->row($sql, $param);
            if (!$res) header('location: /' . PathToTemplateAdmin . '/logout');
            else {
                if (!isset($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = '';
                $admin_info['agent'] = $_SERVER['HTTP_USER_AGENT'];
                $admin_info['referer'] = $_SERVER['HTTP_REFERER'];
                $admin_info['ip'] = $_SERVER['REMOTE_ADDR'];
                $admin_info['id'] = $res['id'];
                $admin_info['type'] = $res['type_moderator'];
                $admin_info['login'] = $res['login'];
                $_SESSION['admin'] = $admin_info;
                if ($_SERVER['REQUEST_URI'] == '/' . PathToTemplateAdmin . '/logout' || (isset($this->params['code']) && $this->params['code'] != '')) header('location: /' . PathToTemplateAdmin);
                else header('location:' . $_SERVER['REQUEST_URI']);
            }
        } elseif (isset($_POST['login'], $_POST['password'])) {
            $error = '';
            $sql = "SELECT id,type_moderator,login FROM `moderators` WHERE `login`=? AND `password`=? AND `active`=?";
            $param = [$_POST['login'], md5($_POST['password']), 1];
            $result = $this->db->row($sql, $param);
            if (!$result) $error = messageAdmin('Неправильный логин или пароль', 'error');
            if ($error == '') {
                $admin_info['agent'] = $_SERVER['HTTP_USER_AGENT'];
                $admin_info['referer'] = $_SERVER['HTTP_REFERER'];
                $admin_info['ip'] = $_SERVER['REMOTE_ADDR'];
                $admin_info['id'] = $result['id'];
                $admin_info['type'] = $result['type_moderator'];
                $admin_info['login'] = $result['login'];
                $_SESSION['admin'] = $admin_info;
                if (isset($_POST['remember'])) {
                    setcookie("login_admin", $_POST['login'], time() + (31566000), '/');
                    setcookie("password_admin", md5($_POST['password']), time() + (31566000), '/');
                }
                if ($_SERVER['REQUEST_URI'] == '/' . PathToTemplateAdmin . '/logout' || (isset($this->params['code']) && $this->params['code'] != '')) header('location: /' . PathToTemplateAdmin);
                else header('location:' . '/'.PathToTemplateAdmin);
            } else $vars['error'] = $error;
        }
        $data['content'] = $this->view->Render('log-in.phtml', $vars);
        return $this->Index($data);
    }

    public function forgotAction()
    {
        if (isset($_POST['email'])) {
            $err = '';
            $sql = "SELECT id,type_moderator,login FROM `moderators` WHERE `email`=? AND `active`=?";
            $param = [$_POST['email_forgot'], 1];
            $res = $this->db->row($sql, $param);
            if (!$res) $err = messageAdmin('E-mail не найден в базе', 'error');
            if ($err == '') {
                $code = md5(mktime());
                $this->db->query("UPDATE moderators SET active_code='$code' WHERE id='{$res['id']}'");
                $text = "Смена пароля на сайте {$_SERVER['HTTP_HOST']}.<br><br>Чтобы поменять пароль,перейдите по ссылке<br><a href=\"" . $prot . $_SERVER['HTTP_HOST'] . "/admin/changepass/code/$code\" target=\"_blank\">" . $prot . $_SERVER['HTTP_HOST'] . "/admin/changepass/code/$code</a><br><br>";
                Mail::send($_SERVER['HTTP_HOST'], "info@" . $_SERVER['HTTP_HOST'], $res['login'], $_POST['email_forgot'], "utf-8", "utf-8", "Смена пароля {$_SERVER['HTTP_HOST']}", $text);
                $vars['err'] = messageAdmin('На ваш E-mail была выслана ссылка для смены пароля');
            } else $vars['err'] = $err;
        } elseif(isset($this->params['code']) && $this->params['code'] != '') {
            $row = $this->db->row("SELECT id,type_moderator,login,email FROM `moderators` WHERE `active_code`=? AND `active`=? AND email!=''", [$this->params['code'], 1]);
            if ($row) {
                $pass = genPassword();
                $this->db->query("UPDATE moderators SET password='" . md5($pass) . "' WHERE id='{$row['id']}'");
                $text = "Смена пароля на сайте {$_SERVER['HTTP_HOST']}.<br><br>Ваш новый пароль: $pass";
                Mail::send($_SERVER['HTTP_HOST'], "info@" . $_SERVER['HTTP_HOST'], $row['login'], $row['email'], "utf-8", "utf-8", "Смена пароля {$_SERVER['HTTP_HOST']}", $text);
                $vars['err'] = messageAdmin('На ваш E-mail была выслан новый пароль');
            }
        }
        $data['content'] = $this->view->Render('forgotpass.phtml', $vars);
        return $this->Index($data);
    }
}