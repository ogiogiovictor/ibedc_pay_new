<?php

namespace App\Livewire;

use Livewire\Component;

class LogDetails extends Component
{
    public $receivedMessage;

    protected $listeners = ['showDetails'];


    public function showDetails($message)
    {
        $this->receivedMessage = $message;
    }


    public function render()
    {
        return view('livewire.log-details', ['receivedMessage' => $this->receivedMessage]);
    }
}
