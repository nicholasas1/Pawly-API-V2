<?php

namespace App\Http\Controllers;

use App\Models\paymentmeth;
use Illuminate\Http\Request;
use stdClass;
use App\Models\wallet;
use App\Http\Controllers\JWTValidator;
use App\Models\orderservice;

class PaymentmethController extends Controller
{
    protected $JWTValidator;
    public function __construct(JWTValidator $JWTValidator)
    {
        $this->JWTValidator = $JWTValidator;
    }
    

    //$service,$payment
    public function payment_method(Request $request){
        $token = $request->header("Authorization");
        $result = $this->JWTValidator->validateToken($token);
        $wallet = wallet::where('users_ids', $result['body']['user_id']);
        $ammount = $wallet->sum('debit') - $wallet->sum('credit');
        $total_transaction = orderservice::where('order_id','like',$request->order_id)->value('subtotal');
       
        $arr = array(
            array('payment_method' => 'Wallet',
            'total_ammount'=>$ammount,
            'image_url'=>'https://www.pngrepo.com/png/287670/512/wallet.png',
            'payment_id'=>'',
            'description'=>'Make your life more simple'),

            array('payment_method' => 'BCA Transfer',
            'total_ammount'=>null,
            'image_url'=>'https://app.moota.co/images/icon-bank-bca.png',
            'payment_id'=>'1KwjmN2BWrl',
            'description'=>'make your life more simple'),
            

            array('payment_method' => 'Dana',
            'total_ammount'=>null,
            'image_url'=>'https://upload.wikimedia.org/wikipedia/commons/thumb/7/72/Logo_dana_blue.svg/2560px-Logo_dana_blue.svg.png',
            'payment_id'=>'',
            'description'=>'make your life more simple'),

            array('payment_method' => 'Ovo',
            'total_ammount'=>null,
            'image_url'=>'https://upload.wikimedia.org/wikipedia/commons/thumb/e/eb/Logo_ovo_purple.svg/2560px-Logo_ovo_purple.svg.png',
            'payment_id'=>'',
            'description'=>'make your life more simple'),
           
            array('payment_method' => 'GoPay',
            'total_ammount'=>null,
            'image_url'=>'https://upload.wikimedia.org/wikipedia/commons/thumb/8/86/Gopay_logo.svg/2560px-Gopay_logo.svg.png',
            'payment_id'=>'',
            'description'=>'make your life more simple'),

        );
      
        $payment_allowed = new stdClass;
        if($result['status'] == 200){
            if($request->payment==NULL){
                $allowed_payment = paymentmeth::where('service',$request->service)->value('allowed_payment');
                $payment_allowed = array();
                if($allowed_payment == NULL){
                    foreach($arr as $arr){ //statis
                        if($arr['payment_method'] == "Wallet" && $total_transaction > $ammount){
                            $active = false;
                            $desc = "Sorry, your balance is less than the total transaction";
                        }else{
                            $active = true;
                            $desc = $arr['description'];
                        }
                        $method = array(
                            'payment_method' => $arr['payment_method'],
                            'total_ammount'=>$arr['total_ammount'],
                            'image_url'=>$arr['image_url'],
                            'payment_id'=>$arr['payment_id'],
                            'description'=> $desc,
                            'active'=>$active
                        );
                        array_push($payment_allowed, $method);
                    }
                }else{
                    $payment_allowed = array();
                    foreach($arr as $arr){ //statis
                        foreach(unserialize($allowed_payment) as $allow){ // dari db      
                            if($arr['payment_method'] == $allow){
                                if($arr['payment_method'] == "Wallet" && $total_transaction > $ammount){
                                    $active = false;
                                    $desc = "Sorry, your balance is less than the total transaction";
                                }else{
                                    $active = true;
                                    $desc = $arr['description'];
                                }
                                $method = array(
                                    'payment_method' => $arr['payment_method'],
                                    'total_ammount'=>$arr['total_ammount'],
                                    'image_url'=>$arr['image_url'],
                                    'payment_id'=>$arr['payment_id'],
                                    'description'=> $desc,
                                    'active'=>$active
                                );
                                break;
                            }else{
                                $method = array(
                                    'payment_method' => $arr['payment_method'],
                                    'total_ammount'=>$arr['total_ammount'],
                                    'image_url'=>$arr['image_url'],
                                    'description'=>"This payment cannot be used for this transaction",
                                    'active'=>false
                                );
                            }
                        }
                        array_push($payment_allowed, $method);
                    }
                }
               
                
                return response()->JSON([
                    'status' => 'success',
                    'results' => $payment_allowed
                ]);
            } else{
                $allowed_payment = unserialize($request->payment);
                
                $payment_allowed = array();
                foreach($arr as $arr){ //statis
                    foreach($allowed_payment as $allow){ // dari db
              

                    if($arr['payment_method'] == $allow){
                        if($arr['payment_method'] == "Wallet" && $total_transaction > $ammount){
                            $active = false;
                            $desc = "Sorry, your balance is less than the total transaction";
                        }else{
                            $active = true;
                            $desc = $arr['description'];
                        }
                        $method = array(
                            'payment_method' => $arr['payment_method'],
                            'total_ammount'=>$arr['total_ammount'],
                            'image_url'=>$arr['image_url'],
                            'payment_id'=>$arr['payment_id'],
                            'description'=> $desc,
                            'active'=>$active
                        );
                        break;
                    }else{
                      
                        $method = array(
                            'payment_method' => $arr['payment_method'],
                            'total_ammount'=>$arr['total_ammount'],
                            'image_url'=>$arr['image_url'],
                            'payment_id'=>$arr['payment_id'],
                            'description'=>$arr['description'],
                            'active'=>false
                        );
                    }
                }
                array_push($payment_allowed, $method);
                }

                
                return response()->JSON([
                    'status' => 'success',
                    'results' => $payment_allowed
                ]);
            }
        }else{
            return array(
                $result
            );
        }

       

    }   
}
