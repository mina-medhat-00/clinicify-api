<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class deleteAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete_past_appointments';

    protected $description = 'delete the free past appointments';

    public function handle()
    {
        $appointments=Appointment::where('appointment_state','=','free')->get();
        foreach ($appointments as $appoint){
            if ($appoint){
                $currentTime = Carbon::now()->addHours(1)->addMilliseconds(120000);
                $scheduledTime = Carbon::parse($appoint->schedule_date . ' ' . $appoint->slot_time);
            if($currentTime->greaterThanOrEqualTo($scheduledTime)){
                $appoint->delete();
            }
        }
        }
    }
}
