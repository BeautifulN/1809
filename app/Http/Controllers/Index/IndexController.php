<?php

namespace App\Http\Controllers\Index;

use App\Model\CartModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
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
                header("Refresh:3;url=index");
                echo "该商品已下架，请重新选择商品";
                die;
            }

            //商品库存是否充足
            if ($goods->goods_num == 0 ){
                header("Refresh:3;url=index");
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
                header('Refresh:3;url=/index');
                die("添加购物车失败");
            }

        }else{
            echo '该商品不存在';
        }
    }
}
