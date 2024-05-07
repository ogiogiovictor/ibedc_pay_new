<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Agency\Agents;
use App\Models\Agency\Targets;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class AddAgencyTarget extends Component
{
    public $id;
    public $agencies;
    public $years = [];
    public $months = [];
    public $tyear;
    public $tmonth;
    public $mtarget;
    public $agency_id;
    public $target = [];

    public function mount() {
        $this->agencies = Agents::where("id", $this->id)->first();
        $this->target = Targets::where("agency_id",  $this->id)->get();


        $currentYear = Carbon::now()->year;

        for ($year = 2020; $year <= $currentYear; $year++) {
            $this->years[] = $year;
        }

        $this->months = [
            1 => 'January', 
            2 => 'February', 
            3 => 'March', 
            4 => 'April', 
            5 => 'May', 
            6 => 'June', 
            7 => 'July', 
            8 => 'August', 
            9 => 'September', 
            10 => 'October', 
            11 => 'November', 
            12 => 'December'
        ];
    }


    public function addTarget(){

       

         $validatedData = $this->validate([
            'tyear' => 'required',
            'tmonth' => 'required',
            'mtarget' => 'required'
        ]);

       

        try {

        /// Check if the target already exists for the specified agency and month
        $existingTarget = Targets::where('agency_id', $this->id)
        ->where('year', $validatedData['tyear'])
        ->where('month', $validatedData['tmonth'])
        ->first();
       
        if ($existingTarget) {
        // If the target already exists, you can return with an error message
        Session::flash('error', 'Target already exists for this agency and month.');
        return;
        }

         // If the target already exists, set an error message
         if ($existingTarget) {
            Session::flash('error', 'Target for this month already exists.');
            $errorMessage = 'Target for this month already exists.';
            return view('livewire.add-agency-target', compact('errorMessage'));
        }

             // Instantiate a new instance of your Target model
        $target = new Targets();

        // Set the attributes of the model
        $target->year = $validatedData['tyear'];
        $target->month = $validatedData['tmonth'];
        $target->target_amount = $validatedData['mtarget'];
        $target->agency_id = $this->id;

       // dd($validatedData['tyear']);

         // Save the model to the database
         $target->save();

         // Store a success message in the session
        Session::flash('success', 'Target added successfully.');
         //session()->flash('success', 'Target added successfully.');

        // Optionally, you can also reset the form fields after successful addition
        $this->reset(['tyear', 'tmonth', 'mtarget']);

        // Optionally, you can redirect the user to another page after successful addition
         return redirect()->route('add_target', ['id' => $this->id]);


        }catch(\Exception $e){

           Session::flash('error', 'Failed to add target.');
          //  session()->flash('error', 'Failed to add target.');
            $errorMessage = 'Failed to add target.';
            return view('livewire.add-agency-target', compact('errorMessage'));
        }
    }

    public function render()
    {
        //return view('livewire.add-agency-target');
        return view('livewire.add-agency-target', [
            'agencies' => $this->agencies, // Passing the agencies to the view
            'years' => $this->years,
            'months' => $this->months
        ]);
    }
}







