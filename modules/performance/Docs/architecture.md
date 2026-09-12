# Performance Management Architecture

Performance owns cycles, goals, reviews, feedback, competencies, calibration, final outcomes, development plans, PIPs, recognition, and immutable history. Every tenant-owned record resolves its tenant through trusted request/job context.

The cycle state machine is `draft → configured → open → goal_setting → in_progress → self_assessment → manager_assessment → calibration|finalization → closed`, with `cancelled` and `archived` alternatives. Goal updates use optimistic version checks and append-only progress records.

Performance publishes tenant-aware events through the shared event store and audits sensitive mutations through the shared audit service. It exposes contracts for compensation, learning, and succession but never changes pay, benefits, or payroll.

Anonymous feedback responses are stored separately from requests; consumer queries must apply the configured minimum threshold before exposing an aggregate, and must never reveal the requester identity without restricted authorization.
