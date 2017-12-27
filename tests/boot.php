<?php
/**
 * phpunit
 * OR
 * phpunit6.phar --bootstrap tests/boot.php tests
 * phpunit6.phar --colors --coverage-html ./coverage/
 */

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Asia/Shanghai');

spl_autoload_register(function($class)
{
    $file = null;

    if (0 === strpos($class,'Inhere\Route\Examples\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('Inhere\Route\Examples\\')));
        $file = __DIR__ . "/{$path}.php";
    } elseif(0 === strpos($class,'Inhere\Route\Tests\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('Inhere\Route\Tests\\')));
        $file = dirname(__DIR__) . "/tests/{$path}.php";
    } elseif (0 === strpos($class,'Inhere\Route\\')) {
        $path = str_replace('\\', '/', substr($class, strlen('Inhere\Route\\')));
        $file = dirname(__DIR__) . "/src/{$path}.php";
    }

    if ($file && is_file($file)) {
        include $file;
    }
});
