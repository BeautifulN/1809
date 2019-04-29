<?php

namespace App\Http\Controllers\Index;

use App\Model\CartModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use App\Model\GoodsModel;
use Illuminate\Support\Str;

class IndexController extends Controller
{
    /*
     * 商品展示
     * */
    public function index(){

        $arr = GoodsModel::where('goods_up',1)->get();

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

        $js_config = [
            'appId'         => env('WX_APPID'),  //公众号APPID
            'timestamp'     => $timestamp,  //时间
            'nonceStr'      => $nonceStr,  //随机字符串
            'signature'     => $sign,  //签名
        ];
        $data = [
            'jsconfig' => $js_config
        ];

        return view('index.index',$data,['arr'=>$arr]);
    }

    /*
     * 商品详情
     * */
    public function goodsdetail(){
        $goods_id = intval($_GET['goods_id']);
//        print_r($goods_id);
        $key = $goods_id;
        $redis_view_keys = 'ss:goods:view';  //获取商品浏览排名
//        print_r($redis_view_keys);
        $history = Redis::incr($key);  //商品浏览次数
//        echo $history;die;
        Redis::zAdd($redis_view_keys,$history,$goods_id);

        $arr = GoodsModel::where(['goods_id'=>$goods_id])->first();
//         var_dump($arr);die;
        //浏览次数+1
        if($arr){
            GoodsModel::where(['goods_id'=>$goods_id])->update(['goods_key'=>$arr['goods_key']+1]);
        }else{
            $detail = [
                'goods_look'=> $arr['goods_look'] +1,
            ];
            GoodsModel::insertGetId($detail);
        }

        //哈希
        $redis_key = 'h:goods_info'.$goods_id;
        $cache_info = Redis::hGetAll($redis_key);
//        print_r($cache_info);
        if ($cache_info){
//            echo '有';
        }else{
//            echo '无';
            $goods_info = GoodsModel::where(['goods_id'=>$goods_id])->first()->toArray();
//            echo '<pre>';print_r($goods_info);echo '</pre>';echo '<hr>';
            Redis::hMset($redis_key,$goods_info);
        }

        $list1 = Redis::Zrangebyscore($redis_view_keys,0,10000,['withscores'=>true]);  //正序
//        echo '<pre>';print_r($list1);echo '</pre>';echo '<hr>';
        $list2 = Redis::Zrevrange($redis_view_keys,0,10000,true);  //倒序
//        echo '<pre>';print_r($list2);echo '</pre>';echo '<hr>';

        //浏览历史
        $info = [];
        foreach ($list2 as $k=>$v){
            $info[] = GoodsModel::where(['goods_id'=>$k])->first()->toArray();  //浏览历史
//            print_r($info);

//            echo '<pre>';print_r($info);echo '</pre>';echo '<hr>';
        }

        //浏览记录
        $data = [];
        foreach ($list1 as $k=>$v){
            $data[] = GoodsModel::where(['goods_id'=>$k])->first()->toArray();  //浏览历史
//            print_r($info);

//            echo '<pre>';print_r($info);echo '</pre>';echo '<hr>';
        }

        $arr = GoodsModel::where('goods_id',$goods_id)->get();  //商品详情

        $code_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];  //二维码路径

//        print_r($code_url);

        return view('index.goodsdetail',['arr'=>$arr,'info'=>$info,'data'=>$data,'code_url'=>$code_url]);
    }

//    /*
//     * 浏览历史
//     * */
//    public function history(){
//        $redis_view_keys = 'ss:goods:view';  //获取商品浏览排名
//        $list2 = Redis::Zrevrange($redis_view_keys,0,10000,true);  //倒序
////        echo '<pre>';print_r($list2);echo '</pre>';echo '<hr>';
//        $info = [];
//        foreach ($list2 as $k=>$v){
//            $info = GoodsModel::where(['goods_id'=>$k])->first()->toArray();
//            print_r($info);
//        }
//        return view('index.goodsdetail',['info'=>$info]);
//    }

    /*
     * 添加购物车
     * */
    public function add($goods_id){
        //是否购买商品
        if(empty($goods_id)){
            header('Refresh:3;url=/cart');
            die("请选择购买的商品");
        }

        //商品是否有效
        $goods = GoodsModel::where(['goods_id'=>$goods_id])->first();
        if ($goods){

            //商品是否上架
            if ($goods->goods_up > 1 ){
                header("Refresh:3;url=indexx");
                echo "该商品已下架，请重新选择商品";
                die;
            }

            //商品库存是否充足
            if ($goods->goods_num == 0 ){
                header("Refresh:3;url=indexx");
                echo "该商品库存不足，请重新选择商品";
                die;
            }

            //进行添加购物车
            $cart_info = [
                'goods_id'        => $goods['goods_id'],
                'goods_name'      => $goods['goods_name'],
                'goods_selfprice' => $goods['goods_selfprice'],
                'user_id'         => Auth::id(),
                'create_time'     => time(),
                'session_id'      => Session::getId(),
                'buy_number'      => 1
            ];
            //执行入库
            $cart_id = CartModel::insertGetId($cart_info);
            if ($cart_id){
                header('Refresh:3;url=/cart');
                die("添加购物车成功，自动跳转至购物车");
            }else{
                header('Refresh:3;url=/indexx');
                die("添加购物车失败");
            }

        }else{
            echo '该商品不存在';
        }
    }

//    public function jsconfig(){
//        $arr = GoodsModel::where('goods_up',1)->get();
//        //计算签名
//        $nonceStr = Str::random(10);
//        $ticket = getJsapiTicket();
//        $timestamp = time();
//        $current_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] .$_SERVER['REQUEST_URI'];
////        print_r($current_url);exit;
////        print_r($ticket);exit;
//
//        $string1 = "jsapi_ticket=$ticket&noncestr=$nonceStr&timestamp=$timestamp&url=$current_url";
//        $sign = sha1($string1);
////        print_r($sign);
//
//        $js_config = [
//            'appId'         => env('WX_APPID'),  //公众号APPID
//            'timestamp'     => $timestamp,  //时间
//            'nonceStr'      => $nonceStr,  //随机字符串
//            'signature'     => $sign,  //签名
//        ];
//        $data = [
//            'jsconfig' => $js_config
//        ];
//        return  view('index.index',$data,['arr'=>$arr]);
//    }
}
?>
