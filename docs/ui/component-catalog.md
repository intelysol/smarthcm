# SmartHCM Enterprise Component Catalog

> **Epic 2.70 — Enterprise UX Completion, Accessibility & Responsive Experience**  
> **Status:** Production Registry  
> **Standard:** Blade + Alpine.js + Tailwind v4 Semantic Tokens

---

## 1. Universal UI Components

| Component | Location | Purpose | Variants / Props | Used By | Dependencies | Accessibility Status | Test Status |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Page Header** | `resources/views/components/ui/page-header.blade.php` | Renders standardized page title, breadcrumb navigation, and primary/secondary actions. | `title`, `description`, `breadcrumbs`, `primaryAction`, `secondaryAction` | All page views across 7 workspaces | FontAwesome | WCAG 2.2 AA (semantic nav, aria-current) | Automated & Verified |
| **Enterprise Data Table** | `resources/views/components/ui/data-table.blade.php` | Reusable data table engine with sorting, live search filter, bulk selection, mobile card view, and pagination. | `columns`, `rows`, `selectable`, `bulkActions`, `pagination`, `emptyTitle` | Directory, requests, attendance, queues, tenants | Alpine.js | WCAG 2.2 AA (role table/col, keyboard sort) | Automated & Verified |
| **Status Badge** | `resources/views/components/ui/status-badge.blade.php` | Semantic status pill with uniform colors and icons across all domains. | `status`, `size` (`xs`, `sm`, `lg`) | Tables, profile views, card headers, widgets | FontAwesome | WCAG 2.2 AA (color + icon indicator) | Automated & Verified |
| **Empty State** | `resources/views/components/ui/empty-state.blade.php` | Contextual illustration/icon with friendly message and call-to-action button when data sets are empty. | `icon`, `title`, `description`, `actionLabel`, `actionUrl` | Data tables, request lists, inbox views | FontAwesome | WCAG 2.2 AA (screen-reader descriptive) | Automated & Verified |
| **Error State** | `resources/views/components/ui/error-state.blade.php` | Recoverable error view with incident reference ID and retry CTA, zero credential/trace leaks. | `title`, `message`, `referenceId`, `retryUrl`, `retryClick` | Failed API loads, error boundaries | FontAwesome | WCAG 2.2 AA (aria-live alert) | Automated & Verified |
| **Loading Skeleton** | `resources/views/components/ui/loading-skeleton.blade.php` | Content-shaped animated skeleton loader replacing blank screens and generic spinners. | `type` (`table`, `cards`, `profile`, `list`), `rows`, `cols` | Lazy-loaded sections, dashboards, detail views | Tailwind CSS | WCAG 2.2 AA (role status, sr-only notice) | Automated & Verified |
| **Accessible Modal** | `resources/views/components/ui/modal.blade.php` | Standardized dialog with backdrop, focus trapping, Escape key dismiss, and return focus restoration. | `id`, `title`, `maxWidth` (`sm`, `md`, `lg`, `xl`, `2xl`) | Create/edit dialogs, detail viewers | Alpine.js | WCAG 2.2 AA (aria-modal, focus trap) | Automated & Verified |
| **Destructive Confirm**| `resources/views/components/ui/destructive-confirm.blade.php`| Consequence-explaining confirmation dialog for destructive actions. | `id`, `title`, `consequence`, `confirmText`, `actionUrl`, `method` | Deletion, deactivation, cancellations | Modal component | WCAG 2.2 AA (explicit warnings) | Automated & Verified |
| **Unsaved Changes** | `resources/views/components/ui/unsaved-changes.blade.php` | Form-dirty guard preventing accidental navigation loss. | `formId` | Complex forms, multi-step wizards | Alpine.js | WCAG 2.2 AA (role alertdialog) | Automated & Verified |
| **Form Validation Summary** | `resources/views/components/ui/form-validation-summary.blade.php` | Accessible error banner summarizing field and server errors with focus-on-render. | `errors` | Form pages across all modules | Blade / Laravel errors | WCAG 2.2 AA (role alert, autofocus) | Automated & Verified |
| **Command Palette** | `resources/views/components/ui/command-palette.blade.php` | Global search and quick-action launcher triggered by `Ctrl+K` / `Cmd+K`. | `currentWorkspace` | All 7 application shells | Alpine.js, WorkspaceType | WCAG 2.2 AA (keyboard trap, aria-modal) | Automated & Verified |
| **Notification Drawer** | `resources/views/components/ui/notification-drawer.blade.php` | Slide-over notification panel supporting categorized tabs and unread badges. | `unreadCount` | All 7 application shells | Alpine.js | WCAG 2.2 AA (role region, aria-label) | Automated & Verified |
| **Mobile Bottom Nav** | `resources/views/components/ui/mobile-nav.blade.php` | Fixed bottom bar on viewports `< 768px` providing 1-touch navigation for high-frequency actions. | `currentWorkspace` | Employee & Manager shells | FontAwesome | WCAG 2.2 AA (semantic nav) | Automated & Verified |

---

## 2. Application Shell Components

| Component | Location | Workspace Target | Landmarks Provided |
| :--- | :--- | :--- | :--- |
| **Shell Header** | `resources/views/components/shells/header.blade.php` | All Workspaces | `<header>`, `<nav>`, Switcher, Search trigger, Notifications trigger |
| **Workspace Switcher** | `resources/views/components/shells/workspace-switcher.blade.php` | Multi-role Users | Dropdown menu with authorized workspace routing |
| **Platform Shell** | `resources/views/shells/platform.blade.php` | Super Admin (`/platform/*`) | Skip link, `<header>`, `<nav>`, `<main>`, `<footer>`, Command Palette |
| **Tenant Shell** | `resources/views/shells/tenant.blade.php` | Tenant Admin (`/admin/*`) | Skip link, `<header>`, `<nav>`, `<main>`, `<footer>`, Command Palette |
| **HR Shell** | `resources/views/shells/hr.blade.php` | HR Operations (`/hr/*`) | Skip link, `<header>`, `<nav>`, `<main>`, `<footer>`, Command Palette |
| **Manager Shell** | `resources/views/shells/manager.blade.php` | Managers (`/manager/*`) | Skip link, `<header>`, `<nav>`, `<main>`, `<footer>`, Bottom Nav |
| **Employee Shell** | `resources/views/shells/employee.blade.php` | Employees (`/employee/*`, `/portal/*`)| Skip link, `<header>`, `<nav>`, `<main>`, `<footer>`, Bottom Nav |
| **Executive Shell** | `resources/views/shells/executive.blade.php` | Executives (`/executive/*`) | Skip link, `<header>`, `<nav>`, `<main>`, `<footer>`, Command Palette |
| **Operations Shell** | `resources/views/shells/operations.blade.php` | Operations (`/operations/*`) | Skip link, `<header>`, `<nav>`, `<main>`, `<footer>`, Command Palette |
