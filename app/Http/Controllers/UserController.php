<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\user_secret;
use App\Models\role;
use App\Models\userpets;
use Illuminate\Support\Facades\DB;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;
use App\Http\Controllers\JWTValidator;
use App\Mail\activateEmail;
use App\Models\wallet;
use Illuminate\Support\Facades\Mail;
use Carbon;

class UserController extends Controller
{
    //
    protected $JWTValidator;
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }

    public function getlist(request $request)
    {
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);

        if($result['status'] == 200){
            return response()->json([
                'status'=>'success', 
                'results'=>User::all()
            ]);
        }else{
            return array(
                $result
            );
        }
        
    }

    public function getuserdetail(request $request){

        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        
        if($result['status'] == 200){
        $userid = $result['body']['user_id'];
        $user = User::where('id',$userid);
        $roles = role::where('userId',$userid)->select(['meta_role','meta_id'])->get();
        $pets = userpets::where('user_id',$userid)->select(['petsname','species','breed','gender','birthdate'])->get();
        $arr = [
            'Id' => $userid,
            'Username' => $user->value('Username'),
            'Nickname' => $user->value('nickname'),
            'Full_Name' => $user->value('fullname'), 
            'Email' => $user->value('email'),
            'phone_number' => $user->value('phone_number'),
            'birthday' => $user->value('birthday'),
            'gender'=> $user->value('gender'),
            'profile_picture'=>$user->value('profile_picture'),
            'is_clinic' => role::where('userId',$userid)->where('meta_role',"Clinic")->count(),
            'is_doctor' => role::where('userId',$userid)->where('meta_role',"Doctor")->count(),
            'is_admin' => role::where('userId',$userid)->where('meta_role',"Super_Admin")->count(),
            'Roles' => $roles, 
            'Pet_count' => userpets::where('user_id',$userid)->count(), 
            'Pets' => $pets,
            'pawly_credit' => (wallet::where('users_ids',$userid)->sum('debit') - wallet::where('users_ids',$userid)->sum('credit')), 
        ]; 
            return response()->json([
                'status'=>'success', 
                'results'=> $arr
            ]);
        }else{
            return $result;
        }
        
    }

    public function deleteuser(request $request){

        $query = User::where('id', $request->id);

        if($query->count()==1){
            User::where('id',$request->id)->delete();
            $status = 'User berhasil dihapus';
        } else{
            $status = "User tidak ditemukan";
        }

        return $status;

    }

    public function login(request $request)
    {
        if(is_numeric($request->username)){
            $field = 'phone_number';
        } elseif (filter_var($request->username, FILTER_VALIDATE_EMAIL)) {
            $field = 'email';
        } else {
            $field = 'username';
        }

        $current_date_time = date('Y-m-d H:i:s');

        $query = User::where($field,$request->username)->where("password",md5($request->password));
        $userId = User::where($field,$request->username)->where("password",md5($request->password))->value('id');
        if($query->count()== 0){
                $status = "Invalid Username or Password";
                return response()->JSON([
                    'status' => $status,
                    'results' => 'null'
                ]);
        }else{
            $status="success";
            if($query->value('status') == "Waiting Activation"){
                $status="Your account is not active. Please check your email to activate your account";
            }
            $secret = str_shuffle('abcdesfrtysjndncdj').str_shuffle('!@#$%*').rand(10,1000).str_shuffle('QWERTY');
            $session_id = str_shuffle('abcdesfrtysjndncdj').rand(10,1000);
            user_secret::insert(
                [
                    'user_id' => $query->value('id'), 
                    'user_secret' => $secret,
                    'session_id' => $session_id,
                    'user_device' => $request->header('device'),
                    'firebase_token' => $request->header('firebase_token'),
                    'created_at' => $current_date_time
                ]
            );
            
            $token = $this->JWTValidator->createToken($query->value('id'), $query->value('username'),$session_id, $secret);
           

            
          
            return response()->json([
                'status'=>$status, 
                'results'=> array(
                    'username'  => $query->value('username'),
                    'is_clinic' => role::where('userId',$userId)->where('meta_role',"Clinic")->count(),
                    'is_doctor' => role::where('userId',$userId)->where('meta_role',"Doctor")->count(),
                    'is_admin' => role::where('userId',$userId)->where('meta_role',"Super_Admin")->count(),
                    'role'      => role::where('userId',$query->value('id'))->get(),
                    'token'     => $token,
                )
            ]);
        }
        

    }

    public function register(request $request){
        if(User::where('username',$request->username)->count() <= 0){
            $status = "success";
            if(User::where('email',$request->email)->count() <= 0){
                $status = "success";
            }else{
                $error = 1;
                $status = "Email sudah terdaftar";
            }
        } else{
            $status = "Username sudah digunakan";
            $error = 1;
        }
        $current_date_time = date('Y-m-d H:i:s');
        $uppercase = preg_match('@[A-Z]@', $request->password);
        $lowercase = preg_match('@[a-z]@', $request->password);
        $number    = preg_match('@[0-9]@', $request->password);
        $specialChars = preg_match('@[^\w]@', $request->password);
        
        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($request->password) < 8) {
            $status ="Pasword setidaknya harus 8 karakter dan harus memiliki huruf besar, huruf kecil, angka, dan spesial karakter.";
            $error = 1;
        }

        if(isset($error) != 1){
            $query = User::insert(
                [
                    'username' => $request->username, 
                    'password' => md5($request->password),
                    'profile_picture' => $request->profile_picture,
                    'nickname' => $request->nick_name, 
                    'fullname' => $request->full_name, 
                    'email' => $request->email, 
                    'birthday' => $request->tanggal_lahir, 
                    'phone_number' => $request->phone_number, 
                    'gender' => $request->gender,
                    'status' => 'Waiting Activation',
                    'create_at' => $current_date_time
                ]
            );

            if($query == 1){
                $userid = User::where('username',$request->username)->value('id');
                Role::insert([
                    'userId'=> $userid,
                    'meta_role' => 'User'
                ]);
                $status = "Registration Success. Please Verified Your Account";
                $urlActivation =  '/profile/ActivateAccount?id=';
                $lastid = base64_encode($userid );
                 Mail::to($request->email)->send(new activateEmail(env('Activate_Account_URL') . $urlActivation . $lastid));
                
            }
            $emails = $request->email;

            return response()->json([
                'status'=>$status,
            ]);
        } else {
            return response()->json([
                'status'=>$status
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
        $query = User::where('id',$userid)->update(
            [
            'profile_picture' => env('IMAGE_URL') . $file
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

    public function update_query(request $request){
        
        $id = $request->query('id');
        $current_date_time = date('Y-m-d H:i:s');
        $query = User::find($id)->update(
            [
                'username' => $request->username, 
                'profile_picture' => $request->profile_picture,
                'nickname' => $request->nick_name, 
                'fullname' => $request->full_name, 
                'birthday' => $request->tanggal_lahir, 
                'phone_number' => $request->phone_number, 
                'gender' => $request->gender,
                'update_at' => $current_date_time
            ]
        );

        if($query == 1){
            $status = 'success';
        } else{
            $status = 'failed';
        }

        return response()->json([
            'status'=>$status,
            'results'=>User::where('id',$id)->select('username','profile_picture','nickname','fullname','birthday','phone_number','gender')->get()
        ]);
    }

    public function update_token(request $request){
        
        $token = $request->header("Authorization");
       
        $result = $this->JWTValidator->validateToken($token);
        $current_date_time = date('Y-m-d H:i:s');
        if($result['status'] == 200){
            if(filter_var($request->profile_picture, FILTER_VALIDATE_URL) === FALSE){

                $image_parts = explode(";base64,", $request->profile_picture);
                $image_type_aux = explode("image/", $image_parts[0]);
                $image_type = $image_type_aux[1];
                $image_base64 = base64_decode($image_parts[1]);
                $file = uniqid() . '.'.$image_type;
        
                file_put_contents(env('Folder_APP').$file, $image_base64);
                $picture = env('IMAGE_URL') . $file;
                
            }else{
                $picture = $request->profile_picture;
            }
            $user = $result['body']['user_id'];
            User::where('id', $user)->update(
                [   
                    'username' => $request->username,
                    'profile_picture' => $picture,
                    'nickname' => $request->nick_name, 
                    'fullname' => $request->full_name, 
                    'birthday' => $request->tanggal_lahir, 
                    'phone_number' => $request->phone_number, 
                    'gender' => $request->gender,
                    'update_at' => $current_date_time
                ]);
            return response()->json([
                'status'=>'success', 
                'result'=> User::where('id',$user)->select('username','profile_picture','nickname','fullname','birthday','phone_number','gender')->get(),
                ]);
                
        }else{
            return  $result;
            
        }
    }

    public function sosmedlogin(request $request){
        
        $query = User::where("email",$request->email)->where("sosmed_login",$request->sosmed_id);
        $emailvalid = User::where("email",$request->email);
        $current_date_time = date('Y-m-d H:i:s');
        if($query->count() == 1){
            $status = "Login Success";
            $secret = str_shuffle('abcdesfrtysjndncdj').str_shuffle('!@#$%*').rand(10,1000).str_shuffle('QWERTY');
            $session_id = str_shuffle('abcdesfrtysjndncdj').rand(10,1000);
            user_secret::insert(
                [
                    'user_id' => $query->value('id'), 
                    'user_secret' => $secret,
                    'session_id' => $session_id,
                ]
            );
            
            $token = $this->JWTValidator->createToken($query->value('id'), $query->value('username'),$session_id, $secret);
                      
            return response()->JSON([
                'status' => $status,
                'token' => $token
            ]);
        } else if(isset($request->email)&&isset($request->sosmed_id)&&$query->count()==0&&$emailvalid->count()==1){
            $addsosmedid = User::where("email",$request->email)->update([
                'sosmed_login' => $request->sosmed_id,
                'status' => 'Active'
            ]);
                $status = "Login Success";
                $secret = str_shuffle('abcdesfrtysjndncdj').str_shuffle('!@#$%*').rand(10,1000).str_shuffle('QWERTY');
                $session_id = str_shuffle('abcdesfrtysjndncdj').rand(10,1000);
                user_secret::insert(
                    [
                        'user_id' => $query->value('id'), 
                        'user_secret' => $secret,
                        'session_id' => $session_id,
                    ]
                );
                
                $token = $this->JWTValidator->createToken($addsosmedid->value('id'), $qaddsosmediduery->value('username'),$session_id, $secret);           
               
                return response()->JSON([
                    'status' => $status,
                    'token' => $token,
                ]);
        } else{
            $insertnew = User::insertGetId([
                    'username' => $request->username, 
                    'profile_picture' => $request->profile_picture,
                    'nickname' => $request->nick_name, 
                    'fullname' => $request->full_name, 
                    'email' => $request->email, 
                    'birthday' => $request->tanggal_lahir, 
                    'phone_number' => $request->phone_number, 
                    'gender' => $request->gender,
                    'status' => 'Active',
                    'sosmed_login' => $request->sosmed_id,
                    'create_at' => $current_date_time
            ]);

                $query = User::where('id',$insertnew)->where('email',$request->email);

                $secret = str_shuffle('abcdesfrtysjndncdj').str_shuffle('!@#$%*').rand(10,1000).str_shuffle('QWERTY');
                $session_id = str_shuffle('abcdesfrtysjndncdj').rand(10,1000);
                user_secret::insert(
                    [
                        'user_id' => $query->value('id'), 
                        'user_secret' => $secret,
                        'session_id' => $session_id,
                    ]
                );
                
                $token = $this->JWTValidator->createToken($insertnew,  $query->value('username'),$session_id, $secret);           
            
                Role::insert([
                    'userId' => $insertnew,
                    'meta_role'=> 'User'
                ]);

                $status = "Registration Success";
                return response()->JSON([
                    'status' => $status,
                    'token' => $token,
                ]);
            
        }
        
    }

    public function ActivateEmail(Request $request){
        $id = base64_decode($request->query('id'));

        $query = User::find($id)->update(
            [
                'status' => 'Active',
            ]
        );

        if($query == 1){
            return view('AccountActive');
        } else{
            return response()->json([
                'status'=>'failed'
            ]);
        }
    }
}
