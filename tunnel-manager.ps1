# ==============================================================================
# KPKNL PALEMBANG - COMPREHENSIVE REMOTE ACCESS & CLOUDFLARE TUNNEL ORCHESTRATOR
# ==============================================================================
# Skrip komprehensif untuk mengelola terowongan aman remote maintenance tanpa
# memerlukan hak akses Administrator (berjalan 100% di User-Space).
# ==============================================================================

[CmdletBinding()]
param (
    [switch]$Quick,
    [switch]$Stop,
    [switch]$Status,
    [string]$TargetUrl = "http://10.24.7.207"
)

$ErrorActionPreference = "Continue"
[Console]::OutputEncoding = [System.Text.Encoding]::UTF8

$ScriptDir = Split-Path -Parent $MyInvocation.MyCommand.Path
if (-not $ScriptDir) { $ScriptDir = "C:\laragon\www" }
$CloudflaredPath = Join-Path $ScriptDir "cloudflared.exe"
$LogPath = Join-Path $ScriptDir "tunnel_log.txt"
$UrlFilePath = Join-Path $ScriptDir "tunnel_url.txt"

# ------------------------------------------------------------------------------
# Helper Functions: Formatting & UI
# ------------------------------------------------------------------------------
function Show-Banner {
    Clear-Host
    Write-Host "================================================================================" -ForegroundColor Cyan
    Write-Host "            KPKNL PALEMBANG - SECURE REMOTE ACCESS ORCHESTRATOR                 " -ForegroundColor Yellow
    Write-Host "       Solusi Akses Pemeliharaan Luar Kantor (100% Bebas Hak Administrator)    " -ForegroundColor Gray
    Write-Host "================================================================================" -ForegroundColor Cyan
    Write-Host ""
}

function Show-Header {
    param([string]$Title)
    Write-Host "--- [ $Title ] ---" -ForegroundColor Cyan
}

function Log-Info {
    param([string]$Msg)
    Write-Host "  [INFO] $Msg" -ForegroundColor Gray
}

function Log-Success {
    param([string]$Msg)
    Write-Host "  [OK]   $Msg" -ForegroundColor Green
}

function Log-Warn {
    param([string]$Msg)
    Write-Host "  [WARN] $Msg" -ForegroundColor Yellow
}

function Log-Error {
    param([string]$Msg)
    Write-Host "  [FAIL] $Msg" -ForegroundColor Red
}

# ------------------------------------------------------------------------------
# Health Checks
# ------------------------------------------------------------------------------
function Test-LocalServer {
    param([string]$Url)
    try {
        $req = [System.Net.WebRequest]::Create($Url)
        $req.Timeout = 2500
        $req.Method = "HEAD"
        $resp = $req.GetResponse()
        $statusCode = [int]$resp.StatusCode
        $resp.Close()
        return ($statusCode -ge 200 -and $statusCode -lt 500)
    } catch {
        return $false
    }
}

function Ensure-CloudflaredBinary {
    if (Test-Path $CloudflaredPath) {
        $ver = & $CloudflaredPath --version 2>$null
        Log-Success "Binary cloudflared.exe terverifikasi ($ver)"
        return $true
    }

    Log-Warn "Binary cloudflared.exe belum ditemukan di $ScriptDir."
    Write-Host "  [DOWNLOAD] Mengunduh binary resmi dari repositori Cloudflare..." -ForegroundColor Cyan
    
    $downloadUrl = "https://github.com/cloudflare/cloudflared/releases/latest/download/cloudflared-windows-amd64.exe"
    try {
        & curl.exe -L $downloadUrl -o $CloudflaredPath
        if (Test-Path $CloudflaredPath) {
            Log-Success "cloudflared.exe berhasil diunduh ke $CloudflaredPath."
            return $true
        }
    } catch {
        Log-Error "Gagal mengunduh cloudflared: $_"
    }
    return $false
}

function Stop-AllTunnels {
    $processes = Get-Process -Name "cloudflared" -ErrorAction SilentlyContinue
    if ($processes) {
        foreach ($p in $processes) {
            try {
                Stop-Process -Id $p.Id -Force -ErrorAction SilentlyContinue
            } catch {}
        }
        Log-Success "Seluruh proses cloudflared.exe yang berjalan telah dimatikan."
    } else {
        Log-Info "Tidak ada proses cloudflared.exe yang sedang berjalan."
    }
    if (Test-Path $UrlFilePath) {
        Remove-Item $UrlFilePath -Force -ErrorAction SilentlyContinue
    }
}

