<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;
    protected $fillable = [
        'booked_from',
        'booked_time',
        'schedule_from',
        'rate',
        'feedback',
        'schedule_date',
        'appointmentFees',
        'slot_time',
        'duration',
        'appointment_type',
        'appointment_state'
    ];
}
