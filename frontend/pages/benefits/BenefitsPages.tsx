import React, { useState } from 'react';
import {
  BenefitCoverageSelector,
  DependentSelector,
  BeneficiaryEditor,
  BenefitPlanComparison,
  ProviderIntegrationStatus,
  PayrollIntegrationStatus,
} from '../../components/benefits/BenefitsComponents';

// 1. Benefits Dashboard Page
export function BenefitsDashboard() {
  return (
    <div className="p-6 space-y-6 bg-slate-950 min-h-screen text-slate-100">
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold">HCM Benefits Administration Command Center</h1>
          <p className="text-xs text-slate-400">Enterprise Plan Governance, Open Enrollment & Deductions</p>
        </div>
        <div className="flex gap-2">
          <button className="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 rounded text-xs font-semibold">
            + New Campaign
          </button>
        </div>
      </div>

      {/* Metric Cards */}
      <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div className="p-4 bg-slate-900 border border-slate-800 rounded-xl">
          <div className="text-xs text-slate-400">Enrolled Population</div>
          <div className="text-2xl font-bold font-mono text-emerald-400 mt-1">87.4%</div>
          <div className="text-[11px] text-slate-500 mt-1">1,420 Active Employees</div>
        </div>
        <div className="p-4 bg-slate-900 border border-slate-800 rounded-xl">
          <div className="text-xs text-slate-400">Open Campaigns</div>
          <div className="text-2xl font-bold font-mono text-indigo-400 mt-1">2027 Annual</div>
          <div className="text-[11px] text-emerald-400 mt-1">Closes in 18 days</div>
        </div>
        <div className="p-4 bg-slate-900 border border-slate-800 rounded-xl">
          <div className="text-xs text-slate-400">Pending Life Events</div>
          <div className="text-2xl font-bold font-mono text-pink-400 mt-1">14</div>
          <div className="text-[11px] text-slate-500 mt-1">Requires document check</div>
        </div>
        <div className="p-4 bg-slate-900 border border-slate-800 rounded-xl">
          <div className="text-xs text-slate-400">Payroll Match Status</div>
          <div className="text-2xl font-bold font-mono text-amber-400 mt-1">99.8%</div>
          <div className="text-[11px] text-slate-500 mt-1">2 Discrepancies flagged</div>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <ProviderIntegrationStatus
          providerName="Aetna Global Health"
          status="Synced"
          recordCount={1250}
          exportedAt="2026-09-04 18:00"
        />
        <PayrollIntegrationStatus
          periodName="September 2026 Regular Cycle"
          matchedCount={1418}
          varianceCount={2}
          status="Reconciled"
        />
      </div>
    </div>
  );
}

// 2. Benefit Program List Page
export function BenefitProgramList() {
  return (
    <div className="p-6 space-y-4 bg-slate-950 min-h-screen text-slate-100">
      <h1 className="text-2xl font-bold">Configured Benefit Programs</h1>
      <p className="text-xs text-slate-400">Umbrella programs containing multiple plans and versions.</p>
      <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
        {['Health & Medical', 'Employee Protection', 'Retirement & Savings'].map((prog, idx) => (
          <div key={idx} className="p-5 bg-slate-900 border border-slate-800 rounded-xl">
            <h3 className="text-base font-bold text-white">{prog}</h3>
            <p className="text-xs text-slate-400 mt-1">Comprehensive benefits package.</p>
            <div className="mt-4 pt-3 border-t border-slate-800 text-xs text-emerald-400">
              Active • v1.0
            </div>
          </div>
        ))}
      </div>
    </div>
  );
}

// 3. Benefit Plan List & Builder
export function BenefitPlanBuilder() {
  return (
    <div className="p-6 max-w-4xl mx-auto space-y-6 bg-slate-950 min-h-screen text-slate-100">
      <h1 className="text-2xl font-bold">Benefit Plan Studio</h1>
      <div className="p-6 bg-slate-900 border border-slate-800 rounded-xl space-y-4">
        <div className="grid grid-cols-2 gap-4">
          <div>
            <label className="text-xs text-slate-400 block mb-1">Plan Code</label>
            <input className="w-full bg-slate-950 border border-slate-800 rounded p-2 text-xs" defaultValue="MED-PREM-01" />
          </div>
          <div>
            <label className="text-xs text-slate-400 block mb-1">Plan Name</label>
            <input className="w-full bg-slate-950 border border-slate-800 rounded p-2 text-xs" defaultValue="Comprehensive Executive Health" />
          </div>
        </div>
        <div className="grid grid-cols-3 gap-4">
          <div>
            <label className="text-xs text-slate-400 block mb-1">Employee Cost ($/mo)</label>
            <input type="number" className="w-full bg-slate-950 border border-slate-800 rounded p-2 text-xs" defaultValue="45.00" />
          </div>
          <div>
            <label className="text-xs text-slate-400 block mb-1">Employer Cost ($/mo)</label>
            <input type="number" className="w-full bg-slate-950 border border-slate-800 rounded p-2 text-xs" defaultValue="180.00" />
          </div>
          <div>
            <label className="text-xs text-slate-400 block mb-1">Waiting Period (Days)</label>
            <input type="number" className="w-full bg-slate-950 border border-slate-800 rounded p-2 text-xs" defaultValue="30" />
          </div>
        </div>
      </div>
    </div>
  );
}

