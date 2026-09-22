# Epic 2.64 — Enterprise Integration Hub & API Management: Architecture Specification

## 1. Executive Summary & Vision
Epic 2.64 establishes the enterprise-grade **Integration Hub and API Management Gateway** for the Enterprise HCM Platform. The subsystem serves as the central nervous system connecting the HCM core to heterogeneous external platforms:
- Third-party Payroll Providers (ADP, Workday, Paychex)
- Financial/ERP Systems (SAP, NetSuite, Dynamics 365, Oracle)
- Banking Networks (SWIFT, NACHA, SEPA, Open Banking APIs)
- Biometric & Physical Access Control Systems (ZKTeco, HID, Suprema)
- Recruitment & Applicant Tracking Systems (LinkedIn, Greenhouse, Lever)
- Learning Management Systems (Cornerstone, Coursera, LinkedIn Learning)
- Benefits & Insurance Carriers
- Government Regulators & Tax Authorities
- Enterprise Identity Providers (Okta, Azure AD, Ping)

Crucially, this capability was achieved with **ZERO new database migrations and ZERO schema modifications**, preserving the authoritative database baseline of **985 tables and 2,180 foreign keys**.

---

## 2. Architectural Boundary & Reusability Mandate
In accordance with strict enterprise governance rules:
- **No Duplicate Workflow Engine:** Asynchronous pipelines dispatch directly through Laravel queues (`IntegrationQueue::INBOUND`, `IntegrationQueue::OUTBOUND`, `IntegrationQueue::WEBHOOKS`).
- **No Duplicate Automation Engine:** Scheduling leverages existing infrastructure (`IntegrationScheduleFrequency` enums and Laravel Scheduler).
- **No Duplicate Notification Engine:** Alerts route through platform notification dispatchers.
- **No Duplicate Audit Engine:** Request tracking integrates with `api_request_logs` and `audit_trails`.
- **No Duplicate AI Engine:** Schema mapping suggestions and dead-letter error diagnostics interface directly with the existing `AiGatewayService` / `OpenAiClient` through `IntegrationAiAssistant`.

---

## 3. Layered Subsystem Architecture

```
+-------------------------------------------------------------------------------+
|                            EXTERNAL CONSUMERS / PRODUCERS                      |
|  [Third-Party HR/Payroll]  [ERP / Finance]  [Biometric Readers]  [Gov / Banks] |
+-------------------------------------------------------------------------------+
                                      |
                      (HTTPS / HMAC / API Keys / Bearer)
                                      v
+-------------------------------------------------------------------------------+
|                        API MANAGEMENT & INGRESS GATEWAY                       |
|  - VerifyApiKey (SHA-256 / SHA-512 hashing, tenant context binding)           |
|  - EnforceApiScopes (Granular RBAC: employees:read, payroll:sync, etc.)       |
|  - EnforceApiRateLimits (Sliding window cache, 429 Retry-After, Tiered limits)|
|  - ApiIdempotencyMiddleware (SHA-256 idempotency keys, duplicate suppression) |
|  - LogApiRequests (Structured telemetry into api_request_logs, latency, IP)  |
|  - WebhookIngressController (HMAC-SHA256 signature, 5-min timestamp skew)     |
+-------------------------------------------------------------------------------+
                                      |
                         (InboundEvent / DTO Handlers)
                                      v
+-------------------------------------------------------------------------------+
|                      CONNECTOR SDK & ORCHESTRATION LAYER                      |
|  - ConnectorRegistry (Dynamic driver discovery, capability detection)         |
|  - ConnectorInterface & AbstractConnector (Lifecycle: test, sync, batch)     |
|  - Reference Drivers: GenericRestConnector, GenericWebhookConnector, DemoHr  |
|  - CredentialManager (AES-256-GCM symmetric encryption via Laravel Crypt)     |
+-------------------------------------------------------------------------------+
                                      |
                                      v
+-------------------------------------------------------------------------------+
|                         TRANSFORMATION & MAPPING ENGINE                       |
|  - MappingEngine (Source-to-target field projection, nested dot notation)     |
|  - MasterCodeTranslator (Integration with HcmGovMasterMapping / canonicals)   |
|  - Schema Validation (JSON-schema rule evaluation & type casting)             |
+-------------------------------------------------------------------------------+
                                      |
                                      v
+-------------------------------------------------------------------------------+
|                          ASYNCHRONOUS RESILIENCY ENGINE                       |
|  - InboundSyncJob (Transactional entity upserts, idempotency verification)    |
|  - OutboundIntegrationJob (Http retry with exponential backoff & jitter)      |
|  - WebhookDeliveryJob (Target delivery with retry & response status capture)  |
|  - DeadLetterService (Dead-letter isolation into integration_dead_letters)    |
|  - IntegrationAiAssistant (Intelligent root-cause diagnosis & suggested fix)  |
+-------------------------------------------------------------------------------+
                                      |
                                      v
+-------------------------------------------------------------------------------+
|                       HCM CORE DOMAIN & PERSISTENCE                           |
|  - 985 Normalized Tables / 2,180 Foreign Keys (Strict Baseline Preserved)      |
|  - EmployeeService, PayrollService, AttendanceLog, AuditLog                   |
+-------------------------------------------------------------------------------+
```

