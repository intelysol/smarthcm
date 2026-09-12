# Analytics platform technical design

The `Analytics` bounded context owns tenant-scoped facts, dimensions, KPI
definitions and calculated KPI values, plus dashboard and report metadata.
Source domains publish facts into the operational store; KPI calculations read
only the tenant-scoped fact set and persist period values for repeatable,
auditable reporting.

The existing dataset, ETL-run, snapshot and report tables provide extension
points for scheduled refresh, warehouse adapters, exports and forecasting.
Dashboard layouts are JSON widget graphs so the React designer can evolve
without database migrations. Security rules remain part of dataset/dashboard
metadata and are evaluated before exposing results.
