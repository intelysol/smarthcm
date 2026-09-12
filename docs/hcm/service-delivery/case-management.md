# Employee Relations & Case Management

## Request vs. Case Boundary

SmartHCM separates everyday service fulfillment from sensitive Employee Relations (ER) cases:

| Characteristic | HR Service Request (`HrServiceRequest`) | ER Case (`EmployeeRelationCase`) |
|---|---|---|
| **Scope** | Routine inquiries, benefits help, certificate requests, payroll questions. | Grievances, workplace harassment, misconduct investigations, disciplinary hearings. |
| **Visibility** | Employee, manager (if non-confidential), shared services agents. | Strictly isolated to authorized ER specialists, Legal, and Compliance officers. |
| **Workflow** | SLA-driven ticket resolution & dynamic forms. | Multi-party witness interviews, evidence vaults, formal investigation findings. |
| **Audit Requirement** | Standard service history. | Legally defensible, immutable chain-of-custody logging. |

---

## Seamless Escalation & Conversion
When a frontline Shared Services agent discovers that a routine inquiry involves workplace harassment or legal risk:
1. The agent invokes the conversion workflow (`ServiceRequestLinkingAndCaseConversion`).
2. An authoritative `EmployeeRelationCase` record is instantiated in the `EmployeeRelations` domain.
3. The origin `HrServiceRequest` is cross-linked via `HrServiceRequestLink` (`link_type: 'converted_to_case'`).
4. Access to the ticket is immediately restricted to prevent unauthorized disclosure.
