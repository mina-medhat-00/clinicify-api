<?php

namespace App\Http\Controllers;

use App\Models\Clinic_detail;
use App\Models\Like;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class moreController extends Controller
{
    public function submit_clinic(Request $request){
        if(!Auth::user()){
            $state="not authorized to access";
            $message="cannot access to api resources";
            $data = [
                'isUser'=>0
            ];
            return response(compact('state', 'message','data'),401);
        }
        $user_id =Auth::user()->id;
        $clinic_before=Clinic_detail::where('doctor_id','=',$user_id)->first();
        $all_data = ($request->input('data'));
        $phone=$all_data['phone'];
        $telephone=isset($all_data['telephone'])?$all_data['telephone']:"none";
        $prefix=$all_data['prefix'];
        $isEdit=isset($all_data['isEdit'])?$all_data['isEdit']:false;
        $name=isset($all_data['clinic_name'])?$all_data['clinic_name']:false;
        $address=$all_data['address'];
        $city=$address['city'];
        $street=$address['street'];
        if(!empty($clinic_before)) {
            if ($isEdit == false) {
                $state = "bad request";
                $message = "inf. not found";
                $data = [
                    'is_exist' => true,
                ];
                return response(compact('state', 'message', 'data'), 200);
            }
            else{
                $clinic_before->city=$city;
                $clinic_before->street=$street;
                $clinic_before->prefix=$prefix;
                $clinic_before->pnumber=$phone;
                $clinic_before->tnumber=$telephone;
                $clinic_before->update();
            }
            $state = "good, ok";
            $message = "your data added successfully";
            $data = [
                'isFirst' => false,
            ];
            return response(compact('state', 'message', 'data'), 200);
        }
        else{
            Clinic_detail::create([
                'doctor_id' => $user_id,
                'clinic_name' => $name,
                'city' => $city,
                'street' => $street,
                'prefix'=>$prefix,
                'pnumber'=>$phone,
                'tnumber'=>$telephone,
            ]);
        }
        $state = "good, ok";
        $message = "your data added successfully";
        $data = [
            'isFirst' => true,
        ];
        return response(compact('state', 'message', 'data'), 200);
    }
}
