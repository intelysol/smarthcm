# Frequently Asked Questions (FAQ)

### 1. How do I log in to the demo environment?
Visit `/login` and use the quick-fill buttons or enter credentials for any of the 5 demo personas (e.g., `admin@example.test` with password `Demo1234!@#$`).

### 2. How can I change the default demo user password?
Set `DEMO_USER_PASSWORD="YourPasswordHere"` in your `.env` file, then run `php artisan app:setup-demo`.

### 3. Why did I get a 403 Forbidden error?
The platform enforces strict server-side workspace authorization. For instance, an employee account cannot access `/admin` or `/platform`. Check that your logged-in user possesses the required role.

### 4. Can a tenant admin view other tenants?
No. Multi-tenancy isolation guarantees that all queries, routes, and data models are scoped strictly to the authenticated tenant's `tenant_id`.

### 5. How do I reset the demo environment back to defaults?
Run `php artisan app:reset-demo --force`. This command removes demo records and re-seeds the demo tenant with pristine sample data. (Disabled in production).

### 6. Where are background queue jobs monitored?
Platform Super Admins can monitor queue workers, job throughput, and failed jobs under `/operations/queues` or through Laravel Horizon.

### 7. How do I create new users inside my organization?
Log in as Tenant Admin (`admin@example.test`), navigate to **Users & Access Control** (`/admin/users`), and click **Add New User**.

### 8. What happens when an account is deactivated?
The user's status is changed to `inactive`. The authentication controller will reject any subsequent login attempts, and active sessions will be terminated.