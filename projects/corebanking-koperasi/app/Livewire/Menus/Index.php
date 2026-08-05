<?php

namespace App\Livewire\Menus;

use App\Traits\LogsActivity;
use Livewire\Component;

class Index extends Component
{
    use LogsActivity;

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Manajemen Menu');
    }

    public function render()
    {
        return view('livewire.menus.index')->layout('layouts.app');
    }
}
