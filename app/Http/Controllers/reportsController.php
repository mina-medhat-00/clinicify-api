<?php

namespace App\Http\Controllers;

use App\Models\Report;
use App\Models\Report_detail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class reportsController extends Controller
{
    public function submit_report(Request $request){

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
        $issue=isset($all_data['issue'])?$all_data['issue']:"none";
        $reportType=isset($all_data['reportType'])?$all_data['reportType']:"none";
        $past_report=Report::where('report_from','=',$user_id)->first();
        if(empty($past_report)){
            Report::create([
                'report_from'=>$user_id,
                'num_reports'=>1
            ])  ;
            Report_detail::create([
                'user_id'=>$user_id,
                'issue'=>$issue,
                'report_type'=>$reportType
            ]);
            $state="good, ok";
            $message="your data added successfully";

            return response(compact('state', 'message', 'all_data'), 200);
        }
        $past_report->num_reports=$past_report->num_reports+1;
        $past_report->save();
        Report_detail::create([
            'user_id'=>$user_id,
            'issue'=>$issue,
            'report_type'=>$reportType
        ]);
        $state="good, ok";
        $message="your data added successfully";

        return response(compact('state', 'message', 'all_data'), 200);
    }
    public function get_reports(Request $request)
    {
        if(!Auth::user()){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $user=Auth::user();
        $user_id =Auth::user()->id;

        if($user->user_type != 'admin'){
            $state="forbbiden";
            $message="don't have permission to access these resources";
            $data = [
                "notPermitted"=> true
            ];
            return response(compact('state', 'message','data'),403);
        }
        //$user_id =Auth::user()->id;
        $all_report=Report::all();
        $data = array();

        foreach ($all_report as $report){
            $user_report_id=$report->report_from;
            $user_report=User::where('id','=',$user_report_id)->first();
                $pushed_data = [
                    'report_from' =>$user_report_id,
                    'num_reports' => $report->num_reports,
                    'latest_report_time' => $report->updated_at,
                    'user_id' => $user_report_id,
                    'user_name' => $user_report->username,
                    'img_url' => asset('storage/' . $user_report->img_url),
                    'nick_name' => $user_report->name,
                    'user_type' => $user_report->user_type,
                    'bdate' => $user_report->bdate,
                    'gender' => $user_report->gender,
                ];
                array_push($data, $pushed_data);
            }
        $state="good, ok";
        $message="your data added successfully";

        return response(compact('state', 'message', 'data'), 200);
    }


    public function get_details_report(Request $request)
    {
        if(!Auth::user()){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $user=Auth::user();
        $user_id =Auth::user()->id;

        if($user->user_type != 'admin'){
            $state="forbbiden";
            $message="don't have permission to access these resources";
            $data = [
                "notPermitted"=> true
            ];
            return response(compact('state', 'message','data'),403);
        }
        $user_report_id=isset($_GET['reportFrom'])?$_GET['reportFrom']:0;
        if($user_report_id == 0){
            $state="good, ok";
            $message="user not found";
            $data = [];
            return response(compact('state','message','data'), 200);
        }
        $user_report=User::where('id','=',$user_report_id)->first();
        if(empty($user_report)){
            $state="good, ok";
            $message="user not found";
            $data = [];
            return response(compact('state','message','data'), 200);
        }
        $report=Report::where('report_from','=',$user_report_id)->first();
        $all_details=Report_detail::where('user_id','=',$user_report_id)->get();
        $data = array();
        foreach ($all_details as $det){
            $pushed_data = [
                'report_id' =>$det->id,
                'user_id' => $user_report_id,
                'issue'=>$det->issue,
                'report_type' => $det->report_type,
                'issued_time' => $det->updated_at,
            ];
            array_push($data, $pushed_data);
        }

        $state="good, ok";
        $message="your data added successfully";

        return response(compact('state', 'message', 'data'), 200);
    }
}
