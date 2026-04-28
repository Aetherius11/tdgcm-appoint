<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Appointment Confirmed - TCM EasyQue</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FA] text-[#1b1b18] min-h-screen">

    <header class="w-full bg-[#111827] text-white shadow-lg">
        <div class="max-w-7xl mx-auto flex justify-between items-center py-3 px-6">
            <div class="flex items-center gap-2">
                <div class="bg-white p-1 rounded">
                    <span class="text-black font-bold text-xs">TCM</span>
                </div>
                <h1 class="text-lg font-bold tracking-tight uppercase">EasyQue</h1>
            </div>

            <nav class="hidden lg:flex gap-5 text-[11px] font-bold uppercase tracking-widest text-gray-300">
                <a href="#" class="hover:text-white transition">Home</a>
                <a href="#" class="hover:text-white transition">DCL</a>
                <a href="#" class="hover:text-white transition">NYKC</a>
                <a href="#" class="hover:text-white transition">WOW</a>
                <a href="#" class="text-white border-b-2 border-blue-500 pb-1">DG3</a>
                <a href="#" class="hover:text-white transition">Cruise Visa Processing</a>
                <a href="#" class="hover:text-white transition">Fleet B</a>
                <a href="#" class="hover:text-white transition">Recruitment</a>
                <a href="#" class="hover:text-white transition">Crew Welfare</a>
            </nav>
        </div>
    </header>

    <main class="max-w-2xl mx-auto px-6 py-12">
        <!-- Vertical Banner Style Layout -->
        <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
            
            <!-- Success Header -->
            <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-3">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h2 class="text-2xl font-extrabold text-gray-900 mb-1">Appointment Confirmed!</h2>
                <p class="text-sm text-gray-600">Your appointment has been successfully booked.</p>
            </div>

            <div class="p-6">
                <!-- QR Code Section - Now on Top as Vertical Banner -->
                <div class="flex flex-col items-center justify-center mb-8">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">Your QR Code</h3>
                    <div class="bg-white p-4 border-2 border-gray-200 rounded-xl shadow-lg mb-4">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($qrData) }}&color=1b1b18&bgcolor=ffffff&margin=10" 
                             alt="QR Code" 
                             width="180" 
                             height="180"
                             class="rounded"
                             onerror="this.src='https://chart.googleapis.com/chart?chs=180x180&cht=qr&chl={{ urlencode($qrData) }}&choe=UTF-8'">
                    </div>
                    <p class="text-sm text-gray-500 text-center max-w-xs">
                        Scan this QR code at the reception or show it to the staff for quick check-in.
                    </p>
                </div>

                <!-- Appointment ID Section -->
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6 text-center">
                    <p class="text-sm font-semibold text-blue-600 uppercase tracking-wide mb-2">Your Appointment ID</p>
                    <p class="text-3xl font-extrabold text-blue-900 font-mono">{{ $appointment->appoint_no }}</p>
                    <p class="text-xs text-blue-500 mt-1">Please save this number for your reference</p>
                </div>

                <!-- Appointment Details -->
                <div class="mb-6">
                    <h3 class="text-base font-bold text-gray-900 border-b pb-2 mb-3">Appointment Details</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Principal:</span>
                            <span class="font-semibold text-gray-900">{{ $appointment->principal->name ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Purpose:</span>
                            <span class="font-semibold text-gray-900">{{ $appointment->purpose->purpose_name ?? 'N/A' }}</span>
                        </div>
                        
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Date:</span>
                            <span class="font-semibold text-gray-900">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('F j, Y') }}</span>
                        </div>
                        
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Time:</span>
                            <span class="font-semibold text-gray-900">
                                {{ $appointment->timeSlot ? \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('h:i A') . ' - ' . \Carbon\Carbon::parse($appointment->timeSlot->end_time)->format('h:i A') : 'N/A' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between py-2 border-b border-gray-100">
                            <span class="text-gray-500">Status:</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                {{ ucfirst($appointment->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Applicant Details -->
                <div class="mb-6 pt-4 border-t border-gray-200">
                    <h3 class="text-base font-bold text-gray-900 mb-3">Applicant Information</h3>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-xs text-gray-500">Name</p>
                            <p class="font-semibold text-sm">{{ $appointment->applicant->first_name }} {{ $appointment->applicant->last_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500">Email</p>
                            <p class="font-semibold text-sm">{{ $appointment->applicant->email }}</p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-xs text-gray-500">Phone</p>
                            <p class="font-semibold text-sm">{{ $appointment->applicant->phone }}</p>
                        </div>
                    </div>
                </div>

                <!-- Important Reminders -->
                <div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <h3 class="text-sm font-bold text-amber-800 mb-2">Important Reminders</h3>
                    <ul class="space-y-1 text-xs text-amber-700">
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Please arrive at least 10 minutes before your scheduled time.
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Bring a valid government-issued ID and this QR code (digital or printed).
                        </li>
                        <li class="flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            Bring any required documents related to your appointment purpose.
                        </li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="window.print()" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow-lg transition-all active:scale-[0.98] flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h6a2 2 0 002-2z"></path>
                        </svg>
                        Print / Save as PDF
                    </button>
                    <a href="{{ route('appointment.create') }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-3 px-4 rounded-lg transition-all active:scale-[0.98] text-center flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        Book Another Appointment
                    </a>
                </div>
            </div>
        </div>
    </main>

    <footer class="mt-auto py-8 text-center text-xs text-gray-400">
        © 2026 TCM EasyQue Appointment System. All rights reserved.
    </footer>

    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            main, main * {
                visibility: visible;
            }
            main {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            header, footer {
                display: none;
            }
        }
    </style>
</body>
</html>