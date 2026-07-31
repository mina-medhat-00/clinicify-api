<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Feedback;
use Illuminate\Support\Facades\Auth;

class homeController extends Controller
{
    public function getStatistics()
    {
        $state="good, ok";
        $message="information retreived successfully";
        $total_appointments=Appointment::count();
        $total_doctors=Doctor::count();
        $total_feedbacks=Feedback::count();
        $avgFees = Appointment::avg('appointmentFees');
        $avgRate= Feedback::avg('rate');

        $data = [
            'totalAppointments' => $total_appointments,
            'avgRate'=>isset($avgRate)?$avgRate:4.7,
            'avgFees'=>isset($avgFees)?$avgFees:200,
            'totalDoctors'=>$total_doctors,
            'totalReviews'=>$total_feedbacks,
            'is_verified'=>1,

        ];
        return response(compact('state', 'message', 'data'), 200);
    }
    public function dash(){
        $user =Auth::user();
        if(!$user){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $state="good, ok";
        $message="information retreived successfully";
        $verify=null;
        if ($user->user_type == 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor->type == 'verify') {
                $verify=1;
            }
            elseif ($doctor->type == 'reject') {
                $verify=0;
            }
        }

        $data = [
            'is_verified'=>$verify?$verify:null,

        ];
        return response(compact('state', 'message', 'data'), 200);
    }

}
