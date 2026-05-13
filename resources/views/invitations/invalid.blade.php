@extends('invitations.guest-layout')

@section('title', $title ?? 'Invalid Invitation')

@section('content')
<div class="text-center">
    <div class="mb-4">
        <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
    </div>

    <h2 class="text-2xl font-bold text-gray-800 mb-2">
        {{ $title ?? 'Invalid or Expired Invitation' }}
    </h2>

    <p class="text-gray-600 mb-6">
        {{ $message ?? 'This invitation link is invalid or has expired. Please contact your administrator to request a new invitation.' }}
    </p>

    <div class="flex items-center justify-center space-x-4">
        <a href="/" class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Go to Homepage
        </a>
        
        @auth
            <a href="{{ route('dashboard') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                Go to Dashboard
            </a>
        @endauth
    </div>
</div>
@endsection