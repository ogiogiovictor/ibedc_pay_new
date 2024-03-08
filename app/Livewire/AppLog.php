<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Logs\AppLog as ApplicationLogs;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Event; // Import the Event facade

class AppLog extends Component
{
    use WithPagination;
    
    public $all_logs;

    public function mount()
    {
        $log = new ApplicationLogs();
        //All Logs
        $this->all_logs = $log->orderby("id", "desc")->paginate(10)->toArray();
      
    }


    public function showDetails($id){
       
       // $this->redirectRoute('details.show', ['id' => $id]);
    }

    public function render()
    {
        return view('livewire.app-log');
    }
}
