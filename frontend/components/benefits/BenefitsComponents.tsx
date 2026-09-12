import React, { useState } from 'react';

// 1. Benefit Coverage Selector
export interface CoverageTier {
  id: string;
  name: string;
  code: string;
  coverage_multiplier: number;
  fixed_employee_cost?: number;
  max_dependents: number;
}

export function BenefitCoverageSelector({
  tiers,
  selectedId,
  onSelect,
}: {
  tiers: CoverageTier[];
  selectedId?: string;
  onSelect: (tier: CoverageTier) => void;
}) {
  return (
    <div className="space-y-2">
      <label className="block text-xs font-semibold text-slate-300 uppercase tracking-wider">
        Select Coverage Level
      </label>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-2">
        {tiers.map((tier) => (
          <button
            key={tier.id}
            type="button"
            onClick={() => onSelect(tier)}
            className={`p-3 rounded-lg text-left border transition flex items-center justify-between ${
              selectedId === tier.id
                ? 'bg-indigo-950/60 border-indigo-500 text-white shadow-sm'
                : 'bg-slate-900 border-slate-800 text-slate-300 hover:border-slate-700'
            }`}
          >
            <div>
              <div className="text-sm font-semibold capitalize">{tier.name.replace(/_/g, ' ')}</div>
              <div className="text-xs text-slate-400">Max dependents: {tier.max_dependents}</div>
            </div>
            <div className="text-xs font-mono font-bold text-emerald-400">
              {tier.coverage_multiplier}x
            </div>
          </button>
        ))}
      </div>
    </div>
  );
}

// 2. Dependent Selector (Reusing Epic 2.32)
export interface DependentItem {
  family_member_id: string;
  name: string;
  relationship: string;
  date_of_birth?: string;
  is_selected?: boolean;
}

export function DependentSelector({
  dependents,
  maxAllowed,
  onChange,
}: {
  dependents: DependentItem[];
  maxAllowed: number;
  onChange: (selected: DependentItem[]) => void;
}) {
  const [selectedIds, setSelectedIds] = useState<string[]>([]);

  const handleToggle = (dep: DependentItem) => {
    let next: string[];
    if (selectedIds.includes(dep.family_member_id)) {
      next = selectedIds.filter((id) => id !== dep.family_member_id);
    } else {
      if (maxAllowed > 0 && selectedIds.length >= maxAllowed) {
        alert(`You can only select up to ${maxAllowed} dependents for this coverage tier.`);
        return;
      }
      next = [...selectedIds, dep.family_member_id];
    }
    setSelectedIds(next);
    onChange(dependents.filter((d) => next.includes(d.family_member_id)));
  };

  return (
    <div className="space-y-3">
      <div className="flex items-center justify-between">
        <label className="text-xs font-semibold text-slate-300 uppercase tracking-wider">
          Enrolled Dependents (Epic 2.32)
        </label>
        <span className="text-xs font-mono text-slate-400">
          Selected: {selectedIds.length} / {maxAllowed > 0 ? maxAllowed : 'Unlimited'}
        </span>
      </div>
      <div className="space-y-2">
        {dependents.map((dep) => (
          <label
            key={dep.family_member_id}
            className={`flex items-center justify-between p-3 rounded-lg border cursor-pointer transition ${
              selectedIds.includes(dep.family_member_id)
                ? 'bg-slate-900 border-indigo-500 text-white'
                : 'bg-slate-950 border-slate-800 text-slate-300 hover:border-slate-700'
            }`}
          >
            <div className="flex items-center gap-3">
              <input
                type="checkbox"
                checked={selectedIds.includes(dep.family_member_id)}
                onChange={() => handleToggle(dep)}
                className="rounded border-slate-700 text-indigo-600 focus:ring-indigo-500"
              />
              <div>
                <div className="text-sm font-semibold">{dep.name}</div>
                <div className="text-xs text-slate-400 capitalize">{dep.relationship}</div>
              </div>
            </div>
            {dep.date_of_birth && (
              <span className="text-xs font-mono text-slate-500">{dep.date_of_birth}</span>
            )}
          </label>
        ))}
      </div>
    </div>
  );
}

// 3. Beneficiary Editor (Validation: Total Allocation = 100%)
export interface BeneficiaryItem {
  id: string;
  name: string;
  relationship: string;
  percentage_allocation: number;
  is_primary: boolean;
  is_contingent: boolean;
}

