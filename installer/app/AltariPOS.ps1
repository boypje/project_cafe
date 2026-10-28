# Pengendali Warung Altari POS versi terpasang.
#   mulai    : nyalakan database + server lalu buka aplikasi
#   latar    : nyalakan database + server tanpa membuka browser (autostart Windows)
#   berhenti : matikan server dan database
#   backup   : simpan salinan database ke folder backup
# Semua berjalan lokal di perangkat ini; tidak membutuhkan internet.
param(
    [ValidateSet('mulai', 'latar', 'berhenti', 'backup')]
    [string]$Aksi = 'mulai'
)

$ErrorActionPreference = 'Stop'
$Root    = Split-Path -Parent $MyInvocation.MyCommand.Path
$PortWeb = 8181
$PortDb  = 3307
$NamaDb  = 'jetstream_project'
$Url     = "http://localhost:$PortWeb"
$Judul   = 'Warung Altari'

$Php      = Join-Path $Root 'php\php.exe'
$Mysqld   = Join-Path $Root 'mysql\bin\mysqld.exe'
$MysqlBin = Join-Path $Root 'mysql\bin'
$App      = Join-Path $Root 'app'
$Logs     = Join-Path $Root 'logs'
$Backup   = Join-Path $Root 'backup'

function Pesan([string]$teks, [int]$ikon = 64) {
    (New-Object -ComObject WScript.Shell).Popup($teks, 0, $Judul, $ikon) | Out-Null
}

function Catat([string]$teks) {
    Add-Content -Path (Join-Path $Logs 'launcher.log') -Value ("[{0:yyyy-MM-dd HH:mm:ss}] {1}" -f (Get-Date), $teks)
}

function PortAktif([int]$port) {
    [bool](Get-NetTCPConnection -State Listen -LocalPort $port -ErrorAction SilentlyContinue)
}

function TungguPort([int]$port, [int]$detik) {
    for ($i = 0; $i -lt $detik * 2; $i++) {
        if (PortAktif $port) { return $true }
        Start-Sleep -Milliseconds 500
    }
    return $false
}

# Konfigurasi ditulis ulang setiap start agar folder instalasi boleh dipindah.
function TulisKonfigurasi {
    foreach ($d in $Logs, $Backup, (Join-Path $Root 'tmp')) { New-Item -ItemType Directory -Force -Path $d | Out-Null }
    $r = $Root -replace '\\', '/'

    @"
[mysqld]
basedir=$r/mysql
datadir=$r/data
port=$PortDb
bind-address=127.0.0.1
mysqlx=0
log-error=$r/logs/mysql.log
tmpdir=$r/tmp
innodb_buffer_pool_size=128M
max_connections=50
"@ | Set-Content -Encoding ASCII (Join-Path $Root 'mysql\my.ini')

    @"
extension_dir="$Root\php\ext"
extension=curl
extension=fileinfo
extension=gd
extension=intl
extension=mbstring
extension=openssl
extension=pdo_mysql
extension=mysqli
zend_extension=opcache
opcache.enable=1
opcache.enable_cli=1
memory_limit=256M
max_execution_time=120
upload_max_filesize=20M
post_max_size=20M
date.timezone=Asia/Jakarta
display_errors=Off
log_errors=On
error_log="$Root\logs\php.log"
sys_temp_dir="$Root\tmp"
"@ | Set-Content -Encoding ASCII (Join-Path $Root 'php\php.ini')
}

