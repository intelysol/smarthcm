# Testing & Verification Guide

## Test Suite Structure
The test suite for Shared Services 2.0 covers unit and feature test scenarios:

| Test File | Focus Area |
|---|---|
| [`ServiceCatalogAndDynamicFormRulesTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/SharedServices/ServiceCatalogAndDynamicFormRulesTest.php) | Categories, popular services, dynamic form schemas & conditional rules. |
| [`ServiceRoutingAndSpecialistTeamsTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/SharedServices/ServiceRoutingAndSpecialistTeamsTest.php) | Intelligent routing to specialized queues, ER queues & team workload capacity. |
| [`ServiceDuplicateDetectionAndMergeTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/SharedServices/ServiceDuplicateDetectionAndMergeTest.php) | Algorithmic duplicate heuristics and non-destructive bidirectional ticket merging. |
| [`ServiceFeedbackAndCsatAnalyticsTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/SharedServices/ServiceFeedbackAndCsatAnalyticsTest.php) | CSAT feedback submission, multi-factor scoring & satisfaction percentage metrics. |
| [`ServiceAutomationAndDocumentFulfillmentTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/SharedServices/ServiceAutomationAndDocumentFulfillmentTest.php) | Template rendering against Core HR data, document vault creation & auto-resolution. |
| [`UnifiedSearchAndAiAdvisoryTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/SharedServices/UnifiedSearchAndAiAdvisoryTest.php) | Token-aware single-box search, deflection matching & advisory response drafting. |
| [`OmnichannelIntakeAndSecurityTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/SharedServices/OmnichannelIntakeAndSecurityTest.php) | Ingestion from Email/Webhooks, employee matching & multi-tenant isolation. |

---

## Running the Tests
Execute the test suite using PHPUnit / Artisan:

```powershell
# Run Shared Services 2.0 Test Suite
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test tests/Feature/SharedServices

# Run Full Self-Service & Shared Services Test Suites
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test --filter=Service
```
