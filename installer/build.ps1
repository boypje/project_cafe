# Membangun installer Warung Altari POS (dist\AltariPOS-Setup.exe).
# Data awal diambil dari database Laragon saat skrip ini dijalankan.
#   powershell -ExecutionPolicy Bypass -File installer\build.ps1
param(
    [string]$LaragonPhp   = 'C:\laragon\bin\php\php-8.3.16-Win32-vs16-x64',
    [string]$LaragonMysql = 'C:\laragon\bin\mysql\mysql-8.4.3-winx64',
    [string]$SumberDb     = 'jetstream_project',
    [int]$SumberPort      = 3306,
    [string]$Iscc         = ''
)

if (-not $Iscc) {
    $Iscc = @(
        "$env:ProgramFiles\Inno Setup 7\ISCC.exe",
        "$env:ProgramFiles\Inno Setup 6\ISCC.exe",
        "${env:ProgramFiles(x86)}\Inno Setup 6\ISCC.exe",
        "$env:LOCALAPPDATA\Programs\Inno Setup 6\ISCC.exe"
    ) | Where-Object { Test-Path $_ } | Select-Object -First 1
}

$ErrorActionPreference = 'Stop'
$Proj  = Split-Path -Parent $PSScriptRoot
$Dist  = Join-Path $PSScriptRoot 'dist'
$Stage = Join-Path $Dist 'stage'
$PortBangun = 3399

function Langkah($t) { Write-Host "==> $t" -ForegroundColor Cyan }
function Robo($dari, $ke, [string[]]$extra) {
    robocopy $dari $ke /E /NFL /NDL /NJH /NJS /NP @extra | Out-Null
    if ($LASTEXITCODE -ge 8) { throw "robocopy gagal: $dari" }
    $global:LASTEXITCODE = 0
}

Langkah 'Membersihkan folder stage'
if (Test-Path $Stage) { Remove-Item $Stage -Recurse -Force }
New-Item -ItemType Directory -Force -Path $Stage | Out-Null

Langkah 'Menyalin PHP'
Robo $LaragonPhp "$Stage\php" @('/XF', 'php.ini*', '*.pdb', '/XD', "$LaragonPhp\dev", "$LaragonPhp\extras")

Langkah 'Menyalin MySQL'
Robo "$LaragonMysql\bin" "$Stage\mysql\bin" @('/XF', '*-debug.dll', '*.lib', '*.pl', '*.pdb', 'mysql_configurator.exe')
Robo "$LaragonMysql\share" "$Stage\mysql\share" @()

# Runtime Visual C++ disertakan lokal agar jalan di PC yang belum memasangnya.
foreach ($dll in 'vcruntime140.dll', 'vcruntime140_1.dll', 'msvcp140.dll', 'msvcp140_1.dll', 'msvcp140_2.dll', 'concrt140.dll') {
    foreach ($tujuan in "$Stage\php", "$Stage\mysql\bin") {
        if (-not (Test-Path "$tujuan\$dll")) { Copy-Item "$env:WINDIR\System32\$dll" $tujuan }
    }
}

Langkah 'Menyalin aplikasi'
Robo $Proj "$Stage\app" @(
    '/XJ',
    '/XD', "$Proj\.git", "$Proj\node_modules", "$Proj\tests", "$Proj\installer", "$Proj\.claude", "$Proj\public\AdminLTE-2",
           "$Proj\storage\logs", "$Proj\storage\framework\sessions", "$Proj\storage\framework\views", "$Proj\storage\framework\cache",
    '/XF', '.env', '.env.*', 'hot', '*.log', '.phpunit.result.cache'
)
Get-ChildItem "$Stage\app\bootstrap\cache" -Filter '*.php' | Remove-Item -Force
foreach ($d in 'logs', 'framework\sessions', 'framework\views', 'framework\cache\data') {
    New-Item -ItemType Directory -Force -Path "$Stage\app\storage\$d" | Out-Null
}
if (-not (Test-Path "$Stage\app\public\build\manifest.json")) { throw 'public\build belum ada. Jalankan npm run build dulu.' }

Langkah 'Menulis .env produksi'
$env0 = Get-Content "$Proj\.env" -Raw
$kunci = [regex]::Match($env0, '(?m)^APP_KEY=(.+)$').Groups[1].Value.Trim()
@"
APP_NAME="Warung ALTARI"
APP_ENV=production
APP_KEY=$kunci
APP_DEBUG=false
APP_URL=http://localhost:8181

LOG_CHANNEL=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=$SumberDb
DB_USERNAME=root
DB_PASSWORD=

BROADCAST_DRIVER=log
CACHE_DRIVER=file
FILESYSTEM_DISK=local
QUEUE_CONNECTION=sync
SESSION_DRIVER=database
SESSION_LIFETIME=720
MAIL_MAILER=log
"@ | Set-Content -Encoding UTF8 "$Stage\app\.env"

