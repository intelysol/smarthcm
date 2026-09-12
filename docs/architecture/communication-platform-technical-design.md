# Notification and communication technical design

The `Communication` bounded context is the single delivery boundary for
in-app, email, SMS, messaging, and push channels. Templates are versioned and
localized; queued deliveries contain rendered content, recipient, provider,
retry count, schedule, and delivery lifecycle timestamps. Providers are
adapters behind the delivery service and must not be called by business
modules.

Preferences are tenant/user scoped and include channels, locale, timezone, and
quiet hours. Delivery tracking is tenant constrained, preventing one tenant
from reading or mutating another tenant's communication history.
