# Enterprise Testing Strategy & Pyramid Architecture

## 1. Executive Summary

This document defines the authoritative testing strategy for the Enterprise Application Platform (SmartHCM and future multi-tenant modules). The platform enforces a layered testing pyramid to guarantee zero regressions, strict multi-tenant boundaries, hardened security invariants, and automated release certification.

---

## 2. The Enterprise Testing Pyramid

```text
               /\
              /  \      Release Certification Gate (platform:certify)
             /----\
            / E2E  \     Critical Journeys (Employee SS, Manager Workflow, Recruitment, Billing)
           /--------\
          / API &    \   Contract Verification, Error Uniformity, Status Codes
         /  Tenant    \  Multi-Tenant Isolation, Scoping, IDOR Defense
        /--------------\
       /   Security &   \ Authentication, RBAC/ABAC, Session Invalidation, AI Safety
      /     Feature      \ Domain Logic, State Transitions, Business Invariants
     /--------------------\
    /     Unit Testing     \ Isolated Calculations, Formula Engines, Value Objects
   /------------------------\
```

### Partition Definitions:

| Suite Name | Scope | Execution Cadence | SLA / Failure Tolerance |
|:---|:---|:---|:---|
| **Unit** | Independent classes, mathematical engines, salary calculators | Every local save / commit | 100% Pass; 0 Failures |
| **Feature / Domain** | Domain services, workflow state transitions, event dispatchers | Every Pull Request | 100% Pass; 0 Failures |
| **Security & Identity** | AuthN, AuthZ, Session Hijack, Password Policies, Rate Limits | Every PR & Pre-Merge | **Zero-Tolerance** (0 failures) |
| **Tenant Isolation** | Foreign key scopes, GlobalScopes, IDOR vectors, cross-tenant reads | Every PR & Pre-Merge | **Zero-Tolerance** (0 leaks) |
| **API Contract** | Uniform envelopes (`success`, `data`/`error`, `request_id`), HTTP status codes | Every PR & Staging Build | 100% Pass; 0 schema drifts |
| **E2E Journeys** | Multi-step user flows across persona boundaries | Staging & Nightly Builds | 100% Pass; 0 P0/P1 defects |
| **Release Certification** | Master gate aggregating all suites into certified binary verdict | Release Candidate Build | **Zero Unresolved Blockers** |

---

## 3. Core Testing Directives

1. **No Untested Production Code**: Every new endpoint or business rule must arrive with corresponding automated tests.
2. **Deterministic Execution**: Tests must not rely on external networks or flaky third-party APIs. Stubs, fakes, and mocks (`Storage::fake`, `Event::fake`, `Notification::fake`) must be used for external dependencies.
3. **Database Isolation**: Tests execute against SQLite in-memory (`:memory:`) or dedicated MySQL ephemeral databases with transaction rollbacks via `RefreshDatabase`.
4. **Mandatory Regression Coverage**: Whenever a defect is discovered in staging or production, a reproduction test must be committed before the fix is merged.
5. **No Blind Skipped Tests**: `markTestSkipped()` or `markTestIncomplete()` require documented Jira/GitHub issue tracking and architecture team approval.

---

## 4. Test Suite Partitions (`phpunit.xml`)

The test harness is cleanly organized into independent partitions:

```xml
<testsuites>
    <testsuite name="Unit">
        <directory>tests/Unit</directory>
    </testsuite>
    <testsuite name="Feature">
        <directory>tests/Feature</directory>
    </testsuite>
    <testsuite name="Security">
        <directory>tests/Security</directory>
    </testsuite>
    <testsuite name="Tenant">
        <directory>tests/Tenant</directory>
    </testsuite>
    <testsuite name="API">
        <directory>tests/API</directory>
    </testsuite>
    <testsuite name="E2E">
        <directory>tests/E2E</directory>
    </testsuite>
</testsuites>
```

---

## 5. Quality Gate Ownership & Sign-off

- **Developer**: Responsible for authoring unit, feature, and integration regression tests.
- **QA Automation Engineer**: Responsible for maintaining cross-domain E2E user journeys and contract mocks.
- **SecOps / Security Lead**: Authoritative owner of `tests/Security` and `tests/Tenant` isolation suites.
- **Release Manager**: Signs off on automated `php artisan platform:certify` audit report before deployment to production clusters.
