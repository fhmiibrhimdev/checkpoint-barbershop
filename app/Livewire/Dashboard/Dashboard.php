<?php

namespace App\Livewire\Dashboard;

use App\Models\User;
use App\Services\GlobalDataService;
use Livewire\Component;
use Livewire\Attributes\Title;
use Illuminate\Support\Facades\Auth;

class Dashboard extends Component
{
    #[Title('Dashboard')]
    public $cabangs;

    public function mount(GlobalDataService $globalDataService)
    {
        $this->cabangs = $globalDataService->getCabangs();
    }

    public function render()
    {
        $user = User::find(Auth::user()->id);

        if ($user->hasRole('direktur')) {
            return view('livewire.dashboard.dashboard-direktur');
        } else if ($user->hasRole('admin')) {
            return view('livewire.dashboard.dashboard-admin');
        } else if ($user->hasRole('kasir')) {
            return view('livewire.dashboard.dashboard-kasir');
        } else if ($user->hasRole('capster')) {
            return view('livewire.dashboard.dashboard-capster');
        }
    }
}
