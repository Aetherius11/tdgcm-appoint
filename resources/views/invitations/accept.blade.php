@extends('invitations.guest-layout')

@section('title', 'Accept Invitation')

@section('content')
<div class="mb-4 text-center">
    <h2 class="text-2xl font-bold text-gray-800">Accept Invitation</h2>
    <p class="mt-2 text-sm text-gray-600">
        You've been invited to join <strong>{{ config('app.name') }}</strong>
        @if($invitation->team)
            as part of the <strong>{{ $invitation->team->name }}</strong> team
        @endif
    </p>
    <p class="mt-1 text-sm text-gray-500">
        Invited by: {{ $invitation->invited_by_name ?? 'Administrator' }} ({{ $invitation->invited_by_email ?? 'admin' }})
    </p>
</div>

@if($existingUser)
    <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-md">
        <p class="text-sm text-blue-800">
            <strong>Note:</strong> An account with the email <strong>{{ $invitation->email }}</strong> already exists. 
            You'll update your account details and set up two-factor authentication.
        </p>
    </div>
@endif

<form method="POST" action="{{ route('invitations.accept', $invitation->token) }}">
    @csrf

    <!-- Name -->
    <div>
        <label for="name" class="block font-medium text-sm text-gray-700">
            Full Name
        </label>
        <input id="name" 
               type="text" 
               name="name" 
               value="{{ old('name', $invitation->email ? explode('@', $invitation->email)[0] : '') }}"
               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
               required 
               autofocus 
               autocomplete="name">
    </div>

    <!-- Email (read-only) -->
    <div class="mt-4">
        <label class="block font-medium text-sm text-gray-700">
            Email Address
        </label>
        <input type="email" 
               value="{{ $invitation->email }}"
               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 bg-gray-100 text-gray-600"
               readonly 
               disabled>
        <p class="text-xs text-gray-500 mt-1">This email is pre-filled from your invitation</p>
    </div>

    <!-- Password -->
    <div class="mt-4">
        <label for="password" class="block font-medium text-sm text-gray-700">
            Password
        </label>
        <input id="password" 
               type="password" 
               name="password" 
               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
               required 
               autocomplete="new-password">
    </div>

    <!-- Confirm Password -->
    <div class="mt-4">
        <label for="password_confirmation" class="block font-medium text-sm text-gray-700">
            Confirm Password
        </label>
        <input id="password_confirmation" 
               type="password" 
               name="password_confirmation" 
               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500"
               required 
               autocomplete="new-password">
    </div>

    <!-- Assigned Role -->
    <div class="mt-4">
        <label class="block font-medium text-sm text-gray-700">Assigned Role(s)</label>
        <div class="mt-1 flex flex-wrap gap-2 p-3 bg-gray-50 border border-gray-200 rounded-md">
            @foreach($invitation->roles ?? ['staff'] as $role)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                    {{ $role === 'admin' ? 'bg-green-100 text-green-800' : ($role === 'guard' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                    {{ $role === 'admin' ? 'Admin' : ($role === 'guard' ? 'Guard' : 'Window Staff') }}
                </span>
            @endforeach
        </div>
        <p class="text-xs text-gray-500 mt-1">Assigned by the administrator — cannot be changed here.</p>
    </div>

    <!-- Two-Factor Authentication -->
    <div class="mt-6">
        <div class="p-4 bg-gray-50 rounded-md border border-gray-200">
            <div class="flex items-center">
                <input id="enable_2fa" 
                       type="checkbox" 
                       name="enable_2fa" 
                       value="1"
                       class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                       checked>
                <label for="enable_2fa" class="ms-2 block text-sm font-medium text-gray-700">
                    <strong>Enable Two-Factor Authentication (Recommended)</strong>
                </label>
            </div>
            <p class="mt-2 text-xs text-gray-600 ms-6">
                After setting up your account, you'll be prompted to scan a QR code with an authenticator app (like Google Authenticator or Authy) to enable two-factor authentication for added security.
            </p>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="flex items-center justify-end mt-6">
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            {{ $existingUser ? 'Update Account & Set Up 2FA' : 'Create Account & Set Up 2FA' }}
        </button>
    </div>
</form>
@endsection