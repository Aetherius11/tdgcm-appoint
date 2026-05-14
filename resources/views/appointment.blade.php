<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TCM EasyQue - Appointment</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#F4F7FA] text-[#1b1b18] min-h-screen">

    <header class="w-full bg-[#111827] text-white shadow-lg">
        <div class="max-w-7xl mx-auto flex justify-between items-center py-6 px-6">
            <div class="flex items-center gap-2">
                <div class="bg-white p-1 rounded">
                    <span class="text-black font-bold text-xs">TCM</span>
                </div>
                <h1 class="text-lg font-bold tracking-tight uppercase">EasyQue</h1>
            </div>

            <nav class="hidden lg:flex gap-5 text-[11px] font-bold uppercase tracking-widest text-gray-300">

            </nav>

            <div class="flex items-center gap-4">
                 <button class="text-gray-400 hover:text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                 </button>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-6 py-12">
        <div class="grid lg:grid-cols-2 gap-16 items-start">

            <div class="space-y-8">
                <div class="text-center lg:text-left">
                    <h2 class="text-2xl font-extrabold text-gray-44j00 uppercase tracking-wide">
                        You're just one click away from securing your appointment.
                            Pick your entry type below and let’s get started!
                    </h2>
                </div>

                <div class="flex flex-col md:flex-row gap-8 items-center lg:items-start">
                    <div class="w-48 shrink-0">
                        <div class="border-4 border-indigo-900 rounded-full p-6 text-center text-[10px] font-bold text-indigo-900">
                            <p>DPO/DPS</p>
                            <p class="text-lg">REGISTERED</p>
                            <div class="my-2 border-t border-indigo-900"></div>
                            <p>DATA PRIVACY ACT 2012</p>
                        </div>
                    </div>

                    <div class="text-sm text-gray-600 leading-relaxed space-y-4">
                        <p>
                            Upon registration, the personal information you have provided will be collected and processed for documentation purposes in accordance with the <strong>Data Privacy Act of 2012 (R.A. 10173 or DPA)</strong> and TDGCM Privacy Policy.
                        </p>

                        <h3 class="font-bold text-gray-800">Important Reminders:</h3>
                        <ol class="list-decimal ml-4 space-y-3">
                            <li>Kindly ensure that all information entered in the form is accurate and complete.</li>
                            <li>Upon confirmation, you are expected to arrive at the office on your scheduled date and specified time.</li>
                            <li>Please bring a valid government-issued ID and any required documents to facilitate your appointment.</li>
                            <li>Walk-in appointments without prior registration may not be accommodated.</li>
                        </ol>
                        <p class="font-semibold italic">TCM EasyQue — Your Time, Our Priority.</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-8">
                    <form action="{{ route('appointment.store') }}" method="POST" class="space-y-5">
                  @csrf


                        <div class="p-6 bg-white rounded-lg border border-blue-500 space-y-4">
    <h3 class="text-xl font-bold text-gray-900">Privacy Consent</h3>

    <div class="flex gap-3 items-start">
        <input type="checkbox" name="consent" id="consent" class="mt-1 h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500" required>
        <label for="consent" class="text-sm font-semibold text-gray-700 leading-relaxed">
            I agree that the personal information I have provided will be collected and processed for documentation purposes in accordance with the Data Privacy Act of 2012 (R.A. 10173 or DPA) and TDGCM Privacy Policy.
        </label>
    </div>
