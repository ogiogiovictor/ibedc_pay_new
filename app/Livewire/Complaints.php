<?php

namespace App\Livewire;

use Livewire\Component;

use App\Models\ContactUs;
use Livewire\WithPagination;

class Complaints extends Component
{
    use WithPagination;

    public $complains = [];

    public function mount() {
        $this->complains = ContactUs::where("status", 1)->orderby("created_at", "desc")->paginate(10)->toArray();
    }

    public function render()
    {
        return view('livewire.complaints');
    }
}
