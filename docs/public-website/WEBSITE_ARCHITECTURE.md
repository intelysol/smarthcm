# SmartHCM Public Marketing Website Architecture

## 1. Executive Summary
The SmartHCM public marketing website is an enterprise-grade, SEO, AIO, and GEO optimized web presence built to clearly articulate the value of SmartHCM, showcase human capital management capabilities, drive enterprise lead generation, and provide authoritative public documentation.

The architecture enforces strict decoupling between public marketing / documentation layers and authenticated enterprise application workspaces.

---

## 2. Decoupling & Isolation Boundary

```
Public Internet / Search Crawlers / Prospective Customers
                         │
                         ▼
        ┌───────────────────────────────────┐
        │   SmartHCM Public Website (/)     │
        │   • Marketing & Value Prop        │
        │   • Mobile GPS Attendance Pillar  │
        │   • Feature Capability Pages      │
        │   • Solution Blueprints           │
        │   • Industry Workflows            │
        │   • Public Documentation (/docs)  │
        │   • Enterprise Lead Forms         │
        └─────────────────┬─────────────────┘
                          │ "Sign In" CTA (/login)
                          ▼
        ┌───────────────────────────────────┐
        │   SmartHCM Authenticated Portals  │
        │   • /platform/control-center      │
        │   • /admin/dashboard (Tenants)    │
        │   • /hr/dashboard (HR Operations) │
        │   • /manager/workbench            │
        │   • /portal (Self-Service)        │
        └───────────────────────────────────┘
```

### Architectural Guardrails:
1. **No Authenticated Route Exposure**: Guests attempting to access private application paths (`/platform/control-center`, `/admin/dashboard`, `/hr/dashboard`, `/manager/workbench`, `/employee/home`, `/executive/overview`, `/operations/dashboard`) are immediately redirected to `/login`.
2. **Dual-Role Route Handling**:
   - `/platform`: Unauthenticated visitors view the public platform architectural overview. Authenticated platform administrators are routed to the global control plane (`/platform/control-center`).
   - `/payroll`, `/recruitment`, `/onboarding`: Unauthenticated visitors view rich public feature capability pages; authenticated users access their respective operational dashboards.
3. **Robots.txt & Sitemap Protection**: Private application paths (`/app`, `/admin`, `/hr`, `/manager`, `/employee`, `/executive`, `/operations`, `/portal`, `/storage/`) are explicitly disallowed from web crawling and omitted from `sitemap.xml`.

---

## 3. Directory & Component Structure

```
smarthcm/
├── app/Domains/PublicWebsite/
│   ├── Http/Controllers/
│   │   └── PublicWebsiteController.php    # Public route dispatching & lead processing
│   ├── Models/
│   │   └── PublicLead.php                 # Eloquent model for inquiries
│   └── Services/
│       ├── PublicContentService.php       # Single source of truth for features, FAQs, solutions
│       └── PublicLeadService.php          # Validation, sanitation, and audit logging of leads
├── database/migrations/
│   └── 2026_09_22_000001_create_public_leads_table.php
├── resources/views/public/
│   ├── layouts/
│   │   └── marketing.blade.php            # Master responsive layout with JSON-LD & Open Graph
│   ├── home.blade.php                     # Complete enterprise homepage
│   ├── platform.blade.php                 # Integrated HCM platform page with roadmap statuses
│   ├── mobile-attendance.blade.php        # Dedicated Mobile GPS Attendance pillar
│   ├── feature.blade.php                  # Reusable feature template
│   ├── pricing.blade.php                  # Transparent enterprise editions
│   ├── about.blade.php                    # Corporate entity & mission
│   ├── security.blade.php                 # Factual enterprise trust controls
│   ├── privacy.blade.php                  # Transparent data policy
│   ├── terms.blade.php                    # Terms of service
│   ├── contact.blade.php                  # Validated contact form
│   ├── demo.blade.php                     # Validated enterprise demo form
│   ├── resources.blade.php                # Knowledge & resource directory
│   ├── glossary.blade.php                 # Entity-first HCM terminology index
│   ├── faq.blade.php                      # Categorized FAQ directory
│   ├── solutions/                         # Solutions directory & detail views
│   ├── industries/                        # Industry directory & detail views
│   ├── docs/                              # Public documentation center
│   └── blog/                              # High-value E-E-A-T articles
└── routes/
    ├── public_web.php                     # Declarative public marketing routing
    └── web.php                            # Root entry requiring public_web.php
```

---

## 4. Design System Compliance
The public website implements the centralized Epic 2.70 corporate UI tokens:
- **Navy Primary**: `#1E3A5F` (Header, prominent cards, primary accents)
- **Dark Navy**: `#142A44` (Footer, dark hero sections, code blocks)
- **Corporate Gold**: `#C9A227` (Action buttons, badges, key accents - 5 to 10%)
- **Light Gold**: `#F4E7B2` (Highlights, breadcrumb badges)
- **Light Background**: `#F7F9FC` (70 to 80% light neutral surfaces)
- **Text & Muted**: `#1F2937` / `#6B7280` (WCAG 2.2 AA compliant contrast)
- **Semantic State Colors**: `#16805C` (Success), `#B7791F` (Warning), `#C0392B` (Danger), `#2563EB` (Info)

---

## 5. Automated Verification
The public architecture is backed by an automated PHPUnit test suite in `tests/Feature/PublicWebsite/`:
- `PublicWebsiteRoutesTest.php`: Asserts status 200, `<title>`, `<meta name="description">`, and `<link rel="canonical">` across all public endpoints.
- `PublicWebsiteSeoAioGeoTest.php`: Asserts Schema.org structured data, Open Graph, sitemap, and robots.txt.
- `PublicWebsiteLeadGenerationTest.php`: Validates server-side CSRF, field validation, and database persistence in `public_leads`.
- `PublicWebsiteSeparationTest.php`: Confirms public pages remain accessible while private application paths require authentication.
