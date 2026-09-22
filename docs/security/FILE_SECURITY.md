# Enterprise File & Document Security

## 1. Storage Architecture & Privacy Boundaries

All employee documents, medical records, contracts, identity papers, and payroll slips are classified as **Confidential** or **Restricted/PII**.

### Storage Rules:
1. **Zero Direct Web Access:** No document is stored in the public web root (`public/storage` or public web directories). All files reside in private storage (`storage/app/private/` or private cloud storage buckets).
2. **Tenant Partitioning:** Physical paths strictly isolate tenants:
   `tenants/{tenant_id}/employees/{employee_id}/{document_uuid}.enc`
3. **Encrypted at Rest:** Files may be encrypted at rest using envelope encryption or KMS-managed customer keys before write.

---

## 2. Inbound File Upload Validation Controls

Every file upload undergoes strict sequential multi-point inspection before being persisted:

1. **Extension Allowlisting:** Only approved extensions are accepted (`pdf`, `png`, `jpg`, `jpeg`, `docx`, `xlsx`, `csv`). Executable extensions (`php`, `exe`, `sh`, `bat`, `cmd`, `js`, `vbs`, `html`, `svg`) are strictly rejected.
2. **MIME Type Sniffing & Magic Byte Verification:** The system inspects file magic bytes using `finfo` / PHP `mime_content_type` rather than trusting the client-supplied `Content-Type` header.
3. **File Size Quotas:**
   - Standard documents (PDF, DOCX): Max 10 MB per file.
   - Profile photos (JPEG, PNG): Max 2 MB per file.
   - Bulk import sheets: Max 25 MB per file.
4. **Filename Sanitization:** Uploaded filenames are stripped of path traversal sequences (`../`, `..\`, null bytes, control characters). The file is saved under a cryptographically generated UUID filename; the original sanitized name is stored as metadata only.
5. **Malware / Antivirus Hook:** The upload pipeline provides a synchronous/asynchronous scanning hook (`VirusScanService`) to integrate ClamAV or cloud scanning APIs prior to marking files `VERIFIED`.

---

## 3. Outbound File Download & Preview Controls

1. **Mandatory Authorization Evaluation:**
   Before a file stream or pre-signed URL is generated, `EmployeeDocumentSecurityService::canView()` is evaluated:
   - Tenant equality (`user->tenant_id === doc->tenant_id`).
   - Self-service ownership (`user->employee_id === doc->employee_id && doc->employee_visible`).
   - Reporting manager relationship (`user->employee_id === employee->reporting_manager_id && doc->manager_visible && doc->confidentiality_level <= INTERNAL`).
   - Special category permissions (`employee_documents.view_er`, `employee_documents.view_medical`, `employee_documents.view_compensation`).
2. **Ephemeral Signed URLs:** When pre-signed URLs are utilized (e.g. S3 downloads), expiration is capped at **5 minutes**, tied to the recipient user's IP/tenant context.
3. **Anti-Sniffing & Download Headers:**
   All download responses include:
   - `Content-Disposition: attachment; filename="sanitized_name.pdf"`
   - `X-Content-Type-Options: nosniff`
   - `Cache-Control: private, no-cache, no-store, must-revalidate`

---

## 4. Export Security & CSV Formula Injection Neutralization

When tabular reports, employee rosters, or payroll ledgers are exported to CSV:
- **Formula Injection Defense:** Any cell beginning with formula operators (`=`, `+`, `-`, `@`, `\t`, `\r`) is automatically prefixed with a single quote (`'`), rendering it harmless text in Microsoft Excel, LibreOffice Calc, and Google Sheets.
- **Export Audit Trail:** Every export action generates an audit event recording: `user_id`, `tenant_id`, `dataset`, `record_count`, `filter_parameters`, and `timestamp`.
