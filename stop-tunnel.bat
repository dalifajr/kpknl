@echo off
title KPKNL Palembang - Hentikan Tunnel
color 0C
cls

echo ===============================================================================
echo            MENGHENTIKAN CLOUDFLARE REMOTE TUNNEL
echo ===============================================================================
echo.
echo [INFO] Mencari dan mematikan proses cloudflared.exe...
taskkill /F /IM cloudflared.exe >nul 2>&1
if errorlevel 1 (
    echo [INFO] Tidak ada proses cloudflared.exe yang sedang berjalan.
) else (
    echo [SUCCESS] Terowongan Cloudflare berhasil dimatikan.
)
echo.
echo ===============================================================================
ping 127.0.0.1 -n 3 >nul
