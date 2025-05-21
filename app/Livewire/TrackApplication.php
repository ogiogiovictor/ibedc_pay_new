<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\NAC\AccoutCreaction;
use App\Models\NAC\UploadAccountCreation;

class TrackApplication extends Component
{
     public $form = [
        'tracking_id' => '',
    ];

     public function submit() {

        $this->validate([
            'form.tracking_id' =>  'required|string|alpha_num|max:255'
        ]);


         $trackingId = $this->form['tracking_id'];

        $customers = new AccoutCreaction();
        $checkExist = $customers
            ->with(['continuation', 'uploadinformation', 'caccounts', 'uploadedPictures'])
            ->where("tracking_id", $trackingId)
            ->whereIn('status', ['processing'])
            ->first();

       // $checkExist = AccoutCreaction::where("tracking_id", $trackingId)->first();


        if(!$checkExist) {
            session()->flash('error', 'Invalid Tracking ID');
            return redirect()->back();
        }

        // You need to check the status of the tracking id if it is not processing.. just return false.

        if($checkExist && $checkExist->status != 'processing') {
            session()->flash('error', 'Tracking Status Invalid');
            return redirect()->back();
        }

        // Store data in session to access in dashboard
        session()->put('tracking_data', $checkExist);

        // Redirect admin user to the dashboard
        return redirect()->route('tracking_details');

    }

    public function render()
    {
        return view('livewire.track-application');
    }
}
