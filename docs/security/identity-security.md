# Identity Security Guide

Tenant identifiers are never accepted as authorization: authenticated requests resolve a
tenant and confirm it matches the user. Login attempts retain an identifier, IP, and
fingerprint hash. Existing rate limits protect login and recovery endpoints. Production
deployment must configure Redis-backed cache/session drivers, HTTPS-only secure cookies,
Horizon workers, a mail/SMS provider, and an audited geolocation provider.
