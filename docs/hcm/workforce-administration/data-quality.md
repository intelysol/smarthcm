# Data Quality Engine & Monitoring

## 1. Objective & Dimensions
The Data Quality Engine proactively scans employee master and transactional records across 5 core dimensions:
1. **Completeness**:
   - Missing reporting manager
   - Missing department / cost center
   - Missing emergency contact or primary address
   - Missing required statutory documents
2. **Validity**:
   - Invalid national identifier formats
   - Inverted employment or probation dates
   - Malformed corporate email addresses
3. **Consistency**:
   - Position department vs employee department mismatch
   - Active employment status with past termination date
   - Ineligible benefit plan enrollments
4. **Uniqueness**:
   - Duplicate tax IDs or passport numbers
   - Duplicate employee numbers
5. **Freshness**:
   - Unreviewed profiles exceeding annual review window
   - Expired certifications and compliance proofs

## 2. Non-Destructive Scanning
Quality scans never modify underlying domain records directly. They generate actionable `OpsDataQualityResult` entries and compute aggregate data health scores (`0 - 100%`).
