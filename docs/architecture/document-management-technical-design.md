# Document Management System technical design

The `Documents` bounded context owns tenant-scoped document metadata, folders,
versions, storage references, audit history and lifecycle state. Binary content
is accessed only through Laravel's configured filesystem disk; callers never
receive a filesystem path and downloads are authorized before a disk stream is
opened. Each upload creates an immutable `document_versions` row and the
document's current version pointer is advanced transactionally by the service.

The current implementation exposes a stable API for explorer, upload,
metadata, version history, preview/download and archive/restore clients. OCR,
sharing links, signatures and retention workers should consume the same
document/version events and must not bypass the service or write another
module's tables. The version row includes `extracted_text` so asynchronous OCR
can populate searchable text without changing the document contract.

Tenant identity is resolved from `TenantContext`; no client tenant identifier
is trusted. Every upload, version change, download lifecycle change and future
share/signature action is auditable through `document_audits`.
