<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\Chat;
use Carbon\Carbon;
use Illuminate\Console\Command;

class openChat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'open_chat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Open chat when the time comes';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $appointments=Appointment::all();
        foreach ($appointments as $appoint){
            if(isset($appoint->booked_from)) {
                $c1 = $appoint->booked_from;
            }
            else{
                continue;
            }
            $c2=$appoint->schedule_from;
            $has_chat=Chat::where([['chat_from', $c1],['chat_to',$c2]])->first();
            if (empty($has_chat)) {
                Chat::create([
                    'chat_from'=>$c1,
                    'chat_to'=>$c2,
                ]);
                Chat::create([
                    'chat_from'=>$c2,
                    'chat_to'=>$c1,
                ]);
            }
            $currentTime = Carbon::now()->addHours(1);
            $scheduledTime = Carbon::parse($appoint->schedule_date . ' ' . $appoint->slot_time);
            $endTime = Carbon::parse($appoint->schedule_date . ' ' . $appoint->slot_time)->addRealMilliseconds($appoint->duration);
            if($currentTime->greaterThanOrEqualTo($scheduledTime) and  $currentTime->lessThanOrEqualTo($endTime)) {
                $openChats = Chat::where([['chat_from', $appoint->schedule_from], ['chat_to', $appoint->booked_from]])
                    ->orWhere([['chat_from', $appoint->booked_from], ['chat_to', $appoint->schedule_from]])->get();
                $appoint->appointment_state = "running";
                $appoint->save();
                foreach ($openChats as $openChat) {
                    if ($openChat->admin_restrict != 1) {
                        $openChat->is_open = 1;
                        $openChat->save();
                    }
                }
            }
            elseif ($currentTime->greaterThanOrEqualTo($scheduledTime) and  $currentTime->greaterThanOrEqualTo($endTime)){
                $openChats = Chat::where([['chat_from', $appoint->schedule_from], ['chat_to', $appoint->booked_from]])
                    ->orWhere([['chat_from', $appoint->booked_from], ['chat_to', $appoint->schedule_from]])->get();
                $appoint->appointment_state="done";
                $appoint->save();
                foreach ($openChats as $openChat) {
                    if ($openChat->admin_restrict != 1) {

                        $openChat->is_open = 0;
                        $openChat->save();
                    }
                }
            }
            }
    }
}
