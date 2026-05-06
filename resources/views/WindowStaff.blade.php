<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Window Staff - TDG Crew Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .tcm-navy { background-color: #003366; }
        .bg-main-frame { background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .bg-light-blue-zone { background-color: #e3f2fd; }
        .btn-gray { background-color: #e9ecef; border: 1px solid #adb5bd; }
        .btn-blue { background-color: #0056b3; color: white; border: 1px solid #0044cc; }
        .ticket-box { background-color: #d1ecf1; border: 1px solid #bee5eb; }
    </style>
</head>
<body class="bg-white font-sans text-gray-900 min-h-screen flex flex-col">

    <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center shadow-lg">
        <div class="flex items-center space-x-6">
            <div class="flex items-center space-x-2">
                <img src="{{ asset('tdg_logo.png') }}" alt="Logo" class="h-10">
                <span class="font-bold text-xl">TDG Crew Management</span>
            </div>
            <a href="/" class="text-gray-300 hover:text-white font-bold transition text-lg">Home</a>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn-gray text-gray-800 px-6 py-2 rounded-lg font-bold hover:bg-gray-300 transition">Sign Out</button>
        </form>
    </div>

    <main class="flex-grow flex flex-col p-4">
        <div class="bg-main-frame rounded-lg p-6 shadow-sm flex flex-col flex-grow">

            <div class="text-center text-lg font-bold border-b border-gray-300 pb-3 mb-6">
                Hi, {{ auth()->user()->name ?? 'Heilly' }}!
            </div>

            <div class="grid grid-cols-6 gap-6 mb-10 px-4">
                @if(isset($appointments) && $appointments->isNotEmpty())
                    @php $currentId = $appointments->first()->id; @endphp

                    <form action="{{ route('window.notify', $currentId) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-blue h-40 w-full rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">Notify</button>
                    </form>

                    <form action="{{ route('window.start', $currentId) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-blue h-40 w-full rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">Start Service</button>
                    </form>

                    <button class="btn-blue h-40 rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">Hold Ticket</button>
                    <button class="btn-blue h-40 rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">Resume</button>
                    <button class="btn-blue h-40 rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">Transfer</button>

                    <form action="{{ route('window.end', $currentId) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-blue h-40 w-full rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">End</button>
                    </form>
                @else
                    @foreach(['Notify', 'Start Service', 'Hold', 'Resume', 'Transfer', 'End'] as $label)
                        <button disabled class="bg-gray-300 text-gray-500 h-40 rounded-xl text-3xl font-bold cursor-not-allowed">{{ $label }}</button>
                    @endforeach
                @endif
            </div>

            <div class="grid grid-cols-2 gap-10 px-4 flex-grow">

                <div class="bg-light-blue-zone p-6 rounded-xl shadow-inner flex flex-col">
                    <div class="tcm-navy text-white text-center py-8 text-4xl font-bold mb-6 rounded-t-lg">
                        Window 1
                    </div>

                    @if(isset($appointments) && $appointments->isNotEmpty())
                        @php $current = $appointments->first(); @endphp
                        <div class="ticket-box rounded-full py-4 px-12 flex justify-between items-center mb-8 shadow-sm">
                            <span class="text-5xl font-bold text-black">{{ str_pad($current->id, 4, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-4xl font-bold uppercase text-black">{{ $current->applicant->first_name }} {{ $current->applicant->last_name }}</span>
                        </div>

                        <div class="bg-gray-200 border border-gray-400 rounded-lg p-8 space-y-6 flex-grow">
                            <p class="text-center text-xl font-semibold">Appointment ID: <span class="font-bold">TIP-000-{{ $current->id }}</span></p>
                            <p class="text-center text-xl font-semibold uppercase text-black">Status: <span class="text-blue-700 font-bold">{{ strtoupper($current->status ?? 'Pending') }}</span></p>
                            <p class="text-center text-5xl font-bold pt-6 border-t border-gray-400 text-black">
                                {{ $current->applicant->first_name }} {{ $current->applicant->last_name }}
                            </p>
                            <p class="text-center text-2xl font-semibold">Time: {{ \Carbon\Carbon::parse($current->appointment_time)->format('g:i A') }}</p>
                        </div>
                    @else
                        <div class="flex-grow flex items-center justify-center text-gray-400 text-5xl font-bold italic">No Active Applicant</div>
                    @endif
                </div>

                <div class="bg-light-blue-zone p-6 rounded-xl shadow-inner flex flex-col">
                    <div class="tcm-navy text-white text-center py-8 text-4xl font-bold mb-6 rounded-t-lg">
                        Next Queue
                    </div>
                    <div class="space-y-4 flex-grow">
                        @if(isset($appointments) && $appointments->count() > 1)
                            @foreach($appointments->skip(1) as $next)
                                <div class="ticket-box rounded-md py-4 px-12 flex justify-between items-center shadow-sm">
                                    <span class="text-4xl font-bold text-black">{{ str_pad($next->id, 4, '0', STR_PAD_LEFT) }}</span>
                                    <span class="text-4xl font-bold uppercase text-black">{{ substr($next->applicant->first_name, 0, 1) }}. {{ $next->applicant->last_name }}</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-10 text-gray-500 text-2xl">Queue is empty</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-center space-x-6 mt-10 pb-4">
                <button class="btn-gray px-12 py-4 rounded-xl font-bold text-xl shadow-md">Open Counter</button>
                <button class="btn-gray px-12 py-4 rounded-xl font-bold text-xl shadow-md">Close Counter</button>
                <button class="btn-gray px-12 py-4 rounded-xl font-bold text-xl shadow-md">Break</button>
            </div>
        </div>
    </main>
</body>
</html>
