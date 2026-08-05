<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait WithLogout
{
    public function logout()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        return redirect()->route('login');
    }
}
