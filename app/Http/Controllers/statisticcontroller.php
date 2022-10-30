<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\orderservice;
use Carbon\Carbon;
use App\Http\Controllers\JWTValidator;
use Illuminate\Http\Request;

class statisticcontroller extends Controller
{
    protected $JWTValidator;
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }
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

    public function saasstat(request $request){
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $date = Carbon::now()->format('Y-m-d');
        $partner_id = $result['body']['user_id'];

        $arr = [
            'new_order' => orderservice::where('partner_user_id',$partner_id)->where('status','like','ON_PROCESS')->count(),
            'pending_payment' => orderservice::where('partner_user_id',$partner_id)->where('status','like','PENDING_PAYMENT')->count(),
            'total_order_today' => orderservice::select(orderservice::raw('CAST(created_at AS DATE) as RegisDate'))->where(orderservice::raw('CAST(created_at AS DATE)'),'=',$date)->where('partner_user_id',$partner_id)->count(),
            'total_booking_today' =>  orderservice::select(orderservice::raw('CAST(booking_date AS DATE) as RegisDate'))->where(orderservice::raw('CAST(booking_date AS DATE)'),'=',$date)->count(),
        ];

        return response()->JSON([
            'status' => 'success',
            'results' => $arr
        ]);
    }
}
