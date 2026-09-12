# HCM Employee Personal Data Management, Emergency Contacts, Dependents, Addresses, Bank Details & Master Data Governance (Epic 2.32)

## 1. Executive Summary

Epic 2.32 introduces the enterprise-grade personal data governance layer for the Flow HCM platform. It establishes controlled personal data maintenance, effective-dating, verification workflows, field-level masking, data quality scoring, and advisory duplicate detection while respecting strict platform boundaries.

---

## 2. Core Architectural Boundaries

1. **Core HR Remains Authoritative Master**:
   - `Employee`, `Department`, `Position`, and `Employment` remain the source of truth for employment status, reporting lines, and org placement.
   - Updates to personal details (`first_name`, `last_name`, `date_of_birth`, `gender`, `personal_email`, `mobile`, addresses) in Epic 2.32 automatically synchronize with Core HR `Employee` records.

2. **Payroll Boundary for Bank Accounts**:
   - Payroll owns active bank accounts and disbursement payment configurations.
   - Epic 2.32 provides controlled **Bank Detail Change Requests** (`hcm_employee_bank_change_requests`), which are reviewed by Payroll administrators before being applied.

3. **Benefits Boundary for Dependents**:
   - Benefits owns benefit plan eligibility, policies, and enrollment.
   - Epic 2.32 maintains only basic family profiles (`hcm_employee_dependents`: spouse, children, parents) and coverage flags.

4. **Advisory Duplicate Detection**:
   - Automated duplicate merges are **strictly prohibited** by system rules.
   - Duplicate detection engine computes multi-factor heuristic confidence scores (exact National ID: 95%, email: 85%, name+DOB: 80%, mobile: 70%) for HR investigation.

5. **Field-Level Masking & Encryption at Rest**:
   - National Identification Numbers, Tax IDs, Passports, and Bank Account Numbers are encrypted at rest using Laravel's encryption.
   - Masking helpers ensure unauthorized users only see masked values (e.g., `35202********1`, `****5678`), while authorized HR administrators (`personal_data.view_sensitive`) can inspect unmasked data.

---

## 3. Database Schema (Tables)

| Table Name | Description | Key Attributes |
| :--- | :--- | :--- |
| `hcm_personal_data` | Supplementary personal info & demographics | `salutation`, `first_name`, `last_name`, `preferred_name`, `dob`, `gender`, `blood_group` |
| `hcm_employee_addresses` | Effective-dated addresses | `address_type`, `line_1`, `city`, `country`, `is_current`, `effective_from`, `effective_to` |
| `hcm_emergency_contacts` | Prioritized emergency contacts | `name`, `relationship`, `primary_phone`, `priority_order`, `is_primary` |
| `hcm_employee_dependents` | Family profiles | `relationship`, `first_name`, `last_name`, `dob`, `is_disabled`, `is_student` |
| `hcm_employee_identifiers` | Encrypted identification documents | `identifier_type`, `identifier_value` (enc), `masked_value`, `expiry_date`, `is_primary` |
| `hcm_employee_bank_change_requests` | Bank change requests for Payroll | `bank_name`, `account_title`, `account_number` (enc), `status`, `payroll_actioned_at` |
| `hcm_employee_data_change_requests` | Master data change requests | `request_number`, `category`, `status`, `effective_date`, `reason` |
| `hcm_employee_data_change_request_items`| Side-by-side proposed changes | `target_entity`, `field_name`, `old_value`, `new_value`, `status` |
| `hcm_employee_data_verifications` | Audit verification lifecycle | `verifiable_type`, `verifiable_id`, `verification_method`, `status` |
| `hcm_employee_data_quality_results` | Multi-dimensional quality scores | `completeness_score`, `validity_score`, `verification_score`, `freshness_score`, `overall` |
| `hcm_employee_data_quality_issues` | Specific data hygiene issues | `issue_code`, `severity`, `category`, `description`, `is_resolved` |
| `hcm_employee_data_bulk_batches` | Bulk upload batches | `batch_number`, `category`, `total_items`, `valid_items`, `error_items`, `status` |
| `hcm_employee_data_bulk_items` | Individual bulk rows | `employee_identifier`, `resolved_employee_id`, `payload`, `validation_errors` |

---

## 4. Quality Scoring Dimensions

1. **Completeness (40%)**: Assesses existence of essential personal details, active address, primary emergency contact, and primary national identifier.
2. **Validity (30%)**: Validates email syntax, working-age limits (16-90 years), and identifier expiration dates.
3. **Verification (20%)**: Proportions of identity documents and addresses formally verified by HR administrators.
4. **Freshness (10%)**: Measures data recency: updated within 12 months (100%), 12-24 months (70%), older than 24 months (40%).

---

## 5. API Endpoints

### Personal Data & Sub-resources
- `GET /api/v1/hcm/employees/{id}/personal-data` — Aggregate bundle.
- `PUT /api/v1/hcm/employees/{id}/personal-data` — Update personal data.
- `GET|POST /api/v1/hcm/employees/{id}/addresses` — Manage address records.
- `PUT|DELETE /api/v1/hcm/personal-data/addresses/{id}` — Edit/delete address.
- `GET|POST /api/v1/hcm/employees/{id}/emergency-contacts` — Manage contacts.
- `PUT|DELETE /api/v1/hcm/personal-data/emergency-contacts/{id}` — Edit/delete contact.
- `GET|POST /api/v1/hcm/employees/{id}/dependents` — Manage dependents.
- `PUT|DELETE /api/v1/hcm/personal-data/dependents/{id}` — Edit/delete dependent.
- `GET|POST /api/v1/hcm/employees/{id}/identifiers` — Manage identifiers.
- `PUT|DELETE /api/v1/hcm/personal-data/identifiers/{id}` — Edit/delete identifier.

### Change Requests & Payroll Bank Requests
- `GET|POST /api/v1/hcm/employees/{id}/bank-change-requests` — Employee bank requests.
- `GET /api/v1/hcm/bank-change-requests` — Pending bank requests for Payroll review.
- `POST /api/v1/hcm/bank-change-requests/{id}/review` — Approve/reject bank change.
- `GET|POST /api/v1/hcm/employees/{id}/personal-data-change-requests` — Submit change request.
- `GET /api/v1/hcm/personal-data-change-requests` — List change requests.
- `POST /api/v1/hcm/personal-data-change-requests/{id}/review` — Approve/reject change request.

### Quality, Duplicates & Bulk Operations
- `GET /api/v1/hcm/employees/{id}/data-quality` — Employee quality scores & issues.
- `POST /api/v1/hcm/employees/{id}/data-quality/recalculate` — Recalculate score.
- `GET /api/v1/hcm/data-quality/tenant-summary` — Tenant quality summary.
- `GET /api/v1/hcm/personal-data/duplicates` — Advisory duplicate candidates.
- `POST /api/v1/hcm/personal-data/bulk/upload` — Dry-run validation / upload.
- `POST /api/v1/hcm/personal-data/bulk/batches/{id}/process` — Process validated batch.
