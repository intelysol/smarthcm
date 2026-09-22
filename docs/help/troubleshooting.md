# Troubleshooting & Diagnostics Guide

This guide details common operational and development issues and their verified remediation steps.

---

## 1. Database Connection Errors (`SQLSTATE[HY000] [2002]`)
**Cause**: The MySQL service is not running, or credentials in `.env` are mismatched.
**Fix**:
1. Check that MySQL/MariaDB service is active (`services.msc` or `systemctl status mysql`).
2. Verify `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.
3. Test connection: `php artisan db:monitor`.

---

## 2. 403 Forbidden Access on Workspaces
**Cause**: The logged-in user does not possess the required workspace role.
**Fix**:
1. Check the user's roles in `/admin/users` or via Tinker:
   ```bash
   php artisan tinker --execute="App\Models\User::where('email', 'user@example.test')->first()->roles;"
   ```
2. Re-assign the required role via seeder or admin portal.

---

## 3. Demo Seeder Fails in Production
**Cause**: Production safety guard blocks demo seeding unless explicitly authorized.
**Fix**:
If seeding a staging/demo instance with `APP_ENV=production`, explicitly set:
```ini
SEED_DEMO_USERS=true
```
in your `.env` file before executing `php artisan app:setup-demo`.

---

## 4. Assets Missing or Broken Styling (CSS/JS)
**Cause**: Vite assets have not been compiled.
**Fix**:
Run:
```bash
npm install
npm run build
```

---

## 5. Queue Jobs Not Processing
**Cause**: The background worker process is not running.
**Fix**:
Start the queue worker:
```bash
php artisan queue:work redis --tries=3
```
For production, use Horizon:
```bash
php artisan horizon
```