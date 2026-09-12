# Offer Management & Versioning

## Offer Versioning
Employment offers are immutable once issued. Any negotiation or adjustment generates a new version:
- Initial Draft: Version 1
- Negotiation: Version 2 (`HcmRecruitmentOfferVersion`)
- `OfferManagementService::createNewOfferVersion` preserves prior compensation snapshots and records the actor and change rationale.

## Offer Workflow
```text
Draft Offer -> Internal Approvals (Finance/HR) -> Approved -> Sent to Candidate -> Accepted / Declined / Expired
```
Accepted offers cannot be modified without formal revocation.
Expired offers are auto-flagged via `CheckOfferExpiryJob`.
