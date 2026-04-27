<div class="container mx-auto py-8">
    <h1 class="text-2xl font-bold mb-6">Appointment Status List</h1>
    <table class="min-w-full bg-white border">
        <thead>
            <tr>
                <th class="px-4 py-2 border">Appoint ID</th>
                <th class="px-4 py-2 border">Name</th>
                <th class="px-4 py-2 border">Principal</th>
                <th class="px-4 py-2 border">Purpose</th>
                <th class="px-4 py-2 border">Date & Time</th>
                <th class="px-4 py-2 border">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($appointments as $appointment)
            <tr>
                <td class="px-4 py-2 border">{{ $appointment->appoint_id }}</td>
                <td class="px-4 py-2 border">{{ $appointment->applicant->first_name }} {{ $appointment->applicant->last_name }}</td>
                <td class="px-4 py-2 border">{{ $appointment->principal->name }}</td>
                <td class="px-4 py-2 border">{{ $appointment->purpose->purpose_name }}</td>
                <td class="px-4 py-2 border">{{ $appointment->appointment_date }}</td>
                <td class="px-4 py-2 border">
                    <span class="px-2 py-1 rounded {{ $appointment->status === 'approved' ? 'bg-green-200' : 'bg-yellow-200' }}">
                        {{ strtoupper($appointment->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    {{ $appointments->links() }}
</div>