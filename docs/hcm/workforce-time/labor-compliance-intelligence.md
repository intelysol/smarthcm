# Labor Compliance Intelligence & Fatigue Guard

## 1. Regulatory Rules Evaluated
- `MAX_DAILY_HOURS`: Flags shifts exceeding statutory daily caps (default 720m / 12h)
- `MIN_REST_PERIOD`: Flags consecutive shifts with rest intervals below statutory requirements (default 660m / 11h)
- `MANDATORY_MEAL_BREAK`: Flags shifts > 6 hours without at least a 30-minute meal break
- `CONSECUTIVE_WORK_DAYS`: Monitors work streaks exceeding 6 consecutive days

## 2. Severity & Waivers
- Severity levels: `warning`, `critical`, `breach`
- Authorized managers can waive checks with documented justification recorded in `hcm_labor_compliance_checks`.