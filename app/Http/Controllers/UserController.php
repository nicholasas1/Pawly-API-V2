<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\role;
use App\Models\userpets;
use Illuminate\Support\Facades\DB;
use ReallySimpleJWT\Token;
use ReallySimpleJWT\Parse;
use ReallySimpleJWT\Jwt;
use ReallySimpleJWT\Decode;
use App\Http\Controllers\JWTValidator;
use App\Mail\activateEmail;
use Illuminate\Support\Facades\Mail;


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
                'success'=>'succes', 
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
            return response()->json([
                'success'=>'succes', 
                'results'=>array([
                    'user' => User::where('id',$userid)->select(['username','email','nickname','fullname','phone_number','birthday','gender','profile_picture'])->get(),
                    'role' => role::where('userId',$userid)->select(['meta_role','meta_id'])->get(),
                    'pets' => userpets::where('user_id',$userid)->select(['petsname','species','breed','gender','birthdate'])->get()
                ])
            ]);
        }else{
            return array(
                $result
            );
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
        $query = User::where($field,$request->username)->where("password",md5($request->password));
        if($query->count()== 0){
                $status = "Invalid Username or Password";
        }else{
            $status="success";
        }

        if($query->value('status') == "Waiting Activation"){
            $status="Your account is not active. Please check your email to activate your account";
        }
        
        $token = $this->JWTValidator->createToken($query->value('id'), $query->value('username'));
      
        return response()->json([
            'status'=>$status, 
            'results'=> array(
                'username'  => $query->value('username'),
                'role'      => role::where('userId',$query->value('id'))->get(),
                'token'     => $token,
            )
        ]);

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
                    'status' => 'Waiting Activation'
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
        }
        

        return response()->json([
            'status'=>$status
        ]);
        
       
    }
    public function uploadBase64(request $request)
    {

        $image_parts = explode(";base64,", $request->img);
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = $image_type_aux[1];
        $image_base64 = base64_decode($image_parts[1]);
        $file = uniqid() . '.'.$image_type;

        file_put_contents(env('Folder_APP').$file, $image_base64);

        return response()->json([
            'status'=>"success", 
            'results'=> array(
                'file_path'  => $file,
                'file_url'   => env('IMAGE_URL') . $file,
            )
        ]);

    }
    public function update_query(request $request){
        
        $id = $request->query('id');
        $query = User::find($id)->update(
            [
                'username' => $request->username, 
                'profile_picture' => $request->profile_picture,
                'nickname' => $request->nick_name, 
                'fullname' => $request->full_name, 
                'birthday' => $request->tanggal_lahir, 
                'phone_number' => $request->phone_number, 
                'gender' => $request->gender
            ]
        );

        if($query == 1){
            $status = 'sukses';
        } else{
            $status = 'gagal';
        }

        return response()->json([
            'status'=>$status
        ]);
    }

    public function update_token(request $request){
        
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);

        if($result['status'] == 200){

            $user = $result['body']['user_id'];
            User::where('id', $user)->update(
                [   
                    'username' => $request->username,
                    'profile_picture' => $request->profile_picture,
                    'nickname' => $request->nick_name, 
                    'fullname' => $request->full_name, 
                    'birthday' => $request->tanggal_lahir, 
                    'phone_number' => $request->phone_number, 
                    'gender' => $request->gender
                ]);
            return response()->json([
                'success'=>'succes', 
                'result'=> User::where('id',$user)->get()
                ]);
        }else{
            return array(
                $result
            );
            
        }
    }

    public function sosmedlogin(request $request){
        
        $query = User::where("email",$request->email)->where("sosmed_login",$request->sosmed_id);
        $emailvalid = User::where("email",$request->email);
        $token = $this->JWTValidator->createToken($query->value('id'),$query->value('email'));
        
        if($query->count() == 1){
            $status = "Login Success"; 
                return response()->JSON([
                    'status' => $status,
                    'results' => array([
                        'User' => User::where('email',$request->email)->where('sosmed_login',$request->sosmed_id)->get(),
                        'token' => $token
                        ])
                ]);
        } else if(isset($request->email)&&isset($request->sosmed_id)&&$query->count()==0&&$emailvalid->count()==1){
            User::where("email",$request->email)->update([
                'sosmed_login' => $request->sosmed_id,
                'status' => 'Active'
            ]);
                $status = "Login Success";
                    return response()->JSON([
                    'status' => $status,
                    'token' => $token,
                    'results' => array([
                        'User' => User::where('email',$request->email)->where('sosmed_login',$request->sosmed_id)->get(),
                        'token' => $token
                        ])
                ]);
        } else{
            User::insertGetId([
                    'username' => $request->username, 
                    'password' => md5($request->password),
                    'profile_picture' => $request->profile_picture,
                    'nickname' => $request->nick_name, 
                    'fullname' => $request->full_name, 
                    'email' => $request->email, 
                    'birthday' => $request->tanggal_lahir, 
                    'phone_number' => $request->phone_number, 
                    'gender' => $request->gender,
                    'status' => 'Active',
                    'sosmed_login' => $request->sosmed_id
            ]);

                $status = "Registration Success";
                return response()->JSON([
                    'status' => $status,
                    'results' => array([
                        'token' => $token,
                    ])
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
                'success'=>'failed'
            ]);
        }
    }
}
