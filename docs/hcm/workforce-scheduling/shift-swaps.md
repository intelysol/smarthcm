# Shift Swaps & Peer Workflows

## Swap Lifecycle

Shift swaps facilitate flexible peer-to-peer schedule adjustments without bypassing operational governance.
Managed via `ShiftSwapService` and stored in `hcm_shift_swap_requests`:

```mermaid
sequenceDiagram
    participant A as Employee A
    participant S as Swap Service
    participant B as Employee B
    participant M as Manager

    A->>S: Request Swap (Assignment A with Assignment B)
    S->>S: Validate Both Workers for Skills, Leave, Rest, Hours
    alt Hard Violations Detected
        S-->>A: Validation Error (Swap Rejected)
    else Both Eligible
        S->>B: Swap Request Notification (Status: pending_peer)
        B->>S: Peer Responds (Accept)
        S->>M: Forward to Manager (Status: pending_manager)
        M->>S: Manager Approves
        S->>S: Swap Roster Assignments & Update Status to 'swapped'
        S-->>A: Notification: Swap Approved
        S-->>B: Notification: Swap Approved
    end
```

## Guardrails
- Cross-tenant swaps are strictly prohibited.
- Both sides of the swap must satisfy skills, working hours caps, and rest intervals.
- The manager approval step ensures operational oversight before active rosters are mutated.