</div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Department</label>
                            <select id="department-select" name="principal_id" class="w-full border-gray-200 border rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">Select Department</option>
                                @foreach($principals as $principal)
                                    <option value="{{ $principal->id }}">{{ $principal->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Purpose</label>
                            <select id="purpose-select" name="purpose_id" class="w-full border-gray-200 border rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none" disabled>
                                <option value="">Select Purpose</option>
                                </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Appointment Date</label>
                            <input type="text" name="appointment_date" id="appointment_date" placeholder="Select a date" class="w-full border-gray-200 border rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white cursor-pointer" readonly>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Time</label>
                            <select name="time_slot_id" id="time_slot_select" class="w-full border-gray-200 border rounded-lg p-3 text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                                <option value="">Select a time</option>
                                @foreach($timeSlots as $slot)
                                    <option value="{{ $slot->id }}" data-start="{{ $slot->start_time }}" data-end="{{ $slot->end_time }}">{{ $slot->start_time }} - {{ $slot->end_time }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-gray-400 mt-1" id="time_note"></p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">First Name</label>
                                <input type="text" name="first_name" placeholder="First Name" class="w-full border-gray-200 border rounded-lg p-3 text-sm outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Last Name</label>
                                <input type="text" name="last_name" placeholder="Last Name" class="w-full border-gray-200 border rounded-lg p-3 text-sm outline-none">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email</label>
                            <input type="email" name="email" placeholder="email@example.com" class="w-full border-gray-200 border rounded-lg p-3 text-sm outline-none">
                        </div>
                    </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Contact Number</label>
                            <input type="text" name="phone" placeholder="Contact Number" class="w-full border-gray-200 border rounded-lg p-3 text-sm outline-none">
                        </div>

                        <button type="submit" class="w-full bg-[#3b82f6] hover:bg-blue-700 text-white font-bold py-4 rounded-lg shadow-lg shadow-blue-200 transition-all active:scale-[0.98]">
                            Submit
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </main>

    <footer class="mt-auto py-8 text-center text-xs text-gray-400">
        © 2026 Appointment System. All rights reserved.
    </footer>




        <script>
    // Current Philippine time passed from controller
    const currentPhilippineTime = '{{ $currentTime }}';
    const currentDate = '{{ $currentDate }}';

    flatpickr("#appointment_date", {
        dateFormat: "Y-m-d",
        minDate: "today",
        disable: [
            function(date) {
                // 0 = Sunday, 5 = Friday, 6 = Saturday
                return (date.getDay() === 0 || date.getDay() === 5 || date.getDay() === 6);
            }
        ],
        locale: {
            "firstDayOfWeek": 0 // Monday start
        },
        onChange: function(selectedDates, dateStr, instance) {
            // Re-evaluate time slots when date changes
            evaluateTimeSlots(dateStr);
        }
    });

    // Function to compare time strings (HH:MM format)
    function timeToMinutes(timeStr) {
        const [hours, minutes] = timeStr.split(':').map(Number);
        return hours * 60 + minutes;
    }

    // Function to evaluate and disable/enable time slots based on current time and selected date
    function evaluateTimeSlots(selectedDate) {
        const timeSelect = document.getElementById('time_slot_select');
        const options = timeSelect.querySelectorAll('option');
        const noteElement = document.getElementById('time_note');

        // Parse selected date
        const selectedDateObj = new Date(selectedDate);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        selectedDateObj.setHours(0, 0, 0, 0);

        const isToday = selectedDateObj.getTime() === today.getTime();

        options.forEach(option => {
            if (!option.value) return; // Skip the placeholder option

            const endTime = option.getAttribute('data-end');
            const startTime = option.getAttribute('data-start');

            if (isToday && endTime) {
                const endMinutes = timeToMinutes(endTime);
                const currentMinutes = timeToMinutes(currentPhilippineTime);

                // Disable if the time slot has already ended
                if (endMinutes <= currentMinutes) {
                    option.disabled = true;
                    option.style.color = '#9ca3af';
                    option.style.fontStyle = 'italic';
                } else {
                    option.disabled = false;
                    option.style.color = '';
                    option.style.fontStyle = '';
                }
            } else {
                // For future dates, all slots are available
                option.disabled = false;
                option.style.color = '';
                option.style.fontStyle = '';
            }
        });

        // Update note
        if (isToday) {
            noteElement.textContent = 'Greyed out slots are no longer available for today.';
        } else if (selectedDate) {
            noteElement.textContent = 'All time slots are available for the selected date.';
        } else {
            noteElement.textContent = '';
        }

        // Reset selection if current selection is disabled
        if (timeSelect.value) {
            const selectedOption = timeSelect.options[timeSelect.selectedIndex];
            if (selectedOption.disabled) {
                timeSelect.value = '';
            }
        }
    }

    // Initialize time slots on page load
    document.addEventListener('DOMContentLoaded', function() {
        evaluateTimeSlots(currentDate);

        // Update time slots every minute to reflect current time changes
        setInterval(function() {
            evaluateTimeSlots(currentDate);
        }, 60000); // Check every minute
    });
</script>

<script>
document.getElementById('department-select').addEventListener('change', function() {
    const principalId = this.value;
    const purposeSelect = document.getElementById('purpose-select');

    // Reset and disable Purpose dropdown
    purposeSelect.innerHTML = '<option value="">Select Purpose</option>';
    purposeSelect.disabled = true;

    if (principalId) {
        // Fetch purposes linked to the selected department
        fetch(`/api/purposes/${principalId}`)
            .then(response => response.json())
            .then(data => {
                data.forEach(purpose => {
                    const option = document.createElement('option');
                    option.value = purpose.id;
                    option.textContent = purpose.purpose_name;
                    purposeSelect.appendChild(option);
                });
                purposeSelect.disabled = false;
            })
            .catch(error => console.error('Error fetching purposes:', error));
    }
});
</script>





</body>
</html>
