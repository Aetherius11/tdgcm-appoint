<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StaffLoginController extends Controller
{
    public function showLoginForm() {
        return view('auth.staff-login');
    }

    public function login(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            // Redirect directly to the WindowStaff page
            return redirect()->intended('/WindowStaff');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our staff records.',
        ]);
    }
}