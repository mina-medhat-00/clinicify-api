<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class cancel_appointment extends Mailable
{
    use Queueable, SerializesModels;

    public $all_data;
    public $user1;
    public $user2;
    public function __construct($all_data,$user1,$user2)
    {
        $this->all_data=$all_data;
        $this->user1=$user1;
        $this->user2=$user2;
    }
    public function build()
    {
        return $this->subject('Cancelling an appointment')
            ->view('emails.cancel')
            ->with([['all_data'=>$this->all_data],['user1'=>$this->user1],['user2'=>$this->user2]]);
    }
}
