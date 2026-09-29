<div class="p-6 space-y-6 max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Procurement & Purchase Invoices</h1>
            <p class="text-xs text-slate-500 mt-1">Review Goods Received Notes (GRN), supplier bills, and payment status.</p>
        </div>
        <a href="{{ route('purchases.create') }}" class="px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-600 hover:to-blue-700 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5 self-start sm:self-auto" wire:navigate>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>New GRN Purchase</span>
        </a>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white p-4 rounded-lg border border-slate-200/80 shadow-xs grid grid-cols-1 md:grid-cols-3 gap-3">
        <div>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search PO #, supplier, challan..." class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg">
        </div>
        <div>
            <select wire:model.live="selectedSupplier" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg">
                <option value="">All Suppliers</option>
                @foreach($suppliers as $s)
                    <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->company_name }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <select wire:model.live="paymentStatus" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg">
                <option value="all">All Payment Status</option>
                <option value="PAID">Paid</option>
                <option value="PARTIAL">Partial</option>
                <option value="DUE">Due</option>
            </select>
        </div>
    </div>

    {{-- Purchases Table --}}
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                <tr>
                    <th class="py-3 px-4">PO Number</th>
                    <th class="py-3 px-3">Supplier</th>
                    <th class="py-3 px-3">Invoice Date</th>
                    <th class="py-3 px-3 text-right">Grand Total</th>
                    <th class="py-3 px-3 text-right">Paid</th>
                    <th class="py-3 px-3 text-right">Due</th>
                    <th class="py-3 px-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($purchases as $po)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-mono font-bold text-sky-800">
                            {{ $po->purchase_no }}
                            @if($po->supplier_invoice_no)
                                <div class="text-[10px] text-slate-400 font-sans">Challan: {{ $po->supplier_invoice_no }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-slate-800">{{ $po->supplier?->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $po->supplier?->company_name }}</div>
                        </td>
                        <td class="py-3 px-3 text-slate-600">
                            {{ $po->invoice_date?->format('d M, Y') ?? 'N/A' }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-slate-900">
                            ৳{{ number_format($po->grand_total, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-emerald-700">
                            ৳{{ number_format($po->paid_amount, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-rose-600">
                            ৳{{ number_format($po->due_amount, 2) }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="inline-flex px-2 py-0.5 rounded text-[10px] font-bold {{ $po->payment_status === 'PAID' ? 'bg-emerald-100 text-emerald-800' : ($po->payment_status === 'PARTIAL' ? 'bg-amber-100 text-amber-800' : 'bg-rose-100 text-rose-800') }}">
                                {{ $po->payment_status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 text-xs">No purchase records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $purchases->links() }}
        </div>
    </div>
</div>
