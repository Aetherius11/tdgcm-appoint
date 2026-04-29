<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Scanner - Guard Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet" />
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <style>
        body { font-family: 'Instrument Sans', sans-serif; }
        #reader { width: 100%; max-width: 500px; margin: 0 auto; border-radius: 12px; overflow: hidden; }
        #reader video { object-fit: cover; border-radius: 12px; }
        #reader canvas { border-radius: 12px; }
        .scanner-container {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
        }
        .scan-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border: 3px solid #3b82f6;
            border-radius: 12px;
            pointer-events: none;
            z-index: 10;
        }
        .scan-corner {
            position: absolute;
            width: 30px;
            height: 30px;
            border-color: #3b82f6;
            border-style: solid;
        }
        .scan-corner.tl { top: -3px; left: -3px; border-width: 4px 0 0 4px; border-radius: 8px 0 0 0; }
        .scan-corner.tr { top: -3px; right: -3px; border-width: 4px 4px 0 0; border-radius: 0 8px 0 0; }
        .scan-corner.bl { bottom: -3px; left: -3px; border-width: 0 0 4px 4px; border-radius: 0 0 0 8px; }
        .scan-corner.br { bottom: -3px; right: -3px; border-width: 0 4px 4px 0; border-radius: 0 0 8px 0; }
        .scan-line {
            position: absolute;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, transparent, #3b82f6, transparent);
            animation: scan 2s linear infinite;
            z-index: 5;
        }
        @keyframes scan {
            0% { top: 10%; opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { top: 90%; opacity: 0; }
        }
    </style>
</head>
<body class="bg-[#F4F7FA] min-h-screen">

    <!-- Header -->
    <header class="w-full bg-[#111827] text-white shadow-lg">
        <div class="max-w-7xl mx-auto flex justify-between items-center py-3 px-6">
            <div class="flex items-center gap-2">
                <div class="bg-white p-1 rounded">
                    <span class="text-black font-bold text-xs">TCM</span>
                </div>
                <h1 class="text-lg font-bold tracking-tight uppercase">EasyQue Guard Dashboard</h1>
            </div>
            <nav class="flex gap-4 text-sm">
                <a href="{{ route('appointments.index') }}" class="hover:text-blue-400 transition">Appointments List</a>
            </nav>
        </div>
    </header>

    <main class="max-w-4xl mx-auto px-6 py-8">
        
        <!-- Scanner Section -->
        <div class="bg-white rounded-2xl shadow-lg border border-gray-200 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-center">
                <h2 class="text-2xl font-bold text-white">QR Code Scanner</h2>
                <p class="text-blue-100 text-sm mt-1">Scan appointment QR codes to check in visitors</p>
            </div>

            <div class="p-8">
                <!-- Camera View -->
                <div class="scanner-container bg-gray-900 rounded-xl mb-6 relative">
                    <div id="reader" class="relative"></div>
                    <div class="scan-overlay">
                        <div class="scan-corner tl"></div>
                        <div class="scan-corner tr"></div>
                        <div class="scan-corner bl"></div>
                        <div class="scan-corner br"></div>
                        <div class="scan-line"></div>
                    </div>
                </div>

                <!-- Camera Status -->
                <div id="camera_status" class="text-center mb-4">
                    <p class="text-sm text-gray-500">Initializing camera...</p>
                </div>

                <!-- Manual Input (fallback) -->
                <div class="mb-6">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Or enter appointment number manually</label>
                    <div class="flex gap-2">
                        <input type="text" id="manual_appoint_no" placeholder="e.g., A-2026042901" 
                            class="flex-1 border border-gray-300 rounded-lg px-4 py-3 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none uppercase"
                            autocomplete="off">
                        <button onclick="processManualInput()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-6 py-3 rounded-lg transition-colors whitespace-nowrap">
                            Check
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">💡 Tip: You can also just type and press Enter</p>
                </div>

                <!-- Status Messages -->
                <div id="status_message" class="hidden rounded-xl p-6 mb-6">
                    <div class="flex items-start gap-4">
                        <div id="status_icon" class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0">
                        </div>
                        <div class="flex-1">
                            <h3 id="status_title" class="text-lg font-bold"></h3>
                            <p id="status_text" class="text-sm mt-1"></p>
                            <div id="appointment_details" class="mt-4 p-4 bg-white/50 rounded-lg hidden">
                                <div class="grid grid-cols-2 gap-3 text-sm">
                                    <div><span class="text-gray-500">Appointment ID:</span> <span id="detail_id" class="font-semibold"></span></div>
                                    <div><span class="text-gray-500">Name:</span> <span id="detail_name" class="font-semibold"></span></div>
                                    <div><span class="text-gray-500">Principal:</span> <span id="detail_principal" class="font-semibold"></span></div>
                                    <div><span class="text-gray-500">Purpose:</span> <span id="detail_purpose" class="font-semibold"></span></div>
                                    <div><span class="text-gray-500">Date:</span> <span id="detail_date" class="font-semibold"></span></div>
                                    <div><span class="text-gray-500">Time Slot:</span> <span id="detail_time" class="font-semibold"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-sm font-bold text-gray-700 uppercase mb-3">How to use:</h3>
                    <ol class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">1</span>
                            <span>Allow camera access when prompted by your browser</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">2</span>
                            <span>Hold the QR code within the blue frame</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">3</span>
                            <span>Wait for automatic detection (usually instant)</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="w-5 h-5 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0">4</span>
                            <span>Or type the appointment number manually and press Enter</span>
                        </li>
                    </ol>
                </div>
            </div>
        </div>

        <!-- Recent Scans -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h3 class="text-sm font-bold text-gray-700 uppercase mb-4">Recent Scans</h3>
            <div id="recent_scans" class="space-y-3">
                <p class="text-sm text-gray-500 italic">No scans yet today.</p>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="mt-auto py-6 text-center text-xs text-gray-500">
        © {{ date('Y') }} TCM EasyQue Guard Dashboard. All rights reserved.
    </footer>

    <script>
    // CSRF Token
    const CSRF_TOKEN = '{{ csrf_token() }}';

    // Recent scans storage
    let recentScans = [];
    let isProcessing = false;
    let lastScannedCode = '';
    let lastScanTime = 0;

    // Initialize QR Scanner with optimized settings
    let html5QrcodeScanner = null;

    function onScanSuccess(decodedText, decodedResult) {
        // Debounce: prevent multiple scans of the same code within 2 seconds
        const now = Date.now();
        if (decodedText === lastScannedCode && (now - lastScanTime) < 2000) {
            return;
        }
        
        lastScannedCode = decodedText;
        lastScanTime = now;

        console.log('QR Code scanned:', decodedText);
        processQRCode(decodedText);
    }

    function onScanFailure(error) {
        // Most errors are normal (no QR code in view)
        // Only log unexpected errors
        if (error && !error.includes('NotFoundException')) {
            console.warn('Scan error:', error);
        }
    }

    // Initialize scanner when page loads
    document.addEventListener('DOMContentLoaded', function() {
        const cameraStatus = document.getElementById('camera_status');
        
        // First, check if camera is available
        async function checkCamera() {
            try {
                // Request camera access first
                const stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { facingMode: 'environment' } 
                });
                
                // Stop the stream - we just wanted to check permission
                stream.getTracks().forEach(track => track.stop());
                
                console.log('Camera permission granted, initializing scanner...');
                initScanner();
            } catch (err) {
                console.error('Camera check failed:', err.name, err.message);
                
                let errorMsg = 'Camera access denied or unavailable.';
                if (err.name === 'NotAllowedError') {
                    errorMsg = 'Camera permission denied. Please allow camera access in your browser settings.';
                } else if (err.name === 'NotFoundError') {
                    errorMsg = 'No camera found on this device.';
                } else if (err.name === 'NotReadableError') {
                    errorMsg = 'Camera is being used by another application.';
                }
                
                cameraStatus.innerHTML = `
                    <p class="text-sm text-orange-600 font-medium">${errorMsg}</p>
                    <p class="text-xs text-gray-500 mt-1">Using manual input instead.</p>
                `;
            }
        }
        
        function initScanner() {
            try {
                html5QrcodeScanner = new Html5Qrcode("reader");
                
                // Optimized configuration for faster, more accurate scanning
                const config = { 
                    fps: 15,
                    qrbox: { width: 250, height: 250 },
                    aspectRatio: 1.0,
                    disableFlip: false
                };

                // Try different camera configurations
                const cameraConfigs = [
                    { facingMode: "environment" },
                    { facingMode: "user" },
                    null // Let the library choose
                ];
                
                let configIndex = 0;
                
                function tryConfig() {
                    if (configIndex >= cameraConfigs.length) {
                        cameraStatus.innerHTML = `
                            <p class="text-sm text-orange-600 font-medium">Could not start camera.</p>
                            <p class="text-xs text-gray-500 mt-1">Please use manual input below.</p>
                        `;
                        return;
                    }
                    
                    const cameraConfig = cameraConfigs[configIndex];
                    console.log(`Trying camera config ${configIndex + 1}:`, cameraConfig);
                    
                    html5QrcodeScanner.start(
                        cameraConfig,
                        config,
                        onScanSuccess,
                        onScanFailure
                    ).then(() => {
                        cameraStatus.innerHTML = '<p class="text-sm text-green-600 font-medium">✓ Camera ready - Point QR code at the frame</p>';
                    }).catch(err => {
                        console.warn(`Config ${configIndex + 1} failed:`, err);
                        configIndex++;
                        tryConfig();
                    });
                }
                
                tryConfig();
            } catch (err) {
                console.error("Scanner initialization error:", err);
                cameraStatus.innerHTML = '<p class="text-sm text-red-600 font-medium">Error initializing camera. Please use manual input.</p>';
            }
        }
        
        // Start the camera check
        checkCamera();
    });

    // Show status message (helper function)
    function showStatusMessage(type, title, text) {
        const statusEl = document.getElementById('status_message');
        const iconEl = document.getElementById('status_icon');
        const titleEl = document.getElementById('status_title');
        const textEl = document.getElementById('status_text');
        const detailsEl = document.getElementById('appointment_details');

        statusEl.classList.remove('hidden');
        detailsEl.classList.add('hidden');

        if (type === 'loading') {
            iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-blue-500 text-white animate-pulse';
            iconEl.innerHTML = '<svg class="w-6 h-6 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.01M4 9h5m0 0V4m0 5h5M20 20v-5h-.01M20 15h-5m0 0v5m0-5h5M4 14v5h.01M4 19h5m0 0v-5m0 5h5M20 9v5h-.01M20 14h-5m0 0v5m0-5h5" /></svg>';
            titleEl.textContent = title;
            titleEl.className = 'text-lg font-bold text-blue-800';
            textEl.textContent = text;
            statusEl.className = 'rounded-xl p-6 mb-6 bg-blue-50 border border-blue-200';
        } else if (type === 'error') {
            iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-red-500 text-white';
            iconEl.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
            titleEl.textContent = title;
            titleEl.className = 'text-lg font-bold text-red-800';
            textEl.textContent = text;
            statusEl.className = 'rounded-xl p-6 mb-6 bg-red-100 border border-red-200';
        }

        setTimeout(() => {
            statusEl.classList.add('hidden');
        }, 10000);
    }

    // Process QR Code data
    function processQRCode(qrData) {
        if (isProcessing) {
            console.log('Already processing, skipping...');
            return;
        }

        isProcessing = true;
        showStatusMessage('loading', 'Processing...', 'Validating appointment...');

        console.log('Sending QR data:', qrData);

        fetch('{{ route("qr.scan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ qr_data: qrData })
        })
        .then(response => {
            console.log('Response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Response data:', data);
            handleScanResult(data);
            isProcessing = false;
        })
        .catch(error => {
            console.error('Fetch error:', error);
            showStatusMessage('error', 'Connection Error', 'Unable to connect to server. Please check your connection and try again.');
            isProcessing = false;
        });
    }

    // Process manual input
    function processManualInput() {
        const input = document.getElementById('manual_appoint_no');
        let appointNo = input.value.trim().toUpperCase();
        
        if (!appointNo) {
            showStatusMessage('error', 'Invalid Input', 'Please enter an appointment number.');
            input.focus();
            return;
        }

        // Clean up the input - remove any extra spaces or characters
        appointNo = appointNo.replace(/[^A-Z0-9-]/g, '');
        
        // More flexible validation - just check it starts with A- and has digits after
        if (!appointNo.match(/^A-\d+$/i)) {
            showStatusMessage('error', 'Invalid Format', 'Appointment number must start with A- followed by numbers (e.g., A-2026042901)');
            return;
        }

        console.log('Processing manual input:', appointNo);

        // Create QR data format matching what's in the QR code
        const qrData = JSON.stringify({ appointment_no: appointNo });
        processQRCode(qrData);
    }

    // Handle scan result
    function handleScanResult(data) {
        const statusEl = document.getElementById('status_message');
        const iconEl = document.getElementById('status_icon');
        const titleEl = document.getElementById('status_title');
        const textEl = document.getElementById('status_text');
        const detailsEl = document.getElementById('appointment_details');

        statusEl.classList.remove('hidden');
        detailsEl.classList.add('hidden');

        switch(data.status) {
            case 'checked_in':
                iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-green-500 text-white';
                iconEl.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>';
                titleEl.textContent = '✓ Checked In Successfully!';
                titleEl.className = 'text-lg font-bold text-green-800';
                textEl.textContent = data.message;
                statusEl.className = 'rounded-xl p-6 mb-6 bg-green-100 border border-green-200';
                
                if (data.appointment) {
                    showAppointmentDetails(data.appointment);
                }
                
                addToRecentScans(data, 'success');
                
                // Play success sound
                playSound('success');
                break;

            case 'expired':
                iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-orange-500 text-white';
                iconEl.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                titleEl.textContent = '⚠ Appointment Expired';
                titleEl.className = 'text-lg font-bold text-orange-800';
                textEl.textContent = data.message;
                statusEl.className = 'rounded-xl p-6 mb-6 bg-orange-100 border border-orange-200';
                
                if (data.appointment) {
                    showAppointmentDetails(data.appointment);
                }
                
                addToRecentScans(data, 'warning');
                playSound('warning');
                break;

            case 'not_found':
                iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-red-500 text-white';
                iconEl.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>';
                titleEl.textContent = '✕ Appointment Not Found';
                titleEl.className = 'text-lg font-bold text-red-800';
                textEl.textContent = data.message;
                statusEl.className = 'rounded-xl p-6 mb-6 bg-red-100 border border-red-200';
                
                addToRecentScans(data, 'error');
                playSound('error');
                break;

            case 'already_checked_in':
                iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-blue-500 text-white';
                iconEl.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>';
                titleEl.textContent = 'Already Checked In';
                titleEl.className = 'text-lg font-bold text-blue-800';
                textEl.textContent = data.message;
                statusEl.className = 'rounded-xl p-6 mb-6 bg-blue-100 border border-blue-200';
                
                if (data.appointment) {
                    showAppointmentDetails(data.appointment);
                }
                
                addToRecentScans(data, 'info');
                break;

            default:
                iconEl.className = 'w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 bg-gray-500 text-white';
                iconEl.innerHTML = '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                titleEl.textContent = 'Error';
                titleEl.className = 'text-lg font-bold text-gray-800';
                textEl.textContent = data.message || 'An unknown error occurred.';
                statusEl.className = 'rounded-xl p-6 mb-6 bg-gray-100 border border-gray-200';
        }

        // Clear status after 15 seconds
        setTimeout(() => {
            statusEl.classList.add('hidden');
        }, 15000);
    }

    function showAppointmentDetails(appointment) {
        const detailsEl = document.getElementById('appointment_details');
        detailsEl.classList.remove('hidden');
        
        document.getElementById('detail_id').textContent = '#' + appointment.appoint_no;
        document.getElementById('detail_name').textContent = appointment.name;
        document.getElementById('detail_principal').textContent = appointment.principal;
        document.getElementById('detail_purpose').textContent = appointment.purpose;
        document.getElementById('detail_date').textContent = appointment.date;
        document.getElementById('detail_time').textContent = appointment.time_slot || 'N/A';
    }

    function addToRecentScans(data, type) {
        const scan = {
            time: new Date().toLocaleTimeString(),
            status: data.status,
            type: type,
            appointment: data.appointment
        };

        recentScans.unshift(scan);
        if (recentScans.length > 10) recentScans.pop();

        renderRecentScans();
    }

    function renderRecentScans() {
        const container = document.getElementById('recent_scans');
        
        if (recentScans.length === 0) {
            container.innerHTML = '<p class="text-sm text-gray-500 italic">No scans yet today.</p>';
            return;
        }

        container.innerHTML = recentScans.map(scan => {
            const typeColors = {
                success: 'bg-green-50 border-green-200',
                warning: 'bg-orange-50 border-orange-200',
                error: 'bg-red-50 border-red-200',
                info: 'bg-blue-50 border-blue-200'
            };

            const statusLabels = {
                checked_in: 'Checked In',
                expired: 'Expired',
                not_found: 'Not Found',
                already_checked_in: 'Already Checked In'
            };

            return `
                <div class="flex items-center justify-between p-3 rounded-lg border ${typeColors[scan.type] || 'bg-gray-50 border-gray-200'}">
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-500">${scan.time}</span>
                        <span class="text-sm font-medium">${scan.appointment ? '#' + scan.appointment.appoint_no : 'Unknown'}</span>
                    </div>
                    <span class="text-xs font-bold uppercase px-2 py-1 rounded ${
                        scan.type === 'success' ? 'bg-green-200 text-green-700' :
                        scan.type === 'warning' ? 'bg-orange-200 text-orange-700' :
                        scan.type === 'error' ? 'bg-red-200 text-red-700' :
                        'bg-blue-200 text-blue-700'
                    }">${statusLabels[scan.status] || scan.status}</span>
                </div>
            `;
        }).join('');
    }

    // Sound effects
    function playSound(type) {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        
        switch(type) {
            case 'success':
                oscillator.frequency.setValueAtTime(523, audioContext.currentTime); // C5
                oscillator.frequency.setValueAtTime(659, audioContext.currentTime + 0.1); // E5
                oscillator.frequency.setValueAtTime(784, audioContext.currentTime + 0.2); // G5
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.4);
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.4);
                break;
            case 'error':
                oscillator.frequency.setValueAtTime(200, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(150, audioContext.currentTime + 0.15);
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
                break;
            case 'warning':
                oscillator.frequency.setValueAtTime(440, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(440, audioContext.currentTime + 0.15);
                gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.3);
                break;
        }
    }

    // Allow Enter key for manual input
    document.getElementById('manual_appoint_no').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            processManualInput();
        }
    });

    // Auto-uppercase input
    document.getElementById('manual_appoint_no').addEventListener('input', function(e) {
        this.value = this.value.toUpperCase();
    });
    </script>
</body>
</html>