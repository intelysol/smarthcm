# Corporate UI Design System Specification
**Product:** SmartHCM Enterprise Operating System  
**Standard:** Enterprise Corporate Visual Language Specification  
**Version:** 2.61.0  
**Status:** Approved & Implemented  

---

## 1. Executive Summary & Philosophy

The **SmartHCM Enterprise Corporate Design System** provides a cohesive, accessible, high-trust visual language tailored for Fortune 500 enterprise workforce operations, executive human resource leadership, and daily employee self-service. 

Enterprise HR and workforce platforms handle sensitive, high-impact transactions: payroll, compensation, grievance cases, investigations, and confidential organizational structures. Visual stability, high contrast, clean hierarchy, and restrained accentuation foster trust and reduce cognitive fatigue.

### The 70-20-10 Enterprise Balance Rule
To maintain focus and avoid sensory overload, all views strictly adhere to the enterprise color distribution formula:
* **70%–80% White & Light Neutral (`#FFFFFF` / `#F7F9FC`):** Canvas backgrounds, operational card surfaces, data tables, dialogs, and detail inspectors.
* **15%–20% Corporate Navy (`#1E3A5F` / `#142A44`):** Primary navigation headers, page titles, section landmarks, primary action buttons, and dominant key elements.
* **5%–10% Corporate Gold (`#C9A227` / `#F4E7B2`):** Intentional accents, active navigation indicators, progress meters, metric highlights, and key operational callouts.

---

## 2. Token Palette & Color Values

```css
:root {
    /* Base Corporate Palette */
    --color-primary: #1E3A5F;        /* Corporate Navy: Headers, nav, primary CTA */
    --color-primary-dark: #142A44;   /* Dark Navy: Hover states, deep elevation */
    --color-accent: #C9A227;         /* Corporate Gold: Selected tabs, metric accents */
    --color-accent-light: #F4E7B2;   /* Soft Gold: Subtle highlight backgrounds */

    /* Neutrals & Surfaces */
    --color-background: #F7F9FC;     /* Light neutral application background */
    --color-surface: #FFFFFF;        /* Crisp white cards, tables, modal surfaces */
    --color-text-primary: #1F2937;   /* High-contrast charcoal for primary typography */
    --color-text-secondary: #6B7280; /* Neutral gray for labels, metadata, captions */
    --color-border: #E5E7EB;         /* Subtle gray border for dividers and cards */

    /* Semantic Status Tokens */
    --color-success: #16805C;        /* Resolved, verified, on-track, healthy SLA */
    --color-warning: #B7791F;        /* At risk, pending action, waiting on employee */
    --color-danger: #C0392B;         /* SLA breached, urgent, critical escalations */
    --color-info: #2563EB;           /* In progress, new notice, informational state */
}
```

---

## 3. Typography Hierarchy

The typographic scale uses modern system sans-serif families (`Inter`, `-apple-system`, `BlinkMacSystemFont`, `Segoe UI`, `Roboto`) optimized for legibility across dense enterprise data grids.

| Level | Size | Weight | Line Height | Color | Usage |
| :--- | :--- | :--- | :--- | :--- | :--- |
| **Display Header** | `24px (1.5rem)` | Bold (700) | `1.25` | `#1F2937` | Command Center & Module Titles |
| **Section Header** | `16px (1.0rem)` | Bold (700) | `1.35` | `#1E3A5F` | Card headers, table headers, drawers |
| **Subhead / Group** | `13px (0.8125rem)`| SemiBold (600) | `1.4` | `#1E3A5F` | Subsection titles, fieldset legends |
| **Body Primary** | `14px (0.875rem)` | Regular (400) | `1.5` | `#1F2937` | Case descriptions, timeline bodies |
| **Body Secondary** | `12px (0.75rem)` | Regular (400) | `1.4` | `#6B7280` | Metadata, timestamps, table cells |
| **Micro Caption** | `10px - 11px` | Bold (700) | `1.2` | `#6B7280` | Status badges, KPI category labels |
| **Monospace / ID** | `12px (0.75rem)` | Bold (700) | `1.0` | `#1E3A5F` | Ticket numbers (`HR-20260912-001`), codes |

---

## 4. Component Patterns

