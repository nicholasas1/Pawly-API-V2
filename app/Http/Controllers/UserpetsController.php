<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\userpets;
use App\Models\User;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;
use App\Http\Controllers\JWTValidator;

class UserpetsController extends Controller
{
    //\
    protected $JWTValidator;
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }

    public function addpet(request $request){
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $userid = $result['body']['user_id'];
        if(filter_var($request->pets_picture, FILTER_VALIDATE_URL) === FALSE){

            $image_parts = explode(";base64,", $request->pets_picture);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = uniqid() . '.'.$image_type;
    
            file_put_contents(env('Folder_APP').$file, $image_base64);
            $picture = env('IMAGE_URL') . $file;
            
        }else{
            $picture = $request->pets_picture;
        }
        $query = userpets::insert([
            'user_id' => $userid,
            'petsname' => $request->pets_name,
            'species' => $request->species,
            'breed' => $request->breed,
            'size' => $request->size,
            'pets_picture' => $picture,
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

        $petowner = User::where('id',$userid)->value('username');
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

        $token = $request->header("Authorization");
        if($token == null){
            $userid = $request->user_id;
        }else{
            $result = $this->JWTValidator->validateToken($token);
            $userid = $result['body']['user_id'];
        }
        $query = userpets::where('user_id',$userid);

        if($query->count()>0){
            $status = 'success';         
            return response()->JSON([
                'status' => $status,
                'results' => $query->get(),
                'total_result' => $query->count()
            ]);
        } else{
            $status = "no_pet_avaiable";
            return response()->JSON([
                'status' => "no_pet_avaiable",
                'results' => null
            ]);
        }
    }

    public function getpetdetail(request $request){

        $query = userpets::where('Id',$request->id);
        $pets = [
        'id' => $request->id,
        'petsname' => $query->value('petsname'),
        'species' => $query->value('species'),
        'breed' => $query->value('breed'),
        'size' => $query->value('size'),
        'pets_picture' => $query->value('pets_picture'),
        'gender' => $query->value('gender'),
        'birth' => $query->value('birthdate'),
        'neutered' => $query->value('neutered'),
        'vaccinated' => $query->value('vaccinated'),
        'fdlwdogs' => $query->value('fdlwdogs'), //friendly with dogs
        'fdlwcats' => $query->value('fdlwcats'), //friendly with cats
        'fdlywkidsless10' => $query->value('fdlywkidsless10'), //friendly with kids     < 10 years old
        'fdlwkidsmore10' => $query->value('fdlwkidsmore10'), //friendly with kids > 10 years old
        'microchipped' => $query->value('microchipped'),
        'purbered' => $query->value('purbered')
        ];

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

        if(filter_var($request->pets_picture, FILTER_VALIDATE_URL) === FALSE){

            $image_parts = explode(";base64,", $request->pets_picture);
            $image_type_aux = explode("image/", $image_parts[0]);
            $image_type = $image_type_aux[1];
            $image_base64 = base64_decode($image_parts[1]);
            $file = uniqid() . '.'.$image_type;
    
            file_put_contents(env('Folder_APP').$file, $image_base64);
            $picture = env('IMAGE_URL') . $file;
            
        }else{
            $picture = $request->pets_picture;
        }
        
        $query = userpets::where('id',$request->id)->update([
            'petsname' => $request->pets_name,
            'species' => $request->species,
            'breed' => $request->breed,
            'size' => $request->size,
            'pets_picture' => $picture,
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
}
