<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;

class JssdkController extends Controller
{
    public function jstest(){

        //计算签名
        $nonceStr = Str::random(10);
        $ticket = getJsapiTicket();
        $timestamp = time();
        $current_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];
//        print_r($current_url);exit;
//        print_r($ticket);exit;

        $string1 = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$current_url";
        $sign = sha1($string1);
//        print_r($sign);

        $js_info = [
            'appId'         => env('WX_APPID'),  //公众号APPID
            'timestamp'     => $timestamp,  //时间
            'nonceStr'      => $nonceStr,  //随机字符串
            'signature'     => $sign,  //签名
        ];
        $data = [
            'jsconfig' => $js_info
        ];
        return  view('weixin.jssdk',$data);
    }

    public function getimg(){
        echo '<pre>';print_r($_GET);echo '</pre>';
    }

}