### 4.1 Primary Navigation Header
* **Background:** Deep Corporate Navy (`#1E3A5F`).
* **Active Indicator:** Bottom 2px accent bar in Corporate Gold (`#C9A227`).
* **Hover State:** Dark Navy background (`#142A44`) with soft white text.
* **Logo / System Badge:** Bold white branding with gold edition pill (`Enterprise OS`).

### 4.2 Standard Corporate Card (`.card-corporate`)
* **Background:** Clean White (`#FFFFFF`).
* **Border:** 1px Solid Neutral (`#E5E7EB`).
* **Border Radius:** `16px (1rem)` for top-level cards, `12px` for nested panels.
* **Shadow:** `0 1px 3px 0 rgb(0 0 0 / 0.05)` (Subtle, non-distracting elevation).

### 4.3 Operational KPI Card (`.card-kpi`)
* **Base Surface:** Crisp White with a top accent indicator bar (4px thick):
  * **Navy (`#1E3A5F`):** Standard volume / throughput metrics.
  * **Success Green (`#16805C`):** SLA compliance, deflection rate.
  * **Danger Red (`#C0392B`):** Overdue cases, breaches.
  * **Gold (`#C9A227`):** Customer satisfaction (CSAT), quality index.
* **Metric Counter:** `24px (1.5rem)` extra bold font in `#1F2937`.
* **Label:** `10px - 11px` uppercase tracking-wider in `#6B7280`.

### 4.4 Buttons & Interactive Controls
* **Primary Action (`.btn-primary`):** 
  * Background: `#1E3A5F`, Hover: `#142A44`, Text: White (`#FFFFFF`).
  * Often accented with an icon in Corporate Gold (`#C9A227`).
* **Secondary / Outline (`.btn-secondary`):**
  * Background: `#FFFFFF`, Border: `1px solid #E5E7EB`, Text: `#1F2937`.
  * Hover: Background `#F7F9FC`, Border `#1E3A5F`.
* **Action Resolve / Success (`.btn-success`):**
  * Background: `#16805C`, Hover: `#12684b`, Text: White (`#FFFFFF`).
* **Critical / Destructive (`.btn-danger`):**
  * Background: `#C0392B`, Hover: `#962d22`, Text: White (`#FFFFFF`).

### 4.5 Data Grids & Inspection Tables
* **Table Wrapper:** White card with overflow scroll and rounded corners.
* **Table Header (`thead`):** Light background (`#F7F9FC`), bottom border (`#E5E7EB`), text `#6B7280`, uppercase 10px bold.
* **Table Rows (`tbody tr`):** White background, border-b (`#E5E7EB`), hover highlight (`#F7F9FC`).
* **At-Risk Highlights:** Subtle red tint (`bg-red-50/40`) with danger icon when SLA deadline is breached.

### 4.6 Status & Priority Badges
Badges use 10% opacity tints for backgrounds with saturated text and 20% opacity borders:
* **Resolved / Closed:** `bg-[#16805C]/10 text-[#16805C] border border-[#16805C]/20`
* **In Progress:** `bg-[#2563EB]/10 text-[#2563EB] border border-[#2563EB]/20`
* **Waiting on Employee:** `bg-[#B7791F]/10 text-[#B7791F] border border-[#B7791F]/20`
* **Critical / Urgent:** `bg-[#C0392B]/10 text-[#C0392B] border border-[#C0392B]/20`
* **Standard / Low:** `bg-[#6B7280]/10 text-[#6B7280] border border-[#6B7280]/20`

---

## 5. Accessibility & Compliance Guidelines
1. **WCAG 2.1 AA Contrast:** All text must meet minimum 4.5:1 contrast against its background. Corporate Navy `#1E3A5F` against `#FFFFFF` achieves 9.8:1 contrast. Primary charcoal `#1F2937` against `#FFFFFF` achieves 12.6:1 contrast.
2. **Color Blindness Differentiation:** Status is NEVER communicated through color alone. Every badge, table row, and SLA counter pairs color with clear text labels or distinct icons (`Breached`, `Urgent`, `Resolved`).
3. **Responsive Scaling:** Responsive grid breakpoints (`grid-cols-1 sm:grid-cols-2 lg:grid-cols-6`) guarantee usability across tablet and mobile form factors without truncated metadata.
