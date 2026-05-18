<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Window Staff - TDG Crew Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .tcm-navy { background-color: #003366; }
        .bg-main-frame { background-color: #f8f9fa; border: 1px solid #dee2e6; }
        .bg-light-blue-zone { background-color: #e3f2fd; }
        .btn-gray { background-color: #e9ecef; border: 1px solid #adb5bd; }
        .btn-blue { background-color: #0056b3; color: white; border: 1px solid #0044cc; }
        .ticket-box { background-color: #d1ecf1; border: 1px solid #bee5eb; }
    </style>
</head>
<body class="bg-white font-sans text-gray-900 min-h-screen flex flex-col">

    <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center shadow-lg">
        <div class="flex items-center space-x-6">
            <div class="flex items-center space-x-2">
                @php $companyLogo = \App\Models\Company::where('is_active', true)->value('branding_logo'); @endphp
                @if($companyLogo)
                <div class="bg-white p-1 rounded-md">
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($companyLogo) }}" alt="Logo" class="h-8 w-8 object-contain">
                </div>
                @endif
                <span class="font-bold text-xl">TDG Crew Management</span>
            </div>
            <a href="/" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white font-bold transition text-lg">Home</a>
            <a href="{{ route('Window.Staff') }}" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white font-bold transition text-lg">Counter</a>
            <a href="{{ route('window.transaction-history') }}" target="_blank" rel="noopener noreferrer" class="text-gray-300 hover:text-white font-bold transition text-lg">Transaction History</a>
        </div>
        <div class="flex items-center space-x-4">
            <!-- Window Number Dropdown -->
            <form action="{{ route('window.change-window') }}" method="POST" class="flex items-center space-x-2">
                @csrf
                <label class="text-gray-300 font-bold">Window:</label>
                <select name="window_number" class="px-3 py-2 rounded-lg text-gray-800 font-bold" onchange="this.form.submit()">
                    @foreach($availableWindows as $win)
                        <option value="{{ $win->id }}" {{ $windowNumber == $win->id ? 'selected' : '' }}>
                            Window {{ $win->name }}
                        </option>
                    @endforeach
                </select>
            </form>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-gray text-gray-800 px-6 py-2 rounded-lg font-bold hover:bg-gray-300 transition">Sign Out</button>
            </form>
        </div>
    </div>

    <main class="flex-grow flex flex-col p-4">
        <div class="bg-main-frame rounded-lg p-6 shadow-sm flex flex-col flex-grow">

            <div class="text-center text-lg font-bold border-b border-gray-300 pb-3 mb-6">
                Hi, {{ auth()->user()->name ?? 'Staff' }}!
            </div>

            <!-- Action Buttons -->
            <div class="grid grid-cols-6 gap-6 mb-10 px-4">
                <!-- Notify Button - enabled when there's a checked-in appointment in queue AND no current appointment being served -->
                @php
                    $windowStatus = session('window_status', 'closed');
                    $firstInQueue = isset($nextQueue) && $nextQueue->isNotEmpty() ? $nextQueue->whereNotIn('status', ['on_hold'])->first() : null;
                    $isCalling = isset($currentAppointment) && $currentAppointment && $currentAppointment->status === 'calling';
                    $isServing = isset($currentAppointment) && $currentAppointment && $currentAppointment->status === 'serving';
                    // Re-call current person if still in 'calling', or call next if no active appointment
                    $notifyId   = $isCalling ? $currentAppointment->id : $firstInQueue?->id;
                    $canNotify  = $notifyId && !$isServing && $windowStatus === 'open';
                @endphp
                @if($canNotify)
                    <form action="{{ route('window.notify') }}" method="POST">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $notifyId }}">
                        <button type="submit" class="btn-blue h-40 w-full rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">Notify</button>
                    </form>
                @else
                    <button disabled class="bg-gray-300 text-gray-500 h-40 w-full rounded-xl text-3xl font-bold cursor-not-allowed">Notify</button>
                @endif

                <!-- Start Service Button - enabled when current appointment is in 'calling' status -->
                @if(isset($currentAppointment) && $currentAppointment && $currentAppointment->status === 'calling')
                    <form action="{{ route('window.start') }}" method="POST">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $currentAppointment->id }}">
                        <button type="submit" class="btn-blue h-40 w-full rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">Start Service</button>
                    </form>
                @else
                    <button disabled class="bg-gray-300 text-gray-500 h-40 w-full rounded-xl text-3xl font-bold cursor-not-allowed">Start Service</button>
                @endif

                <!-- Hold Ticket Button - enabled when current appointment is being served -->
                @if(isset($currentAppointment) && $currentAppointment && $currentAppointment->status === 'serving')
                    <form action="{{ route('window.hold') }}" method="POST">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $currentAppointment->id }}">
                        <button type="submit" class="btn-blue h-40 w-full rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">Hold Ticket</button>
                    </form>
                @else
                    <button disabled class="bg-gray-300 text-gray-500 h-40 w-full rounded-xl text-3xl font-bold cursor-not-allowed">Hold Ticket</button>
                @endif

                <!-- Resume Button - enabled when current appointment is on hold -->
                @if(isset($currentAppointment) && $currentAppointment && $currentAppointment->status === 'on_hold')
                    <form action="{{ route('window.resume') }}" method="POST">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $currentAppointment->id }}">
                        <button type="submit" class="btn-blue h-40 w-full rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">Resume</button>
                    </form>
                @else
                    <button disabled class="bg-gray-300 text-gray-500 h-40 w-full rounded-xl text-3xl font-bold cursor-not-allowed">Resume</button>
                @endif

                <!-- Transfer Button - enabled when there's a current appointment being served or on hold -->
                @if(isset($currentAppointment) && $currentAppointment && in_array($currentAppointment->status, ['serving', 'on_hold']))
                    <button type="button" onclick="openTransferModal()" class="btn-blue h-40 w-full rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">Transfer</button>
                    
                    <!-- Transfer Modal -->
                    <div id="transferModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                        <div class="bg-white rounded-xl shadow-2xl p-8 max-w-md w-full mx-4">
                            <h3 class="text-2xl font-bold text-center mb-6 text-gray-800">Transfer to Window</h3>
                            <form action="{{ route('window.transfer') }}" method="POST">
                                @csrf
                                <input type="hidden" name="appointment_id" value="{{ $currentAppointment->id }}">
                                <select name="window_number" class="w-full px-4 py-4 rounded-lg text-gray-800 font-bold text-2xl text-center mb-6 border-2 border-gray-300 focus:border-blue-500 focus:outline-none" required>
                                    <option value="" disabled selected>Select Window</option>
                                    @foreach($availableWindows as $win)
                                        @if($win->id != $windowNumber)
                                            <option value="{{ $win->id }}">Window {{ $win->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                <div class="flex gap-4">
                                    <button type="submit" class="btn-blue flex-1 py-4 rounded-xl text-xl font-bold hover:bg-blue-800 shadow-md">Confirm Transfer</button>
                                    <button type="button" onclick="closeTransferModal()" class="bg-gray-300 text-gray-700 flex-1 py-4 rounded-xl text-xl font-bold hover:bg-gray-400 shadow-md">Cancel</button>
                                </div>
                            </form>
                        </div>
                    </div>
                @else
                    <button disabled class="bg-gray-300 text-gray-500 h-40 w-full rounded-xl text-3xl font-bold cursor-not-allowed">Transfer</button>
                @endif

                <!-- End Service Button - enabled when there's a current appointment being served or on hold -->
                @if(isset($currentAppointment) && $currentAppointment && in_array($currentAppointment->status, ['serving', 'on_hold']))
                    <form action="{{ route('window.end') }}" method="POST">
                        @csrf
                        <input type="hidden" name="appointment_id" value="{{ $currentAppointment->id }}">
                        <button type="submit" class="btn-blue h-40 w-full rounded-xl text-3xl font-bold hover:bg-blue-800 shadow-md">End</button>
                    </form>
                @else
                    <button disabled class="bg-gray-300 text-gray-500 h-40 w-full rounded-xl text-3xl font-bold cursor-not-allowed">End</button>
                @endif
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="grid grid-cols-2 gap-10 px-4 flex-grow">

                <!-- Current Window Display -->
                <div class="bg-light-blue-zone p-6 rounded-xl shadow-inner flex flex-col">
                    <div class="tcm-navy text-white text-center py-8 text-4xl font-bold mb-6 rounded-t-lg">
                        Window {{ $windowName }}
                    </div>

                    @if(isset($currentAppointment) && $currentAppointment)
                        <div class="ticket-box rounded-full py-4 px-12 flex justify-between items-center mb-8 shadow-sm">
                            <span class="text-5xl font-bold text-black">{{ str_pad(substr($currentAppointment->appoint_no, -4), 4, '0', STR_PAD_LEFT) }}</span>
                            <span class="text-4xl font-bold uppercase text-black">{{ $currentAppointment->applicant->first_name }} {{ $currentAppointment->applicant->last_name }}</span>
                        </div>

                        <div class="bg-gray-200 border border-gray-400 rounded-lg p-8 space-y-6 flex-grow">
                            <p class="text-center text-xl font-semibold">Appointment ID: <span class="font-bold">{{ $currentAppointment->appoint_no }}</span></p>
                            <p class="text-center text-xl font-semibold uppercase text-black">Status: <span class="text-blue-700 font-bold">{{ strtoupper($currentAppointment->status ?? 'Pending') }}</span></p>
                            <p class="text-center text-5xl font-bold pt-6 border-t border-gray-400 text-black">
                                {{ $currentAppointment->applicant->first_name }} {{ $currentAppointment->applicant->last_name }}
                            </p>
                            @if($currentAppointment->timeSlot)
                                <p class="text-center text-2xl font-semibold">Time: {{ \Carbon\Carbon::parse($currentAppointment->timeSlot->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($currentAppointment->timeSlot->end_time)->format('g:i A') }}</p>
                            @endif
                            <!-- Transaction Timestamps -->
                            <div class="border-t border-gray-400 pt-4 space-y-2">
                                @if($currentAppointment->called_at)
                                    <p class="text-center text-lg text-gray-600">
                                        <span class="font-semibold">Called at:</span> {{ $currentAppointment->called_at->format('g:i A') }}
                                    </p>
                                @endif
                                @if($currentAppointment->served_at)
                                    <p class="text-center text-lg text-gray-600">
                                        <span class="font-semibold">Service started:</span> {{ $currentAppointment->served_at->format('g:i A') }}
                                    </p>
                                @endif
                                @if($currentAppointment->transaction_completed_at)
                                    <p class="text-center text-lg text-green-700 font-semibold">
                                        <span class="font-semibold">Transaction completed:</span> {{ $currentAppointment->transaction_completed_at->format('g:i A') }}
                                    </p>
                                @endif
                            </div>
                            <!-- Live Timer for Ongoing Transaction -->
                            @if($currentAppointment->served_at && !$currentAppointment->transaction_completed_at)
                                <div class="border-t border-gray-400 pt-4 mt-4">
                                    <p class="text-center text-lg text-blue-700 font-semibold">
                                        <span class="font-semibold">Service Duration:</span> 
                                        <span id="live-timer" class="text-2xl font-mono text-blue-800" data-start="{{ $currentAppointment->served_at->toIso8601String() }}">00:00:00</span>
                                    </p>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="flex-grow flex items-center justify-center text-gray-400 text-5xl font-bold italic">No Active Applicant</div>
                    @endif
                </div>

                <!-- Next Queue Display -->
                <div class="bg-light-blue-zone p-6 rounded-xl shadow-inner flex flex-col">
                    <div class="tcm-navy text-white text-center py-8 text-4xl font-bold mb-6 rounded-t-lg">
                        Next Queue
                    </div>
                    <div class="space-y-4 flex-grow" id="next-queue-list">
                        @if(isset($nextQueue) && $nextQueue->isNotEmpty())
                            @foreach($nextQueue as $next)
                                <div class="ticket-box rounded-md py-4 px-6 flex justify-between items-center shadow-sm {{ $next->status === 'on_hold' ? 'border-2 border-orange-400 bg-orange-50' : '' }}">
                                    <div class="flex items-center gap-4">
                                        <span class="text-4xl font-bold text-black">{{ str_pad(substr($next->appoint_no, -4), 4, '0', STR_PAD_LEFT) }}</span>
                                        <span class="text-3xl font-bold uppercase text-black">{{ substr($next->applicant->first_name, 0, 1) }}. {{ $next->applicant->last_name }}</span>
                                        @if($next->status === 'on_hold')
                                            <span class="text-xs bg-orange-500 text-white px-2 py-1 rounded font-bold">Held</span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="text-lg text-gray-600">
                                            Checked in: {{ $next->checked_in_at ? $next->checked_in_at->format('g:i A') : 'N/A' }}
                                        </span>
                                        @if($next->status === 'on_hold')
                                            <form action="{{ route('window.resume') }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="appointment_id" value="{{ $next->id }}">
                                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm font-bold px-3 py-2 rounded transition">
                                                    Resume
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-10 text-gray-500 text-2xl">No checked-in applicants in queue</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Today's Transaction Summary -->
            <div class="mt-6 px-4">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800">Today's Transactions</h3>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full font-bold text-sm">
                            Total: {{ $todayTotal ?? 0 }}
                        </span>
                    </div>
                    @if(isset($todayCompleted) && $todayCompleted->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="text-left py-2 px-3 font-semibold text-gray-600">Ticket</th>
                                        <th class="text-left py-2 px-3 font-semibold text-gray-600">Applicant</th>
                                        <th class="text-center py-2 px-3 font-semibold text-gray-600">Served At</th>
                                        <th class="text-center py-2 px-3 font-semibold text-gray-600">Completed At</th>
                                        <th class="text-center py-2 px-3 font-semibold text-gray-600">Duration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($todayCompleted as $completed)
                                        <tr class="border-b border-gray-100 hover:bg-blue-50/30 transition">
                                            <td class="py-2 px-3 font-semibold text-blue-600">{{ $completed->appoint_no }}</td>
                                            <td class="py-2 px-3 text-gray-800">
                                                {{ $completed->applicant->first_name }} {{ $completed->applicant->last_name }}
                                            </td>
                                            <td class="py-2 px-3 text-center text-gray-600">
                                                {{ $completed->served_at ? $completed->served_at->format('g:i A') : '--' }}
                                            </td>
                                            <td class="py-2 px-3 text-center text-gray-600">
                                                {{ $completed->transaction_completed_at ? $completed->transaction_completed_at->format('g:i A') : '--' }}
                                            </td>
                                            <td class="py-2 px-3 text-center">
                                                @if($completed->served_at && $completed->transaction_completed_at)
                                                    @php
                                                        $duration = $completed->served_at->diffInSeconds($completed->transaction_completed_at);
                                                    @endphp
                                                    <span class="text-green-700 font-medium">{{ gmdate('i:s', $duration) }}</span>
                                                @else
                                                    <span class="text-gray-400">--</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($todayTotal > 5)
                            <div class="text-center mt-3">
                                <a href="{{ route('window.transaction-history', ['filter_date' => now()->toDateString()]) }}" target="_blank" rel="noopener noreferrer"
                                   class="text-blue-600 hover:text-blue-800 text-sm font-medium hover:underline">
                                    View all {{ $todayTotal }} transactions →
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-6 text-gray-500">
                            No transactions completed today yet.
                        </div>
                    @endif
                </div>
            </div>

            <div class="flex justify-center space-x-6 mt-10 pb-4">
                @php
                    $windowStatus = session('window_status', 'closed');
                    $windowOpenedAt = session('window_opened_at');
                    $windowClosedAt = session('window_closed_at');
                    $windowBreakAt = session('window_break_at');
                @endphp

                @if($windowStatus === 'open')
                    <form action="{{ route('window.close-counter') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-12 py-4 rounded-xl font-bold text-xl shadow-md hover:bg-red-700 transition">
                            Close Counter
                        </button>
                    </form>
                    <button disabled class="bg-gray-300 text-gray-500 px-12 py-4 rounded-xl font-bold text-xl cursor-not-allowed">
                        Open Counter (Active)
                    </button>
                    <form action="{{ route('window.break') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-yellow-500 text-white px-12 py-4 rounded-xl font-bold text-xl shadow-md hover:bg-yellow-600 transition">
                            Start Break
                        </button>
                    </form>
                @elseif($windowStatus === 'break')
                    <form action="{{ route('window.open-counter') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-12 py-4 rounded-xl font-bold text-xl shadow-md hover:bg-green-700 transition">
                            Resume from Break
                        </button>
                    </form>
                    <button disabled class="bg-gray-300 text-gray-500 px-12 py-4 rounded-xl font-bold text-xl cursor-not-allowed">
                        Break (Active)
                    </button>
                    <form action="{{ route('window.close-counter') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-red-600 text-white px-12 py-4 rounded-xl font-bold text-xl shadow-md hover:bg-red-700 transition">
                            Close Counter
                        </button>
                    </form>
                @else
                    <form action="{{ route('window.open-counter') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="bg-green-600 text-white px-12 py-4 rounded-xl font-bold text-xl shadow-md hover:bg-green-700 transition">
                            Open Counter
                        </button>
                    </form>
                    <button disabled class="bg-gray-300 text-gray-500 px-12 py-4 rounded-xl font-bold text-xl cursor-not-allowed">
                        Close Counter (Inactive)
                    </button>
                    <button disabled class="bg-gray-300 text-gray-500 px-12 py-4 rounded-xl font-bold text-xl cursor-not-allowed">
                        Break (Counter Closed)
                    </button>
                @endif
            </div>

            <!-- Window Status Display -->
            <div class="text-center mt-4 pb-2">
                @if($windowStatus === 'open')
                    <span class="bg-green-100 text-green-800 px-6 py-2 rounded-full font-bold text-lg">
                        Counter Status: OPEN
                        @if($windowOpenedAt)
                            | Since {{ $windowOpenedAt->format('g:i A') }}
                        @endif
                    </span>
                @elseif($windowStatus === 'break')
                    <span class="bg-yellow-100 text-yellow-800 px-6 py-2 rounded-full font-bold text-lg">
                        Counter Status: ON BREAK
                        @if($windowBreakAt)
                            | Since {{ $windowBreakAt->format('g:i A') }}
                        @endif
                    </span>
                @else
                    <span class="bg-red-100 text-red-800 px-6 py-2 rounded-full font-bold text-lg">
                        Counter Status: CLOSED
                        @if($windowClosedAt)
                            | Since {{ $windowClosedAt->format('g:i A') }}
                        @endif
                    </span>
                @endif
            </div>
        </div>
    </main>

    <script>
        function openTransferModal() {
            document.getElementById('transferModal').classList.remove('hidden');
        }

        function closeTransferModal() {
            document.getElementById('transferModal').classList.add('hidden');
        }

        // Close modal when clicking outside the modal content
        const transferModal = document.getElementById('transferModal');
        if (transferModal) {
            transferModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeTransferModal();
                }
            });
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeTransferModal();
            }
        });

        // Live Timer for ongoing transaction
        function updateLiveTimer() {
            const timerElement = document.getElementById('live-timer');
            if (!timerElement) return;

            const startTime = new Date(timerElement.getAttribute('data-start'));
            const now = new Date();
            const diff = Math.floor((now - startTime) / 1000);

            const hours = Math.floor(diff / 3600);
            const minutes = Math.floor((diff % 3600) / 60);
            const seconds = diff % 60;

            timerElement.textContent = 
                String(hours).padStart(2, '0') + ':' + 
                String(minutes).padStart(2, '0') + ':' + 
                String(seconds).padStart(2, '0');
        }

        // Update timer every second
        setInterval(updateLiveTimer, 1000);
        updateLiveTimer(); // Initial call

        // --- Queue-state polling ---
        // Polls every 8 s. If the queue visible to this window changes (new
        // transfer, ticket called, etc.) the page reloads so staff sees the
        // updated Next Queue and Notify button without manual refresh.
        let lastQueueState = null;

        async function pollQueueState() {
            try {
                const res = await fetch('{{ route("window.queue-state") }}', {
                    credentials: 'same-origin',
                    headers: { 'Accept': 'application/json' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const state = JSON.stringify(data.state);
                if (lastQueueState !== null && lastQueueState !== state) {
                    location.reload();
                }
                lastQueueState = state;
            } catch (e) {}
        }

        // Initial snapshot after a short delay, then every 8 s
        setTimeout(pollQueueState, 1500);
        setInterval(pollQueueState, 8000);
    </script>
</body>
</html>
