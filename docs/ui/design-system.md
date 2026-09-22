# SmartHCM Enterprise Design System Specification

> **Epic 2.70 — Enterprise UX Completion, Accessibility & Responsive Experience**  
> **Status:** Production Standard  
> **Target Standard:** WCAG 2.2 Level AA

---

## 1. Color System & Semantic Tokens

All UI components and workspace layouts must consume the centralized corporate tokens rather than arbitrary ad-hoc hex values.

### Corporate Palette Tokens

| Semantic Token | Hex Code | Purpose & Application |
| :--- | :--- | :--- |
| `--color-primary` (`brand-primary`) | `#1E3A5F` | Deep Corporate Navy — primary headers, brand bars, active tabs, primary buttons |
| `--color-primary-dark` (`brand-dark`) | `#142A44` | Dark Navy — button hover states, platform/executive dark shell backgrounds |
| `--color-accent` (`brand-gold`) | `#C9A227` | Corporate Gold — active highlights, badges, key metrics, focus indicators |
| `--color-accent-light` (`brand-gold-light`) | `#F4E7B2` | Light Gold — pill background, subtle highlight containers |
| `--color-background` (`brand-background`) | `#F7F9FC` | Off-white Cool Neutral — primary application canvas background |
| `--color-surface` (`brand-surface`) | `#FFFFFF` | Pure White — cards, tables, modal dialogs, flyouts |
| `--color-text` (`brand-text`) | `#1F2937` | Charcoal Slate — high-contrast primary typography |
| `--color-muted` (`brand-muted`) | `#6B7280` | Cool Gray — secondary text, helper descriptions, table headers |
| `--color-border` (`brand-border`) | `#E5E7EB` | Subtle Neutral — card borders, dividers, table row outlines |
| `--color-success` (`brand-success`) | `#16805C` | Semantic Green — approved states, active status, success confirmations |
| `--color-warning` (`brand-warning`) | `#B7791F` | Semantic Amber — pending reviews, system notices, draft states |
| `--color-danger` (`brand-danger`) | `#C0392B` | Semantic Red — destructive actions, rejected status, validation failures |
| `--color-info` (`brand-info`) | `#2563EB` | Semantic Blue — information notices, deep links, system tips |

---

## 2. Typography Scale

The typography scale uses a strict visual hierarchy configured via Tailwind v4 and `corporate-tokens.css`.

| Role | CSS Class | Size | Weight | Line Height | Tracking | Application |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| **Display** | `.text-display` | 36px (2.25rem) | 800 | 40px (2.5rem) | `-0.025em` | Hero KPI values, executive dashboard totals |
| **H1** | `.text-h1` | 30px (1.875rem) | 700 | 36px (2.25rem) | `-0.02em` | Page main titles |
| **H2** | `.text-h2` | 24px (1.5rem) | 700 | 32px (2.0rem) | `-0.015em` | Section headers, drawer titles |
| **H3** | `.text-h3` | 20px (1.25rem) | 600 | 28px (1.75rem) | `-0.01em` | Modal headers, card titles |
| **H4** | `.text-h4` | 18px (1.125rem) | 600 | 24px (1.5rem) | `normal` | Sub-section cards, widget headers |
| **Body** | `.text-body` | 14px (0.875rem) | 400 | 20px (1.25rem) | `normal` | Primary body text, descriptions |
| **Body Small**| `.text-body-sm` | 12px (0.75rem) | 400 | 16px (1.0rem) | `normal` | Secondary text, table content |
| **Caption** | `.text-caption` | 11px (0.6875rem) | 500 | 14px (0.875rem) | `+0.025em` | Timestamp labels, breadcrumb items |
| **Label** | `.text-label` | 12px (0.75rem) | 600 | 16px (1.0rem) | `+0.05em` | Form labels, table header categories (uppercase) |
| **Button** | `.text-btn` | 12px (0.75rem) | 600 | 16px (1.0rem) | `normal` | Button text, action links |
| **Table** | `.text-table` | 13px (0.8125rem) | 400 | 18px (1.125rem) | `normal` | Table cells |
| **Code** | `.text-code` | 13px (0.8125rem) | 400 | 18px (1.125rem) | `monospace`| Error reference IDs, tenant codes, API keys |

---

## 3. Standard Spacing Scale

Arbitrary margin and padding numbers have been standardized into 7 core spacing increments:

