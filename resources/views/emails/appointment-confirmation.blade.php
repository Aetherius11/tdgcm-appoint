<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment Confirmation</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f7fa;
        }
        .container {
            background-color: #ffffff;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #3b82f6;
            margin-bottom: 20px;
        }
        .header h1 {
            color: #1e40af;
            margin: 0;
            font-size: 24px;
        }
        .header p {
            color: #6b7280;
            margin: 5px 0 0;
            font-size: 14px;
        }
        .appointment-details {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .appointment-details h2 {
            color: #0369a1;
            margin: 0 0 15px;
            font-size: 18px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0f2fe;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            font-weight: 600;
            color: #64748b;
        }
        .detail-value {
            color: #1e293b;
            font-weight: 500;
        }
        .qr-section {
            text-align: center;
            margin: 25px 0;
            padding: 20px;
            background-color: #fafafa;
            border-radius: 8px;
        }
        .qr-section img {
            max-width: 200px;
            height: auto;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 10px;
            background-color: white;
        }
        .qr-section p {
            margin-top: 10px;
            color: #6b7280;
            font-size: 14px;
        }
        .important-info {
            background-color: #fef3c7;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
        }
        .important-info h3 {
            color: #92400e;
            margin: 0 0 10px;
            font-size: 16px;
        }
        .important-info ul {
            margin: 0;
            padding-left: 20px;
            color: #78350f;
        }
        .important-info li {
            margin-bottom: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #9ca3af;
            font-size: 12px;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>TCM EasyQue</h1>
            <p>Appointment Confirmation</p>
        </div>

        <p>Dear <strong>{{ $appointment->applicant->first_name }} {{ $appointment->applicant->last_name }}</strong>,</p>
        
        <p>Your appointment has been successfully scheduled. Please find the details below and save this email for your records.</p>

        <div class="appointment-details">
            <h2>Appointment Details</h2>
            <div class="detail-row">
                <span class="detail-label">Appointment Number:</span>
                <span class="detail-value">{{ $appointment->appoint_no }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Principal:</span>
                <span class="detail-value">{{ $appointment->principal->name ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Purpose:</span>
                <span class="detail-value">{{ $appointment->purpose->purpose_name ?? 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Date:</span>
                <span class="detail-value">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, F j, Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Time Slot:</span>
                <span class="detail-value">{{ $appointment->timeSlot ? $appointment->timeSlot->start_time . ' - ' . $appointment->timeSlot->end_time : 'N/A' }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Status:</span>
                <span class="status-badge status-pending">{{ ucfirst($appointment->status) }}</span>
            </div>
        </div>

        <div class="qr-section">
            <p style="margin-top: 0; font-weight: 600; color: #374151;">Your QR Code</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $appointment->appoint_no }}&color=1b1b18&bgcolor=ffffff" alt="QR Code">
            <p><strong>Appointment ID:</strong> {{ $appointment->appoint_no }}</p>
            <p style="font-size: 12px; color: #9ca3af;">Please present this QR code upon arrival for quick check-in</p>
        </div>

        <div class="important-info">
            <h3>📋 Important Reminders</h3>
            <ul>
                <li>Arrive at least 10 minutes before your scheduled time</li>
                <li>Bring a valid government-issued ID</li>
                <li>Bring any required documents related to your appointment purpose</li>
                <li>Present this QR code at the security desk for check-in</li>
            </ul>
        </div>

        <p>If you have any questions or need to make changes to your appointment, please contact our support team.</p>

        <p>Thank you for choosing TCM EasyQue!</p>

        <div class="footer">
            <p><strong>TCM EasyQue</strong><br>
            Your Time, Our Priority</p>
            <p>This is an automated message. Please do not reply directly to this email.</p>
            <p>&copy; {{ date('Y') }} TDGCM. All rights reserved.</p>
        </div>
    </div>
</body>
</html>