<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use App\Models\Company;
use App\Models\Branch;
use App\Traits\WithLogout;
use App\Traits\LogsActivity;

class Manage extends Component
{
    use WithLogout, LogsActivity;

    public $name;
    public $email;
    public $username;
    
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    // For sidebar
    public $user;
    public $role;
    public $company;
    public $branch;

    public function mount()
    {
        $this->logActivity('NAVIGATE', 'Manajemen Profil');

        $this->user = Auth::user();
        $this->name = $this->user->name;
        $this->email = $this->user->email;
        $this->username = $this->user->username;
        
        $this->role = $this->user->getRoleNames()->first() ?? 'No Role';
        $this->company = Company::find($this->user->company_id);
        $this->branch = Branch::find($this->user->branch_id);
    }

    public function updateProfile()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . $this->user->id,
        ]);

        $this->user->update([
            'name' => $this->name,
            'email' => $this->email,
        ]);

        session()->flash('message', 'Profil berhasil diperbarui.');
    }

    public function updatePassword()
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'new_password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $this->user->update([
            'password' => Hash::make($this->new_password),
        ]);

        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);

        session()->flash('password_message', 'Password berhasil diubah.');
    }

    public function render()
    {
        return view('livewire.profile.manage')->layout('layouts.app');
    }
}
