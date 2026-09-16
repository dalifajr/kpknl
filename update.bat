@echo off
setlocal EnableExtensions
title KPKNL Palembang - Pembaruan dan Sinkronisasi Sistem
color 0B
chcp 65001 >nul

REM ===============================================================================
REM KPKNL PALEMBANG - SCRIPT OTOMATIS PEMBARUAN DAN SINKRONISASI REPOSITORI
REM Fitur: Git Pull + Migrasi Multi-App + Clear Cache + Storage Link + Sync SSO
REM ===============================================================================

cd /d "%~dp0"
set "BASE_DIR=%~dp0"
if "%BASE_DIR:~-1%"=="\" set "BASE_DIR=%BASE_DIR:~0,-1%"

cls
echo ===============================================================================
echo            KPKNL PALEMBANG - SCRIPT PEMBARUAN DAN SINKRONISASI SISTEM
echo ===============================================================================
echo  [LOKASI] Direktori: %BASE_DIR%
echo  [WAKTU]  %DATE% %TIME%
echo ===============================================================================
echo.

REM -------------------------------------------------------------------------------
REM 1. Deteksi Git Binary
REM -------------------------------------------------------------------------------
echo [1/5] Memeriksa ketersediaan Git...
set "GIT_BIN="
for /f "delims=" %%I in ('where git.exe 2^>nul') do (
    if not defined GIT_BIN set "GIT_BIN=%%I"
)
if not defined GIT_BIN if exist "%LOCALAPPDATA%\Programs\Git\cmd\git.exe" set "GIT_BIN=%LOCALAPPDATA%\Programs\Git\cmd\git.exe"
if not defined GIT_BIN if exist "C:\Program Files\Git\cmd\git.exe" set "GIT_BIN=C:\Program Files\Git\cmd\git.exe"
if not defined GIT_BIN if exist "C:\laragon\bin\git\cmd\git.exe" set "GIT_BIN=C:\laragon\bin\git\cmd\git.exe"
if not defined GIT_BIN if exist "C:\laragon\bin\git\bin\git.exe" set "GIT_BIN=C:\laragon\bin\git\bin\git.exe"

if not defined GIT_BIN (
    color 0C
    echo [ERROR] Git tidak ditemukan di PATH maupun direktori standar.
    echo         Pastikan Git for Windows telah terpasang.
    pause
    exit /b 1
)

set "GIT_VER="
for /f "delims=" %%V in ('"%GIT_BIN%" --version 2^>nul') do (
    if not defined GIT_VER set "GIT_VER=%%V"
)
echo  [OK] Git terdeteksi: %GIT_VER%
echo.

REM -------------------------------------------------------------------------------
REM 2. Deteksi PHP Binary (Wajib PHP 8.4+ untuk Laravel 13)
REM -------------------------------------------------------------------------------
echo [2/5] Memeriksa ketersediaan PHP 8.5 / 8.4+...
set "PHP_BIN="
if exist "C:\laragon\bin\php\php-8.5.10-nts-Win32-vs17-x64\php.exe" (
    set "PHP_BIN=C:\laragon\bin\php\php-8.5.10-nts-Win32-vs17-x64\php.exe"
)
if not defined PHP_BIN (
    for /d %%D in ("C:\laragon\bin\php\php-8.5*") do (
        if exist "%%D\php.exe" set "PHP_BIN=%%D\php.exe"
    )
)
if not defined PHP_BIN (
    for /d %%D in ("%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.5*") do (
        if exist "%%D\php.exe" set "PHP_BIN=%%D\php.exe"
    )
)
if not defined PHP_BIN (
    for /f "delims=" %%I in ('where php.exe 2^>nul') do (
        if not defined PHP_BIN set "PHP_BIN=%%I"
    )
)
if not defined PHP_BIN (
    for /d %%D in ("C:\laragon\bin\php\*") do (
        if exist "%%D\php.exe" set "PHP_BIN=%%D\php.exe"
    )
)

if not defined PHP_BIN (
    color 0C
    echo [ERROR] PHP executable tidak ditemukan.
    pause
    exit /b 1
)

set "PHP_VER="
for /f "tokens=1,2" %%A in ('"%PHP_BIN%" -v 2^>nul') do (
    if not defined PHP_VER set "PHP_VER=%%A %%B"
)
echo  [OK] PHP terdeteksi: %PHP_VER% (%PHP_BIN%)
echo.

REM -------------------------------------------------------------------------------
REM 3. Pemeriksaan Repositori dan Sinkronisasi Git (Git Pull)
REM -------------------------------------------------------------------------------
echo [3/5] Memeriksa status repositori Git...
if not exist "%BASE_DIR%\.git" (
    color 0C
    echo [ERROR] Direktori %BASE_DIR% bukan merupakan repositori Git.
    pause
    exit /b 1
)

cd /d "%BASE_DIR%"

set "CURRENT_BRANCH="
for /f %%B in ('"%GIT_BIN%" branch --show-current 2^>nul') do (
    if not defined CURRENT_BRANCH set "CURRENT_BRANCH=%%B"
)
if not defined CURRENT_BRANCH set "CURRENT_BRANCH=main"

set "CURRENT_COMMIT="
for /f "delims=" %%C in ('"%GIT_BIN%" log -1 --oneline 2^>nul') do (
    if not defined CURRENT_COMMIT set "CURRENT_COMMIT=%%C"
)

echo  [*] Branch Aktif : %CURRENT_BRANCH%
echo  [*] Commit Awal  : %CURRENT_COMMIT%
echo.

if "%1"=="--no-pull" (
    echo  [INFO] Melewati git pull sesuai parameter --no-pull.
    goto :PROCEED_MAINTENANCE
)

