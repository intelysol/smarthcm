# EPIC 2.59 — Architecture Assessment: Enterprise Workflow Designer, Scheduled Compliance Exports & Operational Automation

## 1. Executive Summary
Epic 2.59 builds directly upon Epic 2.58 (Tenant Administration & Productization) to deliver two mission-critical enterprise operational capabilities:
1. **Visual Workflow Designer & Builder**: A drag-and-drop and keyboard-accessible workflow builder enabling administrators to configure multi-step approval workflows, condition branching, automated actions, SLAs, dynamic approver resolution, and pre-execution simulations with zero code modification.
2. **Scheduled Compliance Exports & Operational Automation**: A resilient, idempotent compliance export engine enabling tenants to automatically package and push cryptographically verified HCM compliance data (SOX, GDPR Article 30, Labor standards) to AWS S3, Azure Blob Storage, or local storage with automated retry logic, manifest generation, and governance tracking.

---

## 2. Architectural Boundaries & Non-Negotiable Rules
1. **Zero Duplicate Engines**:
   - Reuses and extends existing workflow infrastructure (`App\Domains\Workflow\Models\Workflow`, `WorkflowStep`, `WorkflowEngine`), rules, identity, and tenant models.
   - Does NOT invent a secondary workflow runner; extends definitions and step metadata cleanly.
2. **Accessible Visual Reordering**:
   - Drag-and-drop HTML5 UI supported by robust keyboard accessible controls (Move Up/Down, direct order sequence assignment).
   - Reordering API is atomic and transaction-safe, reindexing step orders 1..N without collision.
3. **Multi-Cloud Storage Abstraction**:
   - `ExportStorageProviderInterface` encapsulates cloud-specific SDKs (AWS S3, Azure Blob Storage) alongside a local/mock driver for seamless local and CI testing.
   - Raw credentials are never stored in plaintext and never leaked through public/admin API responses.
4. **Non-Destructive Workflow Versioning**:
   - Workflows progress through `DRAFT` $\rightarrow$ `REVIEW` $\rightarrow$ `PUBLISHED` $\rightarrow$ `SUPERSEDED` $\rightarrow$ `ARCHIVED`.
   - In-flight workflow instances continue on their locked version; publishing never breaks running processes.
5. **Multi-File Packaging, Manifest Integrity & Verification**:
   - Exports generate a standard `manifest.json` containing record counts, file sizes, SHA-256 cryptographic hashes, and data governance lineage tags (Epic 2.53).
   - Post-upload verification ensures uploaded objects match expected checksums before marking executions as `COMPLETED`.

---

## 3. Database Schema Overview
The new migration `2026_10_10_000001_create_enterprise_workflow_designer_and_compliance_export_tables.php` introduces:
- `hcm_workflow_designer_steps`: Visual layout positions, rich step types (`APPROVAL`, `REVIEW`, `NOTIFICATION`, `CONDITION`, `AUTOMATION`, `ACTION`), condition definitions, routing strategies, and escalation configurations.
- `hcm_workflow_versions`: Historical snapshots and changelogs for workflow definitions across version increments.
- `hcm_workflow_designer_audits`: Immutable audit trail of designer edits, validations, simulations, and publish events.
- `hcm_compliance_storage_destinations`: Tenant-isolated, encrypted cloud storage configurations for AWS S3 and Azure Blob.
- `hcm_compliance_export_profiles`: 7-step wizard export definitions (domains, scope, format, destination, encryption, retention, schedule).
- `hcm_compliance_export_executions`: Execution state machine records (`SCHEDULED`, `QUEUED`, `GENERATING`, `PACKAGING`, `UPLOADING`, `VERIFYING`, `COMPLETED`, `FAILED`).
- `hcm_compliance_export_manifests`: Structured manifest payloads with file entries, row counts, byte sizes, and SHA-256 checksums.

---

## 4. Key Services & Domains
- **Workflow Designer Domain** (`App\Domains\WorkflowDesigner`):
  - `WorkflowDesignerService`: Step management, atomic reordering, versioning, cloning drafts, and publishing.
  - `WorkflowValidationService`: Graph inspection, circular dependency detection, start node check, approver resolution check.
  - `WorkflowSimulationService`: Dry-run execution with mock payloads and approval hierarchy traversal.
- **Compliance Export Domain** (`App\Domains\ComplianceExport`):
  - `ComplianceExportService`: Profile lifecycle, schedule orchestration, execution state machine, and retry handling.
  - `ComplianceExportPackagingService`: Scoped data extraction, CSV/JSON serialization, manifest generation, ZIP compression.
  - `ComplianceExportVerificationService`: SHA-256 computation and remote object verification.
  - `ExportStorageFactory` & Providers: `AwsS3ExportStorageProvider`, `AzureBlobExportStorageProvider`, `LocalMockExportStorageProvider`.

---

## 5. UI / Presentation
- `/admin/workflows/designer`: Interactive visual drag-and-drop step organizer, step editor, live validation badge, and simulation panel.
- `/admin/compliance/exports`: Export profile manager, guided 7-step wizard modal, execution history log with manifest inspection, and instant test export runner.
