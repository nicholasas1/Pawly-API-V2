<?php

namespace App\Http\Controllers;

use App\Models\orderservice;
use Illuminate\Http\Request;

class OrderserviceController extends Controller
{
    public function order_detail($service,$price,$coupon_name){
        if($coupon_name==NULL){
            $total_price = $price;
            $discount = 0;
            $subtotal = $total_price-$discount;
        }
    }
}
