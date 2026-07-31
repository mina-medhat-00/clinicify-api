<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Doctor;
use App\Models\Feedback;
use App\Models\Message;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class chatController extends Controller
{
    public function add_msg(Request $request){
        if(!Auth::user()){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $user1_id =Auth::user()->id;

        $all_data = ($request->input('data'));
        $content=isset($all_data['content'])?$all_data['content']:null;
        $user2_id=isset($all_data['message_to']) ? $all_data['message_to']:2;
        $old_chat=Chat::where([['chat_from','=',$user1_id],['chat_to','=',$user2_id]])
            ->orwhere([['chat_from','=',$user2_id],['chat_to','=',$user1_id]])->get();;

        if(count($old_chat)==0 and $user1_id!=$user2_id ){
            $chat1=  Chat::create([
                'chat_from'=>$user1_id,
                'chat_to'=>$user2_id,
            ]);

            $chat2=  Chat::create([
                'chat_from'=>$user2_id,
                'chat_to'=>$user1_id,
            ]);
            $user1_admin=User::where('id','=',$user1_id)->first();
            $user2_admin=User::where('id','=',$user2_id)->first();

            if(($user1_admin->user_type=="admin") or ($user2_admin->user_type=="admin")){
                $chat1->is_open=1;
                $chat1->update();
                $chat2->is_open=1;
                $chat2->update();
            }


        }
        $sender_chat=Chat::where('chat_from','=',$user1_id)->first();
        $x=User::where('id','=',$user1_id)->first();
        if(($x->user_type=="user") && ($sender_chat->is_open != 1)){
            $state = "not authorized to access";
            $message = "cannot access to api resources";
            $data = [
                'isUser' => 0
            ];
            return response(compact('state', 'message', 'data'), 401);
        }

            $msg=Message::create([
            'message_from'=>$user1_id,
            'message_to'=>$user2_id,
            'content'=>$content,
            'issued_date'=>Carbon::now()->addHours(1),
            'issued_time'=>Carbon::now()->addHours(1),
        ]);
        $time=$msg->issued_time;
        $temp = explode(' ',$time);
        $state="good, ok";
        $message="your data added successfully";
        $data = [
            'message_id' => $msg->id,
            'issued_date' => $temp[0],
            'issued_time' => $temp[1]
        ];
        return response(compact('state', 'message', 'data'), 200);
    }

    public function get_msg()
    {
        if(!Auth::user()){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $user1_id =Auth::user()->id;
        if(isset($_GET['message_to'])){
            $user2_id =$_GET['message_to'];
            $old_chat=Chat::where([['chat_from','=',$user1_id],['chat_to','=',$user2_id]])
                ->orwhere([['chat_from','=',$user2_id],['chat_to','=',$user1_id]])->get();

            if(count($old_chat)==0 and $user1_id!=$user2_id) {
                $chat1 = Chat::create([
                    'chat_from' => $user1_id,
                    'chat_to' => $user2_id,
                ]);
                $chat2 = Chat::create([
                    'chat_from' => $user2_id,
                    'chat_to' => $user1_id,
                ]);
                $user1_admin = User::where('id', '=', $user1_id)->first();
                $user2_admin = User::where('id', '=', $user2_id)->first();
                if(($user1_admin->user_type=="admin") or ($user2_admin->user_type=="admin")){
                    $chat1->is_open=1;
                    $chat1->update();
                    $chat2->is_open=1;
                    $chat2->update();
                }
            }
        }

        $all_msg=Message::where([['message_from', '=', $user1_id],['message_to', '=', $user2_id]])
            ->orwhere([['message_from', '=', $user2_id],['message_to', '=', $user1_id]])->get();
        $data = array();
        foreach ($all_msg as $msg){
            $time=$msg->issued_time;
            $temp = explode(' ',$time);
            if($user1_id ==$msg->message_from) {
                $res = [
                    'message_id' => $msg->id,
                    'message_from' => $user1_id,
                    'message_to' => $user2_id,
                    'content' => $msg->content,
                    'issued_date' => $temp[0],
                    'issued_time' => $temp[1]
                ];
            }else{
                $res = [
                    'message_id' => $msg->id,
                    'message_from' => $user2_id,
                    'message_to' => $user1_id,
                    'content' => $msg->content,
                    'issued_date' => $temp[0],
                    'issued_time' => $temp[1]
                ];
            }
            array_push($data, $res);
        }
        $state="good, ok";
        $message="your data added successfully";
        return response(compact('state', 'message', 'data'), 200);
    }
    public function get_chat(){
        if(!Auth::user()){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $user1_id =Auth::user()->id;
        $user1 =Auth::user();
        $all_chat=Chat::where([['chat_from', '=', $user1_id]])->get();
        $data = array();
        if(isset($_GET['userid']) && $user1->user_type=="admin"){
            $chat_to =$_GET['userid'];
            $all_chat=Chat::where([['chat_from', '=', $chat_to]])->get();
            foreach ($all_chat as $chat){
                $user2=User::where([['id', '=', $chat->chat_to]])->first();
                $spec=null;
                $rate=null;
                if($user2->user_type=="doctor"){
                    $user2doctor=Doctor::where([['user_id', '=', $chat->chat_to]])->first();
                    $spec=$user2doctor->specialty;
                    $rate=Feedback::where('feedback_to','=',$chat->chat_to)->avg('rate');
                }
                $res=[
                    'chat_from'=> $chat_to,
                    'chat_to'=> $user2->id,
                    'nick_name'=> $user2->name,
                    'user_name'=>$user2->username,
                    'user_id'=>$user2->id,
                    'img_url'=>asset('storage/' . $user2->img_url),
                    'user_type'=>$user2->user_type,
                    'specialty'=>$spec,
                    'is_open'=>$chat->is_open,
                    'admin_restrict'=>1,
                    'rate'=>$rate?$rate:0
                ];
                array_push($data, $res);
            }
            $state="good, ok";
            $message="your data added successfully";
            return response(compact('state', 'message', 'data'), 200);
        }


        foreach ($all_chat as $chat){
            $user2=User::where([['id', '=', $chat->chat_to]])->first();
            $spec=null;
            $rate=null;
            if($user2->user_type=="doctor"){
                $user2doctor=Doctor::where([['user_id', '=', $chat->chat_to]])->first();
                $spec=$user2doctor->specialty;
                $rate=Feedback::where('feedback_to','=',$chat->chat_to)->avg('rate');
            }
            $res=[
                'chat_from'=> $user1_id,
                'chat_to'=> $user2->id,
                'nick_name'=> $user2->name,
                'user_name'=>$user2->username,
                'user_id'=>$user2->id,
                'img_url'=>asset('storage/' . $user2->img_url),
                'user_type'=>$user2->user_type,
                'specialty'=>$spec,
                'is_open'=>$chat->is_open,
                'rate'=>$rate?$rate:0
            ];
            array_push($data, $res);
        }
        $state="good, ok";
        $message="your data added successfully";
        return response(compact('state', 'message', 'data'), 200);

    }

}
