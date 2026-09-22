# Corporate UI Implementation Guidelines & Architecture Rulebook
**Product:** SmartHCM Enterprise Operating System  
**Version:** 2.61.0  
**Scope:** Frontend Architecture, Design System Guidelines & Component Rules  

---

## 1. Golden Rules of Corporate UI Architecture

### 1.1 The 70-20-10 Balance Rule
All views in the SmartHCM Enterprise Operating System must respect the visual weight distribution:
1. **70%–80% White & Light Neutrals (`#FFFFFF`, `#F7F9FC`):** Canvas backgrounds, surface cards, forms, modals, and data grids.
2. **15%–20% Corporate Navy (`#1E3A5F`, `#142A44`):** Primary navigation headers, primary CTA buttons, section headings, and key anchor icons.
3. **5%–10% Corporate Gold (`#C9A227`, `#F4E7B2`):** Intentional accents, active navigation indicators, key metrics, status callouts, and star ratings.

### 1.2 Absolute Ban on Dark Mode Overuse
* Operational command centers, case inboxes, forms, and tables MUST NOT be rendered as dark-slate boxes (`bg-slate-900`, `border-slate-800`).
* Enterprise users work under brightly lit office environments. White surfaces with subtle border delimiters (`#E5E7EB`) provide optimal contrast, readability, and reduce eye strain during extended operational shifts.

### 1.3 High Information Density Without Visual Clutter
* Enterprise HR operators process hundreds of cases and employee records daily.
* Do not waste vertical space with large decorative headers or hollow margins.
* Group related fields logically into white cards with clear 1px borders (`border-[#E5E7EB]`).
* Use badge tags with 10% opacity tints and matching 20% border outlines for compact, scannable data grids.

---

## 2. Color Palette & Token Reference

| Semantic Role | Token Variable | Hex Code | Purpose |
| :--- | :--- | :--- | :--- |
| **Primary Navy** | `--color-primary` | `#1E3A5F` | Primary top navbar, primary buttons, major headings |
| **Navy Dark** | `--color-primary-dark` | `#142A44` | Hover states, active pressed states |
| **Accent Gold** | `--color-accent` | `#C9A227` | Active indicators, metrics, priority star badges |
| **Gold Light** | `--color-accent-light` | `#F4E7B2` | Subtle notification highlights, icon badges |
| **Application Canvas** | `--color-background` | `#F7F9FC` | Page background (70-80% surface foundation) |
| **Surface** | `--color-surface` | `#FFFFFF` | Cards, modals, drawers, tables |
| **Primary Text** | `--color-text-primary` | `#1F2937` | High contrast body text, numbers, labels |
| **Secondary Text**| `--color-text-secondary`| `#6B7280` | Subtext, timestamps, metadata |
| **Border Neutral** | `--color-border` | `#E5E7EB` | Card dividers, input borders, table borders |
| **Semantic Success**| `--color-success` | `#16805C` | Resolved cases, SLAs met, verified status |
| **Semantic Warning**| `--color-warning` | `#B7791F` | Pending approvals, nearing SLA deadline |
| **Semantic Danger** | `--color-danger` | `#C0392B` | SLA Breached, urgent escalation, error alert |
| **Semantic Info** | `--color-info` | `#2563EB` | In progress, informational notifications |

---

## 3. UI Component Blueprint & Implementation Rules

### 3.1 Buttons
* **Primary Button:**
  ```html
  <button class="px-4 py-2 bg-[#1E3A5F] hover:bg-[#142A44] text-white rounded-xl text-xs font-semibold shadow-sm transition flex items-center space-x-2">
      <i class="fa-solid fa-plus text-[#C9A227]"></i>
      <span>Action Label</span>
  </button>
  ```
* **Secondary / Outline Button:**
  ```html
  <button class="px-3.5 py-2 bg-white hover:bg-[#F7F9FC] text-[#1F2937] rounded-xl text-xs font-semibold border border-[#E5E7EB] hover:border-[#1E3A5F] transition shadow-sm">
      Action Label
  </button>
  ```
* **Resolution / Success Button:**
  ```html
  <button class="px-4 py-2 bg-[#16805C] hover:bg-[#12684b] text-white rounded-xl text-xs font-semibold shadow-sm transition">
      Resolve Case
  </button>
  ```

### 3.2 KPI & Operational Metric Tiles
* Each KPI card sits on `#FFFFFF` with a top 4px indicator strip.
* Example:
  ```html
  <div class="p-4 rounded-xl bg-white border border-[#E5E7EB] shadow-sm border-t-4 border-t-[#1E3A5F]">
      <div class="text-[10px] uppercase font-bold text-[#6B7280] tracking-wider">Metric Title</div>
      <div class="text-2xl font-black text-[#1F2937] mt-1">1,248</div>
      <div class="text-[10px] text-[#2563EB] font-medium mt-0.5">+12 new today</div>
  </div>
  ```

### 3.3 Tables & Data Lists
* Wrap inside `<div class="bg-white rounded-xl border border-[#E5E7EB] shadow-sm overflow-hidden">`.
* Header row `<thead class="bg-[#F7F9FC] text-[10px] uppercase font-bold text-[#6B7280] border-b border-[#E5E7EB]">`.
* Alternating or hover rows `<tr class="hover:bg-[#F7F9FC] transition border-b border-[#E5E7EB]">`.
* SLA breached highlight: `<tr class="hover:bg-[#F7F9FC] bg-red-50/40">`.

---

## 4. Grounded AI Integration Patterns
* AI summaries, Copilot assistants, and deflection widgets must be clearly distinguishable from human agent responses.
* Use a distinct advisory badge: `Advisory Only` in Gold Light (`bg-[#F4E7B2]/60 text-[#B7791F]`).
* Grounded AI drawers must provide source references and clear next-action recommendations.
