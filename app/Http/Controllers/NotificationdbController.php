<?php

namespace App\Http\Controllers;

use App\Models\notificationdb;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\JWTValidator;


class NotificationdbController extends Controller
{
    protected $JWTValidator;
    public function __construct(JWTValidator $jWTValidator)
    {
        $this->JWTValidator = $jWTValidator;
    }

    public function createnotif($user_id,$meta_role,$meta_id,$order_id,$notif_data,$redirect){
        $query = notificationdb::insert([
            'usersids' => $user_id,
            'meta_role' => $meta_role,
            'meta_id' => $meta_id,
            'order_ids' => $order_id,
            'notification_data' => $notif_data,
            'redirect' => $redirect,
            'view' => NULL,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        if($query==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => ''
            ]);
        }
    }

    public function updatenotif(request $request){
        $query = notificationdb::where('id',$request->id)->update([
            'usersids' => $request->user_id,
            'meta_role' => $request->meta_role,
            'meta_id' => $request->meta_id,
            'order_ids' => $request->order_id,
            'notification_data' => $request->notif_data,
            'redirect' => $request->redirect,
            'updated_at' => Carbon::now()
        ]);

        if($query==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => ''
            ]);
        }
    }

    public function deletenotif(request $request){

        $query = notificationdb::where('id',$request->id)->delete();

        if($query==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => ''
            ]);
        }
    }   

    public function getnotifall(request $request){
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
        $query = notificationdb::select('id','usersids','meta_role','meta_id','order_ids','notification_data','view','redirect');

        foreach($query->limit($limit)->offset($page)->get() as $queries){
            $arr[] = [
                'id' => $queries->id,
                'user_id' => $queries->usersids,
                'meta_role' => $queries->meta_role,
                'meta_id' => $queries->meta_id,
                'order_id' => $queries->order_ids,
                'notification_data' => $queries->notification_data,
                'view' => $queries->view,
                'redirect' => $queries->redirect
            ];
        }

        return response()->JSON([
            'status' => 'success',
            'total_data' => $query->count(),
            'total_page' => ceil($query->count() / $limit),
            'total_result' => count($arr),
            'results' => $arr
        ]);
    }

    public function getnotiffilter(request $request){
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
        $role = $request->role;
        $roles = [];
        if($role=='consumer'){
            $roles[] = ['user'];
        } else if($role=='provider'){
            $roles = ['doctor','clinic'];
        }
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $arr=[];
        if($result['status'] == 200){
            $userid = $result['body']['user_id'];
            $query = notificationdb::where('usersids',$userid)->whereIn('meta_role',$roles)->select('id','usersids','meta_role','meta_id','order_ids','notification_data','view','redirect');

            foreach($query->limit($limit)->offset($page)->get() as $queries){
                $arr[] = [
                    'id' => $queries->id,
                    'user_id' => $queries->usersids,
                    'meta_role' => $queries->meta_role,
                    'meta_id' => $queries->meta_id,
                    'order_id' => $queries->order_ids,
                    'notification_data' => $queries->notification_data,
                    'view' => $queries->view,
                    'redirect' => $queries->redirect
                ];
            }
    
            return response()->JSON([
                'status' => 'success',
                'total_data' => $query->count(),
                'total_page' => ceil($query->count() / $limit),
                'total_data_unread' => $query->where('view',null)->count(),
                'results' => $arr
            ]);
        }else{
            return $result;
        }
        
    }

    public function viewnotif(request $request){
        $query = notificationdb::where('id',$request->id)->update([
            'view' => true,
            'updated_at' => Carbon::now()
        ]);

        if($query==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => ''
            ]);
        }
    }

    public function readNotifAll(request $request){
        $query = notificationdb::where('usersids',$request->id)->update([
            'view' => true,
            'updated_at' => Carbon::now()
        ]);

        if($query==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error',
                'msg' => ''
            ]);
        }
    }
}
