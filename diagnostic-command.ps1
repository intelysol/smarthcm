Write-Host "===== PROJECT ====="
Get-ChildItem -Force | Select-Object Name, Mode

Write-Host "`n===== GIT ====="
git status --short
git branch --show-current
git log -5 --oneline

Write-Host "`n===== PHP ====="
php -v

Write-Host "`n===== COMPOSER ====="
composer --version

Write-Host "`n===== NODE ====="
node -v

Write-Host "`n===== NPM ====="
npm -v

Write-Host "`n===== LARAVEL ====="
php artisan --version

Write-Host "`n===== ROUTES ====="
php artisan route:list --except-vendor

Write-Host "`n===== MIGRATIONS ====="
php artisan migrate:status

Write-Host "`n===== PACKAGES ====="
composer show

Write-Host "`n===== NPM PACKAGES ====="
npm list --depth=0