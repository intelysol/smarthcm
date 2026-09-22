# SmartHCM Search Engine Optimization (SEO) Strategy

## 1. Overview
The SmartHCM public website is built for sustained organic discovery across traditional search engines (Google, Bing). Rather than relying on thin keyword farms or generic content, the site adheres to strict technical SEO hygiene, descriptive URL architecture, and intentional internal link graphs.

---

## 2. Technical SEO Checklist

| Criteria | Implementation Standard | Status |
| :--- | :--- | :--- |
| **Title Tags** | Unique, factual, brand-suffixed (`SmartHCM`) | PASS |
| **Meta Descriptions** | Natural search terminology, qualified value statements, 140–160 chars | PASS |
| **Canonical URLs** | Self-referencing `<link rel="canonical" href="...">` on every page | PASS |
| **Open Graph** | `og:title`, `og:description`, `og:url`, `og:type`, `og:image` | PASS |
| **Twitter Cards** | `twitter:card` set to `summary_large_image` | PASS |
| **Robots.txt** | Declares `Allow: /`, disallows authenticated areas, links sitemap | PASS |
| **XML Sitemap** | Dynamic `/sitemap.xml` listing all canonical, indexable public URLs | PASS |
| **Heading Hierarchy** | Single `<h1>` per page, logical `<h2>` & `<h3>` subsections | PASS |
| **Mobile-First UX** | Responsive layouts optimized from mobile (375px) to 4K displays | PASS |
| **Zero Duplicate Content** | No trailing slash duplication, parameter sprawl, or orphan pages | PASS |

---

## 3. Search Intent Mapping

| Search Intent | Canonical Route | Target Keywords |
| :--- | :--- | :--- |
| Core HCM Platform | `/` | human capital management software, enterprise HCM platform |
| Platform Architecture | `/platform` | integrated HCM platform, modular HR software architecture |
| Mobile GPS Attendance | `/mobile-attendance` | mobile GPS attendance, geofencing attendance, offline attendance app |
| Biometric Shift Tracking | `/attendance` | employee attendance software, shift rostering, timesheet software |
| Multi-Country Payroll | `/payroll` | enterprise payroll software, gross-to-net payroll, payroll compliance |
| Core Employee Records | `/core-hr` | core HR software, employee record management, HRIS system |
| Employee Self-Service | `/employee-self-service` | employee self service portal, ESS app, mobile leave requests |
| Workforce Management | `/workforce-management` | workforce management software, shift allocation, labor compliance |
| Workforce Intelligence | `/workforce-analytics` | workforce analytics software, people analytics, turnover forecasting |
| Solutions Directory | `/solutions` | HR digitization, enterprise HR transformation, multi-tenant SaaS |
| Industry Verticals | `/industries` | manufacturing shift software, healthcare rostering, retail attendance |

---

## 4. XML Sitemap Specification
The XML sitemap is dynamically generated at `/sitemap.xml` adhering to the `http://www.sitemaps.org/schemas/sitemap/0.9` protocol:
- `<loc>`: Absolute HTTPS canonical URL.
- `<lastmod>`: ISO 8601 timestamp.
- `<changefreq>`: `weekly` for core pages, `monthly` for legal/pricing.
- `<priority>`: `1.0` for homepage, `0.9` for platform and mobile attendance, `0.8` for modules and solutions, `0.5` for policy terms.

---

## 5. Robots.txt Specification
Accessible at `/robots.txt`:
```txt
User-agent: *
Allow: /
Disallow: /app
Disallow: /admin
Disallow: /hr
Disallow: /manager
Disallow: /employee
Disallow: /executive
Disallow: /operations
Disallow: /portal
Disallow: /storage/

Sitemap: http://localhost:8000/sitemap.xml
```
