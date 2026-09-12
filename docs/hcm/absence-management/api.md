# REST API Reference

All endpoints are versioned under `/api/v1/hcm/absence` and require authentication.

## Absence Events
- `POST /events`: Record a new absence event.
- `GET /events`: List absence events with filters.
- `GET /events/{id}`: View absence event details.

## Absence Periods
- `GET /periods`: List aggregated continuous absence periods.
- `GET /periods/{id}`: View period details and associated daily events.

## Operational Impact & Coverage
- `GET /impacts`: List operational impacts by shift and department.
- `POST /impacts/{id}/assign-replacement`: Assign replacement worker.

## Return to Work (RTW)
- `POST /rtw-plans`: Create a phased return-to-work plan.
- `GET /rtw-plans`: List RTW plans.
- `GET /rtw-plans/{id}`: View plan details.
- `PUT /rtw-plans/{id}/status`: Update RTW plan status (`active`, `completed`).

## Absence Cases
- `POST /cases`: Initiate a formal absence case.
- `GET /cases`: List absence cases.
- `GET /cases/{id}`: View case details.

## Reconciliation & Analytics
- `POST /reconciliation/run`: Execute multi-way absence reconciliation.
- `GET /reconciliation`: List reconciliation findings.
- `GET /analytics/rates`: Retrieve enterprise absence rates and Bradford factors.
- `GET /analytics/forecasts`: Retrieve forecasted absence rates.
- `GET /ai/advisory-insights`: Retrieve advisory absence insights.