# Learning REST API Documentation

Base URI: `/api/v1/hcm`

### Public Endpoints
- `GET /verify/certificate/{code}`: Public certificate verification web page.
- `GET /v1/hcm/verify/certificate/{code}`: Public sanitized JSON certificate verification.

### Employee Self-Service (ESS)
- `GET /me/learning/dashboard`: Learner summary (in progress, completed, certificates).
- `GET /me/learning/catalog`: Searchable course catalog.
- `POST /me/learning/courses/{id}/enroll`: Self-enrollment request.
- `GET /me/learning/progress`: Active course progress.
- `POST /me/learning/items/{id}/progress`: Update module/lesson progress.
- `POST /me/learning/attempts/{id}/submit`: Submit assessment answers for server scoring.
- `GET /me/learning/certificates`: Issued digital credentials.
- `GET /me/learning/development-plans`: Personal Individual Development Plans (IDP).
- `POST /me/learning/development-plans`: Create new IDP.

### Manager Self-Service (MSS)
- `GET /manager/learning`: Team training compliance dashboard.
- `POST /manager/learning/nominations`: Nominate direct report for a course.

### Admin & L&D Management
- `GET /learning/courses`: Course administration.
- `POST /learning/courses`: Author course and publish version.
- `GET /learning/sessions`: Training session schedules and attendance.
- `POST /learning/ai/recommendations`: Targeted course recommendations.
- `POST /learning/ai/query`: Natural language learning catalog query.
