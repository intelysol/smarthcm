# REST API Reference & Endpoints

## Unified Service Portal & Search
* `GET /api/v1/self-service/portal/search?q={query}`
  * Returns: `{ detected_intent, knowledge_articles, recommended_services, deflection_suggested, ai_assistance }`
* `GET /api/v1/self-service/portal/popular`
  * Returns: List of high-frequency services and top knowledge articles.
* `GET /api/v1/self-service/portal/services/{service_id}/form`
  * Returns: Full service definition, active version, dynamic form schema, and SLA parameters.

---

## Service Request Lifecycle
* `POST /api/v1/self-service/requests`
  * Body: `{ service_definition_id, subject, description, priority, form_data, attachments }`
  * Returns: Created `HrServiceRequest` in `submitted` or `assigned` status.
* `GET /api/v1/self-service/requests/{id}`
  * Returns: Full request details, timeline history, linked documents, and comments.
* `POST /api/v1/self-service/requests/{id}/comments`
  * Body: `{ message, is_internal, attachments }`
  * Returns: Created comment; automatically resumes SLA clock if submitted by employee.

---

## Workspace & Duplicate Management
* `GET /api/v1/self-service/workspace/dashboard`
  * Returns: Open queue count, my assigned cases, SLA breach warnings, and CSAT average.
* `GET /api/v1/self-service/requests/{id}/duplicates`
  * Returns: List of potential matching duplicate requests.
* `POST /api/v1/self-service/requests/{id}/merge`
  * Body: `{ secondary_request_id, reason }`
  * Returns: Merged response status and linked audit confirmation.

---

## Feedback & CSAT
* `POST /api/v1/self-service/requests/{id}/feedback`
  * Body: `{ rating, timeliness_rating, knowledge_rating, helpfulness_rating, comments }`
  * Returns: Created `HrServiceFeedback` record.
* `GET /api/v1/self-service/feedback/summary`
  * Returns: Aggregate CSAT score, satisfaction rate, and rating breakdowns.

---

## Omnichannel Ingestion Webhooks
* `POST /api/v1/self-service/intake/{channel}`
  * Body: Channel-specific payload (Email, MS Teams, WhatsApp webhook).
  * Returns: `{ status: 'success', request_number, request_id }`
