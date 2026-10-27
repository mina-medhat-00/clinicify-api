<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class paymentController extends Controller
{
    public function pay(Request $request)
    {
        \Stripe\Stripe::setApiKey(config("services.stripe.secret"));

        $all_data = $request->input("data");

        // Create a PaymentIntent with amount and currency
        $paymentIntent = \Stripe\PaymentIntent::create([
            "amount" => 5000,
            "currency" => "usd",
            "description" => json_encode($all_data),
            "payment_method_types" => ["card"],
            "capture_method" => "manual",
        ]);

        $output = [
            "clientSecret" => $paymentIntent->client_secret,
        ];

        // $pi=$paymentIntent->client_secret;
        //$intent = \Stripe\PaymentIntent::retrieve('pi_ANipwO3zNfjeWODtRPIg');
        //$intent->capture(['amount_to_capture' => 750]);
        // Return through Laravel so middleware (CORS) applies; a raw echo
        // skipped it and the browser blocked the response.
        return response()->json($output);
    }
}
