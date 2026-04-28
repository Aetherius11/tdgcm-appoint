<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Status List</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 p-8">
    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Appointment Status List</h1>
            
            <form action="{{ url()->current() }}" method="GET" class="flex flex-wrap gap-4 items-end">

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Select Date</label>
                    <input type="date" name="filter_date" value="{{ request('filter_date') }}" onchange="this.form.submit()"
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none h-10 bg-white">
                </div>
    
                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Search</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or ID..." 
                        class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 outline-none h-10">
                </div>

                <div class="flex flex-col gap-1">
                    <label class="text-xs font-bold text-gray-500 uppercase">Filter By</label>
                    <select name="filter_type" id="filter_type" onchange="toggleFilters()" 
                        class="border border-gray-300 rounded-lg px-4 py-2 bg-white h-10 w-40">
                        <option value="all">All</option>
                        <option value="principal" {{ request('filter_type') == 'principal' ? 'selected' : '' }}>Principals</option>
                        <option value="purpose" {{ request('filter_type') == 'purpose' ? 'selected' : '' }}>Purposes</option>
                    </select>
                </div>

                <div id="principal_section" class="flex flex-col gap-1 {{ request('filter_type') == 'principal' ? '' : 'hidden' }}">
                    <label class="text-xs font-bold text-gray-500 uppercase">Select Principal</label>
                    <select name="principal_id" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-4 py-2 bg-white h-10">
                        <option value="">-- Choose Principal --</option>
                        @foreach(\App\Models\Principal::all() as $p)
                            <option value="{{ $p->id }}" {{ request('principal_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="purpose_section" class="flex flex-col gap-1 {{ request('filter_type') == 'purpose' ? '' : 'hidden' }}">
                    <label class="text-xs font-bold text-gray-500 uppercase">Select Purpose</label>
                    <select name="purpose_id" onchange="this.form.submit()" class="border border-gray-300 rounded-lg px-4 py-2 bg-white h-10">
                        <option value="">-- Choose Purpose --</option>
                        @foreach(\App\Models\Purpose::all() as $purpose)
                            <option value="{{ $purpose->id }}" {{ request('purpose_id') == $purpose->id ? 'selected' : '' }}>{{ $purpose->purpose_name }}</option>
                        @endforeach
                    </select>
                </div>

                <a href="{{ url()->current() }}" class="text-sm text-blue-600 hover:underline mb-2 px-2">Reset All</a>
            </form>

            <script>
            function toggleFilters() {
                const type = document.getElementById('filter_type').value;
                const principalSec = document.getElementById('principal_section');
                const purposeSec = document.getElementById('purpose_section');

                if (type === 'all') {
                    window.location.href = "{{ url()->current() }}"; // Reset page if 'All' is selected
                } else if (type === 'principal') {
                    principalSec.classList.remove('hidden');
                    purposeSec.classList.add('hidden');
                } else if (type === 'purpose') {
                    purposeSec.classList.remove('hidden');
                    principalSec.classList.add('hidden');
                }
            }
            </script>
        </div>

        <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600">Appoint ID</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600">Name</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600">Principal</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600">Purpose</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600">Date & Time</th>
                        <th class="px-6 py-4 text-sm font-semibold text-gray-600 text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($appointments as $appointment)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 font-mono text-sm text-blue-600 font-medium">
                                #{{ $appointment->appoint_id }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-800">
                                {{ $appointment->applicant->first_name }} {{ $appointment->applicant->last_name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $appointment->principal->name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $appointment->purpose->purpose_name }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                {{ $appointment->appointment_date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $color = match($appointment->status) {
                                        'approved' => 'bg-green-100 text-green-700',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700'
                                    };
                                @endphp
                                <span class="px-3 py-1 rounded-full text-xs font-bold uppercase {{ $color }}">
                                    {{ $appointment->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 italic">
                                No appointments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $appointments->links() }}
        </div>
    </div>
</body>
</html>