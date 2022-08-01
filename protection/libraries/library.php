<?php
function __autoload($class_name)
{
    $filename = $class_name . '.php';

    if (!include($filename)) {
        return false;
    }
}

function getRootCat($id, $catalog)
{
    foreach ($catalog as $row) if ($row['id'] == $id) break;
    if ($row['sub'] != 0) $row['id'] = getRootCat($row['sub'], $catalog);
    return $row['id'];
}

function redirect301($link)
{
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . PROTOCOL . $_SERVER['HTTP_HOST'] . $link);
    exit();
}

function strip_post()
{
    foreach ($_POST as &$item) {
        $item = strip_tags($item);
    }
    return $_POST;
}

function getUri($languages, $db)
{
    $key_translation = [];
    $url = StringClass::sanitize($_SERVER['REQUEST_URI']);
    $value_lang = explode("/", $url);
    if (preg_match('/^[-a-zA-Z0-9_\/\=\?\;\,]*$/', $_SERVER['REQUEST_URI']) && (isset($value_lang[1]) && ($value_lang[1] != 'ajaxadmin' && $value_lang[1] != 'ajax' && $value_lang[1] != PathToTemplateAdmin && $value_lang[1] != 'js' && $value_lang[1] != 'server' && $value_lang[1] != 'captcha')) || !isset($_SESSION['key_lang'])) $_SESSION['key_lang'] = LANG;
    if (!isset($value_lang[2]) || (isset($value_lang[2]) && $value_lang[2] != PathToTemplateAdmin))
        foreach ($languages as $row) if (isset($value_lang[1]) && $value_lang[1] == $row['language']) {
            $_SESSION['key_lang'] = $row['language'];
            $_SERVER['REQUEST_URI'] = mb_substr($_SERVER['REQUEST_URI'], 3);
        }
    return $_SESSION['key_lang'];
}

function getUriAdm($languages)
{
    $key_translation = [];
    $url = StringClass::sanitize($_SERVER['REQUEST_URI']);
    $value_lang = explode("/", $url);
    if (!isset($_SESSION['key_lang_admin'])) $_SESSION['key_lang_admin'] = LANG;
    if (isset($value_lang[2]) && $value_lang[2] == PathToTemplateAdmin)
        foreach ($languages as $row) if (isset($value_lang[1]) && $value_lang[1] == $row['language']) {
            $_SESSION['key_lang_admin'] = $row['language'];
            $_SERVER['REQUEST_URI'] = mb_substr($_SERVER['REQUEST_URI'], 3);
        }
    return $_SESSION['key_lang_admin'];
}

function var_debug($vars, $d = false)
{
    echo "<pre class='alert alert-info'>\n";
    var_dump($vars);
    echo "</pre>\n";
    if ($d) exit();
}

