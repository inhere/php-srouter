<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2017/7/14
 * Time: 下午9:12
 *
 * you can test use:
 *  php -S 127.0.0.1:5671 -t examples/object
 *
 * then you can access url: http://127.0.0.1:5671
 */

use inhere\sroute\ORouter;

require dirname(__DIR__) . '/simple-loader.php';

error_reporting(E_ALL | E_STRICT);
date_default_timezone_set('Asia/Shanghai');

$router = new ORouter;

// on notFound, output a message.
$router->on(ORouter::NOT_FOUND, function ($path) {
    echo "the page $path not found!";
});

// set config
$router->config([
    'ignoreLastSep' => true,
    'dynamicAction' => true,

    // 'tmpCacheNumber' => 100,

//    'matchAll' => '/', // a route path
//    'matchAll' => function () {
//        echo 'System Maintaining ... ...';
//    },

    // enable autoRoute
    // you can access '/demo' '/admin/user/info', Don't need to configure any route
    'autoRoute' => [
        'enable' => 1,
        'controllerNamespace' => 'inhere\sroute\examples\controllers',
        'controllerSuffix' => 'Controller',
    ],
]);

require __DIR__ . '/routes.php';

//var_dump($router->getRegularRoutes());die;
$router->dispatch();
