# Integration Monitoring & Gateway Health

## 1. Overview
The Workforce Administration module provides consolidated health and throughput visibility over external HCM integration feeds.

## 2. Monitored Integration Feeds
- **Payroll & General Ledger Sync**: REST API / Webhooks.
- **Benefits Carrier EDI Feed**: SFTP / 834 EDI format.
- **Government Compliance Gateway**: HTTPS API (E-Verify / Immigration).
- **Banking & Corporate Card Feed**: Open Banking API.
- **LMS & Learning Webhook Listeners**: Webhook Ingestion.
- **ATS Candidate Ingestion Sync**: REST API Poller.

## 3. Telemetry & Metrics
- Integration Status: `healthy`, `degraded`, `failed`.
- Success rate percentages and latency percentiles.
- Dead-lettered queue volume and retry counts.
