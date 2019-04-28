<?php

namespace App\Admin\Controllers;

use App\Model\AddressModel;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use GuzzleHttp\Client;
class NewsController extends Controller
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {

        $res = AddressModel::get();  //查询用户

//         var_dump($res);
        if(empty($_GET)){

        }else{
            $openid = $_GET['openid'];  //接收view传过来的值
            $name = $_GET['name'];
            // var_dump($openid);
            $open_id = explode(',',$openid);  //字符串转化数组
            // var_dump($opd);
            echo 'ok';
            $result = $this-> text($open_id,$name);  //调用群发消息方法
        }
        return $content
            ->header('Index')
            ->description('description')
            ->body(view('admin.weixin.news',['res'=>$res]));
    }
    //群发消息
    public function text($open_id,$name)
    {
//        $open_id = $_GET['open_id'];
//        $name  = $_GET['name'];
//        echo 11111;die;
//        $open_id = explode(',',$open_id);
//        echo '<pre>';print_r($open_id);echo '</pre>';
//        echo '<pre>';print_r($name);echo '</pre>';die;
        //群发接口
        $url = 'https://api.weixin.qq.com/cgi-bin/message/mass/send?access_token='.getWxAccessToken();

//        print_r($url);die;
        $arr = [
            "touser" => $open_id,  //openid
            "text"=>[
                "content"=>$name,  //内容
            ],
            "msgtype"=>"text",
        ];
        $json = json_encode($arr,JSON_UNESCAPED_UNICODE);  //处理中文
//            var_dump($json);die;
        //发送请求
        $client = new Client();  //实例化guzzle  (post)

        $response = $client->request('POST',$url,[
            'body' => $json
        ]);

        //处理响应
        echo  $response->getBody();
    }

}