<?php
/* * * * *  Db settings  * * * * */
$DB_Host = 'localhost';
$DB_Name = 'bit-monsters-new';
$DB_UserName = 'root';
$DB_Password = '';
$DB_Charset = 'utf8';
//$DB_Name = 'etkfanrm_bit';
//$DB_UserName = 'etkfanrm_bit';
//$DB_Password = 'GD7y40by';

/* * * * *  Admin settings  * * * * */
define('PathToTemplateAdmin',  'administrator-cms');

/* * * * *  Tpl settings  * * * * */
$PathToTemplate = 'resource/';
$PathToCSS = 'css/';
$PathToJavascripts = 'js/';

/* * * * *  Settings  * * * * */
define('LANG', 'en');
define('PROTOCOL', $_SERVER['HTTPS'] ? 'https://' : 'http://');
define('theme', 'front');
define('DEBUG', 1);
define('email_error', 'mushrooms@gmail.com');
define('email_admin', 'mushrooms@gmail.com');
define('default_paging', 20);
define('ext_image', 'jpg,jpeg,JPEG,JPG,bmp,BMP,gif,GIF,PNG,png,tmp');
define('ext_vide', 'mp4');
