# Epic 2.64 — Connector SDK & Driver Framework Specification

## 1. Connector SDK Overview
The Enterprise HCM Connector SDK (`packages/integrations/`) is a decoupled, extensible package providing standard contracts, data transfer objects, and base abstractions for connecting external third-party systems to the HCM Core.

---

## 2. Core Interfaces & Enums

### `ConnectorInterface`
All integration connectors implement `SmartHcm\Integrations\Domain\Contracts\ConnectorInterface`:
```php
interface ConnectorInterface
{
    public function getKey(): string;
    public function getName(): string;
    public function getVersion(): string;
    public function getSupportedAuthTypes(): array;
    public function getCapabilities(): array;
    public function testConnection(array $config): ConnectorResult;
    public function pull(SyncContext $context): ConnectorResult;
    public function push(SyncContext $context, array $records): ConnectorResult;
    public function handleWebhook(InboundEvent $event): ConnectorResult;
    public function getStatus(): ConnectorLifecycleState;
}
```

### `AuthenticationType` Enum
- `API_KEY`: Header or query parameter API keys
- `BEARER_TOKEN`: Static or long-lived Bearer tokens
- `OAUTH2`: OAuth 2.0 Authorization Code or Client Credentials grant
- `BASIC_AUTH`: HTTP Basic authentication (`username:password`)
- `HMAC_SIGNATURE`: Cryptographic message authentication (`X-Hub-Signature-256`)
- `MTLS`: Mutual TLS certificate exchange
- `CUSTOM`: Custom auth schemes with header transformations

### `ConnectorLifecycleState` Enum
- `DRAFT`: Newly created, unconfigured connector
- `ACTIVE`: Tested, operational, and actively synchronizing
- `INACTIVE`: Temporarily disabled by administrator
- `ERROR`: Circuit broken due to consecutive connection failures
- `DEPRECATED`: Scheduled for decommission

---

## 3. Data Transfer Objects (DTOs)

1. **`SyncContext`**:
   Encapsulates all execution metadata for a synchronization run:
   - `tenantId`: Active tenant UUID
   - `connectionId`: ID of the configured integration connection
   - `direction`: `inbound` or `outbound`
   - `entityType`: Target entity (e.g. `employee`, `attendance`, `payroll`)
   - `config`: Decrypted connection parameters & endpoints
   - `since`: High-water mark timestamp for incremental delta sync
   - `options`: Execution parameters (batch size, dry run flag)

2. **`ConnectorResult`**:
   Standardized execution outcome:
   - `success`: Boolean status flag
   - `statusCode`: HTTP or internal outcome code
   - `recordsProcessed`: Count of processed entities
   - `recordsFailed`: Count of failed records
   - `data`: Payload records returned or sent
   - `errors`: Array of structured error messages with field tags
   - `metadata`: Driver-specific headers, pagination cursors, timing metrics

3. **`InboundEvent`**:
   Represents an incoming webhook or real-time event:
   - `eventId`: Unique event ID or idempotency key
   - `eventType`: Event type code (e.g., `employee.created`, `leave.approved`)
   - `tenantId`: Tenant context
   - `payload`: Decoded JSON or associative array payload
   - `headers`: Incoming HTTP request headers
   - `timestamp`: Event creation timestamp

---

## 4. Reference Connector Implementations

### 1. `GenericRestConnector` (`generic_rest`)
- Supports configurable REST endpoints with flexible auth (Bearer, ApiKey, Basic).
- Handles HTTP pagination (page-based, cursor-based, limit-offset).
- Implements exponential backoff retry on transient 5xx / 429 status codes.

### 2. `GenericWebhookConnector` (`generic_webhook`)
- Ingress for generic third-party webhooks.
- Verifies cryptographic HMAC-SHA256 signatures with customizable header keys.
- Normalizes incoming event schemas to `InboundEvent`.

### 3. `DemoHrConnector` (`demo_hr`)
- Canonical reference driver demonstrating complete inbound employee ingestion and outbound notification sync.
- Pre-configured with schema mapping definitions translating third-party employee structures into HCM canonical models.

---

## 5. Dynamic Discovery & Driver Registration
Connectors are registered into the service container via `ConnectorRegistry`:
```php
$registry = app(ConnectorRegistry::class);
$registry->register('generic_rest', GenericRestConnector::class);
$registry->register('generic_webhook', GenericWebhookConnector::class);
$registry->register('demo_hr', DemoHrConnector::class);
```
Third-party developers and partners can dynamically register new connectors in custom service providers without modifying HCM core code.
