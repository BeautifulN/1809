<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');

    $router->resource('goods', GoodsController::class);  //商品管理
    $router->resource('order', OrderController::class);  //订单管理
    $router->resource('address', AddressController::class);  //微信用户

    $router->any('addimg','AddimgController@index');  //上传临时图片
    $router->any('news','NewsController@index');  //消息群发展示
    $router->get('contents','NewsController@index');  //消息群发执行
});