function genPassword($size = 8)
{
    $a = ['e', 'y', 'u', 'i', 'o', 'a'];
    $b = ['q', 'w', 'r', 't', 'p', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm'];
    $c = ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0'];
    $e = ['-'];
    $password = $b[array_rand($b)];
    do {
        $lastChar = $password[strlen($password) - 1];
        @$predLastChar = $password[strlen($password) - 2];
        if (in_array($lastChar, $b)) {//последняя буква была согласной
            if (in_array($predLastChar, $a)) {//две последние буквы были согласными
                $r = rand(0, 2);
                if ($r) $password .= $a[array_rand($a)];
                else $password .= $b[array_rand($b)];
            } else $password .= $a[array_rand($a)];
        } elseif (!in_array($lastChar, $c) && !in_array($lastChar, $e)) {
            $r = rand(0, 2);
            if ($r == 2) $password .= $b[array_rand($b)];
            elseif (($r == 1)) $password .= $e[array_rand($e)];
            else $password .= $c[array_rand($c)];
        } else $password .= $b[array_rand($b)];
    } while (($len = strlen($password)) < $size);
    return $password;
}

function checkAuthAdmin()
{
    if (isset($_SESSION['admin'])) {
        if ($_SESSION['admin']['agent'] != $_SERVER['HTTP_USER_AGENT']) $error = 1;
        if ($_SESSION['admin']['ip'] != $_SERVER['REMOTE_ADDR']) $error = 1;
    }
    if (isset($error)) unset($_SESSION['admin']);
    if (!isset($_SESSION['admin'])) return false;
    return true;
}

function showEditor($name, $body)
{
    return '
    <script src="/resource/administrator-cms/js/ckeditor/ckeditor.js"></script>
    <textarea name="' . $name . '" id="' . $name . '" rows="10" cols="80">' . $body . '</textarea><script>CKEDITOR.replace("' . $name . '")</script>
    ';
}

function messageAdmin($text, $type = '')
{
    if ($type == 'error') {
        return '<div class="modal-error">
                   <div class="modal-error__header">
                        <div class="modal-error__left">
                            <svg><use xlink:href="/resource/administrator-cms/images/svg/svg-sprite.svg#close"></use></svg>
                            <span>Ошибка</span>
                        </div>
                        <div class="modal-error__close">
                            <span aria-hidden="true">×</span>
                        </div>
                    </div>
                    <div class="modal-error__content">' . $text . '</div>
                </div>';
    } else {
        return '<div class="modal-alert">
                    <div class="modal-alert__header">
                        <div class="modal-alert__left">
                            <svg><use xlink:href="/resource/administrator-cms/images/svg/svg-sprite.svg#check"></use></svg>
                            <span>Успех</span>
                        </div>
                        <div class="modal-alert__close">
                            <span aria-hidden="true">×</span>
                        </div>
                    </div>
                    <div class="modal-alert__content">' . $text . '</div>
                </div>';
    }

}

function getProtocol()
{
    return $_SERVER['HTTPS'] ? 'https://' : 'http://';
}

/**
 * Проверка на суперадмина
 */
function isSuperAdmin()
{
    if ((int)$_SESSION['admin']['type'] === 1) return true;
    return false;
}

/**
 * @param $array
 * @param $key
 * @param $value
 * Метод генерирует массив ключ значение из переданного массива где ключем является переданный ключ
 * а значением соответственно поле value
 */
function makeRowsKey($array, $key, $value)
{
    $return = [];
    foreach ($array as $item) {
        $return[$item[$key]] = $item[$value];
    }
    return $return;
}

/**
 * @param $array
 * @param StringClass $id = 'id'
 * @return array
 * Метод оборачивает каждый вложенный массив укзанным идентификатором
 * $array[0] = ['id' => 10, 'name' => 'John']
 * Превратит в
 * $array[10] = ['id' => 10, 'name' => 'John']
 */
function containArrayInHisId($array, $id = 'id')
{
    $return = [];
    foreach ($array as $item) {
        $return[$item[$id]] = $item;
    }
    return $return;
}

function getTree($dataset)
{
    $tree = array();
    foreach ($dataset as $id => &$node) {
        if (!$node['sub']) {
            $tree[$id] = &$node;
        } else {
            $dataset[$node['sub']]['childs'][$id] = &$node;
        }
    }
    return $tree;
}

/**
 * @param array $array
 * @param $key - ключ текущего массива
 * @return bool
 * Проверяем является ли переданный элемент, последним по нумерации в массиве
 */
function isLast($array = [], $key)
{
    $last_key = key(array_slice($array, -1, 1, TRUE));
    if ($last_key === $key) return true;
    return false;
}

function alertMessage($str, $type = 3, $tag = 'div')
{
    if ($type == 0) $type = 'danger';
    if ($type == 1) $type = 'success';
    if ($type == 2) $type = 'warning';
    if ($type == 3) $type = 'info';
    return '<' . $tag . ' class="alert alert-' . $type . '">' . $str . '</' . $tag . '>';
}

/**
 * Clear cache of resource if resource modified < $days
 */
function getPathNoCache($path, $size = '_s', $default = '/files/default.jpg', $days = 30)
{
    $path = preg_replace('/^\//', '', $path);
    if ($size != '_s') {
        $test_path = str_replace('_s', $size, $path);

        if (file_exists($test_path)) {
            $path = $test_path;
        }
    }

    if (file_exists($path)) {
        $path = '/' . $path;
    } else {
        $path = $default;
    }

    $date1 = date_create(date('d-m-Y', filemtime($path)));
    $date2 = date_create(date('d-m-Y'));
    $interval = date_diff($date1, $date2)->format('%a');
    $prefix = '';

    if (intval($interval) < $days) {
        $prefix = '?' . filemtime($path);
    }

    return $path . $prefix;
}

function concatGetString()
{
    $string = '';
    foreach ($_GET as $key => $value) {
        if (!empty($value)) {
            if ($string != '') $string .= '&';
            $string .= $key . '=' . $value;
        }
    }
    return $string == '' ? $string : ('?' . $string);
}