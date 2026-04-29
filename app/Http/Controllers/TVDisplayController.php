<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TVDisplayController extends Controller
{
    public function index()
    {
        // Get appointments that are active (Ready or Ongoing)
        $appointments = \App\Models\Appointment::whereIn('status', ['ready', 'ongoing'])
            ->orderBy('updated_at', 'desc')
            ->take(12) // Matches the 12 slots in your image
            ->get();

        // Get the very latest "Ready" appointment for the big announcement box
        $nowServing = \App\Models\Appointment::where('status', 'ready')
            ->latest('updated_at')
            ->first();

        return view('tv-display', compact('appointments', 'nowServing'));
    }
}
