<?php

namespace App\Http\Controllers\Index;

use App\Model\CartModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Session;
use App\Model\GoodsModel;


class IndexController extends Controller
{
    /*
     * 商品展示
     * */
    public function index(){

        $arr = GoodsModel::where('goods_up',1)->get();

        return view('index.index',['arr'=>$arr]);
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

        return view('index.goodsdetail',['arr'=>$arr,'info'=>$info,'data'=>$data]);
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
}
?>
<script src="/js/jquery/jquery-1.12.4.min.js"></script>
<script src="http://res2.wx.qq.com/open/js/jweixin-1.4.0.js "></script>
<script>
    wx.config({
        //debug: true, // 开启调试模式,调用的所有api的返回值会在客户端alert出来，若要查看传入的参数，可以在pc端打开，参数信息会通过log打出，仅在pc端时才会打印。
        appId: "{{$jsconfig['appId']}}", // 必填，公众号的唯一标识
        timestamp: "{{$jsconfig['timestamp']}}", // 必填，生成签名的时间戳
        nonceStr: "{{$jsconfig['nonceStr']}}", // 必填，生成签名的随机串
        signature: "{{$jsconfig['signature']}}",// 必填，签名
        jsApiList: ['chooseImage','uploadImage'] // 必填，需要使用的JS接口列表
    });

    wx.ready(function () {   //需在用户可能点击分享按钮前就先调用
        //分享给好友
        wx.updateAppMessageShareData({
            title: '这是一个图片', // 分享标题
            desc: '该写什么好呢', // 分享描述
            link:  'http://1809lvmingjin.comcto.com/indexx', // 分享链接，该链接域名或路径必须与当前页面对应的公众号JS安全域名一致
            imgUrl: 'http://1809lvmingjin.comcto.com/images/a1.jpg', // 分享图标
            success: function () {
            // 设置成功
//                    console.log();
            }
        })
    });
</script>
