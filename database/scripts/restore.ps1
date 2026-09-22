<#
.SYNOPSIS
    Enterprise HCM Database Restore & Verification Utility (PowerShell)
.DESCRIPTION
    Verifies SHA-256 integrity, creates target database, imports SQL dump, and verifies table counts.
#>

param (
    [Parameter(Mandatory=$true)]
    [string]$BackupFile,
    [string]$TargetDb = "smarthcm_restore_test",
    [string]$DbHost = "127.0.0.1",
    [int]$DbPort = 3306,
    [string]$DbUser = "root",
    [string]$DbPassword = "",
    [switch]$VerifyChecksum
)

$ErrorActionPreference = "Stop"

if (-not (Test-Path $BackupFile)) {
    throw "Backup file does not exist: $BackupFile"
}

# Locate mysql binary
$mysql = Get-Command "mysql" -ErrorAction SilentlyContinue
if (-not $mysql) {
    $laragonMysqls = Get-ChildItem "C:\laragon\bin\mysql" -Recurse -Filter "mysql.exe" -ErrorAction SilentlyContinue
    if ($laragonMysqls) {
        $mysql = $laragonMysqls[0].FullName
    } else {
        throw "mysql client binary not found in PATH or Laragon directory."
    }
} else {
    $mysql = $mysql.Source
}

Write-Host "====================================================" -ForegroundColor Cyan
Write-Host " Enterprise Platform Database Restore & Drill Utility" -ForegroundColor Cyan
Write-Host " Source File:     $BackupFile"
Write-Host " Target Database: $TargetDb (${DbHost}:${DbPort})"
Write-Host "===================================================="

if ($VerifyChecksum) {
    $checksumFile = "${BackupFile}.sha256"
    if (Test-Path $checksumFile) {
        Write-Host "Verifying SHA-256 checksum.."
        $expectedHash = (Get-Content $checksumFile).Split(" ")[0].Trim()
        $actualHash = (Get-FileHash -Path $BackupFile -Algorithm SHA256).Hash
        if ($expectedHash.ToUpper() -ne $actualHash.ToUpper()) {
            throw "INTEGRITY ERROR: SHA-256 hash mismatch! Expected: $expectedHash, Computed: $actualHash"
        }
        Write-Host "Checksum match: OK ($actualHash)" -ForegroundColor Green
    } else {
        Write-Warning "Checksum file not found: $checksumFile. Skipping checksum validation."
    }
}

# 1. Create target database
Write-Host "Preparing target database [$TargetDb].."
$baseArgs = @("--host=$DbHost", "--port=$DbPort", "--user=$DbUser")
if ($DbPassword) { $baseArgs = @("--password=$DbPassword") + $baseArgs }

& $mysql $baseArgs -e "CREATE DATABASE IF NOT EXISTS $TargetDb CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 2. Import dump
Write-Host "Importing schema and data into [$TargetDb].."
$escapedPath = $BackupFile.Replace("\", "/")
& $mysql $baseArgs $TargetDb -e "source $escapedPath"

# 3. Verify Table Count
$tableCountOutput = & $mysql $baseArgs $TargetDb -N -e "SELECT count(*) FROM information_schema.tables WHERE table_schema = '$TargetDb';"
$tableCount = [int]$tableCountOutput.Trim()

Write-Host "====================================================" -ForegroundColor Cyan
Write-Host " RESTORE VERIFICATION SUMMARY" -ForegroundColor Cyan
Write-Host " Database:     $TargetDb"
Write-Host " Tables Found: $tableCount"
Write-Host "===================================================="

if ($tableCount -ge 985) {
    Write-Host "SUCCESS: Restore verification passed with $tableCount tables!" -ForegroundColor Green
} else {
    Write-Warning "Restored table count ($tableCount) is less than authoritative baseline (985)."
}
