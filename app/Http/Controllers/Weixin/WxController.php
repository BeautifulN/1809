<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class WxController extends Controller
{
    public function index(){
        echo $_GET['echostr'];
    }

    //就收微信推送信息并处理
    public function event(){
        $content = file_get_contents("php://input");

        $time = date('Y-m-d H:i:s');

        $srt = $time . $content . "\n";

        file_put_contents("logs/wx_event.log",$srt,FILE_APPEND);  //创建微信log日志

        $obj = simplexml_load_string($content); //把xml转换成对象
//        print_r($obj);
        echo 'ToUserName:'.$obj['ToUserName'];echo"</br>";//微信号
        echo 'FromUserName:'.$obj['FromUserName'];echo"</br>";//用户openid
        echo 'CreateTime:'.$obj['CreateTime'];echo"</br>";//推送时间
        echo 'Event:'.$obj['Event'];echo"</br>";//消息类型

        die;
        $openid = $obj->FromUserName;

//        事件类型
        $event = $obj['Event'];

        if($event=='subscribe') {
            //根据openid判断用户是否已存在
            $user = DB::table('wx_address')->where(['openid' => $openid])->first();
//            print_r($user);die;

            //如果用户之前关注过
            if ($user) {

            }else{
///               获取用户的信息
                $userinfo = $this->getuser($openid);
///                       print_r($userinfo);die;
//                用户信息
                $info = [
//                'id' => $userinfo['subscribe'],
                    'openid' => $userinfo['openid'],
                    'nickname' => $userinfo['nickname'],
                    'sex' => $userinfo['sex'],
                    'country' => $userinfo['country'],
                    'headimgurl' => $userinfo['headimgurl'],
                    'subscribe_time' => $userinfo['subscribe_time'],
                ];

                $sql = DB::table('wx_address')->insertGetId($info);

            }
        }
    }

    //获取token
    public function token(){
        $key = 'accosse_token';
        $token = Redis::get($key);
        if ($token){
//            echo '11111';
        }else{
//            echo '2222';

            //token获取接口调用
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'';
            $response = file_get_contents($url); //接受url数据
            $arr = json_decode($response,true);
//        var_dump($arr);exit;  输出得 ["access_token"]   ["expires_in"]

            //存缓存 access_token   (redis)
            Redis::set($key,$arr['access_token']);
            Redis::expire($key,3600);

            $token = $arr['access_token'];
        }
        return $token;
    }


    public function getuser($openid){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->token().'&openid='.$openid.'&lang=zh_CN';
//        echo $url;die;
        $data = file_get_contents($url);
//        var_dump($data);die;
        $arr = json_decode($data,true);
        return $arr;
    }

}
