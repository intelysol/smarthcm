# Flow HCM — Employee Document & Digital Personnel File Management

## 1. Executive Summary
Epic 2.30 implements an enterprise-grade **Digital Personnel File and Employee Document Management** orchestration system within Flow HCM.

It follows the strict platform architecture:
```text
HCM DOCUMENT EXPERIENCE (Digital Personnel Files, Categories, Requirements)
          ↓
HCM DOCUMENT METADATA (employee_documents, verification, expiration, requests)
          ↓
SHARED DOCUMENT MANAGEMENT (App\Domains\Documents\Models\Document, versions, storage)
          ↓
FILE STORAGE (Local, S3, MinIO)
```

---

## 2. System of Record Boundaries

- **HCM Employee Document Domain**:
  - Authoritative for: employee-document relationships, personnel file categorization, mandatory vs optional requirements, completeness score calculation, HR verification workflows, expiration reminders, document requests, digital acknowledgements, and HCM access security.
- **Shared Document Management Domain (`App\Domains\Documents`)**:
  - Authoritative for: raw physical file storage, version trees, file hashing (SHA-256 checksums), MIME validation, malware scanning, previews, and legal holds.
- **Core HR Master**:
  - Authoritative for employee identity, employment contracts, and organizational hierarchy.
- **Cross-Domain Producers**:
  - **Recruitment / Onboarding (Epic 2.26 & 2.27)**: Collects identity and contract files, creates initial document requirements.
  - **Lifecycle (Epic 2.28)**: Generates promotion and transfer notification letters referenced in personnel files.
  - **Offboarding (Epic 2.29)**: Produces relieving letters, service certificates, and clearance certificates.
  - **Payroll & Benefits**: Produces payslips, tax certificates, and benefit enrollment records.
  - **Employee Relations**: Holds confidential disciplinary and grievance evidence with strict segregation of duties.

---

## 3. Configurable Document Categories

The platform supports 22 configurable, tenant-aware document categories:
1. `PERSONAL`: Personal details, family records, emergency contact documents.
2. `IDENTITY`: Passports, CNIC/National ID cards, driving licenses.
3. `EMPLOYMENT`: Offer letters, appointment letters, confirmation letters.
4. `CONTRACT`: Employment contracts, service agreements, amendments.
5. `COMPENSATION`: Salary revision letters, compensation structures, payslips.
6. `TAX`: Annual tax withholding certificates, exemption proofs.
7. `BENEFITS`: Medical insurance cards, health plan enrollment forms.
8. `INSURANCE`: Group life insurance policies, accidental coverage details.
9. `RETIREMENT`: Gratuity, provident fund, and pension nomination documents.
10. `LEARNING`: University degrees, training diplomas, course completion certificates.
11. `CERTIFICATION`: Professional practice licenses, technical accreditations.
12. `PERFORMANCE`: Annual appraisal reviews, performance improvement plans.
13. `TALENT`: Succession readiness summaries, 9-box evaluations.
14. `ATTENDANCE`: Roster approvals, biometric exception forms.
15. `LEAVE`: Sabbatical agreements, medical leave certificates.
16. `EMPLOYEE_RELATIONS`: Disciplinary notices, grievance records (segregated).
17. `HR_SERVICE`: Employee verification letters, visa sponsorship requests.
18. `ONBOARDING`: New hire onboarding packets, signed policy acknowledgements.
19. `LIFECYCLE`: Promotion letters, transfer orders, secondment notices.
20. `OFFBOARDING`: Resignation letters, relieving letters, exit interviews.
21. `COMPLIANCE`: Work permits, legal affidavits, statutory clearances.
22. `OTHER`: General miscellaneous archived documentation.

---

## 4. Document Verification Lifecycle

```text
Employee Upload / Bulk Import
            ↓
    Status: SUBMITTED
Verification: PENDING
            ↓
     HR Review
     ┌──────┴──────┐
     ↓             ↓
[VERIFIED]    [REJECTED]
             Mandatory Reason Recorded
                   ↓
             Employee Replaces
                   ↓
            New Version Stored
                   ↓
        Verification Resets to PENDING
```

- Rejections require an explicit, mandatory explanation reason.
- Verifying or waiving a document automatically updates matching employee requirement records.

---

## 5. Expiration Management & Reminders

- The system tracks `issue_date` and `expiry_date`.
- `MonitorEmployeeDocumentExpirationJob` runs on a scheduled cadence.
- Monitors milestones: 90 days, 60 days, 30 days, 7 days, and expired.
- Prevents duplicate alerts via the `employee_document_expiration_events` table.
- Documents passing their expiration date automatically transition to `expired` status.

---

## 6. Document Completeness Score

A real-time operational metric:
$$\text{Completeness Score (\%)} = \left( \frac{\text{Verified Mandatory Documents}}{\text{Total Mandatory Requirements}} \right) \times 100$$
- Missing documents and overdue document requests are highlighted in the HR Document Center.

---

## 7. Security, Confidentiality & Access Policies

Confidentiality levels:
- `PUBLIC_TO_EMPLOYEE`: Visible to employee, manager, and HR.
- `EMPLOYEE_ONLY`: Visible to employee and HR; hidden from manager.
- `MANAGER`: Visible to manager and HR.
- `HR`: Visible exclusively to HR staff and administrators.
- `HR_CONFIDENTIAL`: Sensitive HR files; hidden from managers.
- `RESTRICTED`: High-sensitivity records requiring elevated permissions.
- `HIGHLY_RESTRICTED`: ER records and legal documents requiring explicit permissions.

Special access safeguards:
- **Employee Relations**: Hidden from general personnel file views; requires `employee_documents.view_er`.
- **Medical Documentation**: Masked from managers to protect health privacy; requires `employee_documents.view_medical`.
- **Compensation & Tax**: Masked from unauthorized users; requires `employee_documents.view_compensation`.
- **Downloads**: Files are never exposed via raw disk paths; access is governed by HMAC-signed temporary download routes.

---

## 8. Bulk Processing with Dry-Run Validation

- Asynchronous bulk upload wizard:
  1. Map employee identifiers (Code, Employee Number, Email).
  2. Dry-run validation calculates `valid_items`, `warning_items`, and `error_items` without mutating database records.
  3. Batch processing executes only valid records asynchronously via `ProcessBulkEmployeeDocumentsJob`.

---

## 9. AI Advisory Assistance & Safety Guardrails

- `is_advisory => true` across all AI recommendations.
- Capabilities:
  - Extract document numbers and expiration dates from filenames and text.
  - Suggest document categories and types.
  - Detect possible duplicate uploads for the same employee.
- **Strict Guardrails**: AI is programmatically blocked from approving documents, overriding human verification, determining document authenticity conclusively, or making employment or termination decisions.