| Token | Dimension | CSS Variable | Common Usage |
| :--- | :--- | :--- | :--- |
| `xs` | 4px (0.25rem) | `--spacing-xs` | Gap between icon and label, tight badge padding |
| `sm` | 8px (0.5rem) | `--spacing-sm` | Form input vertical padding, button spacing |
| `md` | 16px (1.0rem) | `--spacing-md` | Card inner padding, section row spacing |
| `lg` | 24px (1.5rem) | `--spacing-lg` | Page container padding, modal body padding |
| `xl` | 32px (2.0rem) | `--spacing-xl` | Between major page sections, dashboard widgets |
| `2xl` | 48px (3.0rem) | `--spacing-2xl` | Empty state vertical spacing, error card gutters |
| `3xl` | 64px (4.0rem) | `--spacing-3xl` | Top-level auth screens, hero banners |

---

## 4. Application Shell Standard

Every workspace utilizes a unified shell architecture with role-appropriate visual theming:
- **Top Bar**: Workspace Switcher (`<x-shells.workspace-switcher>`) + Tenant Indicator + Global Search / Command Palette (`Ctrl+K`) + Notification Drawer Trigger + User Profile Menu.
- **Sub-Navigation**: Role-curated horizontal navigation strip with active indicator and count badges.
- **Main Canvas**: Accessible `<main id="main-content" tabindex="-1">` containing page body.
- **Skip Link**: Instant jump link (`<a href="#main-content" class="skip-link">`) for screen reader and keyboard power users.
- **Mobile Bottom Navigation**: Fixed bottom bar on viewports `< 768px` for Employee and Manager roles providing 1-touch navigation.

---

## 5. Enterprise UI Component Specifications

### 5.1 Page Header (`<x-ui.page-header>`)
Provides breadcrumb navigation, H1 title, contextual subtitle, and primary/secondary action triggers.
```blade
<x-ui.page-header 
    title="Employees"
    description="Manage and view employee profiles and employment records."
    :breadcrumbs="[
        ['label' => 'People', 'url' => '#'],
        ['label' => 'Employees', 'url' => route('portal.employees')]
    ]"
    :primaryAction="['label' => 'Add Employee', 'url' => '#add', 'icon' => 'fa-solid fa-user-plus']"
/>
```

### 5.2 Enterprise Data Table (`<x-ui.data-table>`)
Supports column sorting, live client-side filtering, bulk selection with multi-record actions bar, empty states, and auto-adapts to card view on mobile devices.
```blade
<x-ui.data-table 
    :columns="[
        ['key' => 'name', 'label' => 'Employee', 'sortable' => true],
        ['key' => 'department', 'label' => 'Department', 'sortable' => true],
        ['key' => 'status', 'label' => 'Status']
    ]"
    :rows="$employees"
    selectable="true"
    :bulkActions="[
        ['label' => 'Export Selected', 'action' => 'export', 'icon' => 'fa-solid fa-download'],
        ['label' => 'Deactivate', 'action' => 'deactivate', 'icon' => 'fa-solid fa-ban', 'danger' => true]
    ]"
>
    <!-- Row markup -->
</x-ui.data-table>
```

### 5.3 Status Badges (`<x-ui.status-badge>`)
Ensures uniform semantic color tokens, text casing, and icons across all domains:
- Active / Approved: Emerald green
- Pending / Review: Amber yellow
- Rejected / Inactive: Rose red
- Draft / Paused: Slate gray

### 5.4 Command Palette (`<x-ui.command-palette>`)
Activated globally via `Ctrl+K` or `Cmd+K`. Displays role-filtered navigation shortcuts and direct action triggers. Traps focus and handles `Escape` key dismissal.

### 5.5 Notification Drawer (`<x-ui.notification-drawer>`)
Slide-over drawer providing tabbed categorization (All, Tasks, Approvals, Security, System) with live unread counts and mark-read actions.

---

## 6. Accessibility & Keyboard Navigation (WCAG 2.2 AA)

1. **Focus Management**:
   - Every modal traps focus while open.
   - Closing a modal returns focus to the exact triggering element.
   - Validation summaries autofocus on page load with jump-to-field error links.
   - High-visibility focus ring (`outline: 2px solid #C9A227; outline-offset: 2px`).
2. **Landmarks & Semantic Structure**:
   - Every page contains standard landmark roles: `<header>`, `<nav>`, `<main id="main-content">`, `<footer>`.
   - Heading levels are strictly hierarchical (`h1` -> `h2` -> `h3`).
3. **Contrast Compliance**:
   - All text against background meets or exceeds WCAG 2.2 AA 4.5:1 contrast ratio.
