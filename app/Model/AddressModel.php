<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AddressModel extends Model
{
    protected $table='wx_address';

    public $timestamps = false;

    protected  $primaryKey="id";
}
