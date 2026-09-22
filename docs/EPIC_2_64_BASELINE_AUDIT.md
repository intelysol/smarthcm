# Epic 2.64 — Enterprise Integration Hub & API Management Baseline Audit

> **Document:** Baseline Repository & Architectural Audit  
> **Target Epic:** Epic 2.64 — Enterprise Integration Hub, API Management & External System Connectivity  
> **Date:** September 2026  
> **Platform:** Enterprise HCM Platform (`smarthcm`)

---

## 1. Executive Summary

This audit establishes the baseline for **Epic 2.64: Enterprise Integration Hub, API Management & External System Connectivity**. In accordance with the core architecture rules:
- **No duplicate engines**: We reuse existing Workflow, Automation, Rules, Queue, Notification, Audit, and AI Governance platforms.
- **Authoritative Database Baseline**: The 985-table schema already incorporates complete database tables for connectors, connections, events, mappings, sync runs, dead-letter tracking, webhooks, API clients, API keys, catalog, rate limiting, and request logs. No unnecessary schema mutations are required.
- **Package Foundation**: We extend `packages/integration/` (aliased to `packages/integrations/`) for the connector SDK and framework, working seamlessly with `app/Domains/Integration` and `app/Domains/Api`.

---

## 2. Inventory of Existing Components

### 2.1 Integration & API Subsystems
- **`packages/integrations/`** (and junction `packages/integration/`):
  - Packaged Flow platform capability scaffold (`Domain`, `Application`, `Infrastructure`, `Presentation`, `Routes`, `Config`).
- **`app/Domains/Integration/`**:
  - `Models/IntegrationConnector`: Represents integration connector types and manifests.
  - `Models/IntegrationConnection`: Manages tenant-specific connections, configuration JSON, and encrypted credentials (`encrypted:array`).
  - `Models/IntegrationEvent`: Tracks published integration domain events.
  - `Models/WebhookSubscription`: Manages tenant webhook URLs, event filters, secret references, and retry limits.
  - `Services/IntegrationService`: Dispatches outbound webhooks using HMAC-SHA256 signatures (`X-FEP-Signature`) and provides webhook verification.
  - `Http/Controllers/IntegrationController`: Exposes basic v1 endpoints (`connectors`, `connections`, `events`, `webhooks`).
  - `Routes/api.php`: Prefixed at `v1/integrations` with `['web', 'auth', 'tenant']` middleware.
- **`app/Domains/Api/`**:
  - `Models/ApiClient`: Manages API client identities, tenant bindings, scopes (`array`), and status.
  - `Models/ApiKey`: Manages hashed API keys (`key_hash`), labels, and expiration.
  - `Models/ApiCatalogEntry`: Catalog of API endpoints, versions, request/response schemas.
  - `Support/ApiResponse`: Platform standard error and success JSON response structure (`success`, `data`, `meta`, `error`, `traceId`).
  - `Http/Controllers/ApiGovernanceController`: Client registration, catalog lookup, key revocation.

### 2.2 Reusable Platform Infrastructure (DO NOT DUPLICATE)
- **Workflow Engine**:
  - `App\Domains\Workflow\Services\WorkflowEngine`: Manages multi-step business process lifecycles.
- **Automation Engine**:
  - `App\Domains\Automation\Services\AutomationEngine`: Event-driven trigger-action automation.
- **Rules Platform**:
  - `App\Domains\Rules\Services\RuleEngine` & `ExpressionEvaluator`: High-performance deterministic rule execution and expression parsing for data transformation and mapping.
- **Data Governance Master Mapping**:
  - `App\Domains\WorkforceGovernance\Models\HcmGovMasterMapping`: Standard entity cross-system code translation (`hcm_gov_master_mappings`).
- **Audit Logging**:
  - `App\Domains\Shared\Services\ActivityLogService`: Immutable multi-tenant activity log recorder (`activity_logs`).
- **Notifications & Alerting**:
  - `App\Domains\Platform\Models\PlatformNotification`: Multi-tenant user and administrative notification queue.
- **AI Governance & Telemetry**:
  - `App\Domains\AiOperations\Services\AiOperationsService`: AI assistance, evaluation, telemetry, and diagnostics.
- **Queues & Jobs**:
  - Standard Laravel Queue system (`dispatch()`, `ShouldQueue`, `SyncRunJob`, `WebhookDeliveryJob`).

---

## 3. Database Schema Verification

The authoritative 985-table schema already contains all required relational structures for Epic 2.64:

