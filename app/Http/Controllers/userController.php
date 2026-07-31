<?php

namespace App\Http\Controllers;

use App\Models\Clinic_detail;
use App\Models\Doctor;
use App\Models\Feedback;
use App\Models\User;
use App\Models\History;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class userController extends Controller
{
    public function getUserData($username)
    {
        $verify=0;
        $user = User::where('username', $username)->first();
        $doctor = Doctor::where('username', $username)->first();

        if (!empty($user)) {
            if ($user->user_type == 'doctor') {
                $clinic = Clinic_detail::where('doctor_id', $user->id)->first();
                if ($doctor->type == 'verify') {
                    $verify=1;
                }
                elseif ($doctor->type == 'reject') {
                    $verify=0;
                }
                $number_of_rates=Feedback::where([['feedback_to','=',$doctor->user_id]])->count();
                $avg_of_rates=Feedback::where([['feedback_to','=',$doctor->user_id]])->avg('rate');
                    $userData = [
                    'state' => 'good, ok',
                    'message' => 'information retreived successfully',
                    'data' => [
                        'user' => [
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
                            'age' => Carbon::parse($user->bdate)->age,
                            'img_urls' => [
                                [
                                    'img_url' => asset('storage/' . $user->img_url),
                                ]
                            ]
                        ],
                        'doctor' => [
                            'doctor_id' => $user->id,
                            'current_hospital' => $doctor->current_hospital,
                            'graduation_year' => $doctor->graduation_year,
                            'experience_years' => $doctor->experience_years,
                            'about' => $doctor->about,
                            'specialty' => $doctor->specialty,
                            'experiences' => $doctor->experiences,
                            'salary' => $doctor->salary,
                            'is_verified'=>$verify?$verify:null,

                            'fees' => $doctor->salary,
                            'certificate_count' => $doctor->certificate_count,
                            'rate' => isset($avg_of_rates)?$avg_of_rates:0,
                            'num_rate' => isset($number_of_rates)?$number_of_rates:0,
                            'clinic_prefix'=> $clinic ? $clinic->prefix:null,
                            'clinic_pnumber'=> $clinic ?$clinic->pnumber:null,
                            'clinic_tnumber'=> $clinic ?$clinic->tnumber:null,
                            'clinic_city'=> $clinic ?$clinic->city:null,
                            'clinic_street'=> $clinic ?$clinic->street:null,
                        ]

                    ]
                ];
                return response()->json($userData);
            } else {
                $patient_record=History::where('user_id','=',$user->id)->first();
                $userData = [
                    'state' => 'good, ok',
                    'message' => 'information retreived successfully',
                    'data' => [
                        'user' => [
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
                            "test_results"=> "asclks",
                            "immunizations"=> "immunizations",
                            'street' => $user->street,
                            'age' => Carbon::parse($user->bdate)->age,
                            'img_urls' => [
                                [
                                    'img_url' => asset('storage/' . $user->img_url),
                                ]
                            ]
                        ],
                        'patientRecords'=>[
                        "test_results"=> isset($patient_record->test_results)?$patient_record->test_results:null,
                        "illnesses_history"=> isset($patient_record->illnesses_history)?$patient_record->illnesses_history:null,
                        "current_issue"=> isset($patient_record->current_issue)?$patient_record->current_issue:null,
                        "allergies"=> isset($patient_record->allergies)?$patient_record->allergies:null,
                        "surgeries"=> isset($patient_record->surgeries)?$patient_record->surgeries:null,
                        "immunizations"=> isset($patient_record->immunizations)?$patient_record->immunizations:null,

                ],
                    ]
                ];
                return response()->json($userData);
            }
        }
        else{
            // dearch for id
            $user = User::where('id', $username)->first();

            $doctor = Doctor::where('user_id', $username)->first();
            $clinic = Clinic_detail::where('doctor_id', $username)->first();

            if ($user->user_type == 'doctor') {
                if ($doctor->type == 'verify') {
                    $verify=1;
                }
                elseif ($doctor->type == 'reject') {
                    $verify=0;
                }
                $number_of_rates=Feedback::where([['feedback_to','=',$doctor->user_id]])->count();
                $avg_of_rates=Feedback::where([['feedback_to','=',$doctor->user_id]])->avg('rate');
                $userData = [
                    'state' => 'good, ok',
                    'message' => 'information retreived successfully',
                    'data' => [
                        'user' => [
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
                            'age' => Carbon::parse($user->bdate)->age,

                            'img_urls' => [
                                [
                                    'img_url' => asset('storage/' . $user->img_url),
                                ]
                            ]
                        ],
                        'doctor' => [
                            'doctor_id' => $user->id,
                            'current_hospital' => $doctor->current_hospital,
                            'graduation_year' => $doctor->graduation_year,
                            'experience_years' => $doctor->experience_years,
                            'about' => $doctor->about,
                            'specialty' => $doctor->specialty,
                            'experiences' => $doctor->experiences,
                            'salary' => $doctor->salary,
                            'fees' => $doctor->salary,
                            'certificate_count' => $doctor->certificate_count,
                            'rate' => isset($avg_of_rates)?$avg_of_rates:0,
                            'num_rate' => isset($number_of_rates)?$number_of_rates:0,
                            'clinic_prefix'=> $clinic ? $clinic->prefix:null,
                            'clinic_pnumber'=> $clinic ?$clinic->pnumber:null,
                            'clinic_tnumber'=> $clinic ?$clinic->tnumber:null,
                            'is_verified'=>$verify?$verify:null,

                            'clinic_city'=> $clinic ?$clinic->city:null,
                            'clinic_street'=> $clinic ?$clinic->street:null,
                        ]

                    ]
                ];
                return response()->json($userData);
            } else {
                $patient_record=History::where('user_id','=',$user->id)->first();
                $userData = [
                    'state' => 'good, ok',
                    'message' => 'information retreived successfully',
                    'data' => [
                        'user' => [
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
                            'is_verified'=>1,


                            'age' => Carbon::parse($user->bdate)->age,
                            'img_urls' => [
                                [
                                    'img_url' => asset('storage/' . $user->img_url),
                                ]
                            ]
                        ],
                        'patientRecords'=>[

                                "test_results"=> isset($patient_record->test_results)?$patient_record->test_results:null,
                                "illnesses_history"=> isset($patient_record->illnesses_history)?$patient_record->illnesses_history:null,
                                "current_issue"=> isset($patient_record->current_issue)?$patient_record->current_issue:null,
                                "allergies"=> isset($patient_record->allergies)?$patient_record->allergies:null,
                                "surgeries"=> isset($patient_record->surgeries)?$patient_record->surgeries:null,
                                "immunizations"=> isset($patient_record->immunizations)?$patient_record->immunizations:null,

                        ],
                    ]
                ];
                return response()->json($userData);
            }
        }
    }
    public function editUser(Request $request)
    {
        $alldata= ($request->input('data'));

        $values=$alldata["values"];
        $user=Auth::user();
        if(!$user){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $user_id=$user->id;
        $email=null;
        if(isset($values['email'])){
            $email=$values['email'];
        }
        $name="none";
        if(isset($values['nickname'])){
            $name=$values['nickname'];
        }
        $bdate=null;
        if(isset($values['birth'])){
            $bdate=$values['birth'];
        }
        $prefix=null;
        if(isset($values['prefix'])){
            $prefix=$values['prefix'];
        }

        $phone=null;
        if(isset($values['phone'])){
            $phone=$values['phone'];
        }
        $all_address=null;
        if(isset($values['address'])){
            $all_address=$values['address'];
        }

        $province=null;
        if(isset($all_address['province'])){
            $province=$all_address['province'];
        }

        $city=null;
        if(isset($all_address['city'])){
            $city=$all_address['city'];
        }
        $street=null;
        if(isset($all_address['street'])){
            $street=$all_address['street'];
        }
        $gender=null;
        if(isset($values['gender'])){
            $gender=$values['gender'];
        }
        $edit_user = User::where('id',$user_id)->first();
        $edit_user->name=$name;
        $edit_user->email=$email;
        $edit_user->phone=$phone;
        $edit_user->prefix=$prefix;
        $edit_user->street=$street;
        $edit_user->province=$province;
        $edit_user->city=$city;
        $edit_user->bdate=$bdate;
        $edit_user->gender=$gender;
        $edit_user->save();
        $data = [
            'done'=>true
        ];
        $state="good, ok";

        $message="your data added successfully";
        return response(compact('state', 'message','data'),200);
    }
    public function add_medical(Request $request){
        $alldata= ($request->input('data'));
        $val=$alldata['values'];
        $testResults="none";
        if(isset($val['testResults'])){
            $testResults=$val['testResults'];
        }

        $currentIssue="none";
        if(isset($val['currentIssue'])){
            $currentIssue=$val['currentIssue'];
        }

        $allergies="none";
        if(isset($val['allergies'])){
            $allergies=$val['allergies'];
        }
        $immunizations="none";
        if(isset($val['immunizations'])){
            $immunizations=$val['immunizations'];
        }
        $surgeries="none";
        if(isset($val['surgeries'])){
            $surgeries=$val['surgeries'];
        }

        $illnessesHistory="none";
        if(isset($val['illnessesHistory'])){
            $illnessesHistory=$val['illnessesHistory'];
        }
        $user_id=Auth::user()->id;
        $old_history=History::where('user_id','=',$user_id)->first();
        if(!empty($old_history)){
            $old_history->illnesses_history=$illnessesHistory;
            $old_history->test_results=$testResults;
            $old_history->current_issue=$currentIssue;
            $old_history->allergies=$allergies;
            $old_history->immunizations=$immunizations;
            $old_history->surgeries=$surgeries;
            $old_history->save();
        }
        else {
            $medical = History::create([
                'user_id' => $user_id,
                'illnesses_history' => $illnessesHistory,
                'test_results' => $testResults,
                'current_issue' => $currentIssue,
                'allergies' => $allergies,
                'immunizations' => $immunizations,
                'surgeries' => $surgeries
            ]);
        }
        $state="good, ok";
        $message="your data added successfully";
        $data = [
            'done' => true
        ];
        return response(compact('state', 'message', 'data'), 200);
    }

}
