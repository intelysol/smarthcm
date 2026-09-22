# SmartHCM Lead Generation & Analytics Architecture

## 1. Overview
The public website incorporates a privacy-preserving telemetry and lead-capture layer. It is designed to measure user discovery, content engagement, and demo requests without capturing sensitive employee PII or personal data.

---

## 2. Event Telemetry Dispatcher
The public layout exposes a non-blocking telemetry trigger:
```javascript
window.trackHcmEvent = function(eventName, payload = {}) {
    console.info('[SmartHCM Telemetry]', eventName, payload);
    if (window.dataLayer && Array.isArray(window.dataLayer)) {
        window.dataLayer.push({ event: eventName, ...payload });
    }
};
```

### Core Monitored Events:
- `demo_started`: User focuses or interacts with `/demo` form fields.
- `demo_submitted`: Validated enterprise demo consultation submitted.
- `contact_submitted`: Sales or technical support inquiry dispatched.
- `pricing_viewed`: Visitor views edition tiers at `/pricing`.
- `mobile_attendance_viewed`: Visitor accesses the `/mobile-attendance` pillar.
- `feature_page_viewed`: Detailed module specification viewed.
- `login_clicked`: Visitor transitions to the authenticated application portal.

---

## 3. Server-Side Lead Processing Architecture

### Endpoints:
- `POST /demo`: Validates enterprise organization size, work email, phone, and requirements.
- `POST /contact`: Validates general inquiry messages, names, and contact channels.

### Security & Compliance Safeguards:
1. **CSRF Protection**: Laravel CSRF token verified on every POST mutation.
2. **Server-Side Validation**: String lengths, valid RFC-compliant emails, and sanitization.
3. **Database Persistence**: Saved in the dedicated `public_leads` table with timestamps, IP address, and status (`new`).
4. **Audit Logging**: Successful inquiry submissions log non-sensitive metadata for lead tracking.
5. **Zero Cookie Tracking**: Complies with GDPR/CCPA by avoiding third-party intrusive tracking cookies.
