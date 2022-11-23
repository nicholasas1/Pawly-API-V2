<?php

namespace App\Http\Controllers;

use App\Models\notificationdb;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationdbController extends Controller
{
    public function createnotif($user_id,$meta_role,$meta_id,$notif_data,$redirect){
        $query = notificationdb::insert([
            'usersids' => $user_id,
            'meta_role' => $meta_role,
            'meta_id' => $meta_id,
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
        $query = notificationdb::all();

        foreach($query->limit($limit)->offset($page)->get() as $queries){
            $arr[] = [
                'id' => $queries->id,
                'user_id' => $queries->usersids,
                'meta_role' => $queries->meta_role,
                'meta_id' => $queries->meta_id,
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

        if($role=='consumer'){
            $roles[] = ['user'];
        } else if($role=='provider'){
            $roles = ['doctor','clinic'];
        }
        
        $query = notificationdb::where('usersids',$request->usersids)->whereIn('meta_role',$roles);

        foreach($query->limit($limit)->offset($page)->get() as $queries){
            $arr[] = [
                'id' => $queries->id,
                'user_id' => $queries->usersids,
                'meta_role' => $queries->meta_role,
                'meta_id' => $queries->meta_id,
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

    public function viewnotif(request $request){
        $query = notificationdb::where('id',$request->id)->where('usersids',$request->user_id)->update([
            'view' => true
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
