<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class meetingController extends Controller
{
    public function join(Request $request)
    {
        if (!Auth::user()) {
            return $this->unauthorized();
        }

        $payload = $request->input("data") ?? [];
        $appointmentId = $payload["appointment_id"] ?? null;
        $sessionId = $request->query("session");

        if (!$appointmentId) {
            return response(
                [
                    "state" => "bad request",
                    "message" => "missing appointment",
                    "data" => [],
                ],
                400,
            );
        }

        $appointment = Appointment::find($appointmentId);
        if (!$appointment || !$this->canJoin($appointment, Auth::user()->id)) {
            return $this->noSchedule();
        }

        $customSessionId = $sessionId ?: "appointment-" . $appointment->id;

        try {
            if (!$sessionId) {
                $this->ensureSession($customSessionId);

                return response($customSessionId, 200)->header(
                    "Content-Type",
                    "text/plain; charset=UTF-8",
                );
            }

            $username = $payload["username"] ?? Auth::user()->username;
            $token = $this->createConnection($customSessionId, $username);

            return response($token, 200)->header(
                "Content-Type",
                "text/plain; charset=UTF-8",
            );
        } catch (\Throwable $e) {
            report($e);

            return response(
                [
                    "state" => "bad request",
                    "message" => "cannot start that appointment right now",
                    "data" => ["noSchedule" => true],
                ],
                401,
            );
        }
    }

    private function canJoin(Appointment $appointment, int $userId): bool
    {
        $isParticipant =
            (int) $appointment->schedule_from === $userId ||
            (int) $appointment->booked_from === $userId;

        if (
            !$isParticipant ||
            !in_array(
                $appointment->appointment_type,
                ["videoCall", "videoChat"],
                true,
            )
        ) {
            return false;
        }

        $state = $appointment->appointment_state;
        if (in_array($state, ["canceled", "cancelled", "done", "free"], true)) {
            return false;
        }

        if ($state === "running" || app()->environment("local")) {
            return true;
        }

        $start = Carbon::parse(
            $appointment->schedule_date . " " . $appointment->slot_time,
        );
        $end = (clone $start)->addRealMilliseconds(
            (int) ($appointment->duration ?: 0),
        );

        return Carbon::now()->betweenIncluded(
            $start->copy()->subMinutes(15),
            $end,
        );
    }

    private function ensureSession(string $sessionId): void
    {
        $response = $this->openvidu("POST", "/openvidu/api/sessions", [
            "customSessionId" => $sessionId,
        ]);

        if (in_array($response["status"], [200, 409], true)) {
            return;
        }

        throw new \RuntimeException(
            "OpenVidu session failed: " .
                $response["status"] .
                " " .
                $response["body"],
        );
    }

    private function createConnection(
        string $sessionId,
        string $username,
    ): string {
        $response = $this->openvidu(
            "POST",
            "/openvidu/api/sessions/" . $sessionId . "/connection",
            [
                "type" => "WEBRTC",
                "role" => "PUBLISHER",
                "data" => $username,
            ],
        );

        if ($response["status"] === 404) {
            $this->ensureSession($sessionId);
            $response = $this->openvidu(
                "POST",
                "/openvidu/api/sessions/" . $sessionId . "/connection",
                [
                    "type" => "WEBRTC",
                    "role" => "PUBLISHER",
                    "data" => $username,
                ],
            );
        }

        if ($response["status"] < 200 || $response["status"] >= 300) {
            throw new \RuntimeException(
                "OpenVidu token failed: " .
                    $response["status"] .
                    " " .
                    $response["body"],
            );
        }

        $data = json_decode($response["body"], true) ?: [];
        $token = $data["token"] ?? null;
        if (!$token) {
            throw new \RuntimeException("OpenVidu token missing from response");
        }

        return $this->rewritePublicToken($token);
    }

    private function rewritePublicToken(string $token): string
    {
        $public = rtrim((string) config("openvidu.public_url"), "/");
        $wsPublic = preg_replace("#^https://#", "wss://", $public);
        $wsPublic = preg_replace("#^http://#", "ws://", $wsPublic);

        return preg_replace("#^(wss|ws)://[^/?]+#", $wsPublic, $token, 1) ?:
            $token;
    }

    private function openvidu(
        string $method,
        string $path,
        array $body = [],
    ): array {
        $client = new Client([
            "verify" => false,
            "http_errors" => false,
            "timeout" => 15,
        ]);

        $options = [
            "auth" => ["OPENVIDUAPP", config("openvidu.secret")],
            "headers" => ["Content-Type" => "application/json"],
        ];
        if ($method !== "GET") {
            $options["json"] = $body;
        }

        $res = $client->request(
            $method,
            rtrim((string) config("openvidu.url"), "/") . $path,
            $options,
        );

        return [
            "status" => $res->getStatusCode(),
            "body" => (string) $res->getBody(),
        ];
    }

    private function unauthorized()
    {
        return response(
            [
                "state" => "not authorized to access",
                "message" => "cannot access to api resources",
                "data" => [
                    "name" => "JsonWebTokenError",
                    "message" => "invalid token",
                ],
            ],
            401,
        );
    }

    private function noSchedule()
    {
        return response(
            [
                "state" => "not authorized to access",
                "message" => "cannot start that appointment right now",
                "data" => ["noSchedule" => true],
            ],
            401,
        );
    }
}
