# Public Careers Portal & Candidate Self-Service

## Public Endpoints
- `GET /careers`: Lists active, published job postings with location and department filters.
- `GET /careers/{slug}`: SEO-friendly public job details and requirements.
- `POST /api/v1/public/recruitment/postings/{id}/apply`: Public application submission.

## Security & Anti-Abuse
- Public endpoints require NO internal employee session.
- Submissions create candidates and applications via validated transactions.
- File uploads are validated for MIME type, size, and stored outside web root.
