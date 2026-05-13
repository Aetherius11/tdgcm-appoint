<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Booking Confirmed - TCM EasyQue</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-200 font-['Instrument_Sans']">

    <div class="fixed inset-0 flex items-center justify-center z-50 p-4" style="background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); position: fixed; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center;">
        
        <div class="bg-white rounded-xl shadow-2xl overflow-hidden w-full max-w-[420px]" style="background: white; border-radius: 12px; overflow: hidden; width: 100%; max-width: 420px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);">
            
            <div class="p-6 text-center" style="background-color: #28a745; color: white;">
                <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                    <div style="background: white; border-radius: 50%; width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                        <svg style="width: 28px; height: 28px; color: #28a745;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <h2 class="text-xl font-bold" style="margin: 0; font-size: 1.25rem;">Booking Confirmed! ✓</h2>
                <p class="text-sm opacity-90" style="margin-top: 4px; font-size: 0.875rem;">Your appointment has been successfully booked.</p>
            </div>

            <div class="p-6">
                <div class="rounded-lg p-4 mb-5 text-center" style="background-color: #f1f8f3; border: 1px solid #d4edda; border-radius: 8px;">
                    <p style="font-size: 12px; font-weight: bold; color: #6c757d; margin-bottom: 8px; text-transform: uppercase;">
                        Appointment ID: <span style="color: #1b1b18; font-family: monospace;">{{ $appointment->appoint_no }}</span>
                    </p>
                    
                    <div style="display: flex; justify-content: center; margin: 15px 0;">
                        <div style="background: white; padding: 8px; border: 1px solid #dee2e6; border-radius: 4px;">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ $appointment->appoint_no }}&color=1b1b18&bgcolor=ffffff" 
                                 alt="QR" style="width: 130px; height: 130px;">
                        </div>
                    </div>
                    <p style="font-size: 11px; color: #6c757d; margin: 0;">Please save this ID and QR code for your records.</p>
                </div>

                <div style="background-color: #e3f2fd; border: 1px solid #bbdefb; border-radius: 6px; padding: 10px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <svg style="width: 16px; height: 16px; color: #0d47a1;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    <p style="font-size: 11px; color: #0d47a1; margin: 0; font-weight: 500;">A confirmation email has been sent to your address.</p>
                </div>

                <div style="margin-bottom: 24px;">
                    <h3 style="font-size: 14px; font-weight: bold; margin-bottom: 10px; color: #343a40;">Booking Details:</h3>
                    <div style="font-size: 13px; line-height: 1.6;">
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f8f9fa; padding-bottom: 4px;">
                            <span style="color: #6c757d;">Name:</span>
                            <span style="font-weight: 600;">{{ $appointment->applicant->first_name }} {{ $appointment->applicant->last_name }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f8f9fa; padding-bottom: 4px; padding-top: 4px;">
                            <span style="color: #6c757d;">Department:</span>
                            <span style="font-weight: 600;">{{ $appointment->principal->name ?? 'N/A' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; border-bottom: 1px solid #f8f9fa; padding-bottom: 4px; padding-top: 4px;">
                            <span style="color: #6c757d;">Purpose:</span>
                            <span style="font-weight: 600;">{{ $appointment->purpose->purpose_name ?? 'N/A' }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; padding-top: 4px;">
                            <span style="color: #6c757d;">Date & Time:</span>
                            <span style="font-weight: 600;">
                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, M j, Y') }} at 
                                {{ $appointment->timeSlot ? \Carbon\Carbon::parse($appointment->timeSlot->start_time)->format('h:i A') : 'N/A' }}
                            </span>
                        </div>
                    </div>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button onclick="window.print()" style="flex: 1; background: white; border: 1px solid #ced4da; color: #495057; font-weight: bold; padding: 10px; border-radius: 6px; font-size: 14px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <svg style="width: 16px; height: 16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2h6a2 2 0 002-2z"></path></svg>
                        Save PDF
                    
                </div>
            </div>
            
            <div style="padding: 12px; text-align: center; border-top: 1px solid #f1f1f1; background: #fafafa;">
                <p style="font-size: 10px; color: #adb5bd; margin: 0; font-weight: bold; letter-spacing: 0.5px;">FAST BOOKING WITH TCM EASYQUE</p>
            </div>
        </div>
    </div>

</body>
</html>