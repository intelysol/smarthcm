# Pricing Engine & Proration Architecture

## 1. Supported Pricing Models

The `PricingEngine` dynamically computes component prices across 6 configurable commercial models:

1. **Flat (`flat`)**:
   Fixed recurring price regardless of usage or quantities.
2. **Per Seat / Per Unit (`per_seat`, `per_unit`)**:
   Charged per unit. Supports `included_quantity` (e.g. first 10 seats free) and separate `overage_price` for consumption above included units.
3. **Tiered / Bracket (`tiered`, `volume`)**:
   All units are charged at the single unit rate corresponding to the volume bracket reached.
4. **Graduated / Step (`graduated`)**:
   Units are charged progressively through tier brackets. (e.g. units 1-10 at \$20, units 11-50 at \$15).
5. **Overage Metered (`overage`)**:
   Only consumption strictly exceeding the included allocation is charged at the overage rate.

---

## 2. Deterministic Proration Calculation

The `ProrationCalculator` manages mid-cycle plan upgrades, downgrades, and seat changes:

$$\text{Total Period Seconds} (T) = \text{End Timestamp} - \text{Start Timestamp}$$
$$\text{Remaining Period Seconds} (R) = \text{End Timestamp} - \text{Effective Change Timestamp}$$
$$\text{Remaining Ratio} (P) = \frac{R}{T}$$

$$\text{Unused Current Plan Credit} = \text{Current Plan Monthly Rate} \times P$$
$$\text{New Plan Prorated Charge} = \text{New Plan Monthly Rate} \times P$$
$$\text{Net Prorated Adjustment} = \text{New Plan Charge} - \text{Unused Credit}$$

- If **Net Adjustment > 0**: An immediate prorated invoice charge is created.
- If **Net Adjustment < 0**: A tenant wallet credit is automatically issued to offset subsequent renewals.
