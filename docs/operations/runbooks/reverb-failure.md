# Operational Runbook: Reverb WebSocket Broadcasting Failure

## 1. Overview
- **ID:** `rb-reverb-failure`
- **Severity:** SEV-2 (Significant)
- **Component:** Realtime / WebSockets / Laravel Reverb Daemon
- **Trigger:** `reverb_connection_status == 0` or client disconnect storms.

---

## 2. Immediate Diagnostic Steps
1. **Probe Reverb Process & Port Status:**
   ```bash
   # Check if Reverb process is active on port 8080
   netstat -tlpn | grep 8080
   systemctl status reverb
   ```
2. **Inspect Reverb Logs:**
   ```bash
   journalctl -u reverb -n 100 --no-pager
   ```
3. **Verify WebSocket Handshake:**
   ```bash
   curl -I "http://127.0.0.1:8080/app/reverb-key"
   ```

---

## 3. Mitigation & Recovery Procedures
- **Scenario A: Daemon Stopped or Crashed**
  - Restart Reverb daemon managed via Supervisor or Systemd:
    ```bash
    systemctl restart reverb
    ```
- **Scenario B: File Descriptor Exhaustion**
  - If `Too many open files` appears:
    - Increase `ulimit -n 65535` in Systemd unit file `/etc/systemd/system/reverb.service`:
      ```ini
      [Service]
      LimitNOFILE=65535
      ```
    - Run `systemctl daemon-reload && systemctl restart reverb`.
- **Scenario C: Client Graceful Fallback**
  - Verify frontend clients gracefully fall back to HTTP short-polling when WebSocket disconnect occurs.

---

## 4. Verification & Post-Resolution
- Verify WebSocket connection succeeds from browser console (`Echo.connector.pusher.connection.state === 'connected'`).
- Verify live notifications ping without delay.
