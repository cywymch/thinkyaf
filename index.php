<?php
//获取当前相对地址
if (! defined('_PHP_FILE_')) {
    $_temp = explode('.php', $_SERVER['PHP_SELF']);
    define('_PHP_FILE_', rtrim(str_replace($_SERVER['HTTP_HOST'], '', $_temp[0] . '.php'), '/'));
} else {
    define('_PHP_FILE_', rtrim($_SERVER['SCRIPT_NAME'], '/'));
}
if (! defined('__PATH__')) {
    $_root = rtrim(dirname(_PHP_FILE_), '/');
    define('__PATH__', (($_root == '/' || $_root == '\\') ? '' : $_root));
}
//开发域名配置
define("__ROOT__",dirname(__FILE__));
// Yaf\Loader::import(__ROOT__."/app/library/vendor/autoload.php");
// require_once __ROOT__.'/app/common/function.php';
$app = new Yaf\Application(__ROOT__."/conf/app.ini");
$app->bootstrap()->run();
