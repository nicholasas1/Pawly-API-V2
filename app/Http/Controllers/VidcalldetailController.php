<?php

namespace App\Http\Controllers;

use App\Models\vidcalldetail;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VidcalldetailController extends Controller
{
    protected $request;

    public function __construct(Request $request) {
        $this->request = $request;
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
