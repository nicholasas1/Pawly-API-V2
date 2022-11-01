<?php

namespace App\Http\Controllers;

use App\Models\mobile_banner;
use Illuminate\Http\Request;

class MobileBannerController extends Controller
{
    //
    public function send_notif($title,$body,$image,$url,$recipient,$route,$event){

            $postdata = json_encode(
                [
                    'notification' => 
                        [
                            'title' => $title,
                            'body' => $body,
                            'icon' => $image,
                            'click_action' => $url
                        ]
                    ,
                    'to' => $recipient,
                    'data' => [
                        'route' => $route,
                        'event' => $event
                    ]
                ]
            );

            $opts = array('http' =>
                array(
                    'method'  => 'POST',
                    'header'  => 'Content-type: application/json'."\r\n"
                                .'Authorization: key='.env('FCM_SERVER_KEY')."\r\n",
                    'content' => $postdata
                )
            );

            $context  = stream_context_create($opts);

            $result = file_get_contents('https://fcm.googleapis.com/fcm/send', false, $context);
            if($result) {
                return json_decode($result);
            } else return false;

    }

    public function stream(){
        return response()->stream(function () {
            while (true) {
                echo "event: ping\n";
                $curDate = date(DATE_ISO8601);
                echo 'data: {"time": "' . $curDate . '"}';
                echo "\n\n";

                ob_flush();
                flush();

                // Break the loop if the client aborted the connection (closed the page)
                if (connection_aborted()) {break;}
                usleep(50000); // 50ms
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Content-Type' => 'text/event-stream',
        ]);
    }

    public function notificationdata(request $request){
        $notification = $this->send_notif($request->title,$request->body,$request->image,$request->url,$request->recipient,$request->route,$request->event,NULL,NULL);
        if($notification->success==1){
            return response()->JSON([
                'status' => 'success',
                'results' => $notification->results[0]
            ]);
        }
    }

    public function createbanner(request $request){
        $query = mobile_banner::insert([
            'index' => $request->index,
            'image_url' => $request->imgurl,
            'isactive' => 'false',
            'url' => $request->url,
            'text' => $request->body
        ]);

        if($query == 1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error'
            ]);
        }

    }

    public function editbanner(request $request){
        $query = mobile_banner::where('id',$request->id)->update([
            'index' => $request->index,
            'image_url' => $request->imgurl,
            'url' => $request->url,
            'text' => $request->body
        ]);

        if($query==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error'
            ]);
        }
    }

    public function togglebanner(request $request){
        $toggle = $request->toggle;

        if($toggle == 'true'){
            $query = mobile_banner::where('id',$request->id)->update([
                'isactive' => 'true'
            ]);
        } else if ($toggle == 'false'){
            $query = mobile_banner::where('id',$request->id)->update([
                'isactive' => 'false'
            ]);
        } else{
            $status = 'error';
        }

        return response()->JSON([
            'status' => $status
        ]);
    }

    public function deletebanner(request $request){
        $bannerid = $request->id;

        $query = mobile_banner::where('id',$bannerid)->delete();

        if($query==1){
            return response()->JSON([
                'status' => 'success'
            ]);
        } else{
            return response()->JSON([
                'status' => 'error'
            ]);
        }
    }
}
