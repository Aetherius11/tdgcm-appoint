<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guard Dashboard - Appointment Status</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#F4F7FA] min-h-screen">

    <!-- Header -->
    <header class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center shadow-lg">
        <div class="flex items-center space-x-6">
            <div class="flex items-center space-x-2">
                @php $companyLogo = \App\Models\Company::where('is_active', true)->value('branding_logo'); @endphp
                @if($companyLogo)
                <div class="bg-white p-1 rounded-md">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($companyLogo) }}" alt="Logo" class="h-8 w-8 object-contain">
                </div>
                @endif
                <span class="font-bold text-xl">TDG Crew Management</span>
            </div>
            <a href="{{ route('qr.scanner') }}" target="_blank" rel="noopener noreferrer" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-bold transition text-lg">QR Scanner</a>
            <a href="{{ route('appointment.create') }}" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white font-bold transition text-lg">New Appointment</a>
        </div>
        <div class="flex items-center space-x-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="bg-gray-200 text-gray-800 px-6 py-2 rounded-lg font-bold hover:bg-gray-300 transition">Sign Out</button>
            </form>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-8">
        
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total Tickets</div>
                <div class="text-3xl font-bold text-gray-800 mt-1">{{ $appointments->total() }}</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Pending</div>
                <div class="text-3xl font-bold text-yellow-600 mt-1">
                    {{ $appointments->where('status', 'pending')->count() }}
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Approved</div>
                <div class="text-3xl font-bold text-green-600 mt-1">
                    {{ $appointments->where('status', 'approved')->count() }}
                </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Cancelled</div>
                <div class="text-3xl font-bold text-red-600 mt-1">
                    {{ $appointments->where('status', 'cancelled')->count() }}
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" /></svg>
                Filters
            </h2>
            <form action="{{ url()->current() }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Select Date</label>
                    <input type="date" name="filter_date" value="{{ request('filter_date') }}" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or ID..." 
                        class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Filter By</label>
                    <select name="filter_type" id="filter_type" onchange="toggleFilters()" 
                        class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="all">All Appointments</option>
                        <option value="principal" {{ request('filter_type') == 'principal' ? 'selected' : '' }}>By Department</option>
                        <option value="purpose" {{ request('filter_type') == 'purpose' ? 'selected' : '' }}>By Purpose</option>
                    </select>
                </div>

                <div class="flex items-end">
                    <a href="{{ url()->current() }}" target="_blank" rel="noopener noreferrer" class="text-sm text-blue-600 hover:text-blue-800 hover:underline font-medium px-2 py-2.5">
                        ↻ Reset All Filters
                    </a>
                </div>

                <!-- Dynamic Filter Sections -->
                <div id="principal_section" class="flex flex-col gap-1 {{ request('filter_type') == 'principal' ? '' : 'hidden' }} lg:col-span-2">
                    <label class="text-xs font-bold text-gray-500 uppercase">Select Department</label>
                    <select name="principal_id" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">-- All Departments --</option>
                        @foreach(\App\Models\Principal::all() as $p)
                            <option value="{{ $p->id }}" {{ request('principal_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="purpose_section" class="flex flex-col gap-1 {{ request('filter_type') == 'purpose' ? '' : 'hidden' }} lg:col-span-2">
                    <label class="text-xs font-bold text-gray-500 uppercase">Select Purpose</label>
                    <select name="purpose_id" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                        <option value="">-- All Purposes --</option>
                        @foreach(\App\Models\Purpose::all() as $purpose)
                            <option value="{{ $purpose->id }}" {{ request('purpose_id') == $purpose->id ? 'selected' : '' }}>{{ $purpose->purpose_name }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Appointments Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F9FAFB] border-b border-gray-200">
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Appoint ID</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Applicant Name</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Department</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Purpose</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Time Slot</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Window</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Status</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Transaction History</th>
                            <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($appointments as $appointment)
                            <tr class="hover:bg-blue-50/30 transition-colors duration-150">
                                <td class="px-5 py-4">
                                    <span class="text-sm font-semibold text-blue-600 bg-blue-50 px-2 py-1 rounded">
                                        #{{ $appointment->appoint_no }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm font-medium text-gray-800">
                                        {{ $appointment->applicant->first_name }} {{ $appointment->applicant->last_name }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $appointment->applicant->email }}</div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-gray-700">
                                        {{ $appointment->principal->name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-gray-700">
                                        {{ $appointment->purpose->purpose_name ?? 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-gray-700">
                                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="text-sm text-gray-700">
                                        {{ $appointment->timeSlot ? $appointment->timeSlot->start_time . ' - ' . $appointment->timeSlot->end_time : 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @if($appointment->window_number)
                                        <span class="inline-flex items-center justify-center w-8 h-8 bg-[#003366] text-white rounded-md font-bold text-sm">
                                            {{ $appointment->window_number }}
                                        </span>
                                    @else
                                        <span class="text-gray-400 text-sm">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-center">
                                    @php
                                        $statusConfig = [
                                            'approved' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-200', 'icon' => '✓'],
                                            'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'border' => 'border-yellow-200', 'icon' => '◌'],
                                            'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'border' => 'border-red-200', 'icon' => '✕'],
                                            'calling' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'border' => 'border-blue-200', 'icon' => '📢'],
                                            'serving' => ['bg' => 'bg-green-100', 'text' => 'text-green-700', 'border' => 'border-green-200', 'icon' => '✓'],
                                            'completed' => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'icon' => '✓'],
                                            'on_hold' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'border' => 'border-yellow-200', 'icon' => '⏸'],
                                            'transferred' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'icon' => '↗'],
                                        ];
                                        $config = $statusConfig[$appointment->status] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-200', 'icon' => '•'];
                                    @endphp
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold uppercase {{ $config['bg'] }} {{ $config['text'] }} border {{ $config['border'] }}">
                                        <span class="text-xs">{{ $config['icon'] }}</span>
                                        {{ $appointment->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <div class="text-xs text-gray-600 space-y-1">
                                        @if($appointment->called_at)
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="text-blue-600">📢</span>
                                                <span>{{ $appointment->called_at->format('g:i A') }}</span>
                                            </div>
                                        @endif
                                        @if($appointment->served_at)
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="text-green-600">▶</span>
                                                <span>{{ $appointment->served_at->format('g:i A') }}</span>
                                            </div>
                                        @endif
                                        @if($appointment->transaction_completed_at)
                                            <div class="flex items-center justify-center gap-1">
                                                <span class="text-gray-600">✓</span>
                                                <span>{{ $appointment->transaction_completed_at->format('g:i A') }}</span>
                                            </div>
                                        @endif
                                        @if(!$appointment->called_at && !$appointment->served_at && !$appointment->transaction_completed_at)
                                            <span class="text-gray-400">No activity</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4 text-center">
                                    <a href="{{ route('appointment.show', $appointment) }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 px-3 py-1.5 rounded-lg transition-colors border border-transparent hover:border-blue-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                        View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-5 py-16 text-center">
                                    <div class="flex flex-col items-center gap-3">
                                        <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        <p class="text-gray-500 text-sm">No appointments found for the selected filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        @if($appointments->hasPages())
        <div class="mt-6">
            {{ $appointments->links() }}
        </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-6 text-center text-xs text-gray-500">
        © {{ date('Y') }} TCM EasyQue Guard Dashboard. All rights reserved.
    </footer>

    <script>
    function toggleFilters() {
        const type = document.getElementById('filter_type').value;
        const principalSec = document.getElementById('principal_section');
        const purposeSec = document.getElementById('purpose_section');

        principalSec.classList.add('hidden');
        purposeSec.classList.add('hidden');

        if (type === 'principal') {
            principalSec.classList.remove('hidden');
        } else if (type === 'purpose') {
            purposeSec.classList.remove('hidden');
        } else {
            // Reset to current page without filters
            window.location.href = "{{ url()->current() }}";
        }
    }
    </script>
</body>
</html>