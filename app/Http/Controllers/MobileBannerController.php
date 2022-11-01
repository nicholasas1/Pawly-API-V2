<?php

namespace App\Http\Controllers;

use App\Models\mobile_banner;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        $random_string = chr(rand(65, 90)) . chr(rand(65, 90)) . chr(rand(65, 90));
        $data = [
            'message' => $random_string,
            'name' => 'Sadhan Sarker',
            'time' => date('h:i:s'),
            'id' => rand(10, 100),
        ];

        $response = new StreamedResponse();
        $response->setCallback(function () use ($data){

             echo 'data: ' . json_encode($data) . "\n\n";
             //echo "retry: 100\n\n"; // no retry would default to 3 seconds.
             //echo "data: Hello There\n\n";
             ob_flush();
             flush();
             //sleep(10);
             usleep(2000);
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('X-Accel-Buffering', 'no');
        $response->headers->set('Cach-Control', 'no-cache');

        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:8100');
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, Accept, X-Requested-With');


        $response->send();
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
