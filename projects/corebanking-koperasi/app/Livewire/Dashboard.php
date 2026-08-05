<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use App\Traits\WithLogout;
use App\Models\Company;
use App\Models\Branch;

use App\Models\ActivityLog;
use App\Traits\LogsActivity;

class Dashboard extends Component
{
    use WithLogout, LogsActivity;

    public $user;
    public $role;
    public $company;
    public $branch;
    public $recentMenus = [];

    public function mount()
    {
        $this->user = Auth::user();
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->company = Company::find($this->user->company_id);
        $this->branch = Branch::find($this->user->branch_id);

        $this->logActivity('NAVIGATE', 'Dashboard');
        $this->recentMenus = $this->getRecentMenus();
    }

    private function getRecentMenus()
    {
        $recentLogs = ActivityLog::where('user_id', Auth::id())
            ->where('action', 'NAVIGATE')
            ->where('description', '!=', 'Dashboard') // Exclude dashboard itself
            ->orderBy('created_at', 'desc')
            ->get();

        $menus = [];
        $uniqueMenuNames = [];
        
        // Get menus from Database
        $allMenus = \App\Models\Menu::active()->get();
        $flattenedMenus = [];
        foreach($allMenus as $menu) {
            if (\Illuminate\Support\Facades\Route::has($menu->route)) {
                $flattenedMenus[$menu->name] = [
                    'name' => $menu->name,
                    'icon' => $menu->icon,
                    'route' => $menu->route,
                    'permission' => $menu->permission,
                    'display_name' => $menu->name
                ];
            }
        }

        foreach($recentLogs as $log) {
            $menuName = $log->description;
            if (isset($flattenedMenus[$menuName]) && !in_array($menuName, $uniqueMenuNames)) {
                $menus[] = $flattenedMenus[$menuName];
                $uniqueMenuNames[] = $menuName;
                if (count($menus) >= 3) break;
            }
        }
        
        return $menus;
    }

    public function render()
    {
        return view('livewire.dashboard')->layout('layouts.app');
    }
}