# ------------------------------------------------------------------------------
# Core Action: Start Quick Tunnel
# ------------------------------------------------------------------------------
function Start-QuickTunnel {
    param([string]$Target)
    
    Show-Banner
    Show-Header "MEMULAI TEROWONGAN REMOTE (QUICK TUNNEL)"
    
    # 1. Cek kesehatan Laragon
    Write-Host ""
    Write-Host "1. Memeriksa Kesiapan Server Lokal..." -ForegroundColor White
    $isLocalUp = Test-LocalServer -Url $Target
    if ($isLocalUp) {
        Log-Success "Server lokal aktif dan merespons pada $Target"
    } else {
        Log-Warn "Server lokal di $Target belum merespons."
        Log-Warn "Pastikan Laragon (Apache & MySQL) sudah dalam status 'Start All'!"
        Write-Host ""
        $choice = Read-Host "  Tetap lanjutkan pembuatan tunnel? (Y/N) [Default: Y]"
        if ($choice -match '^[Nn]') {
            return
        }
    }

    # 2. Cek binary
    Write-Host ""
    Write-Host "2. Memverifikasi Engine Cloudflared..." -ForegroundColor White
    if (-not (Ensure-CloudflaredBinary)) {
        Log-Error "Tidak dapat melanjutkan karena binary cloudflared.exe tidak tersedia."
        pause
        return
    }

    # 3. Bersihkan proses lama
    $existing = Get-Process -Name "cloudflared" -ErrorAction SilentlyContinue
    if ($existing) {
        Log-Info "Membersihkan $($existing.Count) proses tunnel lama..."
        Stop-AllTunnels
    }

    # 4. Jalankan Tunnel
    Write-Host ""
    Write-Host "3. Membuka Terowongan Terenkripsi ke Cloudflare Edge..." -ForegroundColor White
    Log-Info "Menghubungkan: $Target -> Cloudflare Global Network"

    $psi = New-Object System.Diagnostics.ProcessStartInfo
    $psi.FileName = $CloudflaredPath
    $psi.Arguments = "tunnel --url $Target"
    $psi.RedirectStandardError = $true
    $psi.RedirectStandardOutput = $true
    $psi.UseShellExecute = $false
    $psi.CreateNoWindow = $true

    $process = [System.Diagnostics.Process]::Start($psi)
    if (-not $process) {
        Log-Error "Gagal meluncurkan proses cloudflared.exe."
        pause
        return
    }

    # 5. Parsing URL Asinkron
    $reader = $process.StandardError
    $extractedUrl = ""
    $sw = [System.Diagnostics.Stopwatch]::StartNew()
    Write-Host "  [WAIT] Menunggu alokasi domain publik HTTPS..." -NoNewline -ForegroundColor Yellow

    while ($sw.ElapsedMilliseconds -lt 25000 -and -not $process.HasExited) {
        $line = $reader.ReadLine()
        Write-Host "." -NoNewline -ForegroundColor Yellow
        if ($line -match 'https://[a-zA-Z0-9-]+\.trycloudflare\.com') {
            $extractedUrl = $matches[0]
            break
        }
    }
    Write-Host ""

    if (-not $extractedUrl) {
        Log-Error "Batas waktu habis! Gagal mendapatkan URL dari Cloudflare."
        Log-Warn "Periksa koneksi internet kantor Anda atau apakah firewall memblokir HTTPS outbound."
        if (-not $process.HasExited) { $process.Kill() }
        pause
        return
    }

    $ssoUrl = "$extractedUrl/sso/public/"
    $timestamp = Get-Date -Format "dd MMMM yyyy HH:mm:ss"

    # Simpan ke file teks
    $fileContent = @"
================================================================================
KPKNL PALEMBANG - TAUTAN AKSES REMOTE SSO & APLIKASI
Dibuat pada: $timestamp
================================================================================

URL PORTAL SSO UTAMA:
$ssoUrl

URL ROOT TUNNEL:
$extractedUrl

Petunjuk:
- Buka link di atas dari browser HP, Laptop di rumah, atau luar jaringan kantor.
- Anda dapat login menggunakan akun Maintenance atau Superadmin.
- Terowongan aktif selama jendela 'start-tunnel.bat' tetap berjalan di server.
================================================================================
"@
    Set-Content -Path $UrlFilePath -Value $fileContent -Encoding UTF8

    # Salin ke Clipboard Windows
    try {
        Set-Clipboard -Value $ssoUrl
        $clipOk = $true
    } catch {
        try {
            $ssoUrl | clip.exe
            $clipOk = $true
        } catch {
            $clipOk = $false
        }
    }

    # Tampilkan Box UI yang Mencolok & Rapi
    [Console]::Beep(1000, 200) 2>$null
    Write-Host ""
    Write-Host "================================================================================" -ForegroundColor Green
    Write-Host "              TEROWONGAN REMOTE BERHASIL DIAKTIFKAN! (STATUS: ONLINE)           " -ForegroundColor Green
    Write-Host "================================================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "  >> TAUTAN PORTAL SSO KPKNL PALEMBANG:" -ForegroundColor Yellow
    Write-Host "     $ssoUrl" -ForegroundColor Cyan -BackgroundColor Black
    Write-Host ""
    Write-Host "  >> TAUTAN ROOT SERVER:" -ForegroundColor Gray
    Write-Host "     $extractedUrl" -ForegroundColor Gray
    Write-Host ""
    if ($clipOk) {
        Write-Host "  [V] Link SSO otomatis DISALIN KE CLIPBOARD (Tinggal Paste / Ctrl+V)!" -ForegroundColor Green
    }
    Write-Host "  [V] Link disimpan di: $UrlFilePath" -ForegroundColor Gray
    Write-Host "================================================================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "KONTROL NAVIGASI:" -ForegroundColor White
    Write-Host "  [B] Buka langsung di Browser Default" -ForegroundColor Yellow
    Write-Host "  [C] Salin ulang link SSO ke Clipboard" -ForegroundColor Cyan
    Write-Host "  [L] Pantau Log Request Terkini" -ForegroundColor Gray
    Write-Host "  [X] Hentikan Tunnel & Tutup Konsol" -ForegroundColor Red
    Write-Host ""

    # Loop Monitoring Interaktif
    while (-not $process.HasExited) {
        if ([Console]::KeyAvailable) {
            $key = [Console]::ReadKey($true).Key
            switch ($key) {
                'B' {
                    Write-Host "  [ACTION] Membuka URL di browser default..." -ForegroundColor Cyan
                    Start-Process $ssoUrl
                }
                'C' {
                    try { Set-Clipboard -Value $ssoUrl } catch { $ssoUrl | clip.exe }
                    Write-Host "  [ACTION] Link berhasil disalin ke clipboard!" -ForegroundColor Green
                }
                'L' {
                    Write-Host "--- LOG AKTIVITAS TERBARU ---" -ForegroundColor Gray
                    for ($i = 0; $i -lt 5; $i++) {
                        if (-not $process.HasExited -and -not $reader.EndOfStream) {
                            $l = $reader.ReadLine()
                            if ($l) { Write-Host "  $l" -ForegroundColor DarkGray }
                        }
                    }
                }
                'X' {
                    Write-Host "  [ACTION] Menghentikan tunnel..." -ForegroundColor Yellow
                    try { $process.Kill() } catch {}
                    break
                }
            }
        }
        Start-Sleep -Milliseconds 300
    }

    Stop-AllTunnels
    Write-Host ""
    Write-Host "Terowongan remote telah ditutup secara bersih." -ForegroundColor Yellow
    Start-Sleep -Seconds 2
}

