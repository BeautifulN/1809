<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderModel extends Model
{
    protected $table='order';

    public $timestamps = false;

    public static function generateOrderSN($user_id)
    {
        $order_amount = 'a1809a_'. date("ymdH");
        $str = time() . $user_id . rand(1111,9999) . Str::random(16);
        $order_amount .=  substr(md5($str),5,16);
        return $order_amount;
    }
}
