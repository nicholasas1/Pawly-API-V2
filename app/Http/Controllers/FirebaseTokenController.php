<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\user_secret;

class FirebaseTokenController extends Controller
{
    public function userFirebaseToken($userId, $device ){      
        $result=[];
        $data = user_secret::where('user_id','like',$userId)->where('user_device','like','%'.$device.'%');
        foreach($data->get() as $arr){
            $method = array(
                'id' => $arr['id'],
                'user_device'=>$arr['user_device'],
                'firebase_token'=>$arr['firebase_token'],
            );
            array_push($result, $method);
        }

        return $result;
    }

    public function userSecretList(Request $request){
        $userId = $request->user_id;
        if($request->device != null){
            $device = $request->device;
        }else{
            $device = '';
        }
      

        $data = user_secret::where('user_id','like',$userId)->where('user_device','like','%'.$device.'%');

        return response()->json([
            'status'=>'success',  
            'results'=>$data->get()
        ]);
    }

    public function delete_user_secret(request $request){
        $query = user_secret::where('id',$request->query('id'))->delete();
        
        if($query==1){
            return response()->JSON([
                'status' => 'success',
                'msg' => ''
            ]);
        } 
        else{
            return response()->JSON([
                'status' => 'error',
                'msg' => 'Failed Delete User Secret'
            ]);
            
        }
    }

}
