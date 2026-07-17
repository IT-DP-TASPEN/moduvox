<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class SsoController extends Controller
{
    /**
     * Generate a temporary signed URL to login to the web portal.
     */
    public function generateToken(Request $request)
    {
        $user = $request->user();
        
        // Target path after login
        $target = $request->query('path', '/admin/dashboard');

        // Generate signed URL for the web route
        $url = URL::temporarySignedRoute(
            'sso.login', 
            now()->addMinutes(5), 
            [
                'user_id' => $user->id,
                'target' => $target
            ]
        );

        return response()->json([
            'url' => $url
        ]);
    }
}
