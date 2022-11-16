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

    public function TopUpManual(request $request){
        $response = $this->AddAmmount($request->user_id,$request->debit,$request->credit,$request->type,$request->description);
        return $response; 
    }

    public function AddAmmount($user_id,$debit,$credit,$type,$description){
        $current_date_time = date('Y-m-d H:i:s');
        $query = wallet::insert([
                'users_ids' => $user_id, 
                'debit' => $debit,
                'credit' => $credit,
                'type' => $type,
                'description' => $description,
                'created_at' => $current_date_time
        ]);

        if($query == 1){
            $status = "Success";
        }else{
            $status = "Failed";
        }
        return response()->json([
            'status'=>$status,
        ]); 
       
    }

    public function WaletTransaction(request $request){
        $token = $request->header("Authorization");
        $search = $request->search;
        if($token  == null){
            $user_id = $request->user_id;
        }else{
            $result = $this->JWTValidator->validateToken($token);
            $user_id = $result['body']['user_id'];
        }
       
        if($request->limit==NULL){
            $limit = 10;
        } else{
            $limit = $request->limit;
        }

        if($request->page==NULL){
            $page = 0;
        } else{
            $page = ($request->page - 1) * $limit;
        }

        $data = wallet::where('users_ids',$user_id)->where('type',$request->type)->where('description','like','%'.$search.'%')->orderBy('created_at','DESC');
        $query = wallet::where('users_ids',$user_id)->where('type',$request->type)->where('description','like','%'.$search.'%')->orderBy('created_at','DESC')->limit($limit)->offset($page);
        
      
        return response()->json([
            'status'=>"success",
            'total_data'=>$data->count(),  
            'total_page'=> ceil($data->count() / $limit),
            'results'=> [
                $request->type => $data->sum('debit') - $data->sum('credit'),
                'transaction' => $query->get()
            ]
        ]); 
       
    }

    public function wallet_activate_param(request $request){
        $id = $request->query('id');
        $current_date_time = date('Y-m-d H:i:s');
        $pin = $request->pin;
        if(is_numeric($request->pin) && strlen($pin) == 6){
            $query = User::find($id)->update(
                [
                    'wallet_status' => 'Active',
                    'wallet_pin' => md5($request->pin),
                    'update_at' => $current_date_time
                ]
            );
            if($query == 1){
                $status = 'success';
                $msg = '';
            } else{
                $status = 'error';
                $msg = 'server error';
            }    
        }else{  
            $status = 'error';
            $msg = 'Masukkan Pin Dengan Benar';
        }   
        

       
        return response()->json([
            'status'=>$status,
            'message'=> $msg
        ]);
    }

    public function wallet_activate_token(request $request){
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $current_date_time = date('Y-m-d H:i:s');
        $pin = $request->pin;
        if($result['status'] == 200){
            $id = $result['body']['user_id'];
            if(is_numeric($request->pin) && strlen($pin) == 6){
                $query = User::find($id)->update(
                    [
                        'wallet_status' => 'Active',
                        'wallet_pin' => md5($request->pin),
                        'update_at' => $current_date_time
                    ]
                );
                if($query == 1){
                    $status = 'success';
                    $msg = '';
                } else{
                    $status = 'error';
                    $msg = 'server error';
                }    
            }else{  
                $status = 'error';
                $msg = 'Masukkan Pin Dengan Benar';
            } 

            return response()->json([
                'status'=>$status,
                'message'=> $msg
            ]);
        }else{
            return  $result;
        } 
    }
}