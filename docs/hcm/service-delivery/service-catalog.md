# Service Catalog & Dynamic Form Engine

## Overview
The **HR Service Catalog** exposes published, categorized HR offerings to employees and managers. Each service item can define dynamic form schemas, effective-dated versions, and conditional business rules.

---

## Data Model Structure

* **`HrServiceCategory`**: High-level groupings (e.g., *Payroll & Benefits*, *HR Letters & Verification*, *Leaves & Attendance*, *Workplace Inquiries*).
* **`HrServiceDefinition`**: Individual service items (e.g., *Employment Certificate Request*, *Tax Withholding Inquiry*).
* **`HrServiceVersion`**: Versioned configurations ensuring historical form submissions remain immutable even as definitions evolve.
* **`HrServiceFormDefinition`**: JSON-based schema definitions containing input controls (`text`, `number`, `select`, `date`, `file_upload`, `textarea`).
* **`HrServiceFormRule`**: Conditional visibility, mandatory requirements, and field disabling based on prior inputs.

---

## Dynamic Form Schema Example

```json
[
  {
    "key": "letter_purpose",
    "label": "Purpose of Letter",
    "type": "select",
    "options": ["bank_account", "visa_application", "rental_agreement", "other"],
    "required": true
  },
  {
    "key": "embassy_name",
    "label": "Target Embassy / Consulate",
    "type": "text",
    "required": false
  },
  {
    "key": "include_salary",
    "label": "Include Monthly Base Salary?",
    "type": "boolean",
    "default": false
  }
]
```

---

## Form Rules Engine
Form rules dynamically manipulate UI controls at runtime:
```json
{
  "field_key": "embassy_name",
  "rule_type": "visibility",
  "condition_logic": {
    "field": "letter_purpose",
    "operator": "equals",
    "value": "visa_application"
  },
  "action_effect": "show_and_require"
}
```
If `letter_purpose` is selected as `visa_application`, the frontend dynamically displays `embassy_name` and enforces validation before submission.
