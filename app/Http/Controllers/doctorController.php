<?php

namespace App\Http\Controllers;

use App\Models\Clinic_detail;
use App\Models\Doctor;
use App\Models\User;

class doctorController extends Controller
{
    public function getDoctorData()
    {
        $spec=Null;
        $doc_name=null;
        $location=null;
        $total=false;
        if(isset($_GET['total'])) {
            $total  =$_GET['total'];
        }

        if($total==true){
            if(isset($_GET['specialty'])) {
                $spec  =$_GET['specialty'];
            }

            if(isset($_GET['dname'])) {
                $doc_name  =$_GET['dname'];
            }
            if(isset($_GET['location'])) {
                $location  =$_GET['location'];
            }
            $doctor = Doctor::all();
            if($spec!=null && $doc_name==null &&$location==null){
                $doctor = Doctor::where('specialty','=',$spec)->get();
            }
            if($spec==null && $doc_name!=null &&$location==null) {
                $doctor = Doctor::where('name','LIKE','%'.$doc_name.'%')->get();
            }

            if($spec!=null && $doc_name!=null &&$location==null){
                $doctor = Doctor::where([['name','LIKE','%'.$doc_name.'%'],['specialty','=',$spec]])->get();
            }

            if($location!=null){
                $clinics = Clinic_detail::where(['city','LIKE','%'.$location.'%'])->get();
                $IDs = array();
                foreach ($clinics as $c){
                    array_push($IDs, $c->doctor_id);
                }
                $doctor = Doctor::whereIn('doctor_id', $IDs)->get();
            }
            $data = array();
            $state= 'good, ok';
            $message = 'information retreived successfully';
            foreach ($doctor as $d) {
                $ver=null;
                if($d->type=="verify"){
                    $ver=1;
                }
                else if($d->type=="reject"){
                    $ver=0;
                }
                $doc_id = $d->user_id;
                $doc_user= User::where('id','=',$doc_id)->first();
                $clinic= Clinic_detail::where('doctor_id','=',$doc_id)->first();


                $doctorData = [
                    'doctor_id' => $d->user_id,
                    'user_name' => $d->username,
                    'rate' => $d->rate,
                    'specialty' => $d->specialty,
                    'is_verified' => $ver,
                    'nick_name' => $d->name,
                    'fees' => $d->salary,
                    'about' => $d->about,

                    'clinic_prefix'=> $clinic ? $clinic->prefix:null,
                    'clinic_pnumber'=> $clinic ?$clinic->pnumber:null,
                    'clinic_tnumber'=> $clinic ?$clinic->tnumber:null,
                    'clinic_city'=> $clinic ?$clinic->city:null,
                    'clinic_street'=> $clinic ?$clinic->street:null,
                    'img_urls' => asset('storage/' . $doc_user->img_url)
                ];
                array_push($data, $doctorData);
            }
            return response(compact('state','message','data'), 200);
        }


        if(isset($_GET['specialty'])) {
            $spec  =$_GET['specialty'];
        }

        if(isset($_GET['dname'])) {
            $doc_name  =$_GET['dname'];
        }
        if(isset($_GET['location'])) {
            $location  =$_GET['location'];
        }
        $doctor = Doctor::where('type','=','verify')->get();
        if($spec!=null && $doc_name==null){
            $doctor = Doctor::where('specialty','=',$spec)->get();
        }
        if($spec==null && $doc_name!=null){
            $doctor = Doctor::where('name','LIKE','%'.$doc_name.'%')->get();
        }

        if($spec!=null && $doc_name!=null){
            $doctor = Doctor::where([['name','LIKE','%'.$doc_name.'%'],['specialty','=',$spec]])->get();
        }
        if($location!=null){
            $clinics = Clinic_detail::where('city','LIKE','%'.$location.'%')->get();
            $IDs = array();

            foreach ($clinics as $c){
                array_push($IDs, $c->doctor_id);

            }

            $doctor = Doctor::whereIn('user_id', $IDs)->get();
/*            return response(compact('doctor'), 200);*/

        }
        $data = array();
        $state= 'good, ok';
        $message = 'information retreived successfully';
        foreach ($doctor as $d) {
            $ver=null;
            if($d->type=="verify"){
                $ver=1;
            }
            else if($d->type=="reject"){
                $ver=0;
            }
            $doc_id = $d->user_id;
            $doc_user= User::where('id','=',$doc_id)->first();
            $clinic= Clinic_detail::where('doctor_id','=',$doc_id)->first();
            $doctorData = [
                'doctor_id' => $d->user_id,
                'user_name' => $d->username,
                'rate' => $d->rate,
                'specialty' => $d->specialty,
                'is_verified' => $ver,
                'nick_name' => $d->name,
                'fees' => $d->salary,
                'about' => $d->about,

                'clinic_prefix'=> $clinic ? $clinic->prefix:null,
                'clinic_pnumber'=> $clinic ?$clinic->pnumber:null,
                'clinic_tnumber'=> $clinic ?$clinic->tnumber:null,
                'clinic_city'=> $clinic ?$clinic->city:null,
                'clinic_street'=> $clinic ?$clinic->street:null,
                'img_urls' => asset('storage/' . $doc_user->img_url)
            ];
            array_push($data, $doctorData);
        }
        return response(compact('state','message','data'), 200);

    }
}
