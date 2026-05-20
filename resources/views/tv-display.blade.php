<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display - TDG Crew Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .tcm-navy  { background-color: #003366; }
        .bg-sky    { background-color: #e3f2fd; }
        .text-navy { color: #003366; }

        .counter-grid {
            display: grid;
            grid-template-columns: 120px 170px 1fr 190px;
            height: 100%;
        }
        .c-hdr {
            display: flex; align-items: center; justify-content: center;
            background: #f3f4f6; border-bottom: 2px solid #d1d5db;
            padding: 10px 6px;
            font-size: 1.3rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 0.05em; color: #4b5563;
        }
        .c-cell {
            display: flex; align-items: center; justify-content: center;
            background: #e3f2fd; border-bottom: 1px solid #dbeafe;
            padding: 6px 8px; overflow: hidden;
        }
        .c-cell-name {
            display: flex; align-items: center; justify-content: center;
            background: #e3f2fd; border-bottom: 1px solid #dbeafe;
            padding: 6px 10px; overflow: hidden;
            font-weight: 700; font-size: 1.35rem; color: #1f2937;
            text-align: center;
        }
    </style>
</head>
<body class="bg-gray-100 h-screen overflow-hidden flex flex-col p-3 gap-3 font-sans">

    <!-- Small sound fallback button — only visible if browser blocks autoplay -->
    <div id="sound-btn" onclick="unlockAudio()" style="position:fixed;bottom:14px;right:14px;background:rgba(0,0,0,0.55);color:#fff;padding:7px 13px;border-radius:8px;cursor:pointer;z-index:9999;font-size:0.85rem;display:none;">
        🔇 Tap to enable sound
    </div>

    <!-- Header -->
    <header class="bg-white border border-gray-200 rounded-lg shadow-sm px-5 py-3 flex items-center flex-shrink-0">
        <div class="flex items-center gap-3">
            @php $companyLogo = \App\Models\Company::where('is_active', true)->value('branding_logo'); @endphp
            @if($companyLogo)
            <div class="bg-white border border-gray-200 rounded-md p-1 flex-shrink-0">
                <img src="{{ \Illuminate\Support\Facades\Storage::url($companyLogo) }}" alt="Logo" class="h-9 w-9 object-contain">
            </div>
            @else
            <div class="w-9 h-9 bg-[#003366] rounded-full flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="20" cy="20" r="16" stroke="currentColor" stroke-width="2"/>
                    <path d="M10 20 L18 12 L22 28 L30 20" stroke="currentColor" stroke-width="2" fill="none"/>
                </svg>
            </div>
            @endif
            <span class="text-xl font-bold text-navy">TDG Crew Management</span>
        </div>
        <div id="tv-datetime" class="ml-auto text-gray-500 text-base font-medium">
            {{ now()->format('l, F j, Y — g:i A') }}
        </div>
    </header>

    <!-- Main Grid -->
    <div class="flex-1 grid grid-cols-12 gap-4 min-h-0">

        <!-- LEFT: Counter Status -->
        <div class="col-span-7 bg-white border border-gray-200 rounded-lg shadow-sm flex flex-col overflow-hidden">
            <div class="tcm-navy text-white px-5 py-3 flex-shrink-0">
                <h2 class="text-lg font-bold tracking-wide">Counter Status</h2>
            </div>
            <div class="flex-1 overflow-hidden">
                <div id="counter-grid" class="counter-grid">
                    <div class="c-hdr">Counter</div>
                    <div class="c-hdr">Appt No.</div>
                    <div class="c-hdr">Name</div>
                    <div class="c-hdr">Status</div>
                    <!-- rows injected by JS -->
                </div>
            </div>
        </div>

        <!-- RIGHT: Now Serving + Up Next -->
        <div class="col-span-5 flex flex-col gap-4 min-h-0">

            <!-- Now Serving -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden flex-shrink-0">
                <div class="tcm-navy text-white px-5 py-3">
                    <h2 class="text-lg font-bold tracking-wide">Now Serving</h2>
                </div>
                <div class="flex flex-col divide-y divide-gray-200">

                    <div>
                        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Appointment No.</span>
                        </div>
                        <div id="ns-apptno" class="bg-sky py-5 text-center text-8xl font-black leading-none text-navy">
                            @if($currentCall)
                                {{ str_pad(substr($currentCall->appoint_no, -4), 4, '0', STR_PAD_LEFT) }}
                            @else
                                <span class="text-gray-300">----</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Name</span>
                        </div>
                        <div id="ns-name" class="bg-sky py-5 flex items-center justify-center min-h-[120px]">
                            @if($currentCall)
                                <div class="text-8xl font-black leading-tight text-navy text-center w-full px-4">{{ substr($currentCall->applicant->first_name, 0, 1) }}. {{ $currentCall->applicant->last_name }}</div>
                            @else
                                <span class="text-gray-300 text-8xl font-black">———</span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <div class="bg-gray-100 px-4 py-2 border-b border-gray-200">
                            <span class="text-xs font-bold text-gray-500 uppercase tracking-wide">Please Proceed To</span>
                        </div>
                        <div id="ns-counter" class="bg-sky py-5 text-center text-8xl font-black leading-none text-navy">
                            @if($currentCall)
                                Counter {{ $currentCall->counter_number }}
                            @else
                                <span class="text-gray-300">———</span>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            <!-- Up Next -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm flex flex-col flex-1 min-h-0 overflow-hidden">
                <div class="tcm-navy text-white px-5 py-3 flex-shrink-0">
                    <h2 class="text-lg font-bold tracking-wide">Up Next</h2>
                </div>
                <div id="up-next" class="flex-1 overflow-y-auto p-3">
                    @if($upNext?->isNotEmpty())
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($upNext as $next)
                                @php
                                    $isTransferred = in_array($next->status, ['pending','approved']) && $next->window_number;
                                    $rowBg = $next->status === 'on_hold'
                                           ? 'bg-orange-50 border-orange-300'
                                           : ($isTransferred ? 'bg-purple-50 border-purple-300' : 'bg-sky border-gray-200');
                                @endphp
                                <div class="border rounded-lg p-3 flex flex-col gap-1 {{ $rowBg }}">
                                    <div class="flex items-center gap-2">
                                        <div class="bg-[#003366] text-white rounded px-2 py-1 font-bold text-base flex-shrink-0">
                                            {{ str_pad(substr($next->appoint_no, -4), 4, '0', STR_PAD_LEFT) }}
                                        </div>
                                        @if($next->status === 'on_hold')
                                            <span class="bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">On Hold</span>
                                        @elseif($isTransferred)
                                            <span class="bg-purple-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">→ {{ $counters[$next->window_number]['counter'] ?? ('Ctr ' . $next->window_number) }}</span>
                                        @endif
                                    </div>
                                    <div class="font-bold text-base text-navy truncate">
                                        {{ substr($next->applicant->first_name, 0, 1) }}. {{ $next->applicant->last_name }}
                                    </div>
                                    @if($next->checked_in_at)
                                        <div class="text-xs text-gray-500">{{ $next->checked_in_at->format('g:i A') }}</div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="h-full flex items-center justify-center text-gray-400 text-lg font-semibold">
                            No upcoming appointments
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>

    <script>
    // --- Helpers ---
    function esc(str) {
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function statusBadge(status) {
        if (status === 'Calling')
            return `<span class="bg-blue-100 text-blue-700 px-4 py-1.5 rounded-full font-bold text-xl animate-pulse">Calling</span>`;
        if (status === 'Serving')
            return `<span class="bg-green-100 text-green-700 px-4 py-1.5 rounded-full font-bold text-xl">Serving</span>`;
        if (status === 'On Break')
            return `<span class="bg-yellow-100 text-yellow-700 px-4 py-1.5 rounded-full font-bold text-xl">On Break</span>`;
        if (status === 'Closed')
            return `<span class="text-gray-400 font-bold text-xl">—</span>`;
        return `<span class="bg-gray-100 text-gray-500 px-4 py-1.5 rounded-full font-bold text-xl">Vacant</span>`;
    }

    // --- Counter grid ---
    function updateCounters(counters) {
        const grid = document.getElementById('counter-grid');
        while (grid.children.length > 4) grid.removeChild(grid.lastChild);
        grid.style.gridTemplateRows = `auto repeat(${counters.length || 1}, 1fr)`;

        counters.forEach(c => {
            const name = c.name ? c.name.slice(0, 22) : null;

            const ctrCell = document.createElement('div');
            ctrCell.className = 'c-cell';
            ctrCell.innerHTML = `<div class="w-14 h-11 bg-[#003366] text-white rounded flex items-center justify-center font-bold text-2xl">${esc(c.counter)}</div>`;

            const apptCell = document.createElement('div');
            apptCell.className = 'c-cell font-black text-4xl text-[#003366] tracking-widest';
            apptCell.innerHTML = c.appoint_no ? esc(c.appoint_no) : '<span class="text-gray-300">----</span>';

            const nameCell = document.createElement('div');
            nameCell.className = 'c-cell-name';
            nameCell.innerHTML = name ? esc(name) : '<span class="text-gray-300">—</span>';

            const statusCell = document.createElement('div');
            statusCell.className = 'c-cell';
            statusCell.innerHTML = statusBadge(c.status);

            grid.appendChild(ctrCell);
            grid.appendChild(apptCell);
            grid.appendChild(nameCell);
            grid.appendChild(statusCell);
        });
    }

    // --- Now Serving ---
    function updateNowServing(call) {
        const nsAppt = document.getElementById('ns-apptno');
        const nsName = document.getElementById('ns-name');
        const nsCtr  = document.getElementById('ns-counter');

        if (call) {
            nsAppt.innerHTML = esc(call.appoint_no);
            nsName.innerHTML = `<div class="text-8xl font-black leading-tight text-navy text-center w-full px-4">${esc(call.name)}</div>`;
            nsCtr.innerHTML  = esc(call.counter_name || ('Counter ' + call.counter_number));
            nsAppt.className = 'bg-sky py-5 text-center text-8xl font-black leading-none text-navy';
            nsCtr.className  = 'bg-sky py-5 text-center text-8xl font-black leading-none text-navy';
        } else {
            nsAppt.innerHTML = '<span class="text-gray-300">----</span>';
            nsName.innerHTML = '<span class="text-gray-300 text-8xl font-black">———</span>';
            nsCtr.innerHTML  = '<span class="text-gray-300">———</span>';
        }
    }

    // --- Up Next ---
    function updateUpNext(items) {
        const box = document.getElementById('up-next');
        if (!items || !items.length) {
            box.innerHTML = '<div class="h-full flex items-center justify-center text-gray-400 text-lg font-semibold">No upcoming appointments</div>';
            return;
        }
        const rows = items.map(item => {
            const bg = item.status === 'on_hold'
                ? 'bg-orange-50 border-orange-300'
                : (item.is_transferred ? 'bg-purple-50 border-purple-300' : 'bg-sky border-gray-200');
            const badge = item.status === 'on_hold'
                ? '<span class="bg-orange-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">On Hold</span>'
                : (item.is_transferred ? `<span class="bg-purple-600 text-white text-xs font-bold px-2 py-0.5 rounded-full">→ ${esc(item.window_name || ('Ctr ' + item.window_number))}</span>` : '');
            const time = item.checked_in_at ? `<div class="text-xs text-gray-500">${esc(item.checked_in_at)}</div>` : '';
            return `<div class="border rounded-lg p-3 flex flex-col gap-1 ${bg}">
                <div class="flex items-center gap-2">
                    <div class="bg-[#003366] text-white rounded px-2 py-1 font-bold text-base flex-shrink-0">${esc(item.appoint_no)}</div>
                    ${badge}
                </div>
                <div class="font-bold text-base text-navy truncate">${esc(item.name)}</div>
                ${time}
            </div>`;
        }).join('');
        box.innerHTML = `<div class="grid grid-cols-2 gap-2">${rows}</div>`;
    }

    // --- Sound unlock (Chrome autoplay policy) ---
    let audioUnlocked = false;

    function unlockAudio() {
        if (audioUnlocked) return;
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            ctx.resume();
        } catch (e) {}
        if (window.speechSynthesis) {
            const u = new SpeechSynthesisUtterance('');
            window.speechSynthesis.speak(u);
        }
        audioUnlocked = true;
        document.getElementById('sound-btn').style.display = 'none';
    }

    // Try immediately — works if the browser already trusts this origin
    unlockAudio();

    // Show small fallback button if still blocked after 800 ms
    setTimeout(() => {
        if (!audioUnlocked) document.getElementById('sound-btn').style.display = 'block';
    }, 800);

    // Also silently unlock on any interaction (no overlay needed)
    ['click', 'keydown', 'touchstart', 'pointerdown'].forEach(evt =>
        document.addEventListener(evt, () => unlockAudio(), { once: true, passive: true })
    );

    // --- Audio / Speech ---
    function playDoorbell() {
        return new Promise(resolve => {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const notes = [
                    { freq: 1318.5, start: 0,    dur: 0.45 },
                    { freq:  987.8, start: 0.05,  dur: 0.45 },
                    { freq:  880.0, start: 0.55,  dur: 0.55 },
                    { freq:  659.3, start: 0.60,  dur: 0.55 },
                ];
                notes.forEach(({ freq, start, dur }) => {
                    const osc = ctx.createOscillator(), gain = ctx.createGain();
                    osc.connect(gain); gain.connect(ctx.destination);
                    osc.type = 'sine'; osc.frequency.value = freq;
                    gain.gain.setValueAtTime(0, ctx.currentTime + start);
                    gain.gain.linearRampToValueAtTime(0.35, ctx.currentTime + start + 0.02);
                    gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + start + dur);
                    osc.start(ctx.currentTime + start);
                    osc.stop(ctx.currentTime + start + dur);
                });
                setTimeout(resolve, 1350);
            } catch (e) { resolve(); }
        });
    }

    function announceCall(callKey, callNum, callName, callCtr) {
        if (!callKey || sessionStorage.getItem('last_announced') === callKey) return;
        sessionStorage.setItem('last_announced', callKey);
        if (!window.speechSynthesis) return;
        const digits = callNum.split('').join(', ');
        const msg = new SpeechSynthesisUtterance(
            `Now calling number ${digits}. ${callName}, please proceed to ${callCtr}.`
        );
        msg.lang = 'en-US'; msg.rate = 0.88; msg.pitch = 1.05; msg.volume = 1;
        function speak() {
            const voices = window.speechSynthesis.getVoices();
            const femalePattern = /female|zira|eva|aria|hazel|samantha|karen|moira|fiona|victoria|elsa|emma|jenny|jane|susan|helen|linda|lisa/i;
            const preferred = voices.find(v => femalePattern.test(v.name) && /en[-_]/i.test(v.lang))
                           || voices.find(v => femalePattern.test(v.name))
                           || voices.find(v => /en[-_]/i.test(v.lang))
                           || voices[0];
            if (preferred) msg.voice = preferred;
            window.speechSynthesis.cancel();
            playDoorbell().then(() => window.speechSynthesis.speak(msg));
        }
        if (window.speechSynthesis.getVoices().length) speak();
        else window.speechSynthesis.addEventListener('voiceschanged', speak, { once: true });
    }

    // --- Polling ---
    async function poll() {
        try {
            const res = await fetch('/api/tv-data');
            if (!res.ok) return;
            const data = await res.json();
            updateCounters(data.counters);
            updateNowServing(data.currentCall);
            updateUpNext(data.upNext);
            document.getElementById('tv-datetime').textContent = data.now;
            if (data.currentCall && data.currentCall.status === 'calling') {
                const ctrLabel = data.currentCall.counter_name || ('Counter ' + data.currentCall.counter_number);
                announceCall(data.currentCall.call_key, data.currentCall.appoint_no, data.currentCall.name, ctrLabel);
            }
        } catch (e) {}
    }

    // --- Boot ---
    document.addEventListener('DOMContentLoaded', () => {
        @php
            $initCounters = collect($counters)->values()->map(fn($c) => [
                'counter'    => $c['counter'],
                'appoint_no' => $c['appointment'] ? str_pad(substr($c['appointment']->appoint_no, -4), 4, '0', STR_PAD_LEFT) : null,
                'name'       => $c['appointment'] ? (substr($c['appointment']->applicant->first_name, 0, 1) . '. ' . $c['appointment']->applicant->last_name) : null,
                'status'     => $c['status'],
            ]);
        @endphp
        updateCounters({!! json_encode($initCounters) !!});

        @if($currentCall && $currentCall->status === 'calling')
        announceCall(
            '{{ $currentCall->appoint_no }}_{{ $currentCall->called_at?->timestamp }}',
            '{{ str_pad(substr($currentCall->appoint_no, -4), 4, "0", STR_PAD_LEFT) }}',
            '{{ substr($currentCall->applicant->first_name, 0, 1) }} {{ $currentCall->applicant->last_name }}',
            'Counter {{ $currentCall->counter_number }}'
        );
        @endif

        setInterval(poll, 5000);
    });
    </script>
</body>
</html>
