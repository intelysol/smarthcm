# Flow HCM Enterprise reference application

The Employee and Organization domains form the reference HCM application on
top of the shared platform services. Existing migrations provide the leave,
attendance, payroll, recruitment, and learning data stores; the HCM reference
controller exposes tenant-scoped workforce, leave, recruitment, payroll, and
learning summaries for executive and HR dashboards.

Business modules should continue to use shared Rules, Workflow, Documents,
Analytics, Communication, and Integration services. The reference dashboard
does not bypass tenant resolution or expose a client-supplied tenant id.
