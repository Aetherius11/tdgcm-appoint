<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display - TDG Crew Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <meta http-equiv="refresh" content="5">
    @livewireStyles
    <style>
        .tcm-navy { background-color: #003366; }
        .bg-sky-light { background-color: #e3f2fd; }
        .bg-sky-medium { background-color: #d1ecf1; }
        .text-tcm-navy { color: #003366; }
    </style>
</head>
<body class="bg-gray-100 m-0 p-0 font-sans">
    <div class="min-h-screen p-4">

        <!-- Header -->
        <div class="bg-white border border-gray-300 rounded-lg shadow-sm mb-4 p-4 flex items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-[#003366] rounded-full flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="20" cy="20" r="16" stroke="currentColor" stroke-width="2"/>
                        <path d="M10 20 L18 12 L22 28 L30 20" stroke="currentColor" stroke-width="2" fill="none"/>
                    </svg>
                </div>
                <span class="text-2xl font-bold text-tcm-navy">TDG Crew Management</span>
            </div>
            <div class="ml-auto text-gray-500 text-lg">
                {{ now()->format('l, F j, Y - g:i A') }}
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6">

            <!-- Left Panel - Counters Table -->
            <div class="col-span-7">
                <div class="bg-white border border-gray-300 rounded-lg shadow-sm overflow-hidden">
                    <!-- Table Header -->
                    <div class="tcm-navy text-white p-4">
                        <h2 class="text-2xl font-bold">Counter Status</h2>
                    </div>
                    <table class="w-full text-xl">
                        <thead>
                            <tr class="bg-gray-100 border-b-2 border-gray-300">
                                <th class="p-4 font-bold text-center w-24">Counter</th>
                                <th class="p-4 font-bold text-center">Appointment No.</th>
                                <th class="p-4 font-bold">Name</th>
                                <th class="p-4 font-bold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($counters as $counter)
                            <tr class="bg-sky-light border-b border-gray-200 hover:bg-sky-medium transition-colors">
                                <td class="p-3">
                                    <div class="w-14 h-12 bg-[#003366] text-white rounded-md flex items-center justify-center font-bold text-2xl mx-auto">
                                        {{ $counter['counter'] }}
                                    </div>
                                </td>
                                <td class="p-3 font-bold text-3xl text-center tracking-widest">
                                    @if($counter['appointment'])
                                        {{ str_pad(substr($counter['appointment']->appoint_no, -4), 4, '0', STR_PAD_LEFT) }}
                                    @else
                                        <span class="text-gray-300">----</span>
                                    @endif
                                </td>
                                <td class="p-3 font-bold text-2xl">
                                    @if($counter['appointment'])
                                        {{ substr($counter['appointment']->applicant->first_name, 0, 1) }}. {{ $counter['appointment']->applicant->last_name }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="p-3 text-center">
                                    @if($counter['status'] === 'Vacant')
                                        <span class="bg-gray-200 text-gray-600 px-4 py-2 rounded-full font-semibold text-lg">
                                            {{ $counter['status'] }}
                                        </span>
                                    @elseif($counter['status'] === 'Calling')
                                        <span class="bg-blue-100 text-blue-700 px-4 py-2 rounded-full font-semibold text-lg animate-pulse">
                                            {{ $counter['status'] }}
                                        </span>
                                    @elseif($counter['status'] === 'Serving')
                                        <span class="bg-green-100 text-green-700 px-4 py-2 rounded-full font-semibold text-lg">
                                            {{ $counter['status'] }}
                                        </span>
                                    @elseif($counter['status'] === 'On Hold')
                                        <span class="bg-yellow-100 text-yellow-700 px-4 py-2 rounded-full font-semibold text-lg">
                                            {{ $counter['status'] }}
                                        </span>
                                    @else
                                        <span class="bg-gray-100 text-gray-500 px-4 py-2 rounded-full font-semibold text-lg">
                                            {{ $counter['status'] }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Panel -->
            <div class="col-span-5 flex flex-col gap-6">

                <!-- Current Call Display -->
                <div class="bg-white border border-gray-300 rounded-lg shadow-sm overflow-hidden">
                    <div class="tcm-navy text-white p-4">
                        <h2 class="text-2xl font-bold">Now Serving</h2>
                    </div>
                    <div class="p-4">

                        <!-- Appointment Number -->
                        <div class="mb-4">
                            <div class="bg-gray-100 p-3 border-b border-gray-300">
                                <span class="text-xl font-bold text-gray-700">APPOINTMENT NO:</span>
                            </div>
                            <div class="bg-sky-light p-6 border border-t-0 border-gray-300 text-center">
                                @if($currentCall)
                                    <div class="text-[120px] font-black leading-none text-[#003366]">
                                        {{ str_pad(substr($currentCall->appoint_no, -4), 4, '0', STR_PAD_LEFT) }}
                                    </div>
                                @else
                                    <div class="text-[120px] font-black leading-none text-gray-300">
                                        ----
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Name -->
                        <div class="mb-4">
                            <div class="bg-gray-100 p-3 border-b border-gray-300">
                                <span class="text-xl font-bold text-gray-700">NAME:</span>
                            </div>
                            <div class="bg-sky-light p-6 border border-t-0 border-gray-300 text-center">
                                @if($currentCall)
                                    <div class="text-7xl font-black leading-tight text-[#003366]">
                                        {{ substr($currentCall->applicant->first_name, 0, 1) }}. {{ $currentCall->applicant->last_name }}
                                    </div>
                                @else
                                    <div class="text-7xl font-black leading-tight text-gray-300">
                                        --------
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Counter -->
                        <div>
                            <div class="bg-gray-100 p-3 border-b border-gray-300">
                                <span class="text-xl font-bold text-gray-700">PLEASE PROCEED TO:</span>
                            </div>
                            <div class="bg-sky-light p-6 border border-t-0 border-gray-300 text-center">
                                @if($currentCall)
                                    <div class="text-[100px] font-black leading-none text-[#003366]">
                                        Counter {{ $currentCall->counter_number }}
                                    </div>
                                @else
                                    <div class="text-[100px] font-black leading-none text-gray-300">
                                        --------
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Up Next Section -->
                <div class="bg-white border border-gray-300 rounded-lg shadow-sm overflow-hidden flex-grow">
                    <div class="tcm-navy text-white p-4">
                        <h2 class="text-2xl font-bold">Up Next</h2>
                    </div>
                    <div class="p-4">
                        @if($upNext && $upNext->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($upNext as $next)
                                    <div class="bg-sky-light border border-gray-200 rounded-lg p-4 flex justify-between items-center">
                                        <div class="flex items-center gap-4">
                                            <div class="bg-[#003366] text-white w-16 h-12 rounded-md flex items-center justify-center font-bold text-2xl">
                                                {{ str_pad(substr($next->appoint_no, -4), 4, '0', STR_PAD_LEFT) }}
                                            </div>
                                            <div class="text-2xl font-bold text-[#003366]">
                                                {{ substr($next->applicant->first_name, 0, 1) }}. {{ $next->applicant->last_name }}
                                            </div>
                                        </div>
                                        <div class="text-gray-500 text-lg">
                                            @if($next->checked_in_at)
                                                Checked in: {{ $next->checked_in_at->format('g:i A') }}
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex items-center justify-center h-48 text-gray-400 text-2xl font-bold">
                                No upcoming appointments
                            </div>
                        @endif
                    </div>
                </div>

            </div>

        </div>
    </div>

    @livewireScripts
</body>
</html>