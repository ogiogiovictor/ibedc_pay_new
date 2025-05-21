<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\AuditLogs as Log;

class AuditLogs extends Component
{
    use WithPagination;
    
    public $all_logs;

    public function mount() {

        $account = new Log();
        $this->all_logs = $account->orderBy('created_at', 'desc')->paginate(50)->toArray();
       
    }


    public function render()
    {
        return view('livewire.audit-logs');
    }
}
