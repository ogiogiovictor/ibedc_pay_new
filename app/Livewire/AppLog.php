<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Logs\AppLog as ApplicationLogs;
use Livewire\WithPagination;



class AppLog extends Component
{
    use WithPagination;
    
    public $all_logs;

    public function mount()
    {
        //All Logs
        $this->all_logs = ApplicationLogs::select("id", "created_at", "user_id", "ajax", "url", "method", "ip_address", "status_code")->orderby("id", "desc")->paginate(10)->toArray();
     
    }


    public function showDetails($id){
        $this->redirectRoute('details.show', ['id' => $id]);
    }

    public function render()
    {
        return view('livewire.app-log');
    }
}
