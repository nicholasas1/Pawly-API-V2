<?php

namespace App\Http\Controllers;

use App\Models\role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function adminRole(request $request){
        $query = role::insert([
            'userId' => $request->user_id,
            'meta_role' => 'super_admin',
            'meta_id' =>''
        ]);

        if($query==1){
            $status = "Registration Admin Success";
        } else{
            $status = 'error';
        }
       
        return response()->JSON([
            'status' => $status
        ]);
    }

    public function addRole(request $request){
        $query = role::insert([
            'userId' => $request->user_id,
            'meta_role' => $request->meta_role,
            'meta_id' =>$request->meta_role
        ]);

        if($query==1){
            $status = "Add Role Success";
        } else{
            $status = 'error';
        }
       
        return response()->JSON([
            'status' => $status
        ]);
    }

    public function deleteRole(request $request){
        $query = role::where('id',$request->id)->delete();
        
        if($query==1){
            $status = "Delete Success";
        } else{
            $status = 'error';
        }
       
        return response()->JSON([
            'status' => $status
        ]);
    }

    public function userRole(request $request){
        $query = role::where('userId',$request->user_id)->select(['id','meta_role','meta_id'])->get();
        
        if($query==1){
            $status = "success";
            
        } else{
            $status = 'error';
        }
       
        return response()->JSON([
            'status' => $status,
            'results' => $query
        ]);
    }
}
