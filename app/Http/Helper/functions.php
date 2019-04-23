<?php
use Illuminate\Support\Facades\Redis;


/*
 * 获取token
 * */
    function getWxAccessToken(){
        $key = 'wx_access_token';

        $access_token = Redis::get($key);

        if ($access_token){
            return $access_token;

        }else{
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'';
            $response = json_decode(file_get_contents($url),true);

            if (isset($response['access_token'])){
                Redis::set($key,$response['access_token']);
                Redis::expire($key,3600);
                return $response['access_token'];
            }else{
                return false;
            }
        }
    }

/*
 * 获取微信 jsapi ticket
 * */
function getJsapiTicket()
{
    $key = 'wx_jsapi_ticket';
    $ticket = Redis::get($key);
    if($ticket){
        return $ticket;
    }else{
        $access_token = getWxAccessToken();
        $url = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token='.$access_token.'&type=jsapi';
        $ticket_info = json_decode(file_get_contents($url),true);
        if(isset($ticket_info['ticket'])){
            Redis::set($key,$ticket_info['ticket']);
            Redis::expire($key,3600);
            return $ticket_info['ticket'];
        }else{
            return false;
        }
    }
}

?>