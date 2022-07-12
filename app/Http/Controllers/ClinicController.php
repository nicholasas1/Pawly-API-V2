<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\JWTValidator;
use App\Models\clinic;

class ClinicController extends Controller
{
    protected $JWTValidator;
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }

    public function addClinic(request $request){
            $query = clinic::insert(
                [
                    'name' => $request->clinic_name, 
                    'address' => $request->address,
                    'long' => $request->long,
                    'lat' => $request->lat, 
                    'description' => $request->description, 
                    'clinic_photo' => $request->clinic_photo, 
                    'opening_hour' => $request->opening_hour, 
                    'close_hour' => $request->close_hour
                ]
            );

            if($query == 1){
                $status = "Clinic Registration Success";
            }

        return response()->json([
            'status'=>$status
        ]);       
    }

    public function update_query(request $request){     
        if($request->query('id')!== NULL){
            $id = $request->query('id');
            $query = clinic::find($id)->update(
                [
                    'name' => $request->clinic_name, 
                    'address' => $request->address,
                    'long' => $request->long,
                    'lat' => $request->lat, 
                    'description' => $request->description, 
                    'clinic_photo' => $request->clinic_photo, 
                    'opening_hour' => $request->opening_hour, 
                    'close_hour' => $request->close_hour
                ]
            );
    
            if($query == 1){
                $status = 'success';
            } else{
                $status = 'error';
            }
        }else{
            $status = "Id tidak boleh kosong";
        }
       

        return response()->json([
            'status'=>$status
        ]);
    }

    public function getDetail(request $request){
        
        if($request->query('id')!== NULL){
            $id = $request->query('id');
            $status = "Success";
            $response = clinic::where('id', $id)->get();
        }else{
            $status = "Id tidak boleh kosong";
            $response = "Null";
        }
       

        return response()->json([
            'status'=> $status,
            'result'=> $response
        ]);
    }

    public function delete(request $request){
        
        if($request->query('id')!== NULL){
            $id = $request->query('id');
            $status = "Success";
            $response = clinic::where('id',$id)->delete();
        }else{
            $status = "Id tidak boleh kosong";
            $response = "Null";
        }
       

        return response()->json([
            'status'=> $status
        ]);
    }
}
