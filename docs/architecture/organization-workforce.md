# Organization & Workforce Structure

The Organization domain retains its existing tenant-scoped CRUD entities and extends the workforce structure with divisions, profit centers, job families, jobs, positions, and dated organization assignments. A position is an authorized seat, not a job definition. `PositionControlService` enforces approved headcount before a position may be filled unless an explicit caller-approved override is supplied.

The schema uses tenant-scoped identifiers and parent references for hierarchy. Assignment records preserve effective dating for later employee integration without adding further direct employee foreign keys.
