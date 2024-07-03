<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\ContactUs;
use Illuminate\Support\Facades\Session;
use App\Jobs\ContactUsJob;
use Illuminate\Support\Facades\Http;

class ViewComplains extends Component
{

    public $contact; 
    public $email;
    public $mymsessage;

    public function mount($id){
        
        $this->contact = ContactUs::where("id", $id)->first();

    }

    public function resolveIssue() {

        if ($this->mymsessage == "") {
            // If the target already exists, you can return with an error message
            Session::flash('error', 'Please enter your message.');
            return;
        }

          // Dispatch the job to send the email
          if ($this->contact) {
            dispatch(new ContactUsJob($this->contact->email, $this->contact->name, $this->contact->subject, $this->mymsessage));
        } else {
            Session::flash('error', 'No valid contact to send the message.');
            return;
        }

       // dispatch(new ContactUsJob($this->contact->email, $this->contact->name, $this->contact->subject, $this->mymsessage));

        $idata = [
            "account_no" => $this->contact->unique_code,
            "classification" => "complain",
            "content" =>  $this->mymsessage
        ];


        //Update the Job
        $response = Http::withoutVerifying()->withHeaders([
            'Authorization' => 'AhqPVu0WkseblIGOdlCmDL9ng8nYeVSvrXuW9wu8ZnoItxWMk5FRQKSuRV9yLjb3',
            'Accept' => 'application/json'
        ])->post("https://customer.ibedc.com/api/v1/tickets", $idata);

        $newResponse =  $response->json();

        \Log::info('CONTACT US REPONSE: ' . json_encode($newResponse));

       // Session::put('success',  $newResponse);
       
       $this->contact->status = '0'; // Assuming 'resolved' is the status you want to set
       $this->contact->save(); // Save the updated contact record

       Session::flash('success', 'Message successfully sent and contact status updated.');

     // Session::flash('success', 'Message successfully sent.');
    }

    public function render()
    {
        return view('livewire.view-complains');
    }
}
