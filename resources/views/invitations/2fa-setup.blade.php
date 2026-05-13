@extends('invitations.guest-layout')

@section('title', 'Set Up Two-Factor Authentication')

@section('content')
<div class="mb-4 text-center">
    <h2 class="text-2xl font-bold text-gray-800">Set Up Two-Factor Authentication</h2>
    <p class="mt-2 text-sm text-gray-600">
        Scan the QR code below with your authenticator app to secure your account.
    </p>
</div>

<div class="mb-6">
    <div class="flex justify-center mb-4">
        <div class="p-4 bg-white border border-gray-200 rounded-lg">
            {!! $qrCodeSvg !!}
        </div>
    </div>
    
    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
        <p class="text-sm text-yellow-800">
            <strong>Instructions:</strong>
        </p>
        <ol class="text-sm text-yellow-700 mt-2 list-decimal list-inside space-y-1">
            <li>Open your authenticator app (Google Authenticator, Authy, etc.)</li>
            <li>Scan the QR code above or enter the secret key manually</li>
            <li>Enter the 6-digit code from your app below to verify</li>
        </ol>
    </div>

    <div class="mt-4">
        <label class="block font-medium text-sm text-gray-700">
            Secret Key (for manual entry)
        </label>
        <div class="mt-1 flex">
            <input type="text" 
                   value="{{ $secretKey }}" 
                   readonly 
                   class="flex-1 block w-full rounded-md shadow-sm border-gray-300 bg-gray-100 text-gray-600 text-sm font-mono"
                   id="secret-key">
            <button type="button" 
                    onclick="navigator.clipboard.writeText(document.getElementById('secret-key').value); alert('Copied!');"
                    class="ml-2 px-3 py-2 bg-gray-200 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-300">
                Copy
            </button>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('invitations.2fa-confirm') }}">
    @csrf

    <!-- Verification Code -->
    <div>
        <label for="code" class="block font-medium text-sm text-gray-700">
            Authentication Code
        </label>
        <input id="code" 
               type="text" 
               name="code" 
               maxlength="6"
               inputmode="numeric"
               pattern="[0-9]{6}"
               autocomplete="one-time-code"
               class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-center text-2xl font-mono tracking-widest"
               placeholder="000000"
               required 
               autofocus>
        @error('code')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
        <p class="text-xs text-gray-500 mt-1">Enter the 6-digit code from your authenticator app</p>
    </div>

    <!-- Recovery Codes Notice -->
    <div class="mt-6 p-4 bg-gray-50 rounded-md border border-gray-200">
        <p class="text-sm font-medium text-gray-800">
            <svg class="inline-block w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            Important: Save Your Recovery Codes
        </p>
        <p class="text-xs text-gray-600 mt-1">
            After confirming 2FA, you'll receive 8 recovery codes. Store them in a safe place - you can use them to access your account if you lose your authenticator device.
        </p>
    </div>

    <!-- Submit Button -->
    <div class="flex items-center justify-end mt-6">
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
            Verify & Enable 2FA
        </button>
    </div>
</form>
@endsection