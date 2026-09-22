# Operational Runbook: LLM AI Provider Outage & Circuit Breaker

## 1. Overview
- **ID:** `rb-ai-provider-failure`
- **Severity:** SEV-3 (Minor / Feature Degraded)
- **Component:** AI Platform / External LLM Gateway / Circuit Breaker
- **Trigger:** `ai_circuit_breaker_state == 1` or external LLM timeouts > 10% over 5m.

---

## 2. Immediate Diagnostic Steps
1. **Check AI Gateway Probes:**
   ```bash
   curl -s https://app.smarthcm.com/health/dependencies | jq .dependencies.ai
   ```
2. **Inspect AI Service Logs:**
   ```bash
   grep -i "EmployeeAi" storage/logs/laravel.log | grep -E "ERROR|WARNING" | tail -n 20
   ```
3. **Verify Upstream Provider Status:**
   - Check OpenAI / Anthropic / Azure OpenAI status page for regional outages.

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: LLM Upstream Outage or High Latency**
  - Verify Circuit Breaker automatically opened:
    - When tripped, `EmployeeAiActionService` serves deterministic rule-based responses (e.g., standard policy lookup links, pre-configured FAQ answers).
    - UI displays: *"AI Assistant is operating in offline mode. Standard search and self-service remain fully functional."*
- **Scenario B: Rate Limits Exceeded (HTTP 429)**
  - Failover to secondary configured LLM provider in `.env` (e.g., Anthropic Claude or Azure OpenAI deployment).
- **Scenario C: Prompt Injection / Safety Filter False Positives**
  - Review blocked prompt logs in AI Governance log table.
  - Adjust moderation sensitivity thresholds in `config/ai.php`.

---

## 4. Verification & Post-Resolution
- Test AI concierge query from employee self-service portal.
- Verify circuit breaker transitions from `open` to `half-open`, then `closed`.
