<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Logs\AppLog as ApplicationLogs;

class AppLog extends Component
{
    public $all_logs;

    public function mount()
    {
        $log = new ApplicationLogs();
        //All Logs
        $this->all_logs = $log->orderby("id", "desc")->paginate(30)->toArray();
    }

    public function render()
    {
        return view('livewire.app-log');
    }
}
