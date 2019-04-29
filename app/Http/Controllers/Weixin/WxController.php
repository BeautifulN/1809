<?php

namespace App\Http\Controllers\Weixin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Redis;

use App\Model\GoodsModel;
use GuzzleHttp\Client;

class WxController extends Controller
{

    /*
     * 首次接入GET请求
     * */
    public function index(){
        echo $_GET['echostr'];
    }

    /*
     * 接收微信时间推送
     * */
    public function wxEvent(){
        //接收微信服务器推送
        $content = file_get_contents("php://input");
//        print_r($content);die;

        $time = date('Y-m-d H:i:s');

        $srt = $time . $content . "\n";

        file_put_contents("logs/wx_event.log",$srt,FILE_APPEND);

        $obj = simplexml_load_string($content); //把xml转换成对象
//        echo '<pre>';print_r($obj);echo '</pre>';die;
//        获取相应的字段 (对象格式)
//        $openid = $obj['FromUserName'];  //用户openid
        $openid = $obj->FromUserName;  //用户openid
        $wxid = $obj->ToUserName;   //微信号ID
//                print_r($wxid);
        $createtime = $obj->CreateTime;
        $msgtype = $obj->MsgType;
        $content = $obj->Content;
        $media_id = $obj->MediaId;

//        $eventkey = $obj->EventKey;
//        $ticket = $obj->Ticket;
//        print_r($ticket);die;
//        echo 'ToUserName:'.$obj->ToUserName;echo"</br>";//微信号
//        echo 'FromUserName:'.$obj->FromUserName;echo"</br>";//用户openid
//        echo 'CreateTime:'.$obj->CreateTime;echo"</br>";//推送时间
//        echo 'Event:'.$obj->Event;echo"</br>";//消息类型
//die;
//        事件类型
        $event = $obj->Event;

//        扫码关注事件
        if($event=='subscribe') {
            //根据openid判断用户是否已存在
            $user = DB::table('wx_address')->where(['openid' => $openid])->first();
//            print_r($user);die;

            //如果用户之前关注过
            if ($user) {
//                echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$wxid.']]></FromUserName><CreateTime>' . time() . '</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.'来了，老弟儿~' . $user->nickname . ']]></Content></xml>';

                $title = "欢迎回来";
//                $openid = $openid;
//                $wxid = $wxid;
                echo'<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$wxid.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>1</ArticleCount>
                    <Articles>
                        <item>
                            <Title><![CDATA['.$title.']]></Title> 
                            <Description><![CDATA[猜猜是什么？！]]></Description>
                            <PicUrl><![CDATA[http://1809lvmingjin.comcto.com/images/a1.jpg]]></PicUrl>
                            <Url><![CDATA[http://1809lvmingjin.comcto.com/indexx]]></Url>
                        </item>
                    </Articles>
                    </xml>';
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

                $title = "欢迎关注";
//                $openid = $openid;
//                $wxid = $wxid;
                echo '<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$wxid.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>1</ArticleCount>
                    <Articles>
                        <item>
                            <Title><![CDATA['.$title.']]></Title> 
                            <Description><![CDATA[猜猜是什么？！]]></Description>
                            <PicUrl><![CDATA[http://1809lvmingjin.comcto.com/images/a1.jpg]]></PicUrl>
                            <Url><![CDATA[http://1809lvmingjin.comcto.com/indexx]]></Url>
                        </item>
                    </Articles>
                    </xml>';
//                echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$wxid.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.'千万人中，关注我；你真牛逼' . $info['nickname'] .']]></Content></xml>';
            }
//            if($event=='SCAN'){
//
//                $info2 = [
////                'id' => $userinfo['subscribe'],
//                    'openid' => $openid,
//                    'nickname' => $wxid,
//                    'eventkey' => $eventkey,
//                    'ticket' => $ticket,
//                    'createtime' => $createtime,
//                ];
//                $sql = DB::table('wx_web_power')->insertGetId($info2);
//            }
        }

        //获取消息素材
        if ($msgtype=='text'){   //文本素材

            $where = [];
            $where[] = ['goods_name','like',"%$obj->Content%"];

        $arr = GoodsModel::where($where)->where('goods_up',1)->get('goods_name')->toArray();
////        $res = [];
        foreach ($arr as $v){
            $res = [
                'goods_name' => $v['goods_name'],
            ];
//            print_r($res);die;
        }

//            print_r($arr);die;
            //回复天气信息
            if(strpos($obj->Content,'+天气')){
                $city = explode('+',$obj->Content)[0];
                $url  = 'https://free-api.heweather.net/s6/weather/now?key=HE1904161044341977&location='.$city;  //天气接口
                $response = file_get_contents($url);
                $arr = json_decode($response,true);
//                print_r($arr);

                if ($arr['HeWeather6'][0]['status'] != 'ok'){   //状态码status
                    echo '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName><FromUserName><![CDATA['.$wxid.']]></FromUserName><CreateTime>'.time().'</CreateTime><MsgType><![CDATA[text]]></MsgType><Content><![CDATA['.'请输入正确的天气：如（北京+天气）'.']]></Content></xml>';
                }else{
                    $fl = $arr['HeWeather6'][0]['now']['tmp'];   //温度
                    $cond_txt = $arr['HeWeather6'][0]['now']['cond_txt'];   //天气状况
                    $hum = $arr['HeWeather6'][0]['now']['hum'];   //相对湿度
                    $wind_sc = $arr['HeWeather6'][0]['now']['wind_sc'];   //风力
                    $wind_dir = $arr['HeWeather6'][0]['now']['wind_dir'];   //风向

                    $str = '温度:'.$fl."\n".'天气状况:'.$cond_txt."\n".'相对湿度:'.$hum."\n".'风力:'.$wind_sc."\n".'风向:'.$wind_dir."\n";
                    echo $xml = '<xml><ToUserName><![CDATA['.$openid.']]></ToUserName>
                                    <FromUserName><![CDATA['.$wxid.']]></FromUserName>
                                    <CreateTime>'.time().'</CreateTime>
                                    <MsgType><![CDATA[text]]></MsgType>
                                    <Content><![CDATA['.$str.']]></Content>
                                </xml>';
                }

            }else if ($obj->Content){   //回复图文信息
//                echo  1111;
                $title = $res['goods_name'];
                $openid = $openid;
                $wxid = $wxid;
//                $time = time();
                echo $itemTpl = '<xml>
                    <ToUserName><![CDATA['.$openid.']]></ToUserName>
                    <FromUserName><![CDATA['.$wxid.']]></FromUserName>
                    <CreateTime>'.time().'</CreateTime>
                    <MsgType><![CDATA[news]]></MsgType>
                    <ArticleCount>1</ArticleCount>
                    <Articles>
                        <item>
                            <Title><![CDATA['.$title.']]></Title> 
                            <Description><![CDATA[猜猜是什么？！]]></Description>
                            <PicUrl><![CDATA[http://1809lvmingjin.comcto.com/images/a1.jpg]]></PicUrl>
                            <Url><![CDATA[http://1809lvmingjin.comcto.com/indexx]]></Url>
                        </item>
                    </Articles>
                    </xml>';
            }

            $info = [
                'openid' => $openid,
//                'nickname' => $nickname,
                'content' => $content,
//                'headimgurl' => $userinfo['headimgurl'],
            ];
            $sql = DB::table('wx_text')->insertGetId($info);

        }else if($msgtype=='image'){   //图片素材
            $access=$this->token();
            $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$media_id";
            $time = time();
            $str = file_get_contents("php://input");
            file_put_contents("image/$time.jpg",$str,FILE_APPEND);
            $image = '/wwwroot/1809ashop/image/'.$time.'.jpg';
//                print_r($image);exit;
            $arr = [
                'openid' => $openid,
                'createtime' => $createtime,
                'image' => $image,
            ];
            $sql = DB::table('wx_image')->insertGetId($arr);
        }else if($msgtype=='voice'){   //语音素材
            $access=$this->token();
            $url = "https://api.weixin.qq.com/cgi-bin/media/get?access_token=$access&media_id=$media_id";
            $time = time();
            $str = file_get_contents($url);
            file_put_contents("/wwwroot/1809ashop/voice/$time.mp3",$str,FILE_APPEND);

            $voice = '/wwwroot/1809ashop/voice/'.$time.'.mp3';
//                print_r($image);exit;
            $arr = [
                'openid' => $openid,
                'createtime' => $createtime,
                'voice' => $voice,
            ];
            $sql = DB::table('wx_voice')->insertGetId($arr);
        }



    }

    /*
     * 获取微信AccessToken
     * */
    public function token(){

        $key = 'access_token';
        $tok = Redis::get($key);
//        var_dump($tok);die;
        if($tok){
            //echo '有缓存';
        }else{
            //echo '无缓存';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'';

            $response = file_get_contents($url);
            $arr = json_decode($response,true);
//        var_dump($arr);exit;  输出得 ["access_token"]   ["expires_in"]

            //存缓存 access_token   (redis)
            Redis::set($key,$arr['access_token']);
            Redis::expire($key,3600);

            $tok = $arr['access_token'];
//            print_r($tok);
        }

        return $tok;
    }

    public function text(){
        $access_token = $this->token();
        echo $access_token;
    }

    /*
     * 获取用户基本信息
     * */
    public function getuser($openid){
        $url = 'https://api.weixin.qq.com/cgi-bin/user/info?access_token='.$this->token().'&openid='.$openid.'&lang=zh_CN';
//        echo $url;die;
        $data = file_get_contents($url);
//        var_dump($data);die;
        $arr = json_decode($data,true);
        return $arr;
    }

    //自定义菜单
    public function menu(){
//        接口
        $url = 'https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$this->token();
//        print_r($url);exit;

//        菜单数据内容
        $arr = [
            'button' => [

                [
                    "type" => "view",
                    "name" => "最新福利",
                    "url"  => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx88877ae88c12e2a2&redirect_uri=http://1809lvmingjin.comcto.com/scope&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect'
                ],

                [
                    "type" => "view",
                    "name" => "点击签到",
                    "url"  => 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx88877ae88c12e2a2&redirect_uri=http://1809lvmingjin.comcto.com/sign&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect'
                ],

                [ "name" => "点我嘿嘿嘿",
                    "sub_button"=>[
                        [
                            "type"=>"view",
                            "name"=>"网站",
                            "url"=>"http://cpc.people.com.cn/"
                        ],
                        [
                            "type"=>"miniprogram",
                            "name"=>"网警举报",
                            "url"=>"http://mp.weixin.qq.com",
                            "appid"=>"wx286b93c14bbf93aa",
                            "pagepath"=>"pages/lunar/index"
                        ],
                        [
                            "type"=>"click",
                            "name"=>"赞一下我们",
                            "key"=>"wx1"
                        ]
                    ]
                ]
            ]
        ];
        $str = json_encode($arr,JSON_UNESCAPED_UNICODE);   //处理中文乱码

        $clinet = new Client();  //发送请求

        $response = $clinet->request('POST',$url,[
            'body' => $str
        ]);

        print_r($response);
        //处理响应回来
        $res = $response->getBody();
        //
        $arr = json_decode($res,true);
//            print_r($arr);
        //判断错误信息
        if($arr['errcode']>0){
            echo "菜单创建失败";
        }else{
            echo "菜单创建成功";
        }

    }

    //消息群发
    public function sendtext($openid,$content){
        //消息群发
//        echo $content;
        $access = $this->token();
        $msg = "https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token=$access";
        $arr = [
            "touser" => $openid,
            "text"=>[
                "content"=>$content,
            ],
            "msgtype"=>"text",
        ];
        $str = json_encode($arr,JSON_UNESCAPED_UNICODE);
        $client = new Client();  //发送请求
        $response = $client->request('POST',$msg,[
            'body' => $str
        ]);
        echo $response->getBody();

    }

    //消息群发 (查询数据库  根据openid群发)
    public function send(){
        $arr = DB::table('wx_address')->where(['sub_status'=>1])->get()->toArray(); //查询关注的用户
        $openid = array_column($arr,'openid');
//        print_r($openid);exit;
        $content = "嘿嘿嘿嘿";
        $response = $this->sendtext($openid,$content);
//        return $response;
    }

    //网页授权
    public function scope(){
//        echo '<pre>';print_r($_GET);echo '</pre>';  //打印code
        $code = $_GET['code'];
//        code作为换取access_token的票据'.env('WX_APP_ID').'
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'&code='.$code.'&grant_type=authorization_code';
        $response = json_decode(file_get_contents($url),true);
//        echo '<pre>';print_r($response);echo '</pre>';  //['access_token']   ['openid']   ['refresh_token']   ['expires_in']   ['scope']

        $access_token = $response['access_token'];
        $openid = $response['openid'];

//        echo '<pre>';print_r($access_token);echo '</pre>';die;

        $url2 = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $response2 = json_decode(file_get_contents($url2),true);
//        echo '<pre>';print_r($response2);echo '</pre>';die;

        $res = DB::table('wx_web_power')->where(['openid'=>$response2['openid']])->first();
//        echo '<pre>';print_r($res);echo '</pre>';die;
        if ($res){
            echo '欢迎'. $res->nickname.'回来';
            header('Refresh:3;url=/indexx');
        }else{
            echo '千万人中，你来到这个网站···'. $response2['nickname'];
                if ($response2['sex']==1){
                    $response2['sex'] ='男';
                }else{
                    $response2['sex'] ='女';
                }
            $info = [
                'openid' => $response2['openid'],
                'nickname' => $response2['nickname'],
                'sex' => $response2['sex'],
                'city' => $response2['city'],
                'province' => $response2['province'],
                'country' => $response2['country'],
                'headimgurl' => $response2['headimgurl'],

            ];
                $arr = DB::table('wx_web_power')->insert($info);  //用户信息入库

        }

    }

    //网页授权签到
    public function sign(){
//        echo '<pre>';print_r($_GET);echo '</pre>';  //打印code
        $code = $_GET['code'];
//        code作为换取access_token的票据'.env('WX_APP_ID').'
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.env('WX_APPID').'&secret='.env('WX_SECRET').'&code='.$code.'&grant_type=authorization_code';
        $response = json_decode(file_get_contents($url),true);
//        echo '<pre>';print_r($response);echo '</pre>';  //['access_token']   ['openid']   ['refresh_token']   ['expires_in']   ['scope']

        $access_token = $response['access_token'];
        $openid = $response['openid'];

//        echo '<pre>';print_r($access_token);echo '</pre>';die;

        $url2 = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid.'&lang=zh_CN';
        $response2 = json_decode(file_get_contents($url2),true);
//        echo '<pre>';print_r($response2);echo '</pre>';die;

        $res = DB::table('wx_web_power')->where(['openid'=>$response2['openid']])->first();
//        echo '<pre>';print_r($res);echo '</pre>';die;
        if ($res){
            echo '又来签到了'. $res->nickname.'老弟';
        }else{
            echo '千万人中，你来到这个签到网站···'. $response2['nickname'];
            if ($response2['sex']==1){
                $response2['sex'] ='男';
            }else{
                $response2['sex'] ='女';
            }
            $info = [
                'openid' => $response2['openid'],
                'nickname' => $response2['nickname'],
                'sex' => $response2['sex'],
                'city' => $response2['city'],
                'province' => $response2['province'],
                'country' => $response2['country'],
                'headimgurl' => $response2['headimgurl'],

            ];
            $arr = DB::table('wx_web_power')->insert($info);  //用户信息入库

        }
        echo "<h1>" .$response2['nickname'].": 签到成功</h1>";
        $key = '1:wx_sign:' . $response2['openid'];
        Redis::lPush($key,date('Y-m-d H:i:s'));
        $record = Redis::lRange($key,0,-1);
        echo '<pre>';print_r($record);echo '</pre>';

    }



    //带参数的二维码
    public function code(){
        $url = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token='.getWxAccessToken();
//        echo '<pre>';print_r($url);echo '</pre>';die;

        $arr = [
            "action_name" => 'QR_STR_SCENE',
            "expire_seconds" => "604800",
            "action_info" =>[
                "scene" =>[
                    "scene_str" => "111",
                ]
            ]

        ];

//        echo '<pre>';print_r($arr);echo '</pre>';die;

        $json = json_encode($arr,JSON_UNESCAPED_UNICODE);  //处理中文
//            var_dump($json);die;
        //发送请求
        $client = new Client();  //实例化guzzle  (post)

        $response = $client->request('POST',$url,[
            'body' => $json
        ]);

        //处理响应
        echo  $response->getBody();
        $res = $response->getBody();

        $arr = json_decode($res,true);
//        echo '<pre>';print_r($arr);echo '</pre>';die;
        $ticket = $arr['ticket'];

        return $ticket;
    }

    public function codes(){
        $ticket = $this->code();
//        echo '<pre>';print_r($ticket);echo '</pre>';die;
        return "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=$ticket";
    }


}
