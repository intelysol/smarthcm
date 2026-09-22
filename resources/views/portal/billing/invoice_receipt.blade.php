<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice {{ $invoice->invoice_number }} &bull; Enterprise Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-6 sm:p-12">
    <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow-sm border border-slate-200 p-8 sm:p-12">
        <div class="flex justify-between items-start border-b border-slate-100 pb-8 mb-8">
            <div>
                <div class="flex items-center space-x-2 mb-2">
                    <span class="w-8 h-8 rounded-lg bg-[#1E3A5F] text-[#C9A227] font-black flex items-center justify-center text-sm">HCM</span>
                    <span class="font-bold text-xl text-[#1E3A5F]">SmartHCM Enterprise SaaS</span>
                </div>
                <p class="text-xs text-slate-500">Global Cloud Platform Operations</p>
                <p class="text-xs text-slate-500">Tax Registration: PK-FBR-9841284-A</p>
            </div>
            <div class="text-right">
                <span class="text-xs uppercase font-bold tracking-wider text-slate-400">Tax Invoice</span>
                <div class="text-xl font-mono font-black text-[#1E3A5F]">{{ $invoice->invoice_number }}</div>
                <p class="text-xs text-slate-500 mt-1">Issue Date: {{ $invoice->issue_date->format('M d, Y') }}</p>
                <p class="text-xs text-slate-500">Due Date: {{ $invoice->due_date->format('M d, Y') }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-8 mb-8 text-xs">
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-1">Billed To</span>
                <strong class="text-slate-800 text-sm block">{{ $tenant->name }}</strong>
                <p class="text-slate-600">{{ $tenant->primary_email ?? 'billing@' . $tenant->slug . '.internal' }}</p>
                <p class="text-slate-500">Tenant Code: {{ $tenant->tenant_code ?? $tenant->slug }}</p>
            </div>
            <div>
                <span class="text-[10px] uppercase font-bold text-slate-400 tracking-wider block mb-1">Billing Period</span>
                <p class="text-slate-700 font-semibold">
                    {{ $invoice->billing_period_start ? $invoice->billing_period_start->format('M d, Y') : '-' }} 
                    &mdash; 
                    {{ $invoice->billing_period_end ? $invoice->billing_period_end->format('M d, Y') : '-' }}
                </p>
                <p class="text-slate-500 mt-1">Status: <strong class="uppercase text-emerald-600">{{ $invoice->status->value }}</strong></p>
            </div>
        </div>

        <!-- Line Items Table -->
        <table class="w-full text-left text-xs mb-8">
            <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider text-[10px] border-y border-slate-100">
                <tr>
                    <th class="py-3 px-2">Description</th>
                    <th class="py-3 px-2 text-center">Qty</th>
                    <th class="py-3 px-2 text-right">Unit Price</th>
                    <th class="py-3 px-2 text-right">Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($invoice->items as $item)
                    <tr>
                        <td class="py-3 px-2 font-medium text-slate-800">{{ $item->description }}</td>
                        <td class="py-3 px-2 text-center font-mono">{{ $item->quantity }}</td>
                        <td class="py-3 px-2 text-right font-mono">${{ number_format((float)$item->unit_price, 2) }}</td>
                        <td class="py-3 px-2 text-right font-mono font-bold">${{ number_format((float)$item->amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Breakdown -->
        <div class="flex justify-end border-t border-slate-100 pt-6 mb-8 text-xs">
            <div class="w-64 space-y-2">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal:</span>
                    <span class="font-mono font-semibold">${{ number_format((float)$invoice->subtotal, 2) }}</span>
                </div>
                @if($invoice->discount_amount > 0)
                    <div class="flex justify-between text-emerald-600">
                        <span>Discount:</span>
                        <span class="font-mono font-semibold">-${{ number_format((float)$invoice->discount_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-600">
                    <span>Sales Tax / VAT:</span>
                    <span class="font-mono font-semibold">${{ number_format((float)$invoice->tax_amount, 2) }}</span>
                </div>
                @if($invoice->credit_amount > 0)
                    <div class="flex justify-between text-indigo-600">
                        <span>Credits Applied:</span>
                        <span class="font-mono font-semibold">-${{ number_format((float)$invoice->credit_amount, 2) }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-slate-900 font-extrabold text-sm border-t border-slate-200 pt-2">
                    <span>Total Amount:</span>
                    <span class="font-mono text-[#1E3A5F]">${{ number_format((float)$invoice->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between text-emerald-700 font-bold">
                    <span>Amount Paid:</span>
                    <span class="font-mono">${{ number_format((float)$invoice->amount_paid, 2) }}</span>
                </div>
                <div class="flex justify-between text-base font-black border-t border-slate-200 pt-2 {{ $invoice->balance_due > 0 ? 'text-rose-600' : 'text-emerald-600' }}">
                    <span>Balance Due:</span>
                    <span class="font-mono">${{ number_format((float)$invoice->balance_due, 2) }}</span>
                </div>
            </div>
        </div>

        <!-- Print action buttons -->
        <div class="no-print flex justify-end space-x-3 pt-6 border-t border-slate-100">
            <button onclick="window.print()" class="px-4 py-2 bg-[#1E3A5F] hover:bg-[#142A44] text-white rounded-lg text-xs font-bold shadow transition">
                Print Invoice
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                Close
            </button>
        </div>
    </div>
</body>
</html>
