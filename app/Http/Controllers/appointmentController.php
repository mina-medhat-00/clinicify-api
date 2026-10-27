<?php

namespace App\Http\Controllers;

use App\Mail\book_appointment;
use App\Mail\cancel_appointment;
use App\Models\Appointment;
use App\Models\Clinic_detail;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class appointmentController extends Controller
{
    public function sched_appointment(Request $request)
    {
        if (!Auth::user()) {
            $state = "not authorized to access";
            $message = "cannot access to api resources";
            $data = [
                "isUser" => 0,
            ];
            return response(compact("state", "message", "data"), 401);
        }
        $doctor_id = Auth::user()->id;

        $all_data = $request->input("data");
        $date = isset($all_data["date"]) ? $all_data["date"] : "2023-07-5";

        if (array_key_exists("addedAppointments", $all_data)) {
            $addedAppointments = $all_data["addedAppointments"];
            if ($addedAppointments != null) {
                foreach ($addedAppointments as $s) {
                    Appointment::create([
                        "schedule_date" => $date,
                        "schedule_from" => $doctor_id,
                        "slot_time" => $s["slotTime"],
                        "appointment_type" => $s["appointmentType"],
                        "appointmentFees" => $s["appointmentFees"],
                        "duration" => $s["appointmentDuration"],
                    ]);
                }
                $state = "good, ok";
                $message = "your data added successfully";
                $data = [
                    "isFirst" => 1,
                ];
                return response(compact("state", "message", "data"), 200);
            }
        }
        /*
         ************************************** Delete appointment **************************************
         */
        if (array_key_exists("deletedAppointments", $all_data)) {
            $deleted_id = $all_data["deletedAppointments"];
            $del_id = $deleted_id[0];
            /*            return response(compact('del_slot'), 200);*/

            $date_delete = Appointment::where("id", "=", $del_id)->first();
            /*            return response(compact('date_delete'), 200);*/
            if (!empty($date_delete)) {
                $del = Appointment::where("id", "=", $del_id)->first();
                $del->delete();
                $state = "good, ok";
                $message = "information retreived successfully";
                $data = [
                    "done" => true,
                ];
                return response(compact("state", "message", "data"), 200);
            } else {
                $state = "bad request";
                $message = "iinf. not found";
                $data = [
                    "timeSlots" => $date_delete->slot_time,
                ];
                return response(compact("state", "message", "data"), 400);
            }
        }
    }

    /*
     * ******************** Book Appointment ********************
     */
    public function book_appointment(Request $request)
    {
        if (!Auth::user()) {
            $state = "not authorized to access";
            $message = "cannot access to api resources";
            $data = [
                "isUser" => 0,
            ];
            return response(compact("state", "message", "data"), 401);
        }
        $user_id = Auth::user()->id;
        $all_data = $request->input("data");
        $doctor_id = $all_data["doctorId"];

        // Find the exact slot. A canceled slot can share its date and time
        // with a newer one, so prefer the id the client sent.
        $slot = Appointment::where([
            ["schedule_from", "=", $doctor_id],
            ["schedule_date", "=", $all_data["date"]],
            ["slot_time", "=", $all_data["bookedSlot"]],
        ])
            ->when(
                isset($all_data["appointmentId"]),
                fn($query) => $query->where(
                    "id",
                    "=",
                    $all_data["appointmentId"],
                ),
                fn($query) => $query->where("appointment_state", "=", "free"),
            )
            ->first();

        // Validate before anything else, for the pre-payment check too.
        if (empty($slot) || $slot->appointment_state == "canceled") {
            $state = "bad request";
            $message = "inf. not found";
            $data = [
                "isCanceled" => true,
            ];
            return response(compact("state", "message", "data"), 400);
        }
        if ($slot->appointment_state != "free") {
            $state = "bad request";
            $message = "inf. not found";
            $data = [
                "isBooked" => true,
            ];
            return response(compact("state", "message", "data"), 400);
        }

        // Pre-payment check: the slot is free; let the doctor know it's coming.
        if (isset($_GET["check"])) {
            $doctor = User::where("id", "=", $doctor_id)->first();
            $user = User::where("id", "=", $user_id)->first();
            Mail::to($doctor->email)->send(
                new book_appointment($all_data, $doctor, $user),
            );
            $state = "good, ok";
            $message = "your data added successfully";
            $data = [
                "done" => 1,
            ];
            return response(compact("state", "message", "data"), 200);
        }

        // Take the held payment only once the slot is known to be available.
        if (isset($_GET["pi"])) {
            \Stripe\Stripe::setApiKey(config("services.stripe.secret"));
            \Stripe\PaymentIntent::retrieve($_GET["pi"])->capture();
        }

        $slot->appointment_state = "booked";
        $slot->booked_from = $user_id;
        $slot->booked_time = \Carbon\Carbon::now()->toDateTimeString();
        $slot->save();

        $state = "good, ok";
        $message = "your data added successfully";
        $data = [
            "isFirst" => 0,
        ];
        return response(compact("state", "message", "data"), 201);
    }
    /*
     * ******************** get_slots ********************
     */
    public function get_slots(Request $request)
    {
        $all_data = $request->input("data");
        $date = $all_data["date"];
        $doctor_id = $all_data["doctorId"];
        $totalSlots = [];
        $bookedSlots = [];
        $freeSlots = [];
        $runningSlots = [];
        $ts = Appointment::where([
            ["schedule_from", "=", $doctor_id],
            ["schedule_date", "=", $date],
            ["appointment_state", "!=", "canceled"],
            ["appointment_state", "!=", "done"],
        ])->get();
        /*        return response(compact('ts'),200);*/
        $slot = [
            "appointmentState" => "0",
            "appointmentType" => "none",
            "slotTime" => "12:00 am",
            "appointmentDuration" => "0",
            "schedule_date" => "none",
            "clinic_city" => "none",
            "clinic_street" => "none",
        ];

        $bs = Appointment::where([
            ["schedule_from", "=", $doctor_id],
            ["schedule_date", "=", $date],
            ["appointment_state", "=", "booked"],
        ])->get();
        $fs = Appointment::where([
            ["schedule_from", "=", $doctor_id],
            ["schedule_date", "=", $date],
            ["appointment_state", "=", "free"],
        ])->get();
        $rs = Appointment::where([
            ["schedule_from", "=", $doctor_id],
            ["schedule_date", "=", $date],
            ["appointment_state", "=", "running"],
        ])->get();
        foreach ($ts as $t_s) {
            $slot["appointmentState"] = $t_s->appointment_state;
            $slot["appointmentType"] = $t_s->appointment_type;
            $slot["appointmentFees"] = $t_s->appointmentFees;
            $slot["appointmentId"] = $t_s->id;
            $slot["schedule_date"] = $t_s->schedule_date;
            $slot["slotTime"] = $t_s->slot_time;
            $slot["appointmentDuration"] = $t_s->duration;
            $slot["clinic_street"] = "msr";
            $slot["clinic_city"] = "msr";
            array_push($totalSlots, $slot);
        }

        foreach ($bs as $b_s) {
            $slot["appointmentState"] = $b_s->appointment_state;
            $slot["appointmentType"] = $b_s->appointment_type;
            $slot["slotTime"] = $b_s->slot_time;
            $slot["appointmentDuration"] = $b_s->duration;
            $slot["schedule_date"] = $b_s->schedule_date;

            $slot["appointmentFees"] = $b_s->appointmentFees;
            $slot["appointmentId"] = $b_s->id;
            array_push($bookedSlots, $slot);
        }

        foreach ($fs as $f_s) {
            $slot["appointmentState"] = $f_s->appointment_state;
            $slot["appointmentType"] = $f_s->appointment_type;
            $slot["slotTime"] = $f_s->slot_time;
            $slot["appointmentDuration"] = $f_s->duration;
            $slot["appointmentFees"] = $f_s->appointmentFees;
            $slot["schedule_date"] = $f_s->schedule_date;

            $slot["appointmentId"] = $f_s->id;
            array_push($freeSlots, $slot);
        }

        foreach ($rs as $r_s) {
            $slot["appointmentState"] = $r_s->appointment_state;
            $slot["appointmentType"] = $r_s->appointment_type;
            $slot["slotTime"] = $r_s->slot_time;
            $slot["appointmentDuration"] = $r_s->duration;
            $slot["appointmentFees"] = $r_s->appointmentFees;
            $slot["schedule_date"] = $r_s->schedule_date;

            $slot["appointmentId"] = $r_s->id;
            array_push($runningSlots, $slot);
        }
        if (empty($freeSlots)) {
            $freeSlots = null;
        }
        $state = "good, ok";
        $message = "information retreived successfully";
        $data = [
            "totalSlots" => $totalSlots,
            "bookedSlots" => $bookedSlots,
            "freeSlots" => $freeSlots,
            "runningSlots" => $runningSlots,
        ];
        return response(compact("state", "message", "data"), 200);
    }
    /*
     *
     * Cancel Appointments
     */
    public function cancel_appointment(Request $request)
    {
        if (!Auth::user()) {
            $state = "not authorized to access";
            $message = "cannot access to api resources";
            $data = [
                "isUser" => 0,
            ];
            return response(compact("state", "message", "data"), 401);
        }

        $all_data = $request->input("data");

        $date = $all_data["date"];
        $cancelFrom = $all_data["cancelFrom"];
        $bookedSlot = $all_data["bookedSlot"];
        $user = Auth::user();

        // Doctors cancel their own slots; patients their own bookings; admins any.
        if ($cancelFrom == "doctor") {
            $doctor_id = $user->id;
        } else {
            $doctor_id = $all_data["doctorId"];
        }
        $isAdmin = $cancelFrom == "admin" && $user->user_type == "admin";

        // Canceled slots can share a date and time with a newer one, so prefer
        // the id the client sent, else the booked slot.
        $data_update = Appointment::where([
            ["schedule_from", "=", $doctor_id],
            ["schedule_date", "=", $date],
            ["slot_time", "=", $bookedSlot],
        ])
            ->when(
                isset($all_data["appointmentId"]),
                fn($query) => $query->where(
                    "id",
                    "=",
                    $all_data["appointmentId"],
                ),
                fn($query) => $query->where("appointment_state", "=", "booked"),
            )
            ->when(
                $cancelFrom != "doctor" && !$isAdmin,
                fn($query) => $query->where("booked_from", "=", $user->id),
            )
            ->first();
        if (
            empty($data_update) ||
            $data_update->appointment_state == "canceled"
        ) {
            $state = "bad request";
            $message = "appointment not found or already canceled";
            $data = [
                "isCanceled" => true,
            ];
            return response(compact("state", "message", "data"), 400);
        }

        // Tell the other side: the patient when staff cancel, else the doctor.
        $patient = User::where("id", "=", $data_update->booked_from)->first();
        $doctor = User::where("id", "=", $doctor_id)->first();
        $recipient = $cancelFrom == "patient" ? $doctor : $patient;
        if ($recipient) {
            Mail::to($recipient->email)->send(
                new cancel_appointment($all_data, $user, $recipient),
            );
        }

        $data_update->appointment_state = "canceled";
        $data_update->save();
        $state = "good, ok";
        $message = "information retreived successfully";
        $data =
            $cancelFrom == "patient"
                ? ["fromPatient" => $user->id]
                : ["fromDoctor" => $doctor_id];
        return response(compact("state", "message", "data"), 200);
    }
    public function get_appointments()
    {
        if (!Auth::user()) {
            $state = "not authorized to access";
            $message = "cannot access to api resources";
            $data = [
                "name" => "TokenExpiredError",
                "message" => "jwt expired",
                "expiredAt" => "2023-03-17T20:39:58.000Z",
            ];
            return response(compact("state", "message", "data"), 401);
        }
        $date = $_GET["date"];
        $user_id = Auth::user()->id;
        $state = "good, ok";
        $message = "information retreived successfully";

        if (isset($_GET["doctor"])) {
            $all_data = Appointment::where([
                ["schedule_from", "=", $user_id],
                ["schedule_date", "=", $date],
            ])->get();
            $user_data = User::where("id", "=", $user_id)->first();
            $doc_data = Doctor::where("user_id", "=", $user_id)->first();

            $data = [];
            foreach ($all_data as $d) {
                $patient = User::where("id", "=", $d->booked_from)->first();
                $doctorData = [
                    "patientId" => $d->booked_from,
                    "doctorId" => $user_id,
                    "schedule_date" => $date,
                    "booked_time" => $d->updated_at,
                    "appointmentDuration" => $d->duration,
                    "appointment_duration" => $d->duration,

                    "slot_time" => $d->slot_time,
                    "appointment_state" => $d->appointment_state,
                    "appointmentFees" => $d->appointmentFees,
                    "appointmentId" => $d->id,
                    "appointment_type" => $d->appointment_type,
                    "uimgUrl" => $patient
                        ? asset("storage/" . $patient->img_url)
                        : null,
                    "dimgUrl" => asset("storage/" . $user_data->img_url),
                    "username" => $patient ? $patient->name : null,
                    "doctorName" => $user_data->name,
                    "rate" => $doc_data->rate,
                    "fees" => $d->appointmentFees,
                    "specialty" => $doc_data->specialty,
                    "test_results" => "asclks",
                    "current_issue" => "current_issue",
                    "allergies" => null,
                    "immunizations" => null,
                    "surgeries" => null,
                    "is_verified" => 1,

                    "illnesses_history" => null,
                ];
                array_push($data, $doctorData);
            }
            return response(compact("state", "message", "data"), 200);
        }
        // Patient Appointments
        $all_data = Appointment::where([
            ["booked_from", "=", $user_id],
            ["schedule_date", "=", $date],
        ])->get();
        $user_data = User::where("id", "=", $user_id)->first();

        $data = [];
        foreach ($all_data as $d) {
            $doctor_id = $d->schedule_from;
            $doctor_user = User::where("id", "=", $doctor_id)->first();
            $doctor_doc = Doctor::where("user_id", "=", $doctor_id)->first();
            $clinic = Clinic_detail::where(
                "doctor_id",
                "=",
                $doctor_id,
            )->first();
            $data_push = [
                "patientId" => $user_id,
                "doctorId" => $doctor_id,
                "schedule_date" => $date,
                "booked_time" => $d->updated_at,
                "slot_time" => $d->slot_time,
                "appointment_state" => $d->appointment_state,
                "appointmentId" => $d->id,
                "appointment_type" => $d->appointment_type,
                "appointmentDuration" => $d->duration,
                "appointment_duration" => $d->duration,
                "clinic_city" => isset($clinic) ? $clinic->city : "none",
                "clinic_street" => isset($clinic) ? $clinic->street : "none",

                "uimgUrl" => asset("storage/" . $user_data->img_url),
                "dimgUrl" => asset("storage/" . $doctor_user->img_url),

                "username" => $user_data->username,
                "doctorName" => $doctor_user->name,
                "rate" => $doctor_doc->rate,
                "fees" => $d->appointmentFees,
                "specialty" => $doctor_doc->specialty,
                "is_verified" => 1,
            ];
            array_push($data, $data_push);
        }
        return response(compact("state", "message", "data"), 200);
    }

    /*
     * edit appointment
     */
    public function edit_appointments(Request $request)
    {
        if (!Auth::user()) {
            $state = "not authorized to access";
            $message = "cannot access to api resources";
            $data = [
                "isUser" => 0,
            ];
            return response(compact("state", "message", "data"), 401);
        }
        $doctor_id = Auth::user()->id;
        $all_data = $request->input("data");
        $date = $all_data["date"];
        $edited_id = $all_data["editSlot"];
        $edit_id = $edited_id[0];
        $editSlot = $all_data["addedAppointments"];

        $appointment_edit = Appointment::where("id", "=", $edit_id)->first();

        //$appointment_edit = Appointment::where([['schedule_from', '=', $doctor_id], ['schedule_date', '=', $date], ['slot_time', '=', $editSlot],['appointment_state', '=', 'free']])->first();

        if (array_key_exists("addedAppointments", $all_data)) {
            $addedAppointments = $all_data["addedAppointments"];

            if ($addedAppointments != null) {
                /*                return response(compact('addedAppointments'), 200);*/
                $temp = $addedAppointments[0];
                /*                return response(compact('appointment_edit'), 200);*/

                $appointment_edit->slot_time = $temp["slotTime"];
                $appointment_edit->duration = $temp["appointmentDuration"];
                $appointment_edit->appointment_type = $temp["appointmentType"];
                $appointment_edit->appointmentFees = $temp["appointmentFees"];
                $appointment_edit->save();
                $state = "good, ok";
                $message = "your data added successfully";
                $data = [
                    "done" => true,
                ];
                return response(compact("state", "message", "data"), 200);
            }
        }
    }
}
