<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class SsoController extends Controller
{
    /**
     * Handle the signed login request.
     */
    public function login(Request $request)
    {
        // Validating the signature is handled by the middleware 'signed'
        // which we will apply in web.php
        
        $userId = $request->user_id;
        $target = $request->query('target', '/admin/dashboard');

        $user = User::findOrFail($userId);

        // Login the user to web session
        Auth::login($user);

        return redirect($target);
    }
}
