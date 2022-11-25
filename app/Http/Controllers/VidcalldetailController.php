<?php

namespace App\Http\Controllers;

use App\Models\vidcalldetail;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\MobileBannerController;
use App\Http\Controllers\FirebaseTokenController;
use App\Models\orderservice;


class VidcalldetailController extends Controller
{
    protected $request;

    public function __construct(Request $request,FirebaseTokenController $fb_token,MobileBannerController $mobile_banner) {
        $this->request = $request;
        $this->fb_token = $fb_token;
        $this->mobile_banner = $mobile_banner;
    }

    public function vidcallhit(request $request){
        $type = $request->type;
        $rolename = $request->data['roleName'];
        $meetingid = $request->data['meetingId'];
        $time = $request->createdAt;

        $roomisvalid = vidcalldetail::where('meeting_id',$meetingid);

        if($type == 'room.client.joined'){
            if($roomisvalid->count()==1){
                if($rolename == 'host'){
                    $update = vidcalldetail::where('meeting_id',$meetingid)->update([
                        'partner_join_time' => carbon::now()->timestamp,
                        'updated_at' => carbon::now()
                    ]);
                    $token_fb = $this->fb_token->userFirebaseToken( orderservice::where('order_id','like',$roomisvalid->value('booking_id'))->value('users_ids'),'Consumer App');
                    foreach( $token_fb as $token){
                        if($token['firebase_token'] != NULL){
                            $notification = $this->mobile_banner->send_notif('Your doctor is waiting for you','Join now','','',$token['firebase_token'],"/order-detail"."/".$roomisvalid->value('booking_id'),NULL);
                        }
                    }
                } else{
                    $update = vidcalldetail::where('meeting_id',$meetingid)->update([
                        'user_join_time' => carbon::now()->timestamp,
                        'updated_at' => carbon::now()
                    ]);
                }
    
                if($update==1){
                    return response()->JSON([
                        'status' => 'success',
                        'results' => vidcalldetail::where('meeting_id',$meetingid)->get()
                    ]);
                }
            } else{
                return response()->JSON([
                    'status' => 'error',
                    'msg' => 'room is not valid'
                ]);
            }
        } else if($type == 'room.session.ended'){
            if($roomisvalid->count()==1){
                $update = vidcalldetail::where('meeting_id',$meetingid)->update([
                    'session_done_time' => carbon::now()->timestamp,
                    'status' => 'DONE'
                ]);
            if($update==1){
                return response()->JSON([
                    'status' => 'success',
                ]);
            }
        } else{
                return response()->JSON([
                    'status' => 'error',
                    'msg' => 'room is not valid'
                ]);
            }
        }

    }
}
