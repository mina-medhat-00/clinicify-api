<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class paymentController extends Controller
{
    public function pay(Request $request)
    {
        if (!Auth::user()) {
            $state = "not authorized to access";
            $message = "cannot access to api resources";
            $data = ["isUser" => 0];
            return response(compact("state", "message", "data"), 401);
        }
        $all_data = $request->input("data");

        // Price comes from the slot in the database, never from the client.
        $slot = Appointment::where(
            "id",
            "=",
            $all_data["appointmentId"] ?? null,
        )
            ->where("schedule_from", "=", $all_data["doctorId"] ?? null)
            ->first();
        if (empty($slot) || $slot->appointment_state != "free") {
            $state = "bad request";
            $message = "this slot is no longer available";
            $data =
                empty($slot) || $slot->appointment_state == "canceled"
                    ? ["isCanceled" => true]
                    : ["isBooked" => true];
            return response(compact("state", "message", "data"), 400);
        }

        \Stripe\Stripe::setApiKey(config("services.stripe.secret"));
        // Held now, captured when the booking succeeds (see book_appointment).
        $paymentIntent = \Stripe\PaymentIntent::create([
            // Stripe amounts are in the smallest unit (piastres for EGP).
            "amount" => (int) round($slot->appointmentFees * 100),
            "currency" => config("services.stripe.currency"),
            "description" => json_encode($all_data),
            "payment_method_types" => ["card"],
            "capture_method" => "manual",
        ]);

        // Return through Laravel so middleware (CORS) applies; a raw echo
        // skipped it and the browser blocked the response.
        return response()->json([
            "clientSecret" => $paymentIntent->client_secret,
        ]);
    }
}
