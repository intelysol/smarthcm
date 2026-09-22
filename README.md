# Flow Enterprise Platform (Smart HCM)

> **Enterprise-Grade Human Capital Management, Multi-Tenant SaaS, and Business Operations Platform**

The **Flow Enterprise Platform (FEP)** is an enterprise application platform evolved from Smart HCM. It combines modular HCM domains (workforce, time & attendance, payroll, benefits, performance, recruitment) with high-availability cloud architecture: multi-tenant data isolation, observability, automated disaster recovery, RBAC access control, data lifecycle management, compliance governance, and an in-app documentation center.

---

## ⚡ Quick Start (5 Minutes)

### 1. Prerequisites
- **PHP**: 8.2+ (PHP 8.3 recommended)
- **Composer**: 2.x
- **MySQL / MariaDB**: MySQL 8.0+ or MariaDB 10.5+
- **Redis**: 6.0+ (Queues, Caching, Sessions)
- **Node.js**: 18+ (Node 20 LTS recommended)

### 2. Installation & Setup
```bash
# Clone the repository
git clone https://github.com/intelysol/smarthcm.git
cd smarthcm

# Install PHP and Node dependencies
composer install
npm install && npm run build

# Configure environment
cp .env.example .env
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed the demo environment & 5 personas
php artisan app:setup-demo

# Start the application server
php artisan serve
```

Navigate to `http://localhost:8000` to access the login portal.

---

## 👥 Demo Personas & Credentials

The platform includes 5 pre-configured demo personas ready for immediate end-to-end evaluation:

| Persona | Role | Email | Password | Initial Redirect Destination |
| :--- | :--- | :--- | :--- | :--- |
| **Platform Super Admin** | Platform Super Admin | `superadmin@example.test` | `Demo1234!@#$` | `/platform` (Global SaaS Control Plane) |
| **Tenant Admin** | Tenant Administrator | `admin@example.test` | `Demo1234!@#$` | `/admin/dashboard` (Organization Management) |
| **HR Administrator** | HR Director | `hr@example.test` | `Demo1234!@#$` | `/hr/dashboard` (HCM Operations Center) |
| **People Manager** | Engineering Manager | `manager@example.test` | `Demo1234!@#$` | `/manager/workbench` (Team Management) |
| **Employee** | Senior Software Engineer | `employee@example.test` | `Demo1234!@#$` | `/portal` (Self-Service Portal) |

> [!NOTE]
> In local and staging environments, the `/login` page features **one-click demo login buttons** for instant role switching.

To customize the default password for seeded accounts, set `DEMO_USER_PASSWORD` in your `.env` file before executing `php artisan app:setup-demo`.

---

## 🛡️ Production Safety Safeguards

The platform is engineered with strict production defenses:
- **Seeding Guard**: `DemoEnvironmentSeeder` automatically halts and throws an exception if `APP_ENV=production` unless `SEED_DEMO_USERS=true` is explicitly configured.
- **Reset Guard**: The `php artisan app:reset-demo` command is permanently locked in production environments.
- **Tenant Isolation**: Eloquent global scopes and middleware strictly enforce tenant boundaries (`WHERE tenant_id = ?`) across all queries.

---

## 🛠️ Essential Artisan Commands

| Command | Description |
| :--- | :--- |
| `php artisan app:setup-demo` | Seeds or updates the demo tenant, company, departments, positions, and 5 demo accounts. |
| `php artisan app:reset-demo --force` | Completely flushes and re-seeds the demo environment (dev/staging only). |
| `php artisan horizon` | Starts the Redis queue monitoring workers and dashboard. |
| `php artisan app:health:check` | Executes platform health checks across DB, Redis, Horizon, and Storage. |

---

## 📚 Documentation & Guides

Comprehensive documentation is available both directly in markdown files and within the interactive in-app Help Center at `/help`:

### Getting Started
- [Installation & Environment Setup](docs/getting-started/installation.md)
- [5-Minute Quick Start Guide](docs/getting-started/quick-start.md)
- [Demo Accounts & Credentials](docs/getting-started/demo-accounts.md)

### Administration & Operations
- [Platform Super Admin Guide](docs/administration/super-admin-guide.md)
- [Tenant Administrator Guide](docs/administration/tenant-admin-guide.md)
- [User Management Architecture](docs/administration/user-management.md)
- [Roles & Permissions (RBAC)](docs/administration/roles-and-permissions.md)
- [Tenant Management & Isolation](docs/administration/tenant-management.md)

### HCM & Workforce Workspaces
- [HR Administrator Guide](docs/hcm/hr-admin-guide.md)
- [People Manager Guide](docs/hcm/manager-guide.md)
- [Employee Self-Service Guide](docs/hcm/employee-guide.md)

### Help, FAQ & Diagnostics
- [Frequently Asked Questions (FAQ)](docs/help/faq.md)
- [Troubleshooting & Diagnostics](docs/help/troubleshooting.md)
- [Epic 2.80 Specification](docs/epics/EPIC_2_80_ADMIN_ACCESS_AND_HELP.md)

---

## 🏛️ Architecture & Governance

The mandatory [Master Development Constitution](docs/architecture/master-development-constitution.md) governs all architectural boundaries, and the [FEP Architecture Contract](docs/architecture/flow-enterprise-platform.md) establishes rules for core platform extensions.

---

## 📄 License

The Flow Enterprise Platform is open-source software licensed under the [MIT License](LICENSE).
