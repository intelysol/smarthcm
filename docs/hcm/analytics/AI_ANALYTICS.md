# AI People Analytics Assistant Architecture

## Non-Autonomous Design Principles
1. **No Arbitrary SQL Execution**: Natural language inquiries are mapped to structured, whitelist-validated analytical metrics (`HCM_HEADCOUNT_ACTIVE`, `HCM_TURNOVER_RATE`, `HCM_ATTENDANCE_RATE`).
2. **Advisory Summaries Only**: AI outputs analytical explanations and cites underlying datasets (Core HR, Workforce Snapshots, Attendance Engine).
3. **No Employment Decisions**: The AI engine is strictly prohibited from recommending promotions, salary adjustments, disciplinary actions, or terminations.
