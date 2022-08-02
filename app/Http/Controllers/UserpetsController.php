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
            $status = 'add success';
            return response()->JSON([
                'status' => $status,
                'result' => array([
                    'pet owner' => $petowner,
                    'pet name' => $request->pets_name,
                    'species and breed' => $request->breed .' '. $request->species,
                ])
            ]);
        } else{
            $status = "Failed to add";
            return response()->JSON([
                'status' => $status
            ]);
        }
    }

    public function getuserpet(request $request){

        $query = userpets::where('user_id',$request->user_id);

        if($query->count()>0){
            $status = 'there is '.$query->count().' pets';
            return response()->JSON([
                'status' => $status,
                'pets' => userpets::where('user_id',$request->user_id)->get()
            ]);
        } else{
            $status = "no pets";
            return response()->JSON([
                'status' => $status
            ]);
        }
    }

    public function getpetdetail(request $request){

        $query = userpets::where('Id',$request->id);

        if($query->count()>0){
            $status = 'Success';
            return response()->JSON([
                'status' => $status,
                'results' => userpets::where('id',$request->id)->get()
            ]);
        } else{
            $status = "no pets";
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
            $status = 'sukses';
            return response()->JSON([
                'status' => $status,
                'result' => userpets::where('id',$request->id)->get()
            ]);
        } else{
            $status = 'failed to update';
            return response()->JSON([
                'status' => $status
            ]);
        }

    }

    public function deletepet(request $request){
        $query = userpets::where('id',$request->id)->delete();

        if($query==1){
            $status = 'delete success';
            return response()->JSON([
                'status' => $status
            ]);
        } else{
            $status = 'no pet to delete';
            return response()->JSON([
                'status' => $status
            ]);
        }
    }
}