| Table Name | Purpose / Responsibility | Key Columns |
| :--- | :--- | :--- |
| `integration_connectors` | Connector definitions and capability manifests | `id`, `tenant_id`, `key`, `name`, `connector_type`, `manifest`, `status` |
| `integration_connections` | Tenant-configured active integration instances | `id`, `tenant_id`, `connector_id`, `name`, `configuration`, `encrypted_credentials`, `status`, `credential_expires_at` |
| `integration_events` | Outbound and domain integration event stream | `id`, `tenant_id`, `event_type`, `subject_type`, `subject_id`, `payload`, `occurred_at`, `published_at` |
| `integration_mappings` | Declarative field transformation schemas | `id`, `connection_id`, `name`, `source_format`, `target_format`, `mapping`, `validation_rules` |
| `integration_sync_runs` | Sync execution state, batch tracking, checkpoints | `id`, `connection_id`, `sync_type`, `status`, `processed`, `failed`, `checkpoint`, `error_message`, `started_at`, `completed_at` |
| `integration_dead_letters`| Dead-letter queue for failed/unretryable payloads | `id`, `connection_id`, `job_type`, `payload`, `error_message`, `attempts`, `failed_at`, `replayed_at` |
| `webhook_subscriptions` | Registered webhook endpoints and filters | `id`, `tenant_id`, `connection_id`, `url`, `event_filters`, `secret_reference`, `status`, `max_retries` |
| `webhook_deliveries` | Webhook delivery attempts, status, HTTP codes | `id`, `subscription_id`, `event_id`, `status`, `attempt`, `response_code`, `response_body`, `delivered_at`, `next_retry_at` |
| `api_clients` | External and tenant API client registrations | `id`, `tenant_id`, `name`, `client_type`, `client_identifier`, `encrypted_secret`, `scopes`, `ip_allowlist`, `status` |
| `api_keys` | Hashed API credentials per client | `id`, `client_id`, `key_hash`, `label`, `expires_at`, `last_used_at`, `status` |
| `api_products` | API product groups and OpenAPI specifications | `id`, `name`, `version`, `status`, `openapi`, `sunset_on` |
| `api_catalog` | Centralized registry of all public and tenant APIs | `id`, `version`, `method`, `path`, `name`, `description`, `request_schema`, `response_schema`, `security`, `status` |
| `api_endpoints` | Granular endpoint metadata and required scopes | `id`, `api_product_id`, `method`, `path`, `resource`, `required_scopes`, `field_policies`, `status` |
| `api_rate_limit_policies`| Client- and product-specific rate limit rules | `id`, `api_product_id`, `api_client_id`, `requests_per_minute`, `burst_limit` |
| `api_request_logs` | Structured audit trail of API calls | `id`, `tenant_id`, `api_client_id`, `api_endpoint_id`, `correlation_id`, `response_status`, `latency_ms`, `ip_address`, `requested_at` |
| `api_idempotency_keys` | Replay protection and duplicate suppression | `id`, `tenant_id`, `key`, `method`, `path`, `response_status`, `response_body`, `expires_at` |
| `api_bulk_jobs` | Asynchronous bulk processing queue | `id`, `tenant_id`, `api_client_id`, `operation`, `resource`, `status`, `payload`, `result`, `download_document_id` |
| `api_changelog_entries` | API version deprecations and releases | `id`, `api_product_id`, `version`, `change_type`, `summary`, `details`, `release_date` |

---

## 4. Architectural Gap Analysis & Strategy

| Functional Requirement | Baseline State | Required Enhancement (Epic 2.64) |
| :--- | :--- | :--- |
| **Connector SDK** | None | Implement `Flow\Packages\Integrations\Domain\Contracts\ConnectorInterface` with lifecycle, health checks, capabilities, pull/push. |
| **Connector Registry** | Hardcoded | Dynamic connector registry supporting REST, Webhooks, SFTP/CSV, and Demo HR Provider. |
| **Credential Security** | Encrypted casting | Centralized `CredentialManager` supporting API Key, Bearer, OAuth 2.0 (Auth Code, Client Credentials), HMAC. Secrets masked in logs/APIs. |
| **API Management** | Rudimentary catalog | Comprehensive API Gateway middleware: scope enforcement, API key verification, rate limiting, request logging, idempotency. |
| **Inbound Processing** | None | Secure inbound webhook/API pipeline: signature validation, timestamp verification, idempotency check, tenant isolation, domain delegation. |
| **Outbound Processing** | Direct HTTP in service | Queued outbound event listener, subscriber resolution, declarative transformation, exponential backoff retries, delivery tracking. |
| **Mapping & Transform** | Database table only | Deterministic mapping engine integrated with `RuleEngine` and `HcmGovMasterMapping`. |
| **Sync Engine** | Database table only | Asynchronous chunked sync runner with checkpointing, dead-letter capture, and replay capabilities. |
| **Command Center & UI**| None | Enterprise Integration Hub dashboard conforming to corporate gold/navy design tokens. |
| **AI Integration** | None | Integration diagnostics, mapping recommendations, and anomaly detection via `AiOperationsService`. |
| **Demo Connector** | None | `DemoHrConnector` simulating employee and attendance sync with end-to-end idempotency test. |

---

## 5. Architectural Alignment & Boundaries

1. **Zero Schema Duplication**: Do NOT alter table definitions or add duplicate tables. The 985-table schema is already authoritative.
2. **Domain Service Integrity**: Inbound synchronizations MUST invoke domain services (e.g. `EmployeeService`, `AttendanceService`) rather than executing direct raw database inserts.
3. **Tenant Boundary**: All connectors, credentials, clients, webhooks, and sync runs MUST enforce `tenant_id` scope isolation.
4. **Secret Masking**: No raw credentials, API secrets, or sensitive employee PII may appear in logs, exceptions, or audit events.
