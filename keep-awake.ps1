<#
.SYNOPSIS
    KPKNL Palembang - Windows Keep Awake & Anti-Lock System
.DESCRIPTION
    Mencegah layar laptop meredup, mati, atau terkunci otomatis (sleep / auto-lock)
    dengan menggerakkan kursor 1 piksel secara periodik dan menyegarkan
    Windows Thread Execution State via Win32 API.
    100% User-Space | Tanpa Hak Administrator (Non-UAC) | Aman & Ringan.
#>

param (
    [Parameter(Position=0)]
    [int]$Interval = 0
)

# Set UTF-8 encoding
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

# Definisi P/Invoke C# untuk mouse_event dan SetThreadExecutionState
$csharpCode = @'
using System;
using System.Runtime.InteropServices;

public class NativeAwake {
    [DllImport("user32.dll")]
    public static extern void mouse_event(uint dwFlags, int dx, int dy, uint dwData, UIntPtr dwExtraInfo);

    [DllImport("kernel32.dll", CharSet = CharSet.Auto, SetLastError = true)]
    public static extern uint SetThreadExecutionState(uint esFlags);

    public const uint MOUSEEVENTF_MOVE = 0x0001;
    public const uint ES_CONTINUOUS = 0x80000000;
    public const uint ES_SYSTEM_REQUIRED = 0x00000001;
    public const uint ES_DISPLAY_REQUIRED = 0x00000002;

    public static void PreventSleepAndMove() {
        // Beritahu Windows Kernel untuk menjaga monitor dan CPU tetap aktif
        SetThreadExecutionState(ES_CONTINUOUS | ES_SYSTEM_REQUIRED | ES_DISPLAY_REQUIRED);
        
        // Gerakkan kursor 1px ke kanan lalu kembalikan ke posisi awal
        // Memicu hardware input event nyata sehingga timer idle OS di-reset
        mouse_event(MOUSEEVENTF_MOVE, 1, 0, 0, UIntPtr.Zero);
        System.Threading.Thread.Sleep(50);
        mouse_event(MOUSEEVENTF_MOVE, -1, 0, 0, UIntPtr.Zero);
    }

    public static void RestoreSleep() {
        // Kembalikan manajemen daya ke pengaturan normal Windows
        SetThreadExecutionState(ES_CONTINUOUS);
    }
}
'@

try {
    Add-Type -TypeDefinition $csharpCode -ErrorAction SilentlyContinue
} catch {}

# Bersihkan layar konsol
Clear-Host
$Host.UI.RawUI.WindowTitle = "KPKNL Palembang - Keep Awake & Anti-Lock Monitor"

Write-Host "===============================================================================" -ForegroundColor Cyan
Write-Host "          KPKNL PALEMBANG - WINDOWS KEEP AWAKE & ANTI-LOCK MONITOR             " -ForegroundColor Yellow
Write-Host "===============================================================================" -ForegroundColor Cyan
Write-Host " Fitur : Mencegah Layar Mati, Dimming, Sleep, dan Auto-Lock Windows           " -ForegroundColor White
Write-Host " Sistem: 100% User-Space (Bebas Administrator / Non-UAC)                      " -ForegroundColor DarkGray
Write-Host "===============================================================================" -ForegroundColor Cyan
Write-Host ""

# Jika interval tidak diberikan via argumen, tanyakan ke pengguna
if ($Interval -le 0) {
    Write-Host "Pilih interval waktu pergerakan kursor (dalam detik):" -ForegroundColor White
    Write-Host " - Rekomendasi: 30 atau 60 detik" -ForegroundColor Gray
    Write-Host " - Tekan [ENTER] langsung untuk menggunakan nilai standar (60 detik)" -ForegroundColor DarkGray
    $inputVal = Read-Host "Masukkan detik [Default: 60]"
    
    if ([string]::IsNullOrWhiteSpace($inputVal)) {
        $Interval = 60
    } else {
        $parsed = 0
        if ([int]::TryParse($inputVal, [ref]$parsed) -and $parsed -gt 0) {
            $Interval = $parsed
        } else {
            Write-Host "[INFO] Masukan tidak valid, menggunakan interval default: 60 detik." -ForegroundColor Yellow
            $Interval = 60
        }
    }
}

Write-Host ""
Write-Host "-------------------------------------------------------------------------------" -ForegroundColor DarkCyan
Write-Host " STATUS PROTEKSI        : " -NoNewline; Write-Host "[ AKTIF / BERJALAN ]" -ForegroundColor Green
Write-Host " INTERVAL PERGERAKAN    : " -NoNewline; Write-Host "$Interval detik sekali" -ForegroundColor Yellow
Write-Host " METODE PROTEKSI        : " -NoNewline; Write-Host "Dual-Layer (Win32 mouse_event + SetThreadExecutionState)" -ForegroundColor Cyan
Write-Host " WAKTU MULAI            : " -NoNewline; Write-Host (Get-Date -Format "dd MMMM yyyy, HH:mm:ss") -ForegroundColor White
Write-Host "-------------------------------------------------------------------------------" -ForegroundColor DarkCyan
Write-Host " PETUNJUK:" -ForegroundColor Yellow
Write-Host "  * Anda tetap dapat mengetik dan menggunakan laptop/mouse seperti biasa." -ForegroundColor Gray
Write-Host "  * Pergerakan kursor sangat halus (1 piksel) sehingga tidak akan mengganggu." -ForegroundColor Gray
Write-Host "  * Tekan [CTRL + C] pada jendela ini untuk menghentikan proteksi kapan saja." -ForegroundColor Gray
Write-Host "===============================================================================" -ForegroundColor Cyan
Write-Host ""

$cycle = 0
$startTime = Get-Date

try {
    while ($true) {
        $cycle++
        $currentTime = Get-Date -Format "HH:mm:ss"
        
        # Eksekusi pergerakan kursor dan reset state sleep
        [NativeAwake]::PreventSleepAndMove()
        
        $elapsed = (Get-Date) - $startTime
        $elapsedStr = "{0:D2}j {1:D2}m {2:D2}d" -f $elapsed.Hours, $elapsed.Minutes, $elapsed.Seconds
        
        $cycleText = "[SIKLUS #{0:D3}] " -f $cycle
        Write-Host "[$currentTime] " -NoNewline -ForegroundColor DarkGray
        Write-Host $cycleText -NoNewline -ForegroundColor Green
        Write-Host "Kursor digerakkan (1px) " -NoNewline -ForegroundColor White
        Write-Host "| Display: AWAKE " -NoNewline -ForegroundColor Cyan
        Write-Host "| Berjalan: $elapsedStr" -ForegroundColor DarkGray
        
        # Jeda waktu sesuai interval
        Start-Sleep -Seconds $Interval
    }
} finally {
    # Pulihkan pengaturan hemat daya normal Windows saat keluar
    [NativeAwake]::RestoreSleep()
    Write-Host ""
    Write-Host "===============================================================================" -ForegroundColor Yellow
    Write-Host " [SELESAI] Proteksi Anti-Lock telah dihentikan." -ForegroundColor Yellow
    Write-Host "           Manajemen daya normal Windows telah dipulihkan kembali." -ForegroundColor White
    Write-Host "===============================================================================" -ForegroundColor Yellow
    Write-Host ""
}