export function BeneficiaryEditor({
  beneficiaries,
  onChange,
}: {
  beneficiaries: BeneficiaryItem[];
  onChange: (items: BeneficiaryItem[]) => void;
}) {
  const total = beneficiaries.reduce((sum, b) => sum + (Number(b.percentage_allocation) || 0), 0);
  const isValid = Math.abs(total - 100.0) < 0.01;

  const handleUpdate = (index: number, field: keyof BeneficiaryItem, value: any) => {
    const updated = [...beneficiaries];
    updated[index] = { ...updated[index], [field]: value };
    onChange(updated);
  };

  const handleAdd = () => {
    onChange([
      ...beneficiaries,
      {
        id: Math.random().toString(),
        name: '',
        relationship: 'spouse',
        percentage_allocation: 0,
        is_primary: true,
        is_contingent: false,
      },
    ]);
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <label className="text-xs font-semibold text-slate-300 uppercase tracking-wider">
          Designate Beneficiaries
        </label>
        <span
          className={`text-xs font-mono font-bold px-2 py-0.5 rounded ${
            isValid
              ? 'bg-emerald-950 text-emerald-400 border border-emerald-800'
              : 'bg-red-950 text-red-400 border border-red-800 animate-pulse'
          }`}
        >
          Total Allocation: {total.toFixed(1)}% {isValid ? '(Valid)' : '(Must equal 100%)'}
        </span>
      </div>

      <div className="space-y-2">
        {beneficiaries.map((b, idx) => (
          <div
            key={b.id}
            className="flex flex-col sm:flex-row items-center gap-2 p-3 bg-slate-900 border border-slate-800 rounded-lg"
          >
            <input
              type="text"
              placeholder="Full Name"
              value={b.name}
              onChange={(e) => handleUpdate(idx, 'name', e.target.value)}
              className="flex-1 bg-slate-950 border border-slate-800 rounded px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none"
            />
            <select
              value={b.relationship}
              onChange={(e) => handleUpdate(idx, 'relationship', e.target.value)}
              className="bg-slate-950 border border-slate-800 rounded px-2.5 py-1.5 text-xs text-slate-200 focus:outline-none"
            >
              <option value="spouse">Spouse</option>
              <option value="child">Child</option>
              <option value="parent">Parent</option>
              <option value="sibling">Sibling</option>
              <option value="estate">Estate</option>
              <option value="trust">Trust</option>
            </select>
            <div className="flex items-center gap-1">
              <input
                type="number"
                min="0"
                max="100"
                step="1"
                value={b.percentage_allocation}
                onChange={(e) => handleUpdate(idx, 'percentage_allocation', parseFloat(e.target.value) || 0)}
                className="w-16 bg-slate-950 border border-slate-800 rounded px-2 py-1.5 text-xs font-mono text-right text-emerald-400 focus:outline-none"
              />
              <span className="text-xs text-slate-400">%</span>
            </div>
          </div>
        ))}
      </div>

      <button
        type="button"
        onClick={handleAdd}
        className="text-xs text-indigo-400 hover:text-indigo-300 font-medium flex items-center gap-1.5"
      >
        + Add Beneficiary
      </button>
    </div>
  );
}

// 4. Benefit Plan Comparison Component
export function BenefitPlanComparison({
  plans,
}: {
  plans: Array<{
    id: string;
    name: string;
    category?: string;
    employee_cost_monthly: number;
    employer_cost_monthly: number;
    annual_limit: string | number;
    waiting_period_days: number;
  }>;
}) {
  return (
    <div className="border border-slate-800 rounded-xl overflow-hidden bg-slate-900 shadow-xl">
      <div className="p-3 bg-slate-950 border-b border-slate-800 text-xs font-semibold text-slate-300 uppercase">
        Factual Plan Comparison (No Medical Advice)
      </div>
      <table className="w-full text-left text-xs text-slate-300">
        <thead className="bg-slate-950/60 text-slate-400 border-b border-slate-800">
          <tr>
            <th className="p-3 font-semibold">Plan Name</th>
            <th className="p-3 font-semibold">Employee Cost</th>
            <th className="p-3 font-semibold">Employer Cost</th>
            <th className="p-3 font-semibold">Annual Limit</th>
            <th className="p-3 font-semibold">Waiting Period</th>
          </tr>
        </thead>
        <tbody className="divide-y divide-slate-800">
          {plans.map((p) => (
            <tr key={p.id} className="hover:bg-slate-850">
              <td className="p-3 font-bold text-white">{p.name}</td>
              <td className="p-3 font-mono text-emerald-400">${p.employee_cost_monthly.toFixed(2)}/mo</td>
              <td className="p-3 font-mono text-indigo-400">${p.employer_cost_monthly.toFixed(2)}/mo</td>
              <td className="p-3">{p.annual_limit}</td>
              <td className="p-3">{p.waiting_period_days} days</td>
            </tr>
          ))}
        </tbody>
      </table>
    </div>
  );
}

// 5. Provider Integration Status Card
export function ProviderIntegrationStatus({
  status,
  providerName,
  exportedAt,
  recordCount,
}: {
  status: string;
  providerName: string;
  exportedAt?: string;
  recordCount: number;
}) {
  return (
    <div className="p-4 bg-slate-900 border border-slate-800 rounded-xl flex items-center justify-between">
      <div>
        <div className="text-xs text-slate-400 font-mono">PROVIDER ORCHESTRATION</div>
        <div className="text-sm font-bold text-white mt-0.5">{providerName}</div>
        <div className="text-xs text-slate-500 mt-1">
          {recordCount} records exported | {exportedAt ?? 'Pending sync'}
        </div>
      </div>
      <span className="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-emerald-950 text-emerald-400 border border-emerald-800">
        {status}
      </span>
    </div>
  );
}

// 6. Payroll Reconciliation Status Card
export function PayrollIntegrationStatus({
  periodName,
  matchedCount,
  varianceCount,
  status,
}: {
  periodName: string;
  matchedCount: number;
  varianceCount: number;
  status: string;
}) {
  return (
    <div className="p-4 bg-slate-900 border border-slate-800 rounded-xl flex items-center justify-between">
      <div>
        <div className="text-xs text-slate-400 font-mono">PAYROLL DEDUCTION RECONCILIATION</div>
        <div className="text-sm font-bold text-white mt-0.5">{periodName}</div>
        <div className="text-xs text-slate-400 mt-1">
          Matched: <span className="text-emerald-400 font-mono font-bold">{matchedCount}</span> | Discrepancies: <span className="text-red-400 font-mono font-bold">{varianceCount}</span>
        </div>
      </div>
      <span className="px-2.5 py-1 rounded-full text-xs font-semibold uppercase tracking-wider bg-indigo-950 text-indigo-400 border border-indigo-800">
        {status}
      </span>
    </div>
  );
}
