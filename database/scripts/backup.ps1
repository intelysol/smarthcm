<#
.SYNOPSIS
    Enterprise HCM Database Backup Utility (PowerShell)
.DESCRIPTION
    Creates a transactionally consistent mysqldump of the authoritative 985-table database,
    computes SHA-256 checksums, and tags metadata for disaster recovery.
#>

param (
    [string]$DbName = "smarthcm",
    [string]$DbHost = "127.0.0.1",
    [int]$DbPort = 3306,
    [string]$DbUser = "root",
    [string]$DbPassword = "",
    [string]$OutputDir = "storage/backups"
)

$ErrorActionPreference = "Stop"

$timestamp = Get-Date -Format "yyyyMMdd_HHmmss"
$resolvedOutputDir = Join-Path $PSScriptRoot "..\..\$OutputDir"

if (-not (Test-Path $resolvedOutputDir)) {
    New-Item -ItemType Directory -Path $resolvedOutputDir -Force | Out-Null
}

$dumpFileName = "${DbName}_backup_${timestamp}.sql"
$dumpFilePath = Join-Path $resolvedOutputDir $dumpFileName

Write-Host "====================================================" -ForegroundColor Cyan
Write-Host " Enterprise Platform Database Backup Utility" -ForegroundColor Cyan
Write-Host " Target Database: $DbName (${DbHost}:${DbPort})"
Write-Host " Destination:     $dumpFilePath"
Write-Host "===================================================="

# Locate mysqldump
$mysqldump = Get-Command "mysqldump" -ErrorAction SilentlyContinue
if (-not $mysqldump) {
    # Check Laragon path
    $laragonDumps = Get-ChildItem "C:\laragon\bin\mysql" -Recurse -Filter "mysqldump.exe" -ErrorAction SilentlyContinue
    if ($laragonDumps) {
        $mysqldump = $laragonDumps[0].FullName
    } else {
        throw "mysqldump utility not found in PATH or Laragon directory."
    }
} else {
    $mysqldump = $mysqldump.Source
}

Write-Host "Using mysqldump: $mysqldump"

$dumpArgs = @(
    "--host=$DbHost",
    "--port=$DbPort",
    "--user=$DbUser",
    "--single-transaction",
    "--quick",
    "--routines",
    "--triggers",
    "--default-character-set=utf8mb4",
    "--result-file=$dumpFilePath",
    $DbName
)

if ($DbPassword) {
    $dumpArgs = @("--password=$DbPassword") + $dumpArgs
}

& $mysqldump $dumpArgs

if (Test-Path $dumpFilePath) {
    $fileSize = (Get-Item $dumpFilePath).Length
    $hash = (Get-FileHash -Path $dumpFilePath -Algorithm SHA256).Hash
    $checksumPath = "${dumpFilePath}.sha256"
    "$hash  $dumpFileName" | Out-File -FilePath $checksumPath -Encoding ascii

    Write-Host "SUCCESS: Backup completed successfully!" -ForegroundColor Green
    Write-Host "Size:      $([math]::Round($fileSize / 1MB, 2)) MB"
    Write-Host "SHA-256:   $hash"
    Write-Host "Checksum:  $checksumPath"
} else {
    throw "Backup failed: Dump file was not created."
}
