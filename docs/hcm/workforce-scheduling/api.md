# Workforce Scheduling API Reference

All endpoints require authentication and are prefixed by `/api/v1/hcm/scheduling/`:

| Method | Endpoint | Description |
|---|---|---|
| `POST` | `periods/{id}/validate` | Runs validation engine; returns hard violations and soft warnings. |
| `POST` | `periods/{id}/publish` | Publishes validated schedule; gates on critical violations. |
| `POST` | `periods/{id}/lock` | Locks schedule period to prevent unauthorized edits. |
| `GET` | `periods/{id}/coverage` | Returns coverage matrix and heatmap by date and shift. |
| `POST` | `periods/{id}/coverage/ingest` | Ingests demand requirements from capacity calculations. |
| `POST` | `periods/{id}/optimize` | Runs optimization heuristic; returns proposed assignments. |
| `POST` | `optimization-runs/{runId}/apply` | Applies proposed assignments into active roster schedule. |
| `GET` | `periods/{id}/cost` | Calculates projected regular and overtime labor cost. |
| `GET` | `periods/{id}/ai-insights` | Generates advisory scheduling suggestions (`is_advisory_only: true`). |
| `POST` | `swaps` | Submits peer shift swap request with eligibility check. |
| `POST` | `swaps/{id}/peer-respond` | Peer worker accepts or declines swap request. |
| `POST` | `swaps/{id}/manager-approve` | Manager approves or rejects swap request. |
| `POST` | `open-shifts` | Creates an open shift for employee bidding. |
| `POST` | `open-shifts/{id}/bid` | Worker submits bid with automatic eligibility scoring. |
| `POST` | `open-shifts/bids/{bidId}/award` | Manager awards open shift to winning bidder. |
| `GET` | `realtime-coverage` | Evaluates real-time coverage vs actual attendance clock punches. |
| `POST` | `availabilities` | Saves employee availability block. |
| `POST` | `preferences` | Saves employee shift preference. |
