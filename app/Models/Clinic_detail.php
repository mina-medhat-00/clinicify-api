<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clinic_detail extends Model
{
    use HasFactory;
    protected $fillable = [
        'doctor_id',
        'clinic_name',
        'city',
        'street',
        'pnumber',
        'tnumber',
        'prefix'
    ];
}