# vendor disalin apa adanya (composer.lock tidak cocok dengan PHP 8.3, tapi vendor terpasang sudah teruji).
Langkah 'Menyusun ulang cache paket Laravel'
& "$LaragonPhp\php.exe" "$Stage\app\artisan" package:discover --ansi | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'package:discover gagal' }

Langkah 'Membangun database awal dari data sekarang'
$dump = Join-Path $Dist 'data_awal.sql'
& "$LaragonMysql\bin\mysqldump.exe" --host=127.0.0.1 "--port=$SumberPort" -uroot --single-transaction --routines --default-character-set=utf8mb4 $SumberDb "--result-file=$dump"
if ($LASTEXITCODE -ne 0) { throw 'Gagal mengambil data dari MySQL Laragon (pastikan Laragon MySQL menyala).' }

$mysqld = "$Stage\mysql\bin\mysqld.exe"
$data = "$Stage\data"
& $mysqld --no-defaults --initialize-insecure "--basedir=$Stage\mysql" "--datadir=$data" 2>&1 | Out-Null
if ($LASTEXITCODE -ne 0) { throw 'Inisialisasi database gagal' }
$proses = Start-Process -FilePath $mysqld -PassThru -WindowStyle Hidden -ArgumentList `
    '--no-defaults', "--basedir=`"$Stage\mysql`"", "--datadir=`"$data`"", "--port=$PortBangun", '--bind-address=127.0.0.1', '--mysqlx=0'
for ($i = 0; $i -lt 120 -and -not (Get-NetTCPConnection -State Listen -LocalPort $PortBangun -ErrorAction SilentlyContinue); $i++) { Start-Sleep -Milliseconds 500 }

$mysql = "$Stage\mysql\bin\mysql.exe"
$sumberSql = $dump -replace '\\', '/'
& $mysql --host=127.0.0.1 "--port=$PortBangun" -uroot -e "CREATE DATABASE $SumberDb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
& $mysql --host=127.0.0.1 "--port=$PortBangun" -uroot --default-character-set=utf8mb4 $SumberDb -e "source $sumberSql"
if ($LASTEXITCODE -ne 0) { throw 'Import data awal gagal' }
& $mysql --host=127.0.0.1 "--port=$PortBangun" -uroot $SumberDb -e 'TRUNCATE TABLE sessions'
& $mysql --host=127.0.0.1 "--port=$PortBangun" -uroot -N -e "SELECT CONCAT('produk: ', COUNT(*)) FROM $SumberDb.produk; SELECT CONCAT('pengguna: ', COUNT(*)) FROM $SumberDb.users"
& "$Stage\mysql\bin\mysqladmin.exe" --host=127.0.0.1 "--port=$PortBangun" -uroot shutdown
$proses.WaitForExit(60000) | Out-Null
Remove-Item "$data\*.err", "$data\binlog.*" -ErrorAction SilentlyContinue

Langkah 'Menyalin skrip pengendali dan ikon'
Copy-Item "$PSScriptRoot\app\AltariPOS.ps1", "$PSScriptRoot\app\jalankan.vbs" $Stage
Add-Type -AssemblyName System.Drawing
$sumberIkon = [System.Drawing.Image]::FromFile("$Proj\public\img\altari.png")
$kecil = New-Object System.Drawing.Bitmap 256, 256
$g = [System.Drawing.Graphics]::FromImage($kecil)
$g.InterpolationMode = 'HighQualityBicubic'
$g.DrawImage($sumberIkon, 0, 0, 256, 256)
$ms = New-Object System.IO.MemoryStream
$kecil.Save($ms, [System.Drawing.Imaging.ImageFormat]::Png)
$png = $ms.ToArray()
$g.Dispose(); $kecil.Dispose(); $sumberIkon.Dispose()
# Format ICO berisi satu gambar PNG 256x256.
$ico = New-Object System.IO.MemoryStream
$w = New-Object System.IO.BinaryWriter $ico
$w.Write([uint16]0); $w.Write([uint16]1); $w.Write([uint16]1)
$w.Write([byte]0); $w.Write([byte]0); $w.Write([byte]0); $w.Write([byte]0)
$w.Write([uint16]1); $w.Write([uint16]32); $w.Write([uint32]$png.Length); $w.Write([uint32]22)
$w.Write($png); $w.Flush()
[System.IO.File]::WriteAllBytes("$Stage\altari.ico", $ico.ToArray())

Langkah 'Mengompilasi installer'
if (-not (Test-Path $Iscc)) { throw "Inno Setup tidak ditemukan di $Iscc" }
& $Iscc /Q "$PSScriptRoot\altari.iss"
if ($LASTEXITCODE -ne 0) { throw 'Kompilasi Inno Setup gagal' }
Get-Item "$Dist\AltariPOS-Setup.exe" | ForEach-Object { Write-Host ("Selesai: {0} ({1:N0} MB)" -f $_.FullName, ($_.Length / 1MB)) -ForegroundColor Green }
