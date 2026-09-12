# Certifications & Public Verification

## Certificate Management

1. **Certificate Generation**:
   - Issued automatically upon meeting completion criteria (all modules finished, passing assessment score achieved).
   - Generates a unique, non-sequential Certificate Number and SHA-256 Verification Hash.
2. **Public Verification Endpoint**:
   - Web: `/verify/certificate/{code}`
   - API: `/api/v1/hcm/verify/certificate/{code}`
   - Sanitized payload: Exposes only credential authenticity, recipient public name, course name, issue date, and validity status. Zero personal, financial, or performance data is exposed.
3. **Certification Expiration & Recertification**:
   - Monitors validity windows (e.g. 1 year, 2 years).
   - Automated notification triggers at 90, 60, 30, and 7 days before expiration.
   - Triggers recertification assignments and refresher courses.
