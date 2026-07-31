<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Clinic_detail;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class adminController extends Controller
{
    public function verification(Request $request){
        if(!Auth::user()){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $all_data = ($request->input('data'));
        $doctor_id=$all_data['doctorId'];
        $type=$all_data['type'];

        $doctor = Doctor::where('user_id', $doctor_id)->first();
        $doctor->type=$type;
        $doctor->save();
        $state= 'good, ok';
        $message = 'information retreived successfully';
        $data = [
            'isDone' => true,

        ];
        return response(compact('state', 'message', 'data'), 200);
    }
    public function change_user(Request $request)
    {
        if (!Auth::user()) {
            $state = "not authorized to access";
            $message = "cannot access to api resources";
            $data = [
                'isUser' => 0
            ];
            return response(compact('state', 'message', 'data'), 401);
        }
        $all_data = ($request->input('data'));
        if (isset($all_data['userId']) and isset($all_data['type'])) {
            $type = $all_data['type'];
            $user_id = $all_data['userId'];
            if ($type == 'delete') {
                User::where('id', '=', $user_id)->delete();

            }
            $state = 'good, ok';
            $message = 'information retreived successfully';
            $data = [
                'isDone' => true
            ];
            return response(compact('state', 'message', 'data'), 200);

        }

        if (isset($all_data['chat_from']) and isset($all_data['chat_to'])) {
            if ($all_data['type'] == 'restrict') {
                $chat_from = $all_data['chat_from'];
                $chat_to = $all_data['chat_to'];
                $is_open = $all_data['is_open'];
                $type = $all_data['type'];
                $chats = Chat::where([['chat_from', '=', $chat_from], ['chat_to', '=', $chat_to]])->first();
                $chats->is_open = $is_open;
                $chats->save();

            }
        }
        $state= 'good, ok';
        $message = 'information retreived successfully';
        $data = [
            'isDone' => true
        ];
        return response(compact('state', 'message', 'data'), 200);
    }

    public function users()
    {
        $data = array();
        $users = User::all();
        /*                return response(compact('flag'),200);*/
        if (!empty($users)) {
            foreach ($users as $user){
            if ($user->user_type == 'doctor') {
                $doctor=Doctor::where('id',$user->id)->first();
                $clinic=Clinic_detail::where('doctor_id',$user->id)->first();
                $userData = [
                            'user_id' => $user->id,
                            'nick_name' => $user->name,
                            'user_type' => $user->user_type,
                            'bdate' => $user->bdate,
                            'gender' => $user->gender,
                            'prefix' => $user->prefix,
                            'pnumber' => $user->pnumber,
                            'email' => $user->email,
                            'province' => $user->province,
                            'city' => $user->city,
                            'street' => $user->street,
                            'img_url' => asset('storage/' . $user->img_url),
                            'age' => Carbon::parse($user->bdate)->age,
                            'img_urls' => [
                                [
                                    'img_url' => asset('storage/' . $user->img_url)
                                ],
                            'doctor_id' => $user->id,
                            'current_hospital' =>$doctor? $doctor->current_hospital:null,
                            'graduation_year' => $doctor?$doctor->graduation_year:null,
                            'experience_years' =>$doctor? $doctor->experience_years:null,
                            'about' => $doctor?$doctor->about:null,
                            'specialty' =>$doctor? $doctor->specialty:null,
                            'experiences' => $doctor?$doctor->experiences:null,
                            'salary' => $doctor?$doctor->salary:null,
                            'fees' => $doctor?$doctor->salary:null,
                            'certificate_count' => $doctor?$doctor->certificate_count:null,
                            'rate' => $doctor?$doctor->rate:null,
                            'num_rate' => $doctor?$doctor->num_rate:null,
                            'clinic_prefix' => $clinic ? $clinic->prefix : null,
                            'clinic_pnumber' => $clinic ? $clinic->pnumber : null,
                            'clinic_tnumber' => $clinic ? $clinic->tnumber : null,
                            'clinic_city' => $clinic ? $clinic->city : null,
                            'clinic_street' => $clinic ? $clinic->street : null,
                        ]

                ];

                array_push($data, $userData);

            } else {
                $userData = [
                            'user_id' => $user->id,
                            'nick_name' => $user->name,
                            'user_type' => $user->user_type,
                            'bdate' => $user->bdate,
                            'gender' => $user->gender,
                            'prefix' => $user->prefix,
                            'pnumber' => $user->phone,
                            'email' => $user->email,
                            'province' => $user->province,
                            'city' => $user->city,
                            'street' => $user->street,
                            'img_url' => asset('storage/' . $user->img_url),
                            'age' => Carbon::parse($user->bdate)->age,
                            'img_urls' => [
                                [
                                    'img_url' => asset('storage/' . $user->img_url)
                                ]
                            ]
                ];
                array_push($data, $userData);

            }
        }
    }
        $state= 'good, ok';
        $message = 'information retreived successfully';
        return response(compact('state','message','data'), 200);

    }

}
