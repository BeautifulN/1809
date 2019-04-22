<?php

namespace App\Http\Controllers\Cart;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Model\GoodsModel;
use App\Model\CartModel;

class CartController extends Controller
{
    /**
     * 购物车页面
     */
    public function cart()
    {
        $cart_list = CartModel::where(['user_id'=>Auth::id(),'session_id'=>Session::getId()])->get()->toArray();
        if ($cart_list) {
            $total_price = 0;
            foreach ($cart_list as $k => $v) {
                $cart = GoodsModel::where(['goods_id' => $v['goods_id']])->first()->toArray();
                $total_price += $cart['goods_selfprice'];
                $goods_list[] = $cart;

                //                print_r($goods_list);die;
            }
            //展示购物车
            $data = [
                'goods_list' => $goods_list,
                'total' => $total_price
            ];
            return view('cart.cart', $data);
        } else {
            header('Refresh:3;url=/index');
            die("购物车为空,跳转至首页");
        }
    }
}
