<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class activateEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $send_data;

    public function __construct($send_data)
    {
        $this->send_data = $send_data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($address = 'no-reply@pawly.my.id', $name = 'Pawly Account')
        ->view('activateAccountEmail')
        ->with(
         [
             'url'   => $this->send_data,
         ]);
    }
}
