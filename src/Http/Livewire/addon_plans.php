<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Addon;

class AddonPlan extends Component
{
    public $plans;
    public $selectedPlan;

    public function mount()
    {
        $plans = Addon::all();
    }

    public function render()
    {
        return view('livewire.addon-plan');
    }
}