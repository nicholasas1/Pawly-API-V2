<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\JWTValidator;
use App\Models\wallet;

class WalletController extends Controller
{
    protected $JWTValidator;
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }

    public function AddAmmount(request $request){
        $current_date_time = date('Y-m-d H:i:s');
        $query = wallet::insert([
                    'users_ids' => $request->user_id, 
                    'debit' => $request->debit,
                    'credit' => $request->credit,
                    'created_at' => $current_date_time
        ]);

        if($query == 1){
            $status = "Success";
        }
        return response()->json([
            'status'=>$status,
        ]); 
       
    }

    public function WaletTransaction(request $request){
        var_dump($request->user_id);
        
        $query = wallet::where('users_ids',$request->user_id);
      
        return response()->json([
            'status'=>"success",
            'results'=> [
                'pawly_credit' => $query->sum('debit') - $query->sum('credit'),
                'transaction' => $query->get()
            ]
        ]); 
       
    }
}