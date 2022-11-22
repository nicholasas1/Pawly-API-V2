<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\activateEmail;
use Illuminate\Support\Facades\Mail;

class MailServer extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */




    public function __invoke(Request $request)
    {
        //
    }

   


    public function index(){
        Mail::to("nicholasantonius46@gmail.com")->send(new activateEmail('lam'));
 
		return "Email telah dikirim";
    }

    public function InvoicePendingPayment($details){
        Mail::to($details['user_detail']['email'])->send(new \App\Mail\CustomerInvoicePendinngPayment($details));
    }

    public function InvoiceCancelCusttomer($details){
        Mail::to($details['user_detail']['email'])->send(new \App\Mail\CustumerInvoiceCancel($details));
    }
}
