# Workforce Labor Cost Model

## Cost Categories

- `base_salary`: Base monthly/annual contracted wage.
- `overtime`: Estimated or budgeted overtime wages.
- `bonus`: Discretionary and performance incentive compensation.
- `commission`: Sales commission pools.
- `benefits`: Health insurance, retirement/pension contributions, allowances.
- `statutory_taxes`: Employer social security, unemployment, and national insurance taxes.
- `recruitment`: Agency fees, advertising, background screening costs.
- `training`: Formal upskilling and certification allowances.
- `relocation`: Relocation stipends for specialized talent.

## Financial Precision
- Stored as `decimal(18, 2)`.
- Rounded using standard rounding (`round($val, 2)`).
- Zero floating-point drift.
