<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;

class AddimgController extends Controller
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
//        echo '<pre>';print_r($_FILES);echo '</pre>';

//        $img = $_FILES;  //接收图片
////        echo '<pre>';print_r($img);echo '</pre>';
//        $imgname = $img['img']['name'];  //取出图片名称
//        $tmpname = $img['img']['tmp_name'];  //图片路径
//        $newname = rand(1111,9999)."$imgname";

//        echo '<pre>';print_r($imgname);echo '</pre>';
        if (empty($_FILES)){

            return $content
                ->header('Index')
                ->description('description')
                ->body(view('admin.weixin.addimg'));
        }else{
            $img = $_FILES;  //接收图片
//        echo '<pre>';print_r($img);echo '</pre>';
            $imgname = $img['img']['name'];  //取出图片名称
            $tmpname = $img['img']['tmp_name'];  //图片路径
            $newname = rand(1111,9999)."$imgname";

            $url = 'https://api.weixin.qq.com/cgi-bin/media/upload?access_token='.getWxAccessToken().'&type=image';  //临时素材端口
            $clinet = new Client();  //发送请求
//                    var_dump($url);
            $path = "/wwwroot/1809/public/image/$newname"; //拼接路径
            move_uploaded_file($tmpname,$path); //存储图片

            $response = $clinet->request('POST',$url,[
                'multipart' => [
                    [
                        'name'      => 'media',
                        'contents'  => fopen("image/".$newname,"r"),
                    ]
                ]
            ]);
//            echo $response->getBody();  //输出是json格式为正确

            $res = $response->getBody();
            $arr = json_decode($res,true); //转换
//            echo '<pre>';print_r($arr);echo '</pre>';
            $info = [  //取出 media_id
                'media_id' => $arr['media_id'],
                'created_at' => $arr['created_at'],
            ];
//            echo '<pre>';print_r($info);echo '</pre>';
            $sql = DB::table('wx_text')->insertGetId($info);  //入库
            if ($sql){
                echo 'ok';
            }

            return $content
                ->header('Index')
                ->description('description')
                ->body(view('admin.weixin.addimg'));
        }
        return $content
            ->header('Index')
            ->description('description')
            ->body(view('admin.weixin.addimg'));
    }
}