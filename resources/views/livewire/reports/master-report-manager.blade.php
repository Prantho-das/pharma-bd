<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                </svg>
                Pharmacy Business Reports: {{ ucwords(str_replace('-', ' ', $type)) }}
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Filter and export comprehensive operational, tax, financial, and inventory audit statements.
            </p>
        </div>

        <div class="flex items-center gap-2 bg-white p-2 rounded-lg border border-slate-200 shadow-sm">
            <input type="date" wire:model.live="startDate" class="px-2.5 py-1 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-sky-600" />
            <span class="text-xs text-slate-400">to</span>
            <input type="date" wire:model.live="endDate" class="px-2.5 py-1 text-xs rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-sky-600" />
            <button type="button" onclick="window.print()" class="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 text-xs font-bold rounded-lg transition border border-slate-200">Print</button>
        </div>
    </div>

    <!-- Quick Report Category Links -->
    <div class="border-b border-slate-200 overflow-x-auto bg-white p-1 rounded-lg shadow-sm">
        <nav class="flex space-x-2 min-w-max text-xs font-semibold">
            @php
                $reportsList = [
                    'sales' => 'Sale',
                    'sale-returns' => 'Sale Return',
                    'purchases' => 'Purchase',
                    'purchase-returns' => 'Purchase Return',
                    'incomes' => 'All Income',
                    'expenses' => 'All Expense',
                    'stocks' => 'Current Stock',
                    'customer-ledger' => 'Customer Ledger',
                    'supplier-ledger' => 'Supplier Ledger',
                    'customer-due' => 'Customer Due',
                    'supplier-due' => 'Supplier Due',
                    'loss-profit' => 'Loss & Profit',
                    'transactions' => 'Transaction',
                    'expired' => 'Expired Product',
                ];
            @endphp
            @foreach ($reportsList as $key => $title)
                <button wire:click="$set('type', '{{ $key }}')" class="py-2.5 px-3.5 rounded-lg transition-all {{ $type === $key ? 'bg-sky-50 text-sky-800 font-bold shadow-sm' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                    {{ $title }}
                </button>
            @endforeach
        </nav>
    </div>

    <!-- Report Table -->
    <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="border-b border-slate-200 text-xs font-semibold uppercase text-slate-600 bg-slate-50">
                        @if ($type === 'sales')
                            <th class="px-5 py-3">Invoice No</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3 text-right">Total</th>
                            <th class="px-5 py-3 text-right">Paid</th>
                            <th class="px-5 py-3 text-right">Due</th>
                        @elseif ($type === 'sale-returns')
                            <th class="px-5 py-3">Return No</th>
                            <th class="px-5 py-3">Original Invoice</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3 text-right">Refund Amount</th>
                        @elseif ($type === 'purchases')
                            <th class="px-5 py-3">PO Number</th>
                            <th class="px-5 py-3">Supplier</th>
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3 text-right">Grand Total</th>
                        @elseif ($type === 'incomes' || $type === 'expenses')
                            <th class="px-5 py-3">Date</th>
                            <th class="px-5 py-3">Title / Note</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3 text-right">Amount</th>
                        @elseif ($type === 'stocks')
                            <th class="px-5 py-3">Medicine Name</th>
                            <th class="px-5 py-3">Category</th>
                            <th class="px-5 py-3 text-center">In-Stock Pieces</th>
                        @elseif ($type === 'customer-due' || $type === 'supplier-due')
                            <th class="px-5 py-3">Party Name</th>
                            <th class="px-5 py-3">Phone</th>
                            <th class="px-5 py-3 text-right">Outstanding Due</th>
                        @else
                            <th class="px-5 py-3">Reference / Name</th>
                            <th class="px-5 py-3">Details</th>
                            <th class="px-5 py-3 text-right">Amount / Value</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-800">
                    @forelse ($data as $item)
                        <tr class="hover:bg-slate-50 transition">
                            @if ($type === 'sales')
                                <td class="px-5 py-3 font-mono font-bold text-sky-700">{{ $item->invoice_no }}</td>
                                <td class="px-5 py-3 font-medium">{{ $item->customer?->name ?? 'Walk-in' }}</td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $item->created_at->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-slate-900">৳{{ number_format($item->grand_total, 2) }}</td>
                                <td class="px-5 py-3 text-right font-mono text-emerald-600 font-semibold">৳{{ number_format($item->paid_amount, 2) }}</td>
                                <td class="px-5 py-3 text-right font-mono text-red-600 font-semibold">৳{{ number_format($item->due_amount, 2) }}</td>
                            @elseif ($type === 'sale-returns')
                                <td class="px-5 py-3 font-mono font-bold text-sky-700">{{ $item->return_no }}</td>
                                <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $item->sale?->invoice_no }}</td>
                                <td class="px-5 py-3 font-medium">{{ $item->customer?->name ?? 'Walk-in' }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-red-600">৳{{ number_format($item->total_refund_amount, 2) }}</td>
                            @elseif ($type === 'purchases')
                                <td class="px-5 py-3 font-mono font-bold text-sky-700">{{ $item->purchase_no }}</td>
                                <td class="px-5 py-3 font-bold text-slate-900">{{ $item->supplier->name }}</td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $item->created_at->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-slate-900">৳{{ number_format($item->grand_total, 2) }}</td>
                            @elseif ($type === 'incomes' || $type === 'expenses')
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $item->date->format('d/m/Y') }}</td>
                                <td class="px-5 py-3 font-bold text-slate-900">{{ $item->title }}</td>
                                <td class="px-5 py-3 text-xs text-slate-600">{{ $item->category?->name ?? 'General' }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-slate-900">৳{{ number_format($item->amount, 2) }}</td>
                            @elseif ($type === 'stocks')
                                <td class="px-5 py-3 font-bold text-slate-900">{{ $item->name }}</td>
                                <td class="px-5 py-3 text-xs text-slate-600">{{ $item->category?->name ?? 'General' }}</td>
                                <td class="px-5 py-3 text-center font-bold font-mono text-sky-700">{{ $item->total_stock ?? 0 }} {{ $item->baseUnit?->name ?? 'Pcs' }}</td>
                            @elseif ($type === 'customer-due' || $type === 'supplier-due')
                                <td class="px-5 py-3 font-bold text-slate-900">{{ $item->company_name ?: $item->name }}</td>
                                <td class="px-5 py-3 font-mono text-xs text-slate-600">{{ $item->phone }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-red-600">৳{{ number_format($item->current_due, 2) }}</td>
                            @else
                                <td class="px-5 py-3 font-semibold text-slate-900">{{ $item->name ?? $item->invoice_no ?? $item->batch_number ?? '#' }}</td>
                                <td class="px-5 py-3 text-xs text-slate-500">{{ $item->created_at?->format('d/m/Y') ?? '-' }}</td>
                                <td class="px-5 py-3 text-right font-mono font-bold text-slate-900">৳{{ number_format($item->grand_total ?? $item->amount ?? $item->current_due ?? 0, 2) }}</td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">No records found for {{ $type }}.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-200">{{ $data->links() }}</div>
    </div>
</div>
