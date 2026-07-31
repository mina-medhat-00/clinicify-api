<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class feedbackController extends Controller
{
    public function filter_feedback()
    {

         $state= "good, ok";
         $message = "information retreived successfully";
        if(isset($_GET['username'])){
            $username=$_GET['username'];
            $doctor = Doctor::where('username', $username)->first();
            if(!empty($doctor)){
                $doctor_user = User::where('username', $username)->first();
                $feedback = feedback::where('feedback_to', $doctor->user_id)->skip(0)->take(5)->get();
                $data = array();
                foreach ($feedback as $f) {
                    $userpat = User::where('id', $f->feedback_from)->first();
                    $userData = [
                                'feedback_from' => $f->feedback_from,
                                'feedback_to' => $f->feedback_to,
                                'rate' => $f->rate,
                                'issued_time' => $f->updated_at,
                                'feedback' => $f->feedback,
                                'uimgUrl' => asset('storage/' . $userpat->img_url),
                                'dimgUrl' => asset('storage/' . $doctor_user->img_url),
                                'username' => $userpat->username,
                                'doctorName' => $doctor->name,
                    ];
                    array_push($data, $userData);

                }
                return response(compact('state','message','data'), 200);
            }
            //else for id
            else{
                $doctor = Doctor::where('user_id', $username)->first();

                $doctor_user = User::where('id', $username)->first();
                $feedback = feedback::where('feedback_to', $doctor->user_id)->skip(0)->take(5)->get();
                $data = array();
                foreach ($feedback as $f) {
                    $userpat = User::where('id', $f->feedback_from)->first();
                    $userData = [
                        'feedback_from' => $f->feedback_from,
                        'feedback_to' => $f->feedback_to,
                        'rate' => $f->rate,
                        'issued_time' => $f->updated_at,
                        'feedback' => $f->feedback,
                        'uimgUrl' => asset('storage/' . $userpat->img_url),
                        'dimgUrl' =>asset('storage/' . $doctor_user->img_url),
                        'username' => $userpat->username,
                        'doctorName' => $doctor->name,
                    ];
                    array_push($data, $userData);

                }
                return response(compact('state','message','data'), 200);
            }

        }
        if(isset($_GET['limit'])){
            $limit=$_GET['limit'];
            $feedback = feedback::limit($limit)->get();
            $data = array();
            foreach ($feedback as $f) {
                $userpat = User::where('id', $f->feedback_from)->first();
                $userpdoc = User::where('id', $f->feedback_to)->first();
                $userData = [
                            'feedback_from' => $f->feedback_from,
                            'feedback_to' => $f->feedback_to,
                            'rate' => $f->rate,
                            'issued_time' => $f->updated_at,
                            'feedback' => $f->feedback,
                            'uimgUrl' => asset('storage/' . $userpat->img_url),
                            'dimgUrl' => asset('storage/' . $userpdoc->img_url),
                            'username' => $userpat->username,
                            'doctorName' => $userpdoc->name,
                ];
                array_push($data, $userData);

            }
            return response(compact('state','message','data'), 200);
        }
        $feedback = feedback::limit(10)->get();
        $data = array();
        foreach ($feedback as $f) {
            $userpat = User::where('id', $f->feedback_from)->first();
            $userpdoc = User::where('id', $f->feedback_to)->first();
            $userData = [
                        'feedback_from' => $f->feedback_from,
                        'feedback_to' => $f->feedback_to,
                        'rate' => $f->rate,
                        'issued_time' => $f->updated_at,
                        'feedback' => $f->feedback,
                        'uimgUrl' => asset('storage/' . $userpat->img_url),
                        'dimgUrl' => asset('storage/' . $userpdoc->img_url),
                        'username' => $userpat->username,
                        'doctorName' => $userpdoc->name,
            ];
            array_push($data, $userData);
        }
        return response(compact('state','message','data'), 200);

    }
    public function add_feedback(Request $request)
    {
        if(!Auth::user()){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $user_id =Auth::user()->id;
        $all_data = ($request->input('data'));
        $rate=$all_data['rate'];
        $feedback=$all_data['feedback'];
        $feedback_to=$all_data['feedback_to'];
        $all_feed=Feedback::where([['feedback_from', '=', $user_id],['feedback_to', '=', $feedback_to]])->first();
/*        return response(compact('all_feed'), 200);*/

        if(empty($all_feed)) {
            $feed=Feedback::create([
                'feedback_from'=>$user_id,
                'feedback_to'=>$feedback_to,
                'rate'=>$rate,
                'feedback'=>$feedback,
            ]);
            $first=true;
        }
        else{
            $all_feed->delete();
            $feed=Feedback::create([
                'feedback_from'=>$user_id,
                'feedback_to'=>$feedback_to,
                'rate'=>$rate,
                'feedback'=>$feedback,
            ]);
            $first=false;
        }



        $state="good, ok";
        $message="your data added successfully";
        $data = [
            'isFirst' => $first
        ];
        return response(compact('state', 'message', 'data'), 200);
    }
}
