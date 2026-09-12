# Testing & Quality Assurance Guide

## Test Suite Structure
Located under `tests/Feature/OrganizationDesign/`:

| Test Class | Focus Area | Assertions |
|---|---|---|
| [`JobArchitectureAndHierarchyTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/OrganizationDesign/JobArchitectureAndHierarchyTest.php) | Job Families, Sub-Families, Career Tracks, Career Levels, Job Levels. | Validates complete tree and hierarchy integrity. |
| [`JobProfileLifecycleAndVersioningTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/OrganizationDesign/JobProfileLifecycleAndVersioningTest.php) | Job Profile creation, skill & competency attachment, version snapshotting. | Validates version bumps and immutable snapshot persistence. |
| [`JobEvaluationScoringTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/OrganizationDesign/JobEvaluationScoringTest.php) | Multi-factor point scoring and non-binding job grade recommendation. | Validates score math and non-destructive grade linking. |
| [`OrganizationScenarioAndComparisonTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/OrganizationDesign/OrganizationScenarioAndComparisonTest.php) | Non-destructive org tree cloning and delta comparison. | Validates that Core HR remains completely untouched during scenario modeling. |
| [`ArchitectureImpactAnalysisTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/OrganizationDesign/ArchitectureImpactAnalysisTest.php) | Cross-domain impact analysis. | Validates blast radius calculations across positions, workers, requisitions, and pay bands. |
| [`ArchitectureHealthAndAdvisoryAiTest.php`](file:///c:/laragon/www/smarthcm/tests/Feature/OrganizationDesign/ArchitectureHealthAndAdvisoryAiTest.php) | Automated anomaly scans and advisory AI guardrails. | Validates health issue logging and `is_advisory_only: true` constraints. |

---

## Running Test Commands
```powershell
& "C:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe" artisan test tests/Feature/OrganizationDesign
```