---

## 4. Package Separation & File Layout
The implementation is partitioned cleanly between reusable SDK libraries and domain orchestration:
- **`packages/integrations/`** (Connector SDK):
  - `Domain/Contracts/ConnectorInterface.php`
  - `Domain/Contracts/CredentialManagerInterface.php`
  - `Domain/DTOs/ConnectorResult.php`, `SyncContext.php`, `InboundEvent.php`
  - `Domain/Enums/ConnectorLifecycleState.php`, `AuthenticationType.php`
  - `Infrastructure/Connectors/AbstractConnector.php`
  - `Infrastructure/Connectors/GenericRestConnector.php`
  - `Infrastructure/Connectors/GenericWebhookConnector.php`
  - `Infrastructure/Connectors/DemoHrConnector.php`
  - `Infrastructure/Services/ConnectorRegistry.php`
  - `Infrastructure/IntegrationsServiceProvider.php`
- **`app/Domains/Integration/`** (Integration Hub Domain):
  - `Services/CredentialManager.php`
  - `Services/WebhookSecurityService.php`
  - `Services/MappingEngine.php`
  - `Services/MasterCodeTranslator.php`
  - `Services/DeadLetterService.php`
  - `Services/SyncEngine.php`
  - `Services/IntegrationMonitoringService.php`
  - `Services/IntegrationAiAssistant.php`
  - `Jobs/InboundSyncJob.php`, `OutboundIntegrationJob.php`, `WebhookDeliveryJob.php`
  - `Http/Controllers/WebhookIngressController.php`, `IntegrationWebController.php`
- **`app/Domains/Api/`** (API Gateway Domain):
  - `Http/Middleware/VerifyApiKey.php`
  - `Http/Middleware/EnforceApiScopes.php`
  - `Http/Middleware/EnforceApiRateLimits.php`
  - `Http/Middleware/ApiIdempotencyMiddleware.php`
  - `Http/Middleware/LogApiRequests.php`
  - `Http/Controllers/ApiCatalogController.php`, `ApiGovernanceController.php`
- **`resources/views/integrations/`** (Corporate UX):
  - `index.blade.php` (Compliant with Corporate UI Design Tokens: Navy `#1E3A5F`, Gold `#C9A227`, Slate `#F8FAFC`).

---

## 5. Security & Isolation Model
1. **Multi-Tenancy Isolation:**
   All tables (`integration_connectors`, `integration_mappings`, `integration_sync_runs`, `api_clients`, `api_keys`, `api_request_logs`, `integration_dead_letters`) enforce strict `tenant_id` scoping.
2. **Credential Vaulting:**
   Secrets (API tokens, client secrets, basic auth passwords, private certificates) are stored encrypted via AES-256-GCM using Laravel's cryptographic subsystem. Unencrypted secrets are never written to disk, database rows, or application logs.
3. **Defense-in-Depth Ingress:**
   Every incoming HTTP call passes through API key hash lookup -> IP allowlist verification -> scope enforcement -> rate limit verification -> duplicate replay check -> telemetry logging.
