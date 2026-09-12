# Payroll Ingestion & Consumption Pipeline

## Idempotent Consumption
When a payroll run reaches `approved` status, `LaborCostAggregationService::ingestPayrollRun()` extracts the snapshot details:
```text
PayrollRun (Approved) 
         │
         ▼
Extract Calculation Snapshots ──> Generate HcmWorkforceCostLine
                                  (BASE_PAY, EMPLOYER_TAX)
```
- Checks existing `source_record_id` and `source_domain = 'payroll'` to guarantee idempotency.
- Never mutates payroll calculation records.
- Records original payroll currencies and handles conversions via tenant base currency.