<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*微信*/
//Route::get('index', 'Weixin\WxController@index');
//Route::any('index', 'Weixin\WxController@event');
//Route::any('event', 'Weixin\WxController@event');
//Route::get('token', 'Weixin\WxController@token');
//Route::get('getuser', 'Weixin\WxController@getuser');
//Route::post('menu2', 'Weixin\WxController@menu2');

//项目
Route::get('index', 'Index\IndexController@index');
Route::any('add/{goods_id?}', 'Index\IndexController@add');
Route::get('cart', 'Cart\CartController@cart');

Route::get('create', 'Order\OrderController@create');
Route::get('lists', 'Order\OrderController@lists');

//微信支付
Route::get('test/{order_id?}','Weixin\WxPayController@test');           //支付
Route::post('anotify','Weixin\WxPayController@anotify');       //微信支付回调地址
Route::get('success', 'Weixin\WxPayController@success');

Route::get('create', 'Order\OrderController@create');
Route::get('paystatus', 'Order\OrderController@paystatus');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
