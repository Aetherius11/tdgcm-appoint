# CLAUDE.md — TDG Crew Management (TCM EasyQue)

## Project Overview
Queue management / appointment system for TDG Crew Management.
- Public applicants book appointments online, receive a QR code, and check in via the guard's QR scanner.
- Window staff call, serve, hold, transfer, and complete tickets from their counter panel.
- A TV display shows the live queue to waiting applicants.

## Stack
- **Laravel 12** + **Jetstream** + **Sanctum** + **Filament 3** (admin panel) + **Livewire 3** + **Alpine.js**
- **MySQL** via XAMPP (local dev, port 3306, DB: `tdgcm_appoint`)
- **Tailwind CSS** (CDN in most blade files; Vite used only in `appointment-success.blade.php`)
- Server: `php artisan serve` or XAMPP Apache on port 8000

## Critical: Window ID vs Display Name
Windows table IDs start at **2**, but display names are sequential from **"1"**.
- DB ID 2 → Counter "1", DB ID 3 → Counter "2", etc.
- **Always use `Window::find($id)->name` to get the display number**, never the raw DB ID.
- `counter_number` on appointments stores the display name (cast to int), not the DB ID.
- Session key `staff_window_id` holds the DB ID.

## Key Models & Files

| Model | Table | Notes |
|---|---|---|
| `Appointment` | `appointments` | Core record; has `hold_started_at`, `hold_duration_seconds` for pause-timer |
| `AppointmentWindowLog` | `appointment_window_logs` | Per-window visit log (called_at, served_at, ended_at, end_type) |
| `Window` | `windows` | id, name (display number as string) |
| `Principal` | `principals` | Departments; has `windows()` relationship |
| `Purpose` | `purposes` | Linked to principals via pivot |
| `Applicant` | `applicants` | |
| `Company` | `companies` | Branding logo (`branding_logo` → Storage) |

## Appointment Status Flow
```
pending → approved → [checked_in_at set by QR scan] → calling → serving → completed
                                                             ↓           ↓
                                                        on_hold     transferred (log closed, status→pending, window_number=target)
```

## Key Controllers

### `WindowStaffController`
- `index()` — Window Staff page; uses `staff_window_id` session key
- `notify()` — Calls next ticket; wrapped in `DB::transaction` + `lockForUpdate()` to prevent double-call race conditions; creates `AppointmentWindowLog` entry
- `startService()` — Stamps `served_at`; **resets `hold_started_at` and `hold_duration_seconds` to 0** (important for transferred tickets)
- `holdTicket()` — Sets `status=on_hold`, stamps `hold_started_at`
- `resumeTicket()` — Accumulates `hold_duration_seconds += (now - hold_started_at)`, clears `hold_started_at`
- `transfer()` — Closes current window log (`end_type=transferred`) BEFORE changing `window_number`
- `transactionHistory()` — Queries `AppointmentWindowLog`, not `Appointment`

### `TVDisplayController`
- `buildUpNext()` — Only shows tickets for **currently open or on-break windows**; transferred tickets only show if their target window is active
- `getData()` — JSON endpoint polled every 5 s by TV display; sets `is_transferred` flag when `window_number` is set
- `counter_name` = `'Counter ' . $currentCall->counter_number` (display name, not DB ID)

### `AppointmentController`
- `processQRScan()` — Guard check-in; supports `guard_override=true` for wrong-date and expired appointments; expired appointments are **not** pre-cancelled before showing override prompt

## Key Views

| View | Route | Auth |
|---|---|---|
| `resources/views/WindowStaff.blade.php` | `/WindowStaff` | Sanctum |
| `resources/views/tv-display.blade.php` | `/tv-display` | Public |
| `resources/views/transaction-history.blade.php` | `/window/transaction-history` | Sanctum |
| `resources/views/qr-scanner.blade.php` | `/qr-scanner` | Sanctum |
| `resources/views/appointment-success.blade.php` | shown after booking | Public |

## Window Staff Page — Timer Logic
- Timer shows `(now - served_at) - hold_duration_seconds` while `status = serving`
- Timer shows frozen value at `(hold_started_at - served_at) - hold_duration_seconds` with "(PAUSED)" while `status = on_hold`
- `data-start` and `data-hold` attributes on `#live-timer` element drive the JS

## TV Display — Sound
- No blocking overlay; tries `unlockAudio()` immediately on load
- Small "🔇 Tap to enable sound" button in bottom-right appears only if Chrome still blocks after 800 ms
- `sessionStorage` (not `localStorage`) used for TTS dedup key so announcements replay after page refresh
- Female English voice preferred; fallback chain: female+English → female any → English any → any

## Queue State Polling
- **Window Staff** polls `/window/queue-state` every **8 s**; reloads page if state changes
- **TV Display** polls `/api/tv-data` every **5 s**; updates DOM in-place

## Guard Override (QR Scanner)
- **Wrong date** (past or future): returns `status=wrong_date` + `is_early` flag; blade shows amber "Override — Allow Check-in" button
- **Expired** (today but >1 hr past end time): returns `status=expired`; blade shows orange "Override — Allow Late Check-in" button
- Both overrides re-submit with `guard_override=true`; controller checks this flag before any status mutation

## Up Next — Transferred Badge
- Purple `→ Counter X` badge on TV display and Window Staff Next Queue when `window_number` is set and status is `pending`/`approved`
- Window Staff: detected via `$next->window_number == $windowId`
- TV display: detected via `is_transferred` flag in JSON

## Running Migrations
```powershell
cd c:\xampp\htdocs\tdgcm-appoint
php artisan migrate
```

## Common Tinker Patterns
```php
# In a temp PHP file (tinker has PowerShell quoting issues):
php -r "require 'vendor/autoload.php'; ..."
# Or write a temp .php file, run it, then delete it
```
