@echo off
setlocal EnableExtensions
title KPKNL Palembang - Keep Awake and Anti-Lock System
color 0B
chcp 65001 >nul

:: ===============================================================================
:: KPKNL PALEMBANG - WINDOWS KEEP AWAKE & ANTI-LOCK LAUNCHER
:: Mencegah Layar Mati, Dimming, Sleep, dan Auto-Lock Windows
:: 100%% User-Space | Bebas Hak Administrator (Non-UAC)
:: ===============================================================================

cd /d "%~dp0"

:: Cek apakah PowerShell tersedia di sistem
where powershell.exe >nul 2>&1
if %errorlevel% neq 0 (
    color 0C
    echo [ERROR] PowerShell tidak ditemukan di sistem Windows ini.
    echo         Fitur pergerakan kursor memerlukan PowerShell.
    pause
    exit /b 1
)

:: Jalankan skrip PowerShell
if exist "%~dp0keep-awake.ps1" (
    powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0keep-awake.ps1" %*
    goto :EXIT_SCRIPT
)

:: Fallback jika keep-awake.ps1 tidak berada di folder yang sama
echo [INFO] Menjalankan Keep Awake Standalone Mode...
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "& { Add-Type -TypeDefinition 'using System; using System.Runtime.InteropServices; public class A { [DllImport(\"user32.dll\")] public static extern void mouse_event(uint f, int x, int y, uint d, UIntPtr e); [DllImport(\"kernel32.dll\")] public static extern uint SetThreadExecutionState(uint s); public static void Run() { SetThreadExecutionState(0x80000003); mouse_event(1, 1, 0, 0, UIntPtr.Zero); System.Threading.Thread.Sleep(50); mouse_event(1, -1, 0, 0, UIntPtr.Zero); } }'; Clear-Host; Write-Host '=== KPKNL Palembang Keep Awake (Standalone Mode) ===' -ForegroundColor Cyan; Write-Host 'Status: AKTIF (Layar & Kursor dijaga tetap aktif setiap 60 detik)' -ForegroundColor Green; Write-Host 'Tekan Ctrl + C untuk menghentikan.' -ForegroundColor Yellow; $i=0; while($true){ $i++; [A]::Run(); Write-Host ('[' + (Get-Date -Format 'HH:mm:ss') + '] Siklus #' + $i + ' - Kursor digerakkan (1px) | Display AWAKE') -ForegroundColor Gray; Start-Sleep -Seconds 60 } }"

:EXIT_SCRIPT
if %errorlevel% neq 0 (
    if %errorlevel% neq 1 (
        echo.
        echo [INFO] Script selesai dijalankan.
        pause
    )
)
exit /b 0
