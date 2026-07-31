<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Chat;
use App\Models\Clinic_detail;
use App\Models\Doctor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class appointmentextraController extends Controller
{
    public function update_appointments(Request $request)
    {        if(!Auth::user()){
        $state="not authorized to access";
        $message="cannot access to api resources";
        $data = [
            'name'=>"TokenExpiredError",
            'message'=>"jwt expired",
            'expiredAt'=>"2023-03-17T20:39:58.000Z",
        ];
        return response(compact('state', 'message','data'),401);
    }
        $all_data = ($request->input('data'));
        $date = isset($_GET['date'])?$_GET['date']:"2023-07-04";
        $patient_id = $all_data['patientId'];
        $doctor_id = $all_data['doctorId'];
        $all_appointment = Appointment::where([['schedule_from', '=', $doctor_id], ['schedule_date', '=', $date], ['booked_from', '=', $patient_id]])->get();
        $currentTime = Carbon::now()->addHours(1);
        foreach ($all_appointment as $appointment) {
            $scheduledTime = Carbon::parse($appointment->schedule_date . ' ' . $appointment->slot_time);
            $endTime = Carbon::parse($appointment->schedule_date . ' ' . $appointment->slot_time)->addRealMilliseconds($appointment->duration);
            if ($currentTime->greaterThanOrEqualTo($scheduledTime) and $currentTime->lessThanOrEqualTo($endTime)) {
                $appointment->appointment_state = "running";
                $appointment->save();
            } elseif ($currentTime->greaterThanOrEqualTo($scheduledTime) and $currentTime->greaterThanOrEqualTo($endTime)) {
                $appointment->appointment_state = "done";
                $appointment->save();
            }
        }

        //get appointments
        $user_id = Auth::user()->id;
        $state = 'good, ok';
        $message = 'information retreived successfully';
        if(isset($_GET['doctor'])){

            $all_data = Appointment::where([['schedule_from', '=', $user_id], ['schedule_date', '=', $date]])->get();
            $user_data=User::where( 'id','=',$user_id)->first();
            $doc_data=Doctor::where( 'user_id','=',$user_id)->first();

            $data = array();
            foreach ($all_data as $d) {
                $doctorData = [
                    'patientId' => $d->booked_from,
                    'doctorId' => $user_id,
                    'schedule_date' => $date,
                    'booked_time' => $d->updated_at,
                    'appointmentDuration'=>$d->duration,
                    'appointment_duration'=>$d->duration,

                    'slot_time' => $d->slot_time,
                    'appointment_state' => $d->appointment_state,
                    'appointmentFees' => $d->appointmentFees,
                    'appointmentId' => $d->id,
                    'appointment_type' => $d->appointment_type,
                    'uimgUrl'=> null,
                    'dimgUrl'=> asset('storage/' . $user_data->img_url),
                    'username'=>  $user_data->username,
                    'doctorName'=>  $user_data->name,
                    'rate' =>$doc_data->rate,
                    'fees' =>$d->appointmentFees,
                    'specialty' =>$doc_data->specialty,
                    "test_results"=> null,
                    "current_issue"=> null,
                    "allergies"=> null,
                    "immunizations"=> null,
                    "surgeries"=> null,
                    'is_verified'=>1,

                    "illnesses_history"=> null
                ];
                array_push($data, $doctorData);
            }
            return response(compact('state', 'message','data'),200);
        }

        // Patient Appointments
        $all_data = Appointment::where([['booked_from', '=', $user_id], ['schedule_date', '=', $date]])->get();
        $user_data=User::where( 'id','=',$user_id)->first();

        $data = array();
        foreach ($all_data as $d) {
            $doctor_id=$d->schedule_from;
            $doctor_user=User::where( 'id','=',$doctor_id)->first();
            $doctor_doc=Doctor::where( 'user_id','=',$doctor_id)->first();
            $clinic=Clinic_detail::where('doctor_id','=',$doctor_id)->first();

            $data_push = [
                'patientId' => $user_id,
                'doctorId' => $doctor_id,
                'schedule_date' => $date,
                'booked_time' => $d->updated_at,
                'slot_time' => $d->slot_time,
                'appointment_state' => $d->appointment_state,
                'appointmentId' => $d->id,
                'appointment_type' => $d->appointment_type,
                'appointmentDuration'=>$d->duration,
                'appointment_duration'=>$d->duration,

                'clinic_city'=>isset($clinic)?$clinic->city:'none',
                'clinic_street'=>isset($clinic)?$clinic->street:'none',

                'uimgUrl'=> asset('storage/' . $user_data->img_url),
                'dimgUrl'=> asset('storage/' . $doctor_user->img_url),
                'username'=>  $user_data->username,
                'doctorName'=>  $doctor_user->name,
                'rate' =>$doctor_doc->rate,
                'fees' =>$d->appointmentFees,
                'specialty' =>$doctor_doc->specialty,
                'is_verified'=>1,

            ];
            array_push($data, $data_push);
        }
        return response(compact('state', 'message','data'),200);
    }
}