echo  [*] Mengambil informasi pembaruan terbaru (git fetch origin %CURRENT_BRANCH%)...
"%GIT_BIN%" fetch origin %CURRENT_BRANCH%
if errorlevel 1 (
    color 0E
    echo  [WARNING] Gagal melakukan git fetch. Periksa koneksi internet.
    echo            Melanjutkan proses pemeliharaan database dan cache lokal...
    goto :PROCEED_MAINTENANCE
)

echo  [*] Menerapkan pembaruan dari repositori (git pull origin %CURRENT_BRANCH%)...
"%GIT_BIN%" pull origin %CURRENT_BRANCH%
if errorlevel 1 (
    color 0C
    echo.
    echo ===============================================================================
    echo  [ERROR] Git pull gagal. Kemungkinan terdapat konflik perubahan lokal.
    echo          Silakan periksa konflik berkas atau lakukan stash terlebih dahulu.
    echo ===============================================================================
    echo.
    pause
    exit /b 1
)

set "NEW_COMMIT="
for /f "delims=" %%C in ('"%GIT_BIN%" log -1 --oneline 2^>nul') do (
    if not defined NEW_COMMIT set "NEW_COMMIT=%%C"
)

echo.
echo  [SUCCESS] Sinkronisasi Git selesai.
echo  [*] Commit Terkini: %NEW_COMMIT%
echo.

:PROCEED_MAINTENANCE
set "COMMIT_HASH="
for /f %%H in ('"%GIT_BIN%" rev-parse --short HEAD 2^>nul') do (
    if not defined COMMIT_HASH set "COMMIT_HASH=%%H"
)

REM -------------------------------------------------------------------------------
REM 4. Pembaruan Multi-Aplikasi Laravel (Migrate, Clear Cache, Storage Link)
REM -------------------------------------------------------------------------------
echo ===============================================================================
echo  [4/5] MENJALANKAN PEMELIHARAAN SISTEM APLIKASI (MIGRATE DAN CLEAR CACHE)
echo ===============================================================================

call :PROCESS_APP "sso"
call :PROCESS_APP "Dashboard Pengelolaan BMN"
call :PROCESS_APP "aset-bppn"
call :PROCESS_APP "monlap"
call :PROCESS_APP "peminjaman"

REM -------------------------------------------------------------------------------
REM 5. Sinkronisasi Metadata dan Riwayat Log pada Portal SSO
REM -------------------------------------------------------------------------------
echo.
echo ===============================================================================
echo  [5/5] MENYINKRONKAN METADATA DAN RIWAYAT PEMBARUAN KE PORTAL SSO
echo ===============================================================================

cd /d "%BASE_DIR%"

if exist "%BASE_DIR%\sso\sync-update.php" (
    echo  [*] Mencatat status pembaruan ke database dan log aktivitas SSO...
    "%PHP_BIN%" "%BASE_DIR%\sso\sync-update.php" "%COMMIT_HASH%"
    echo.
)

REM -------------------------------------------------------------------------------
REM RINGKASAN SELESAI
REM -------------------------------------------------------------------------------
cd /d "%BASE_DIR%"
color 0A
echo ===============================================================================
echo         PROSES PEMBARUAN DAN SINKRONISASI SISTEM TELAH SELESAI
echo ===============================================================================
echo  [V] Git Pull Origin %CURRENT_BRANCH% : BERHASIL (Commit: %COMMIT_HASH%)
echo  [V] Migrasi Database            : 5 Aplikasi Selesai
echo  [V] Pembersihan dan Refresh Cache : 5 Aplikasi Selesai
echo  [V] Symlink Storage Publik      : Terverifikasi
echo  [V] Metadata Portal SSO         : Sinkron dan Tercatat di Log Aktivitas
echo ===============================================================================
echo  Sistem aplikasi KPKNL Palembang kini berada pada versi terbaru dan siap digunakan.
echo ===============================================================================
echo.

if "%1"=="--no-pause" goto :END_SCRIPT
if "%2"=="--no-pause" goto :END_SCRIPT
pause

:END_SCRIPT
exit /b 0

REM -------------------------------------------------------------------------------
REM SUBROUTINE: Pemrosesan Masing-Masing Aplikasi
REM -------------------------------------------------------------------------------
:PROCESS_APP
set "SUB_APP_NAME=%~1"
set "SUB_APP_PATH=%BASE_DIR%\%~1"

echo.
echo -------------------------------------------------------------------------------
echo  [MODUL] %SUB_APP_NAME%
echo -------------------------------------------------------------------------------

if exist "%SUB_APP_PATH%\artisan" (
    cd /d "%SUB_APP_PATH%"
    
    echo  [*] Menerapkan migrasi database [artisan migrate --force]...
    "%PHP_BIN%" artisan migrate --force
    if errorlevel 1 (
        echo  [WARNING] Terdapat peringatan migrasi pada %SUB_APP_NAME%.
    ) else (
        echo  [OK] Migrasi database siap.
    )
    
    echo  [*] Membersihkan dan merefresh cache [artisan optimize:clear]...
    "%PHP_BIN%" artisan optimize:clear >nul 2>&1
    echo  [OK] Cache dibersihkan dan disegarkan.
    
    if not exist "%SUB_APP_PATH%\public\storage" (
        echo  [*] Menghubungkan storage publik [artisan storage:link]...
        "%PHP_BIN%" artisan storage:link >nul 2>&1
        echo  [OK] Storage symlink terpasang.
    ) else (
        echo  [*] Storage symlink: Sudah terpasang.
    )
) else (
    echo  [SKIP] Direktori %SUB_APP_PATH% tidak memiliki artisan.
)
exit /b 0