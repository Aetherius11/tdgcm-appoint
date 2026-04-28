<main class="max-w-md mx-auto px-4 py-10">
    <div class="bg-white rounded-3xl shadow-2xl overflow-hidden border border-gray-100">
        
        <div class="bg-[#22c55e] p-6 text-center text-white">
            <div class="inline-flex items-center justify-center w-12 h-12 bg-white/20 rounded-full mb-3">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-xl font-bold uppercase tracking-tight">Booking Confirmed!</h2>
            <p class="text-xs opacity-90">Your appointment has been successfully booked.</p>
        </div>

        <div class="p-6 bg-slate-50 text-center border-b border-dashed border-gray-300 relative">
            <div class="absolute -bottom-3 -left-3 w-6 h-6 bg-[#F4F7FA] rounded-full border border-gray-200"></div>
            <div class="absolute -bottom-3 -right-3 w-6 h-6 bg-[#F4F7FA] rounded-full border border-gray-200"></div>
            
            <p class="text-[10px] font-bold text-gray-400 uppercase mb-2">Appointment ID</p>
            <p class="text-sm font-bold text-gray-900 mb-4">{{ $appointment->appoint_no }}</p>
            
            <div class="inline-block p-3 bg-white rounded-xl shadow-md border border-gray-100">
                <img src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{ $appointment->appoint_no }}&choe=UTF-8" 
                     alt="QR Code" width="200" height="200" class="mx-auto">
            </div>
            <p class="mt-4 text-[11px] text-gray-500 italic">Please save this QR code for your records.</p>
        </div>

        <div class="p-8">
            <div class="bg-blue-50 border border-blue-100 rounded-lg p-3 text-center mb-6">
                <p class="text-[11px] text-blue-600 font-medium">A confirmation email has been sent to your address.</p>
            </div>

            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Booking Details</h3>
            <div class="space-y-4 text-sm">
                <div class="flex flex-col">
                    <span class="text-gray-400 text-[10px] uppercase font-bold">Customer</span>
                    <span class="font-semibold text-gray-900">{{ $appointment->applicant->first_name }} {{ $appointment->applicant->last_name }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-gray-400 text-[10px] uppercase font-bold">Principal</span>
                    <span class="font-semibold text-gray-900">{{ $appointment->principal->name ?? 'N/A' }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-gray-400 text-[10px] uppercase font-bold">Service</span>
                    <span class="font-semibold text-gray-900">{{ $appointment->purpose->purpose_name ?? 'N/A' }}</span>
                </div>
                <div class="flex flex-col">
                    <span class="text-gray-400 text-[10px] uppercase font-bold">Date & Time</span>
                    <span class="font-semibold text-gray-900">
                        {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, F j, Y') }} 
                        at {{ $appointment->timeSlot ? \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('h:i A') : 'N/A' }}
                    </span>
                </div>
            </div>

            <div class="mt-8">
                <button onclick="window.print()" class="w-full bg-[#1e40af] text-white font-bold py-3 rounded-lg hover:bg-blue-800 transition no-print">
                    Close
                </button>
            </div>
        </div>
    </div>
</main>