// 4. Benefit Eligibility Builder
export function BenefitEligibilityBuilder() {
  return (
    <div className="p-6 max-w-4xl mx-auto space-y-4 bg-slate-950 min-h-screen text-slate-100">
      <h1 className="text-2xl font-bold">Rule Engine Eligibility Criteria</h1>
      <p className="text-xs text-slate-400">Configure multi-attribute criteria evaluated dynamically by shared RuleEngine.</p>
      <div className="p-5 bg-slate-900 border border-slate-800 rounded-xl space-y-3">
        <div className="text-xs text-indigo-400 font-semibold uppercase">Active Eligibility Filters</div>
        <div className="flex flex-wrap gap-2 text-xs">
          <span className="px-2 py-1 bg-slate-800 rounded border border-slate-700">Employment Type: Full-Time</span>
          <span className="px-2 py-1 bg-slate-800 rounded border border-slate-700">Tenure: &gt;= 30 Days</span>
          <span className="px-2 py-1 bg-slate-800 rounded border border-slate-700">Location: USA / PK</span>
          <span className="px-2 py-1 bg-slate-800 rounded border border-slate-700">Status: Active</span>
        </div>
      </div>
    </div>
  );
}

// 5. Open Enrollment Dashboard
export function OpenEnrollmentDashboard() {
  return (
    <div className="p-6 space-y-6 bg-slate-950 min-h-screen text-slate-100">
      <h1 className="text-2xl font-bold">Open Enrollment 2027 Campaign</h1>
      <div className="p-6 bg-slate-900 border border-slate-800 rounded-xl">
        <div className="flex justify-between items-center mb-4">
          <span className="text-sm font-semibold">Total Completion Progress</span>
          <span className="font-mono text-emerald-400 font-bold">91.4%</span>
        </div>
        <div className="w-full h-3 bg-slate-800 rounded-full overflow-hidden flex">
          <div className="bg-emerald-500 h-full" style={{ width: '82%' }}></div>
          <div className="bg-amber-500 h-full" style={{ width: '9%' }}></div>
        </div>
      </div>
    </div>
  );
}

// 6. Benefits Enrollment Wizard (Self-Service)
export function BenefitsEnrollmentWizard() {
  const [step, setStep] = useState(1);
  return (
    <div className="p-6 max-w-4xl mx-auto space-y-6 bg-slate-950 min-h-screen text-slate-100">
      <div className="flex items-center justify-between border-b border-slate-800 pb-4">
        <h1 className="text-2xl font-bold">Step {step} of 9 — Benefits Enrollment</h1>
        <div className="flex gap-2">
          {step > 1 && (
            <button onClick={() => setStep(step - 1)} className="px-3 py-1.5 bg-slate-800 rounded text-xs">
              Back
            </button>
          )}
          {step < 9 && (
            <button onClick={() => setStep(step + 1)} className="px-3 py-1.5 bg-indigo-600 rounded text-xs">
              Next Step
            </button>
          )}
        </div>
      </div>
      <div className="p-6 bg-slate-900 border border-slate-800 rounded-xl">
        <p className="text-sm text-slate-300">
          Guided selection covering eligibility, plan comparison, coverage levels, family dependents, beneficiary allocations, cost projections, and review.
        </p>
      </div>
    </div>
  );
}

// 7. Benefit Statement
export function BenefitStatement() {
  return (
    <div className="p-6 max-w-4xl mx-auto space-y-6 bg-slate-950 min-h-screen text-slate-100">
      <h1 className="text-2xl font-bold">Annual Total Benefits Statement</h1>
      <div className="p-6 bg-slate-900 border border-slate-800 rounded-xl space-y-4">
        <div className="flex justify-between items-center">
          <span className="text-slate-400 text-sm">Estimated Total Benefits Package Value:</span>
          <span className="text-2xl font-bold font-mono text-emerald-400">$18,450.00</span>
        </div>
      </div>
    </div>
  );
}
