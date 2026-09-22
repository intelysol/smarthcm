# Enterprise UX, Error Handling & Interactive Standard

## 1. Non-Negotiable Interactive States

Every interactive element must strictly reside in one of four states:
1. **WORKING**: Active, bound to event handler, performs action or asynchronous call with visual feedback.
2. **DISABLED WITH REASON**: Inactive with `disabled` attribute, `cursor-not-allowed`, and a clear `title` or tooltip explaining the prerequisite.
3. **CONDITIONALLY HIDDEN**: Excluded from DOM when user lacks permission or prerequisite data.
4. **INTENTIONALLY NON-ACTIONABLE**: Styled distinctly as a static badge, label, or chip, never as a clickable button or faux link.

## 2. Asynchronous Action Lifecycle

```text
[IDLE STATE] 
     │
     ▼ (User Click / Submit)
[LOADING STATE]  ──> Disable submit button, show spinner, store original text
     │
     ├──────────────────────┬──────────────────────┐
     ▼                      ▼                      ▼
[SUCCESS STATE]       [VALIDATION ERROR]     [SERVER ERROR / 419 / 500]
- Toast Notification  - Highlight fields     - Toast with Request ID
- Refresh Data        - Inline message       - Session renewal if 419
- Close Modal         - Re-enable submit     - Re-enable submit
```

## 3. Standard Toast Notification System

Invoked globally via `window.showNotification(type, message, title, referenceId)`:
- **Success (`#16805C`)**: Clean check icon, 4-second auto-dismiss.
- **Warning (`#B7791F`)**: Exclamation triangle icon, 6-second auto-dismiss.
- **Danger (`#C0392B`)**: X-circle icon, persistent until dismissed, displays traceable `Request ID`.
- **Info (`#1E3A5F`)**: Info circle icon, corporate navy accent.

## 4. Double-Submit Protection

All forms and async action buttons are automatically locked during request flight:
```javascript
function submitAsync(btn, task) {
    if (btn.dataset.loading === 'true') return;
    btn.dataset.loading = 'true';
    btn.disabled = true;
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Processing...';
    task().finally(() => {
        btn.disabled = false;
        btn.dataset.loading = 'false';
        btn.innerHTML = originalHtml;
    });
}
```
