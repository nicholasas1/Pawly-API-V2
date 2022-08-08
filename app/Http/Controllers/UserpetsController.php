<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\userpets;
use App\Models\User;

class UserpetsController extends Controller
{
    //
    public function addpet(request $request){

        $query = userpets::insert([
            'user_id' => $request->user_id,
            'petsname' => $request->pets_name,
            'species' => $request->species,
            'breed' => $request->breed,
            'size' => $request->size,
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
            'neutered' => $request->neutered,
            'vaccinated' => $request->vaccinated,
            'fdlwdogs' => $request->fdlwdogs, //friendly with dogs
            'fdlwcats' => $request->fdlwcats, //friendly with cats
            'fdlywkidsless10' => $request->fdlywkidsless10, //friendly with kids < 10 years old
            'fdlwkidsmore10' => $request->fdlwkidsmore10, //friendly with kids > 10 years old
            'microchipped' => $request->microchipped,
            'purbered' => $request->purbered
        ]);

        $petowner = User::where('id',$request->user_id)->value('username');
        if($query==1){
            $status = 'success';
            $pet = ['pet_owner' => $petowner, 'pet_name' => $request->pets_name, 'species_breed' => $request->breed.' '.$request->species];
            return response()->JSON([
                'status' => $status,
                'result' => $pet
            ]);
        } else{
            $status = "fail";
            return response()->JSON([
                'status' => $status
            ]);
        }
    }

    public function getuserpet(request $request){

        $query = userpets::where('user_id',$request->user_id);

        if($query->count()>0){
            $status = 'success';
            return response()->JSON([
                'status' => $status,
                'results' => userpets::where('user_id',$request->user_id)->get(),
                'total_result' => $query->count()
            ]);
        } else{
            $status = "no_pet_avaiable";
            return response()->JSON([
                'status' => $status,
                'results' => userpets::where('user_id',$request->user_id)->get()
            ]);
        }
    }

    public function getpetdetail(request $request){

        $query = userpets::where('Id',$request->id);
        $pets = userpets::where('id',$request->id)->get();

        if($query->count()>0){
            $status = 'Success';
            return response()->JSON([
                'status' => $status,
                'results' => $pets
            ]);
        } else{
            $status = "no_pet_avaiable";
            return response()->JSON([
                'status' => $status
            ]);
        }
    }

    public function updatepet(request $request){
        
        $query = userpets::where('id',$request->id)->update([
            'petsname' => $request->pets_name,
            'species' => $request->species,
            'breed' => $request->breed,
            'size' => $request->size,
            'gender' => $request->gender,
            'birthdate' => $request->birthdate,
            'neutered' => $request->neutered,
            'vaccinated' => $request->vaccinated,
            'fdlwdogs' => $request->fdlwdogs, //friendly with dogs
            'fdlwcats' => $request->fdlwcats, //friendly with cats
            'fdlywkidsless10' => $request->fdlywkidsless10, //friendly with kids     < 10 years old
            'fdlwkidsmore10' => $request->fdlwkidsmore10, //friendly with kids > 10 years old
            'microchipped' => $request->microchipped,
            'purbered' => $request->purbered
        ]);

        if($query==1){
            $status = 'success';
            return response()->JSON([
                'status' => $status,
                'result' => userpets::where('id',$request->id)->get()
            ]);
        } else{
            $status = 'fail';
            return response()->JSON([
                'status' => $status
            ]);
        }

    }

    public function deletepet(request $request){
        $query = userpets::where('id',$request->id)->delete();

        if($query==1){
            $status = 'success';
            return response()->JSON([
                'status' => $status
            ]);
        } else{
            $status = 'fail';
            return response()->JSON([
                'status' => $status
            ]);
        }
    }

    public function uploadBase64(request $request)
    {

        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);

        $image_parts = explode(";base64,", $request->img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = uniqid() . '.'.$image_type;

        file_put_contents(env('Folder_APP').$file, $image_base64);
        $userid = $result['body']['user_id'];
        $query = userpets::where('id',$request->id)->update(
            [
            'pets_picture' => env('IMAGE_URL') . $file
            ]
        );
        if($query==1){
            return response()->json([
                'status'=>"success", 
                'results'=> array(
                    'file_path'  => $file,
                    'file_url'   => env('IMAGE_URL') . $file,
                )
            ]);
        } else{
            return response()->JSON([
                'status' => 'data_not_loaded',
                'results' => array()
            ]);
        }
        

    }
}