# ------------------------------------------------------------------------------
# Core Action: Token Tunnel (Cloudflare Zero Trust)
# ------------------------------------------------------------------------------
function Start-TokenTunnel {
    Show-Banner
    Show-Header "CLOUDFLARE ZERO TRUST (TUNNEL TOKEN)"
    Write-Host ""
    Write-Host "Jika Anda telah mendaftarkan domain resmi di Cloudflare Zero Trust," -ForegroundColor Gray
    Write-Host "Anda dapat menjalankan tunnel permanen menggunakan Token Tunnel Anda." -ForegroundColor Gray
    Write-Host ""
    $token = Read-Host "Masukkan Cloudflare Tunnel Token Anda (atau kosongkan untuk batal)"
    if ([string]::IsNullOrWhiteSpace($token)) {
        return
    }

    if (-not (Ensure-CloudflaredBinary)) { return }
    Stop-AllTunnels

    Write-Host ""
    Write-Host "Menjalankan Cloudflare Tunnel dengan Token..." -ForegroundColor Green
    & $CloudflaredPath tunnel run --token $token
    Stop-AllTunnels
}

# ------------------------------------------------------------------------------
# Main Interactive Menu
# ------------------------------------------------------------------------------
function Show-Menu {
    while ($true) {
        Show-Banner
        Write-Host "PILIHAN MENU ORCHESTRATOR:" -ForegroundColor White
        Write-Host "  [1] Mulai Quick Tunnel (HTTPS Publik Instan - Rekomendasi)" -ForegroundColor Green
        Write-Host "  [2] Hubungkan dengan Cloudflare Token (Domain Permanen / Zero Trust)" -ForegroundColor Cyan
        Write-Host "  [3] Uji Kesehatan Server Lokal (Apache & MySQL)" -ForegroundColor Yellow
        Write-Host "  [4] Hentikan Semua Tunnel yang Sedang Berjalan" -ForegroundColor Red
        Write-Host "  [5] Buka Log & Tautan Terakhir (tunnel_url.txt)" -ForegroundColor Gray
        Write-Host "  [0] Keluar" -ForegroundColor DarkGray
        Write-Host ""
        
        Write-Host "Pilih opsi [1-5, 0] (Otomatis memilih [1] dalam 5 detik): " -NoNewline -ForegroundColor White
        
        $timeoutSeconds = 5
        $sw = [System.Diagnostics.Stopwatch]::StartNew()
        $choice = ""
        while ($sw.Elapsed.TotalSeconds -lt $timeoutSeconds) {
            if ([Console]::KeyAvailable) {
                $keyInfo = [Console]::ReadKey($false)
                $choice = $keyInfo.KeyChar.ToString()
                Write-Host ""
                break
            }
            Start-Sleep -Milliseconds 100
        }
        
        if ([string]::IsNullOrWhiteSpace($choice)) {
            Write-Host ""
            Write-Host "  [TIMEOUT] Otomatis memilih [1] Quick Tunnel..." -ForegroundColor Green
            $choice = "1"
            Start-Sleep -Seconds 1
        }

        switch ($choice) {
            "1" { Start-QuickTunnel -Target $TargetUrl }
            "2" { Start-TokenTunnel }
            "3" {
                Show-Banner
                Show-Header "DIAGNOSTIK KESEHATAN SERVER LOKAL"
                Write-Host ""
                $isLocal = Test-LocalServer -Url $TargetUrl
                if ($isLocal) {
                    Log-Success "Web Server Lokal ($TargetUrl): MERESPONS DENGAN BAIK"
                } else {
                    Log-Error "Web Server Lokal ($TargetUrl): TIDAK MERESPONS"
                    Log-Warn "Silakan nyalakan Apache & MySQL di aplikasi Laragon."
                }
                Write-Host ""
                pause
            }
            "4" {
                Show-Banner
                Show-Header "PEMBERSIHAN PROSES TUNNEL"
                Write-Host ""
                Stop-AllTunnels
                Write-Host ""
                pause
            }
            "5" {
                if (Test-Path $UrlFilePath) {
                    notepad.exe $UrlFilePath
                } else {
                    Log-Warn "File $UrlFilePath belum tersedia. Jalankan tunnel terlebih dahulu."
                    Start-Sleep -Seconds 2
                }
            }
            "0" {
                Write-Host "Keluar dari orchestrator." -ForegroundColor Gray
                return
            }
            default {
                Write-Host "Pilihan tidak valid." -ForegroundColor Red
                Start-Sleep -Seconds 1
            }
        }
    }
}

# ------------------------------------------------------------------------------
# Entry Point CLI Handler
# ------------------------------------------------------------------------------
if ($Stop) {
    Stop-AllTunnels
    exit 0
}

if ($Status) {
    $running = Get-Process -Name "cloudflared" -ErrorAction SilentlyContinue
    if ($running) {
        Write-Host "Tunnel Aktif (PID: $($running.Id -join ', '))" -ForegroundColor Green
    } else {
        Write-Host "Tunnel Tidak Aktif" -ForegroundColor Yellow
    }
    exit 0
}

if ($Quick) {
    Start-QuickTunnel -Target $TargetUrl
    exit 0
}

# Default: Show Interactive Menu with countdown
Show-Menu
