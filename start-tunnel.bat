@echo off
title KPKNL Palembang - Cloudflare Remote Access Orchestrator
color 0B
cls

:: ===============================================================================
:: KPKNL PALEMBANG - SECURE REMOTE ACCESS LAUNCHER
:: 100%% User-Space | Bebas Hak Administrator (Non-UAC) | Enkripsi End-to-End
:: ===============================================================================

cd /d "%~dp0"

:: Cek apakah PowerShell tersedia di sistem
where powershell.exe >nul 2>&1
if %errorlevel% equ 0 (
    if exist "%~dp0tunnel-manager.ps1" (
        powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0tunnel-manager.ps1" %*
        exit /b %errorlevel%
    )
)

:: Fallback Standalone jika PowerShell terkendala
echo ===============================================================================
echo                KPKNL PALEMBANG - CLOUDFLARE SECURE REMOTE TUNNEL
echo ===============================================================================
echo  [INFO] Menghubungkan server lokal KPKNL Palembang ke jaringan remote...
echo  [INFO] Status: Berjalan 100%% di User-Space (Tanpa Hak Administrator / UAC)
echo  [INFO] Server Lokal Target: http://10.24.7.207
echo ===============================================================================
echo.

if not exist "%~dp0cloudflared.exe" (
    echo [DOWNLOAD] File cloudflared.exe tidak ditemukan di folder ini.
    echo [DOWNLOAD] Sedang mengunduh versi resmi terbaru via curl...
    curl.exe -L "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe" -o "%~dp0cloudflared.exe"
    if errorlevel 1 (
        echo [ERROR] Gagal mengunduh cloudflared.exe. Pastikan koneksi internet aktif.
        pause
        exit /b 1
    )
    echo [SUCCESS] cloudflared.exe berhasil diunduh!
    echo.
)

echo [RUNNING] Membuka terowongan remote Cloudflare...
echo [PETUNJUK] Perhatikan baris URL "https://...trycloudflare.com" di bawah ini.
echo            Salin (copy) link tersebut dan buka di browser HP/Laptop di luar kantor.
echo [PETUNJUK] Tekan CTRL + C atau tutup jendela ini untuk mematikan tunnel kapan saja.
echo ===============================================================================
echo.

"%~dp0cloudflared.exe" tunnel --url http://10.24.7.207

echo.
echo ===============================================================================
echo  Tunnel telah dihentikan.
echo ===============================================================================
pause
