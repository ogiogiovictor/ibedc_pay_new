<?php

namespace App\Listeners;

use App\Events\ContactUs;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;


class SendContactUsEmail implements ShouldQueue
{
    use InteractsWithQueue;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(ContactUs $event): void
    {
        $contact = $event->contact;
        Mail::to($contact->emails)->cc(["ebasupport@ibedc.com", "fatima.ayandeko@ibedc.com", "victor.ogiogio@ibedc.com"])->send(new ContactMail(
       // Mail::to($contact->emails)->cc(["customercare@ibedc.com", "fatima.ayandeko@ibedc.com", "victor.ogiogio@ibedc.com"])->send(new ContactMail(
            $contact->name,
            $contact->email,
            $contact->subject,
            $contact->account_type,
            $contact->unique_code,
            $contact->message
        ));
    }
}
