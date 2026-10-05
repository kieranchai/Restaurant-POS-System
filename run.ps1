# ============================================================================
#  Ember POS - one-command launcher (Windows / PowerShell)
#
#  Downloads a small self-contained PHP runtime into .\.runtime on first run
#  (no system install needed), then serves the app. Uses SQLite, so there is
#  no database server to set up - the DB is created and seeded automatically.
#
#  Usage:   right-click > "Run with PowerShell", or:  ./run.ps1
#  If blocked:  powershell -ExecutionPolicy Bypass -File run.ps1
# ============================================================================
$ErrorActionPreference = "Stop"
Set-Location -Path $PSScriptRoot

$PhpVersion = "8.4.23"
$Port = if ($env:PORT) { $env:PORT } else { "8000" }
$RuntimeDir = ".runtime"
$PhpBin = Join-Path $RuntimeDir "php.exe"

if (-not (Test-Path $PhpBin)) {
    Write-Host "Downloading portable PHP $PhpVersion (Windows x64)..."
    New-Item -ItemType Directory -Force -Path $RuntimeDir | Out-Null
    $Url = "https://dl.static-php.dev/static-php-cli/common/php-$PhpVersion-cli-win.zip"
    $Zip = Join-Path $RuntimeDir "php.zip"
    Invoke-WebRequest -Uri $Url -OutFile $Zip
    Expand-Archive -Path $Zip -DestinationPath $RuntimeDir -Force
    Remove-Item $Zip
    Write-Host "PHP runtime ready."
}

$AppUrl = "http://localhost:$Port"
Write-Host ""
Write-Host "  Ember POS is running at  $AppUrl"
Write-Host "  Staff logins:  admin / admin1   -   staff / johnlogin"
Write-Host "  Press Ctrl+C to stop."
Write-Host ""

Start-Process $AppUrl
& $PhpBin -S "localhost:$Port" -t .
