# Onboarding API Reference

## Employee Self-Service Endpoints
```http
GET  /api/v1/me/onboarding
GET  /api/v1/me/onboarding/tasks
POST /api/v1/me/onboarding/tasks/{id}/complete
POST /api/v1/me/onboarding/policies/acknowledge
POST /api/v1/me/onboarding/forms/{formId}
```

## HR & Administrative Endpoints
```http
GET  /api/v1/hcm/onboarding/cases
POST /api/v1/hcm/onboarding/cases
GET  /api/v1/hcm/onboarding/cases/{id}

POST /api/v1/hcm/onboarding/tasks/{id}/complete
POST /api/v1/hcm/onboarding/tasks/{id}/block
POST /api/v1/hcm/onboarding/tasks/{id}/prerequisite

POST /api/v1/hcm/onboarding/documents/{id}/submit
POST /api/v1/hcm/onboarding/documents/{id}/verify
POST /api/v1/hcm/onboarding/documents/{id}/reject

GET  /api/v1/hcm/onboarding/probations
POST /api/v1/hcm/onboarding/probations/{id}/extend
POST /api/v1/hcm/onboarding/probations/{id}/review

GET  /api/v1/hcm/onboarding/analytics/kpis
GET  /api/v1/hcm/onboarding/cases/{id}/ai/welcome
GET  /api/v1/hcm/onboarding/cases/{id}/ai/readiness
POST /api/v1/hcm/onboarding/ai/query
```
