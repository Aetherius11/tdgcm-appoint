<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Window {{ $windowId }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .tcm-navy { background-color: #003366; }
        .bg-main-frame { background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .btn-gray { background-color: #e9ecef; border: 1px solid #adb5bd; }
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
            <a href="{{ route('Window.Staff') }}" class="text-gray-300 hover:text-white font-bold transition text-lg">Counter</a>
        </div>
        <div class="flex items-center space-x-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-gray text-gray-800 px-6 py-2 rounded-lg font-bold hover:bg-gray-300 transition">Sign Out</button>
            </form>
        </div>
    </div>

    <main class="flex-grow flex flex-col p-6">
        <div class="max-w-7xl mx-auto w-full">
            
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Transaction History - Window {{ $windowId }}</h1>
                <p class="text-gray-600">View all completed transactions for your window</p>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Total Completed</div>
                    <div class="text-3xl font-bold text-gray-800 mt-1">{{ $stats['total_completed'] }}</div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Avg. Service Time</div>
                    <div class="text-3xl font-bold text-blue-600 mt-1">
                        @if($stats['avg_service_time'] > 0)
                            {{ gmdate('i:s', $stats['avg_service_time']) }}
                        @else
                            --
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Avg. Wait Time</div>
                    <div class="text-3xl font-bold text-green-600 mt-1">
                        @if($stats['avg_wait_time'] > 0)
                            {{ gmdate('i:s', $stats['avg_wait_time']) }}
                        @else
                            --
                        @endif
                    </div>
                </div>
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="text-xs font-bold text-gray-500 uppercase tracking-wide">Date Filter</div>
                    <div class="text-lg font-bold text-gray-800 mt-1">
                        {{ $filterDate ? \Carbon\Carbon::parse($filterDate)->format('M d, Y') : 'All Time' }}
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
                <h2 class="text-sm font-bold text-gray-700 uppercase tracking-wide mb-4">Filters</h2>
                <form action="{{ route('window.transaction-history') }}" method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-bold text-gray-500 uppercase">Date</label>
                        <input type="date" name="filter_date" value="{{ $filterDate }}" 
                            class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="text-xs font-bold text-gray-500 uppercase">Search</label>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Name, ID, or email..." 
                            class="border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                    </div>
                    <div class="flex items-end gap-2">
                        <button type="submit" class="btn-blue bg-[#0056b3] text-white px-6 py-2.5 rounded-lg font-bold hover:bg-blue-800 transition h-[42px]">
                            Apply Filters
                        </button>
                        <a href="{{ route('window.transaction-history') }}" class="text-sm text-blue-600 hover:text-blue-800 hover:underline font-medium px-4 py-2.5 h-[42px] inline-flex items-center">
                            Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Transaction Table -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-[#F9FAFB] border-b border-gray-200">
                                <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Ticket No.</th>
                                <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider">Applicant Name</th>
                                <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Called At</th>
                                <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Service Started</th>
                                <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Completed At</th>
                                <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Service Duration</th>
                                <th class="px-5 py-4 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">Wait Duration</th>
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
                                    <td class="px-5 py-4 text-center">
                                        @if($appointment->called_at)
                                            <span class="text-sm text-gray-700">{{ $appointment->called_at->format('g:i A') }}</span>
                                        @else
                                            <span class="text-gray-400 text-sm">--</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($appointment->served_at)
                                            <span class="text-sm text-green-700">{{ $appointment->served_at->format('g:i A') }}</span>
                                        @else
                                            <span class="text-gray-400 text-sm">--</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($appointment->transaction_completed_at)
                                            <span class="text-sm text-gray-700">{{ $appointment->transaction_completed_at->format('g:i A') }}</span>
                                        @else
                                            <span class="text-gray-400 text-sm">--</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($appointment->served_at && $appointment->transaction_completed_at)
                                            @php
                                                $serviceDuration = $appointment->served_at->diffInSeconds($appointment->transaction_completed_at);
                                            @endphp
                                            <span class="text-sm font-medium text-blue-600">{{ gmdate('i:s', $serviceDuration) }}</span>
                                        @else
                                            <span class="text-gray-400 text-sm">--</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4 text-center">
                                        @if($appointment->called_at && $appointment->served_at)
                                            @php
                                                $waitDuration = $appointment->called_at->diffInSeconds($appointment->served_at);
                                            @endphp
                                            <span class="text-sm font-medium text-green-600">{{ gmdate('i:s', $waitDuration) }}</span>
                                        @else
                                            <span class="text-gray-400 text-sm">--</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-5 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                                            <p class="text-gray-500 text-sm">No completed transactions found for the selected filters.</p>
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

        </div>
    </main>

    <footer class="mt-auto py-6 text-center text-xs text-gray-500">
        © {{ date('Y') }} TCM EasyQue Window Staff. All rights reserved.
    </footer>

    <style>
        .btn-blue { background-color: #0056b3; color: white; border: 1px solid #0044cc; }
    </style>
</body>
</html>