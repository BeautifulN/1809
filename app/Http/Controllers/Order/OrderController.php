<?php

namespace App\Http\Controllers\Order;


use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Model\OrderModel;
use App\Model\OrderDetailModel;
use App\Model\CartModel;

class OrderController extends Controller
{
    public function create()
    {
        //计算订单金额
        $goods = CartModel::where(['user_id'=>Auth::id(),'session_id'=>Session::getId()])->get()->toArray();
//        echo '<pre>';print_r($goods);echo '</pre>';die;
        $order_amount = 0;
        foreach($goods as $k=>$v){
            $order_amount += $v['goods_selfprice'];       //计算订单金额
        }
        $order_info = [
            'user_id'               => Auth::id(),
            'order_number'          => OrderModel::generateOrderSN(Auth::id()),     //订单编号
            'order_amount'      => $order_amount,
            'create_time'          => time()
        ];
        $order_id = OrderModel::insertGetId($order_info);        //写入订单表
//        print_r($order_id);die;
        //订单详情
        foreach($goods as $k=>$v){
            $detail = [
                'order_id'      => $order_id,
                'goods_id'      => $v['goods_id'],
                'goods_name'    => $v['goods_name'],
                'goods_selfprice'   => $v['goods_selfprice'],
                'user_id'           => Auth::id()
            ];
            //写入订单详情表
            OrderDetailModel::insertGetId($detail);
        }
        header('Refresh:3;url=/indexx');
        echo "生成订单成功";
    }

    //订单展示
    public function lists(){

        $arr = OrderModel::where('is_del',1)->get();

        return view('order.lists',['arr'=>$arr]);
    }

    /**
     * 查询订单支付状态
     */
    public function paystatus()
    {
        $order_id = intval($_GET['order_id']);
        $info = OrderModel::where(['order_id'=>$order_id])->first();
//        print_r($info);die;
        $response = [];
        if($info){
            if($info->create_time>0){      //已支付
                $response = [
                    'pay_status'    => 0,       // 0 已支付
                    'msg'       => 'ok'
                ];
            }
            //echo '<pre>';print_r($info->toArray());echo '</pre>';
        }else{
            die("订单不存在");
        }
        die(json_encode($response));
    }

    /*
     * 删除过期订单 (每分钟查一次)
     * */
    public function delorder(){
        echo __METHOD__."\n";
        $all = OrderModel::all()->toArray();
        foreach($all as $k=>$v){
            if (time() - $v['create_time'] > 60 && $v['pay_status'] == 1){
                OrderModel::where(['order_id'=>$v['order_id']])->update(['is_del'=>0]);
            }
        }
    }
}
