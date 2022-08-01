<?php
if (DEBUG) error_reporting(1); else error_reporting(0);
$sep = getenv('COMSPEC') ? ';' : ':';
$dir = '';
$scandir = scandir(MODULES);
foreach ($scandir as $i => $row) if ($i > 1) $dir .= $sep . MODULES . $row . '/';
$path = CLASSES . $sep . CONTROLLERS . $sep . MODULES . $dir . LIBRARY . $dir;
require_once(SITE_PATH . "protection/libraries/library.php");
ini_set('include_path', $path);
ini_set('session.use_trans_sid', false);
header("Content-Type: text/html; charset=utf-8");
session_start();
$db = ['host' => $DB_Host, 'name' => $DB_Name, 'user' => $DB_UserName, 'password' => $DB_Password, 'charset' => $DB_Charset];
$tpl = ['source' => $PathToTemplate, 'styles' => $PathToCSS, 'jscripts' => $PathToJavascripts];
$registry = new Registry;
$registry->set('db_settings', $db);
$registry->set('tpl_settings', $tpl);
$db = new PDOchild($registry);
$language = $db->rows("SELECT * FROM `language`");
$registry->set('key_lang', getUri($language, $db));
$registry->set('key_lang_admin', getUriAdm($language));
$row = $db->row("SELECT * FROM `config` WHERE `name`='theme'");
if ($row) {
	$registry->set('theme', $row['value']);
} else {
	$registry->set('theme', theme);
}
//admin
if ($_SESSION['key_lang_admin'] == LANG) define('LINK_ADMIN', '');
else define('LINK_ADMIN', '/' . $_SESSION['key_lang_admin']);
//front
if ($_SESSION['key_lang'] == LANG) define('LINK', '');
else define('LINK', '/' . $_SESSION['key_lang']);
$parser = new Parser();

if (!empty($_POST)) $parser->parse_recursive_tree($_POST);
if (!empty($_GET)) $parser->parse_recursive_tree($_GET);
if (!empty($_COOKIE)) $parser->parse_recursive_tree($_COOKIE);