<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MainMenu;
use App\Models\SubMenu;


class ViewContrologs extends Component
{
    public $id = [];

    public function render()
    {
        return view('livewire.view-contrologs');
    }
}
