<?php
$dotenv = new Dotenv\Dotenv(dirname(dirname(__DIR__ )));
$dotenv->load();

// comment out the following two lines when deployed to production
defined('YII_DEBUG') or getenv('YII_DEBUG') && define('YII_DEBUG', getenv('YII_DEBUG') === 'true');
defined('YII_ENV') or getenv('YII_ENV') && define('YII_ENV', getenv('YII_ENV'));

if (file_exists(dirname(__DIR__) .'/release')) {
    define('YII_APP_VERSION', trim(file_get_contents(dirname(__DIR__) .'/release')));
}
defined('YII_APP_VERSION') or define('YII_APP_VERSION', '');

/*if (YII_ENV_DEV) {
    error_reporting(E_ALL & ~E_DEPRECATED);
    ini_set('display_errors', 1);
}*/
