<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Supplier Accounts & Payables Ledger</h1>
            <p class="text-xs text-slate-500 mt-1">Manage distributor ledger accounts, purchase invoices, and vendor bill payments.</p>
        </div>
        <div class="bg-amber-50 border border-amber-200 px-4 py-2 rounded-lg">
            <span class="text-xs font-bold text-amber-800">Total Supplier Payable:</span>
            <span class="text-base font-black text-amber-900 font-mono ml-1">৳{{ number_format($totalPayable, 2) }}</span>
        </div>
    </div>

    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-lg flex justify-between">
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)">&times;</button>
        </div>
    @endif

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Supplier List --}}
        <div class="bg-white rounded-lg border border-slate-200 shadow-xs p-4 space-y-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search supplier company, name..." class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg font-medium">

            <div class="space-y-2 overflow-y-auto max-h-[70vh]">
                @foreach($suppliers as $s)
                    <div 
                        wire:click="$set('selectedSupplierId', {{ $s->id }})"
                        class="p-3 rounded-lg border transition cursor-pointer flex items-center justify-between {{ $selectedSupplierId === $s->id ? 'bg-sky-50 border-sky-500 shadow-xs' : 'bg-slate-50/70 border-slate-200 hover:bg-slate-100' }}"
                    >
                        <div>
                            <div class="font-bold text-xs text-slate-900">{{ $s->company_name ?: $s->name }}</div>
                            <div class="text-[11px] text-slate-500">{{ $s->name }} &bull; <span class="font-mono">{{ $s->phone }}</span></div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono font-bold text-xs {{ $s->current_due > 0 ? 'text-amber-700' : 'text-slate-700' }}">
                                ৳{{ number_format($s->current_due, 2) }}
                            </div>
                            @if($s->current_due > 0)
                                <button 
                                    wire:click.stop="openPayModal({{ $s->id }})"
                                    class="text-[10px] bg-sky-700 hover:bg-sky-800 text-white font-bold px-2 py-0.5 rounded mt-1"
                                >
                                    Pay Supplier
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="pt-2 border-t border-slate-100">
                {{ $suppliers->links() }}
            </div>
        </div>

        {{-- Supplier Ledger Statements --}}
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-xs p-5 space-y-4">
            @if($selectedSupplier)
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="font-black text-sm text-slate-900">{{ $selectedSupplier->company_name ?: $selectedSupplier->name }}</h3>
                        <p class="text-xs text-slate-500">Contact: {{ $selectedSupplier->name }} &bull; Phone: {{ $selectedSupplier->phone }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-500">Total Payable Balance:</span>
                        <div class="font-black font-mono text-lg {{ $selectedSupplier->current_due > 0 ? 'text-amber-700' : 'text-emerald-700' }}">
                            ৳{{ number_format($selectedSupplier->current_due, 2) }}
                        </div>
                    </div>
                </div>

                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-2">Type</th>
                            <th class="py-2.5 px-2">Description</th>
                            <th class="py-2.5 px-2 text-right">Debit / Paid (-)</th>
                            <th class="py-2.5 px-2 text-right">Credit / Bill (+)</th>
                            <th class="py-2.5 px-3 text-right">Payable Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ledgerEntries as $entry)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-2 px-3 text-slate-500 font-mono text-[11px]">{{ $entry->transaction_date->format('d/m/Y h:i A') }}</td>
                                <td class="py-2 px-2">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $entry->transaction_type === 'PURCHASE' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $entry->transaction_type }}
                                    </span>
                                </td>
                                <td class="py-2 px-2 text-slate-600">{{ $entry->note }}</td>
                                <td class="py-2 px-2 text-right font-mono text-emerald-700">{{ $entry->debit > 0 ? '৳' . number_format($entry->debit, 2) : '-' }}</td>
                                <td class="py-2 px-2 text-right font-mono text-slate-800">{{ $entry->credit > 0 ? '৳' . number_format($entry->credit, 2) : '-' }}</td>
                                <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">৳{{ number_format($entry->balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">No transactions recorded for this supplier yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <div class="py-24 text-center text-slate-400 text-xs">Select a supplier from the left to view their detailed double-entry statement.</div>
            @endif
        </div>
    </div>

    {{-- Pay Supplier Modal --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-sky-800 px-5 py-3.5 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Pay Supplier / Settle Bill</h3>
                    <button wire:click="$set('showPaymentModal', false)" class="text-white font-bold">&times;</button>
                </div>
                <div class="p-5 space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-slate-700">Amount to Pay (৳) *</label>
                        <input wire:model="payAmount" type="number" step="0.01" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg font-bold font-mono text-lg text-sky-800">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">Payment Channel</label>
                        <select wire:model="paymentMethod" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                            <option value="BANK_TRANSFER">Bank Transfer</option>
                            <option value="CHEQUE">Bank Cheque</option>
                            <option value="CASH">Cash Drawer</option>
                            <option value="MFS">MFS (bKash/Nagad)</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">Cheque / Trx Reference</label>
                        <input wire:model="paymentNote" type="text" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showPaymentModal', false)" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancel</button>
                    <button wire:click="saveSupplierPayment" class="px-5 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Record Payment</button>
                </div>
            </div>
        </div>
    @endif
</div>
