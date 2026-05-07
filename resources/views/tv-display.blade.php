<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">
    <title>TV Display - TDG Crew Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .tcm-navy { background-color: #003366; }
        .tcm-light-blue { background-color: #e3f2fd; }
        .text-huge { font-size: 12vw; } /* Scales with screen width */
        .text-large-serving { font-size: 5vw; }
    </style>
</head>
<body class="bg-gray-100 font-sans h-screen overflow-hidden flex flex-col">

    <div class="tcm-navy text-white p-4 flex justify-between items-center shadow-lg border-b-4 border-yellow-500">
        <div class="flex items-center space-x-4">
            <span class="text-4xl font-black tracking-tighter">🚢 TDG CREW MANAGEMENT</span>
        </div>
        <div class="text-3xl font-bold" id="liveClock">
            {{ now()->format('M d, Y - h:i A') }}
        </div>
    </div>

    <div class="grid grid-cols-12 gap-2 flex-grow p-2">

        <div class="col-span-7 bg-white rounded-xl shadow-2xl border-2 border-gray-300 flex flex-col overflow-hidden">
            <div class="tcm-navy text-white text-center py-4 text-3xl font-black uppercase tracking-widest">
                Counter Status
            </div>
            <table class="w-full flex-grow text-left">
                <thead class="bg-gray-200 border-b-4 border-gray-300">
                    <tr class="text-gray-800 text-2xl font-black uppercase">
                        <th class="py-4 px-6 text-center">Counter</th>
                        <th class="py-4 px-6 text-center">Appoint No.</th>
                        <th class="py-4 px-6">Name</th>
                        <th class="py-4 px-6 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-gray-200">
                    @foreach(range(1, 10) as $i) @php $app = $appointments[$i-1] ?? null; @endphp
                        <tr class="{{ $app ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="py-3 px-6">
                                <div class="tcm-navy text-white w-20 h-16 flex items-center justify-center rounded-lg text-4xl font-black mx-auto shadow-md">
                                    {{ $i }}
                                </div>
                            </td>
                            <td class="py-3 px-6 text-center font-black text-5xl tracking-widest text-blue-900">
                                {{ $app ? str_pad($app->appoint_no, 4, '0', STR_PAD_LEFT) : '----' }}
                            </td>
                            <td class="py-3 px-6 text-4xl font-extrabold text-gray-900">
                                {{ $app ? strtoupper($app->applicant_name) : '-' }}
                            </td>
                            <td class="py-3 px-6 text-right">
                                @if($app)
                                    <span class="bg-yellow-400 text-black px-4 py-1 rounded-full text-2xl font-black animate-pulse">
                                        {{ strtoupper($app->status) }}
                                    </span>
                                @else
                                    <span class="text-gray-300 text-2xl font-bold uppercase italic">Vacant</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-span-5 flex flex-col gap-2">

            <div class="flex-1 flex flex-col shadow-2xl">
                <div class="tcm-navy text-white p-3 rounded-t-xl">
                    <p class="text-3xl font-black text-center">NOW SERVING</p>
                </div>
                <div class="tcm-light-blue border-4 border-t-0 border-[#003366] flex-grow flex flex-col items-center justify-center rounded-b-xl">
                    <p class="text-4xl font-black text-blue-900 -mb-4">APPOINTMENT NO:</p>
                    <span class="text-huge font-black leading-none text-black drop-shadow-md">
                        {{ $nowServing ? str_pad($nowServing->appoint_no, 4, '0', STR_PAD_LEFT) : '0000' }}
                    </span>
                </div>
            </div>

            <div class="h-1/4 flex flex-col shadow-xl">
                <div class="bg-gray-800 text-white p-2 rounded-t-xl">
                    <p class="text-2xl font-black text-center">NAME</p>
                </div>
                <div class="bg-white border-4 border-t-0 border-gray-800 flex-grow flex items-center justify-center rounded-b-xl">
                    <span class="text-large-serving font-black text-center px-4 leading-none text-gray-900 uppercase">
                        {{ $nowServing ? $nowServing->applicant_name : '---' }}
                    </span>
                </div>
            </div>

            <div class="h-1/4 flex flex-col shadow-xl">
                <div class="bg-yellow-500 text-black p-2 rounded-t-xl">
                    <p class="text-2xl font-black text-center">PLEASE PROCEED TO</p>
                </div>
                <div class="bg-white border-4 border-t-0 border-yellow-500 flex-grow flex items-center justify-center rounded-b-xl">
                    <span class="text-7xl font-black text-black uppercase">
                        Counter {{ $nowServing->counter_number ?? '-' }}
                    </span>
                </div>
            </div>

        </div>
    </div>

    <div class="tcm-navy text-white py-2 overflow-hidden border-t-4 border-yellow-500">
        <div class="whitespace-nowrap text-3xl font-bold animate-marquee">
            Welcome to TDG Crew Management • Please have your documents ready • Wait for your number to be called • Thank you for your cooperation!
        </div>
    </div>

    <style>
        @keyframes marquee {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }
        .animate-marquee {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 30s linear infinite;
        }
    </style>
</body>
</html>