function NyalakanDatabase {
    if (PortAktif $PortDb) { return }
    Start-Process -FilePath $Mysqld -ArgumentList "--defaults-file=`"$Root\mysql\my.ini`"" -WindowStyle Hidden
    if (-not (TungguPort $PortDb 60)) { throw "Database tidak mau menyala. Lihat $Logs\mysql.log" }
}

function NyalakanServer {
    if (PortAktif $PortWeb) { return }
    # Migrasi otomatis supaya installer versi baru langsung memperbarui struktur tabel.
    & $Php -c "$Root\php\php.ini" "$App\artisan" migrate --force --no-interaction *>> (Join-Path $Logs 'migrate.log')
    $router = "$App\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php"
    Start-Process -FilePath $Php -WorkingDirectory "$App\public" -WindowStyle Hidden `
        -ArgumentList '-c', "`"$Root\php\php.ini`"", '-S', "127.0.0.1:$PortWeb", '-t', "`"$App\public`"", "`"$router`""
    if (-not (TungguPort $PortWeb 30)) { throw "Server aplikasi tidak mau menyala di port $PortWeb." }
}

function BukaAplikasi {
    $peramban = @(
        'HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\msedge.exe',
        'HKLM:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe',
        'HKCU:\SOFTWARE\Microsoft\Windows\CurrentVersion\App Paths\chrome.exe'
    ) | ForEach-Object { (Get-ItemProperty $_ -ErrorAction SilentlyContinue).'(default)' } |
        Where-Object { $_ -and (Test-Path $_) } | Select-Object -First 1

    if ($peramban) {
        # Mode aplikasi: jendela sendiri tanpa tab dan address bar.
        Start-Process -FilePath $peramban -ArgumentList "--app=$Url", '--start-maximized'
    } else {
        Start-Process $Url
    }
}

function BuatBackup([switch]$Diam) {
    NyalakanDatabase
    $file = Join-Path $Backup ("altari_{0:yyyy-MM-dd_HHmm}.sql" -f (Get-Date))
    & "$MysqlBin\mysqldump.exe" --host=127.0.0.1 "--port=$PortDb" -uroot --single-transaction --routines --default-character-set=utf8mb4 $NamaDb "--result-file=$file"
    if ($LASTEXITCODE -ne 0) { throw 'Backup gagal dibuat.' }

    # Simpan 60 backup terakhir saja.
    Get-ChildItem $Backup -Filter 'altari_*.sql' | Sort-Object Name -Descending | Select-Object -Skip 60 | Remove-Item -Force
    if (-not $Diam) {
        Pesan "Backup tersimpan:`n$file`n`nSalin file ini ke flashdisk atau Google Drive secara berkala."
        Start-Process explorer.exe -ArgumentList "/select,`"$file`""
    }
}

# Backup otomatis sekali sehari saat aplikasi dinyalakan.
function BackupHarian {
    $hariIni = "altari_{0:yyyy-MM-dd}_*.sql" -f (Get-Date)
    if (-not (Get-ChildItem $Backup -Filter $hariIni -ErrorAction SilentlyContinue)) {
        try { BuatBackup -Diam } catch { Catat "Backup harian gagal: $_" }
    }
}

function Matikan {
    Get-Process php -ErrorAction SilentlyContinue | Where-Object { $_.Path -like "$Root\*" } | Stop-Process -Force
    if (PortAktif $PortDb) {
        & "$MysqlBin\mysqladmin.exe" --host=127.0.0.1 "--port=$PortDb" -uroot shutdown 2>$null
        for ($i = 0; $i -lt 60 -and (Get-Process mysqld -ErrorAction SilentlyContinue | Where-Object { $_.Path -like "$Root\*" }); $i++) {
            Start-Sleep -Milliseconds 500
        }
    }
}

try {
    switch ($Aksi) {
        'mulai'    { TulisKonfigurasi; NyalakanDatabase; NyalakanServer; BukaAplikasi; BackupHarian }
        'latar'    { TulisKonfigurasi; NyalakanDatabase; NyalakanServer; BackupHarian }
        'berhenti' { Matikan }
        'backup'   { TulisKonfigurasi; BuatBackup }
    }
} catch {
    try { Catat "$Aksi gagal: $_" } catch {}
    if ($Aksi -ne 'berhenti') { Pesan "Terjadi masalah:`n$_" 16 }
    exit 1
}
