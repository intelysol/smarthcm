# Operational Runbook: Search Indexing & Query Failure

## 1. Overview
- **ID:** `rb-search-failure`
- **Severity:** SEV-2 (Significant)
- **Component:** Search Engine / Laravel Scout / Elasticsearch / Meilisearch / Database Fallback
- **Trigger:** Search queries failing or search index queue backlogging > 500 items.

---

## 2. Immediate Diagnostic Steps
1. **Check Search Cluster Health:**
   ```bash
   # If using Meilisearch:
   curl -s http://127.0.0.1:7700/health | jq .
   # If using Elasticsearch:
   curl -s http://127.0.0.1:9200/_cluster/health | jq .
   ```
2. **Inspect Search Query Exceptions in Logs:**
   ```bash
   grep -i "scout" storage/logs/laravel.log | tail -n 20
   ```

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Search Node Unresponsive**
  - Restart search daemon:
    ```bash
    systemctl restart meilisearch
    ```
- **Scenario B: Desynchronized or Corrupted Search Index**
  - Trigger background re-indexing for affected domain models:
    ```bash
    php artisan scout:flush "App\Domains\Employee\Models\Employee"
    php artisan scout:import "App\Domains\Employee\Models\Employee"
    ```
- **Scenario C: Search Engine Outage Fallback**
  - Temporarily switch `SCOUT_DRIVER=database` in `.env` to ensure searches fall back to standard SQL `LIKE` queries without user disruption:
    ```bash
    php artisan config:cache
    ```

---

## 4. Verification & Post-Resolution
- Execute search query from global command palette (`Cmd/Ctrl + K`) in the UI.
- Verify instant search results return correctly with tenant isolation preserved.
