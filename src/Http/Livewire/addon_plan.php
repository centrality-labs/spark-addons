<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Addon;

class AddonPlans extends Component
{
    public $plan;

    public function mount($plan)
    {
        $this->plan = $plan;
    }

    public function render()
    {
        return view('livewire.addon-plan');
    }
}