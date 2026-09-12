# Workforce Planning REST API Documentation

Base URI: `/api/v1/hcm/workforce-planning`

### Endpoints

| Method | Endpoint | Description |
|---|---|---|
| `GET` | `/dashboard/summary` | Executive dashboard KPI summary |
| `GET` | `/plans` | Paginated workforce plans |
| `POST` | `/plans` | Create a new plan cycle |
| `GET` | `/plans/{id}` | Full plan details & periods |
| `POST` | `/plans/{id}/submit` | Submit plan for approval |
| `POST` | `/plans/{id}/approve` | Approve plan |
| `POST` | `/plans/{id}/lock` | Lock plan and generate immutable snapshot |
| `POST` | `/plans/{id}/versions` | Create a new plan version (`v1` $\to$ `v2`) |
| `GET` | `/plans/{id}/positions` | List planned positions & budgets |
| `POST` | `/plans/{id}/positions` | Add planned position |
| `POST` | `/positions/{id}/freeze` | Freeze a position |
| `POST` | `/positions/{id}/unfreeze` | Unfreeze a position |
| `POST` | `/positions/{id}/eliminate` | Eliminate a position |
| `GET` | `/plans/{id}/hiring-plan` | List hiring requirements |
| `POST` | `/plans/{id}/hiring-plan` | Create hiring requirement |
| `POST` | `/hiring-plans/{id}/handoff` | Generate recruitment requisition payload |
| `GET` | `/plans/{id}/costs` | Labor cost summary & categories |
| `POST` | `/plans/{id}/costs` | Record cost plan line |
| `GET` | `/plans/{id}/scenarios` | List what-if scenarios |
| `POST` | `/plans/{id}/scenarios` | Create what-if scenario |
| `POST` | `/scenarios/{id}/simulate` | Run scenario simulation |
| `POST` | `/scenarios/compare` | Compare multiple scenarios |
| `GET` | `/plans/{id}/actual-vs-plan` | Actual vs Plan variance matrix |
| `GET` | `/plans/{id}/ai/summary` | AI-generated plan executive summary |
| `GET` | `/scenarios/{id}/ai/explanation`| AI scenario variance explanation |
| `POST` | `/ai/query` | Natural language planning inquiry |
