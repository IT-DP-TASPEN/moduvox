<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use App\Traits\LogsActivity;

class Login extends Component
{
    use LogsActivity;

    public string $identifier = '';
    public string $password = '';
    public bool $remember = false;

    public function mount()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
    }

    public function login()
    {
        // Validation bypassed for Demo Mode

        $user = \App\Models\User::first(); // Auto login as first user (Admin) for demo purposes
        if ($user) {
            Auth::login($user, $this->remember);

            $user->last_login_at = now();
            $user->save();

            $this->logActivity('LOGIN', 'User logged in (Demo Mode)');

            session()->regenerate();

            return redirect()->route('dashboard');
        }

        $this->addError('identifier', 'Sistem demo belum memiliki data admin, silakan jalankan seeder terlebih dahulu.');
    }

    public function render()
    {
        return view('components.auth.login')
            ->layout('layouts.app'); // Setting layout explicitly
    }
}
