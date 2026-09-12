# Recruitment & ATS API Reference

## Public Endpoints
```http
POST /api/v1/public/recruitment/postings/{id}/apply
```

## Requisitions
```http
GET  /api/v1/hcm/recruitment/requisitions
POST /api/v1/hcm/recruitment/requisitions
GET  /api/v1/hcm/recruitment/requisitions/{id}
POST /api/v1/hcm/recruitment/requisitions/{id}/submit
POST /api/v1/hcm/recruitment/requisitions/{id}/approve
```

## Candidates
```http
GET  /api/v1/hcm/recruitment/candidates
POST /api/v1/hcm/recruitment/candidates
GET  /api/v1/hcm/recruitment/candidates/{id}
POST /api/v1/hcm/recruitment/candidates/check-duplicates
```

## Applications & Screening
```http
GET  /api/v1/hcm/recruitment/applications
POST /api/v1/hcm/recruitment/applications
GET  /api/v1/hcm/recruitment/applications/{id}
POST /api/v1/hcm/recruitment/applications/{id}/stage
POST /api/v1/hcm/recruitment/applications/{id}/screen
POST /api/v1/hcm/recruitment/applications/{id}/hire
```

## Interviews & Scorecards
```http
POST /api/v1/hcm/recruitment/interviews
POST /api/v1/hcm/recruitment/interviews/{id}/evaluate
GET  /api/v1/hcm/recruitment/interviews/{id}/summary
```

## Offers
```http
POST /api/v1/hcm/recruitment/offers
GET  /api/v1/hcm/recruitment/offers/{id}
POST /api/v1/hcm/recruitment/offers/{id}/version
POST /api/v1/hcm/recruitment/offers/{id}/approve
POST /api/v1/hcm/recruitment/offers/{id}/send
POST /api/v1/hcm/recruitment/offers/{id}/accept
```

## Analytics & AI
```http
GET  /api/v1/hcm/recruitment/analytics/funnel
GET  /api/v1/hcm/recruitment/analytics/kpis
POST /api/v1/hcm/recruitment/ai/match
POST /api/v1/hcm/recruitment/ai/job-description
POST /api/v1/hcm/recruitment/ai/query
```
