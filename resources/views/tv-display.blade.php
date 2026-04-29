<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="30">
    <title>TV Display - TDG Crew Management</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-200 font-sans h-screen overflow-hidden p-4">
    
    <div class="bg-white p-3 rounded shadow-sm mb-4 flex items-center">
        <div class="text-gray-700 font-bold text-lg">
             🚢 TDG Crew Management
        </div>
    </div>

    <div class="grid grid-cols-12 gap-4 h-[calc(100vh-100px)]">
        
        <div class="col-span-7 bg-white rounded-lg shadow-md border border-gray-300 overflow-hidden">
            <table class="w-full h-full text-left">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr class="text-gray-600 text-xl uppercase tracking-wider">
                        <th class="py-3 px-6 w-24 text-center">Counter</th>
                        <th class="py-3 px-6 text-center">Appointment No.</th>
                        <th class="py-3 px-6">Name</th>
                        <th class="py-3 px-6 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach(range(1, 12) as $i)
                        @php $app = $appointments[$i-1] ?? null; @endphp
                        <tr class="text-2xl font-bold">
                            <td class="py-3 px-6">
                                <div class="bg-[#4DD0E1] text-black w-12 h-10 flex items-center justify-center rounded-md mx-auto">
                                    {{ $i }}
                                </div>
                            </td>
                            <td class="py-3 px-6 text-center tracking-widest text-3xl">
                                {{ $app ? str_pad($app->appoint_no, 4, '0', STR_PAD_LEFT) : '' }}
                            </td>
                            <td class="py-3 px-6 text-gray-800">
                                {{ $app ? strtoupper($app->applicant_name) : '-' }}
                            </td>
                            <td class="py-3 px-6 text-right">
                                <span class="{{ !$app ? 'text-gray-400' : 'text-gray-700' }}">
                                    {{ $app ? ucfirst($app->status) : 'Vacant' }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-span-5 flex flex-col gap-4">
            
            <div class="flex flex-col h-1/3">
                <div class="bg-white border-t border-x border-gray-300 p-2 rounded-t-lg">
                    <p class="text-xl font-bold text-gray-800">APPOINTMENT NO:</p>
                </div>
                <div class="bg-[#E0F7FA] border border-gray-300 flex-1 flex items-center justify-center shadow-inner">
                    <span class="text-[10rem] font-black leading-none">
                        {{ $nowServing ? str_pad($nowServing->appoint_no, 4, '0', STR_PAD_LEFT) : '0000' }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col h-1/3">
                <div class="bg-white border-t border-x border-gray-300 p-2">
                    <p class="text-xl font-bold text-gray-800">NAME:</p>
                </div>
                <div class="bg-[#E0F7FA] border border-gray-300 flex-1 flex items-center justify-center shadow-inner">
                    <span class="text-6xl font-bold text-center px-4 leading-tight">
                        {{ $nowServing ? strtoupper($nowServing->applicant_name) : '---' }}
                    </span>
                </div>
            </div>

            <div class="flex flex-col h-1/3">
                <div class="bg-white border-t border-x border-gray-300 p-2">
                    <p class="text-xl font-bold text-gray-800">PLEASE PROCEED TO:</p>
                </div>
                <div class="bg-[#E0F7FA] border border-gray-300 flex-1 flex items-center justify-center shadow-inner rounded-b-lg">
                    <span class="text-8xl font-black text-black">
                        Counter {{ $nowServing->counter_number ?? '-' }}
                    </span>
                </div>
            </div>

        </div>
    </div>
</body>
</html>