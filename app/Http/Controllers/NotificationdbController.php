<?php

namespace App\Http\Controllers;

use App\Models\notificationdb;
use Illuminate\Http\Request;

class NotificationdbController extends Controller
{
    public function createnotif(request $request){
        $query = notificationdb::insert([
            'usersids' => $request->user_id,
            'meta_role' => $request->meta_role,
            'meta_id' => $request->meta_id,
            'notification_data' => $request->notif_data,
            'view' => $request->view,
            'redirect' => $request->redirect
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
            'view' => $request->view,
            'redirect' => $request->redirect
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
                'id' => $query->id,
                'user_id' => $query->usersids,
                'meta_role' => $query->meta_role,
                'meta_id' => $query->meta_id,
                'notification_data' => $query->notification_data,
                'view' => $query->view,
                'redirect' => $query->redirect
            ];
        }

        return response()->JSON([
            'status' => 'success',
            'total_data' => count($query),
            'total_page' => ceil(count($query) / $limit),
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
        
        $query = notificationdb::where('usersids',$request->usersids)->whereIn('meta_role',$roles)->get();

        foreach($query->limit($limit)->offset($page)->get() as $queries){
            $arr[] = [
                'id' => $query->id,
                'user_id' => $query->usersids,
                'meta_role' => $query->meta_role,
                'meta_id' => $query->meta_id,
                'notification_data' => $query->notification_data,
                'view' => $query->view,
                'redirect' => $query->redirect
            ];
        }

        return response()->JSON([
            'status' => 'success',
            'total_data' => count($query),
            'total_page' => ceil(count($query) / $limit),
            'total_result' => count($arr),
            'results' => $arr
        ]);
    }

    public function viewnotif(request $request){
        
    }
}
