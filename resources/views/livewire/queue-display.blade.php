<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display - TDG Crew Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-white m-0 p-0 font-sans">
    <div wire:poll.3s class="min-h-screen border-4 border-blue-600 rounded-lg p-4">

        <!-- Header -->
        <div class="flex items-center mb-6 px-4">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="18" stroke="#0f766e" stroke-width="2"/>
                    <path d="M10 20 L18 12 L22 28 L30 20" stroke="#0f766e" stroke-width="2" fill="none"/>
                </svg>
                <span class="text-xl font-medium">TDG Crew Management</span>
            </div>
        </div>

        <div class="grid grid-cols-12 gap-6 px-2">

            <!-- Left Panel - Counters Table -->
            <div class="col-span-7">
                <table class="w-full border border-gray-400 rounded-lg text-xl">
                    <thead>
                        <tr class="text-left">
                            <th class="p-3 font-bold">Counter</th>
                            <th class="p-3 font-bold">Appointment No.</th>
                            <th class="p-3 font-bold">Name</th>
                            <th class="p-3 font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($counters as $counter)
                        <tr class="bg-sky-100 border-t border-gray-300">
                            <td class="p-2">
                                <div class="w-12 h-10 bg-sky-200 border border-gray-400 rounded-md flex items-center justify-center font-bold text-xl">
                                    {{ $counter['counter'] }}
                                </div>
                            </td>
                            <td class="p-2 font-bold text-3xl">
                                @if($counter['appointment'])
                                    {{ str_pad(substr($counter['appointment']->appoint_no, -4), 4, '0', STR_PAD_LEFT) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-2 font-bold text-3xl">
                                @if($counter['appointment'])
                                    {{ substr($counter['appointment']->applicant->first_name, 0, 1) }}. {{ $counter['appointment']->applicant->last_name }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="p-2 font-semibold text-lg">
                                {{ $counter['status'] }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Right Panel - Current Call Display -->
            <div class="col-span-5">
                <div class="border border-gray-400 rounded-lg p-1">

                    <!-- Appointment Number -->
                    <div class="mb-4">
                        <div class="bg-gray-200 p-3 border border-gray-300 rounded-t-lg">
                            <span class="text-2xl font-bold">APPOINTMENT NO:</span>
                        </div>
                        <div class="bg-sky-100 p-6 border border-gray-300 rounded-b-lg text-center">
                            @if($currentCall)
                                <div class="text-[160px] font-black leading-none">
                                    {{ str_pad(substr($currentCall->appoint_no, -4), 4, '0', STR_PAD_LEFT) }}
                                </div>
                            @else
                                <div class="text-[160px] font-black leading-none text-gray-300">
                                    ----
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Name -->
                    <div class="mb-4">
                        <div class="bg-gray-200 p-3 border border-gray-300 rounded-t-lg">
                            <span class="text-2xl font-bold">Name:</span>
                        </div>
                        <div class="bg-sky-100 p-6 border border-gray-300 rounded-b-lg text-center">
                            @if($currentCall)
                                <div class="text-[100px] font-black leading-none">
                                    {{ substr($currentCall->applicant->first_name, 0, 1) }}. {{ $currentCall->applicant->last_name }}
                                </div>
                            @else
                                <div class="text-[100px] font-black leading-none text-gray-300">
                                    --------
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Counter -->
                    <div>
                        <div class="bg-gray-200 p-3 border border-gray-300 rounded-t-lg">
                            <span class="text-2xl font-bold">PLEASE PROCEED TO :</span>
                        </div>
                        <div class="bg-sky-100 p-6 border border-gray-300 rounded-b-lg text-center">
                            @if($currentCall)
                                <div class="text-[140px] font-black leading-none">
                                    Counter {{ $currentCall->counter_number }}
                                </div>
                            @else
                                <div class="text-[140px] font-black leading-none text-gray-300">
                                    --------
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    @livewireScripts
</body>
</html>
