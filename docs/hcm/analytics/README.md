# HCM Reporting, Workforce Analytics & People Analytics

## Overview
Epic 2.23 delivers an enterprise-grade, read-oriented analytics and reporting platform for Flow HCM. It converts transactional data across all 22 HCM operational modules into real-time management dashboards, workforce analytics, demographic snapshots, and AI-assisted insights without modifying core transactional tables or executing arbitrary SQL.

## Architecture Highlights
- **Strictly Read-Oriented**: Consumes events and domain snapshots; does not own or mutate transactional records.
- **Platform Analytics Reuse**: Leverages existing Platform Analytics dimensions, facts, metric registry, and reporting pipelines.
- **Reproducible Metric Versioning**: Supports `effective_from` and `effective_to` versioning for all registered KPIs.
- **Privacy by Design**: Enforces strict minimum group threshold ($\ge 5$) suppression on employee surveys to prevent de-anonymization.
- **Row-Level & Manager Scope Security**: Granular permissions (`hcm.analytics.payroll.view`, `hcm.analytics.er.aggregate.view`) and manager team isolation.
