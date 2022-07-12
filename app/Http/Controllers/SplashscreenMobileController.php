<?php

namespace App\Http\Controllers;
use App\Models\splashscreen_mobile;

use Illuminate\Http\Request;

class SplashscreenMobileController extends Controller
{
    public function getSplash()
    {
        return response()->json([
            'success'=>'succes', 
            'splash_image' => splashscreen_mobile::where('meta_name','splash_image')->value('meta_value'),
            'splash_image_id' => splashscreen_mobile::where('meta_name','splash_image')->value('id'),
            'total' =>splashscreen_mobile::where('meta_name','splash_text')->count(),
            'result' => splashscreen_mobile::where('meta_name','splash_text')->get('id','meta_value')
        ]);
    }

    public function deleteSplash(request $request)
    {
        $id = $request->query('id');

        $delete = splashscreen_mobile::where('id',$id)->delete();

        if($delete == 1){
            $status = "success";
        }else{
            $status = "error";
        }

        return response()->json([
            'success'=>$status
        ]);
    }

    public function createSplash(request $request)
    {

        $query = splashscreen_mobile::insert(
            [
                'meta_name' =>  $request->meta_name,
                'meta_value' => $request->meta_value
            ]
        );

        if($query == 1 ){
            $status = "success";
        }else{
            $status = "error";
        }

        return response()->json([
            'success'=>$status
        ]);
    }

    public function updateSplash(request $request)
    {

        $id = $request->query('id');
        $query = splashscreen_mobile::find($id)->update(
            [
                'meta_name' =>  $request->meta_name,
                'meta_value' => $request->meta_value
            ]
        );

        if($query == 1 ){
            $status = "success";
        }else{
            $status = "error";
        }

        return response()->json([
            'success'=>$status
        ]);
    }
}
