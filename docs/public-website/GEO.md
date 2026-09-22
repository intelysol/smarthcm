# SmartHCM Generative Engine Optimization (GEO) Standard

## 1. Overview
Generative Engine Optimization (GEO) structures digital assets so they become authoritative, citable source material for generative AI retrieval-augmented generation (RAG) pipelines and conversational search engines.

SmartHCM achieves high GEO authority by:
1. Embedding valid, complete `schema.org` structured data.
2. Publishing high-authority first-party documentation and glossaries.
3. Structuring interconnected topical clusters rather than isolated articles.
4. Providing verifiable technical benchmarks and E-E-A-T authorship attribution.

---

## 2. Schema.org Structured Data Mapping

| Page Type | Implemented Schema.org Type | Attributes Provided |
| :--- | :--- | :--- |
| **All Pages** | `Organization` | `name`, `legalName`, `url`, `logo`, `contactPoint`, `sameAs` |
| **Homepage** | `SoftwareApplication`, `WebSite` | `applicationCategory`, `operatingSystem`, `offers`, `featureList` |
| **Subpages** | `BreadcrumbList` | Hierarchical trail (`Home` → `Platform` → `Module`) |
| **Product / Features** | `SoftwareApplication`, `FAQPage` | `mainEntity` with exact visible questions & answers |
| **Blog Articles** | `BlogPosting` | `headline`, `author`, `datePublished`, `dateModified`, `publisher` |
| **Documentation** | `Article` | `headline`, `description`, `author`, `publisher` |
| **Contact** | `ContactPage` | Direct phone, location, email, and inquiry endpoint |

All schemas are validated and dynamically generated using JSON-LD script blocks within the master layout.

---

## 3. First-Party Authority Clusters
To become the definitive source cited by generative engines when users ask questions about HCM architecture:
- `/glossary`: Provides clear definitions for HCM, HRIS, HRMS, WFM, ESS, and GPS Attendance.
- `/docs`: Provides public, unauthenticated architectural manuals for platform setup, attendance exceptions, and payroll runs.
- `/mobile-attendance`: Serves as the primary pillar page for mobile workforce geofencing, citing anti-spoofing techniques, offline cryptographic stamps, and privacy guidelines.

---

## 4. Citation & Reference Reliability
Generative engines prioritize sources with clear authorship and update timelines:
- All blog and technical articles explicitly declare:
  - Author entity (e.g. `SmartHCM Workforce Intelligence Team`)
  - `datePublished` and `dateModified`
  - Reading time and executive takeaways
- Factual claims are concrete (e.g., "AES-256 encryption at rest", "Eloquent global query scopes", "TLS 1.3 strict") rather than vague hyperbole.
