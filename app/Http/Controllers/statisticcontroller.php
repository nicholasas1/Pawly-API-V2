<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\orderservice;
use Carbon\Carbon;

use Illuminate\Http\Request;

class statisticcontroller extends Controller
{
    public function statistic(){
        $month = Carbon::now()->month;
        $date = Carbon::now()->format('Y-m-d');
        $graph_user_daily = User::select(User::raw('CAST(create_at AS DATE) as RegisDate'),User::raw('COUNT(CAST(create_at AS DATE)) as Total'))->groupBy('RegisDate')->get() ;
        $orderOmset = orderservice::select(orderservice::raw('MONTH(`created_at`) AS Month'),'status',orderservice::raw('COUNT(CAST(created_at AS DATE)) as Total'),orderservice::raw('SUM(subtotal) as Sum'))->where(orderservice::raw('MONTH(`created_at`)'),'=',$month)->groupBy('Month')->where('status','=','COMPLATE');
        //SELECT MONTH(`created_at`) AS Month,COUNT(MONTH(`created_at`))  FROM orderservices GROUP BY Month;
        $arr = [
            'total_user' =>  User::count(),
            'graph_user_daily' =>  $graph_user_daily,
            'total_order_month' => orderservice::select(orderservice::raw('MONTH(`created_at`) AS Month'))->where(orderservice::raw('MONTH(`created_at`)'),'=',$month)->count(),
            'total_order_daily' =>  orderservice::select(orderservice::raw('CAST(created_at AS DATE) as RegisDate'))->where(orderservice::raw('CAST(created_at AS DATE)'),'=',$date)->count(),
            'total_omset_month'  => $orderOmset->value('Sum'),
            'service_total_month'  => orderservice::select(orderservice::raw('MONTH(`created_at`) AS Month'),'service',orderservice::raw('COUNT(service) AS Total'))->where(orderservice::raw('MONTH(`created_at`)'),'=',$month)->groupBy('service')->get(),
        ];  

        return response()->json([
            'status'=>'success',
            'results'=>$arr
        ]);
    }
}
