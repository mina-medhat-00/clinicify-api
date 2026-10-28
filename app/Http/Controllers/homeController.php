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
        $state = "good, ok";
        $message = "information retreived successfully";
        $total_appointments = Appointment::count();
        $total_doctors = Doctor::count();
        $total_feedbacks = Feedback::count();
        $avgFees = Appointment::avg("appointmentFees");
        $avgRate = Feedback::avg("rate");

        $data = [
            "totalAppointments" => $total_appointments,
            // No data yet means 0, not a made-up average.
            "avgRate" => round($avgRate ?? 0, 2),
            "avgFees" => round($avgFees ?? 0, 2),
            "totalDoctors" => $total_doctors,
            "totalReviews" => $total_feedbacks,
            "is_verified" => 1,
        ];
        return response(compact("state", "message", "data"), 200);
    }
    public function dash()
    {
        $user = Auth::user();
        if (!$user) {
            $state = "not authorized to access";
            $message = "cannot access to api resources";
            $data = [
                "isUser" => 0,
            ];
            return response(compact("state", "message", "data"), 401);
        }
        $state = "good, ok";
        $message = "information retreived successfully";
        $verify = null;
        if ($user->user_type == "doctor") {
            $doctor = Doctor::where("user_id", $user->id)->first();
            if ($doctor->type == "verify") {
                $verify = 1;
            } elseif ($doctor->type == "reject") {
                $verify = 0;
            }
        }

        $data = [
            "is_verified" => $verify ? $verify : null,
        ];
        if ($user->user_type == "doctor") {
            // The doctor's own numbers for the dashboard statistics tab.
            $appointments = Appointment::where("schedule_from", $user->id);
            $byState = (clone $appointments)
                ->selectRaw("appointment_state, count(*) as total")
                ->groupBy("appointment_state")
                ->pluck("total", "appointment_state");
            $feedback = Feedback::where("feedback_to", $user->id);
            $data["statistics"] = [
                "totalAppointments" => (clone $appointments)->count(),
                "free" => (int) ($byState["free"] ?? 0),
                "booked" => (int) ($byState["booked"] ?? 0),
                "done" => (int) ($byState["done"] ?? 0),
                "canceled" => (int) ($byState["canceled"] ?? 0),
                "avgRate" => round((clone $feedback)->avg("rate") ?? 0, 2),
                "totalReviews" => (clone $feedback)->count(),
            ];
        }
        return response(compact("state", "message", "data"), 200);
    }
}
