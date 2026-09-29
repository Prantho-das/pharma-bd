<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Customer Accounts & Double-Entry Ledgers</h1>
            <p class="text-xs text-slate-500 mt-1">Track customer debit/credit statements, invoices, and record due recoveries.</p>
        </div>
        <div class="bg-rose-50 border border-rose-200 px-4 py-2 rounded-lg">
            <span class="text-xs font-bold text-rose-700">Total Due Receivable:</span>
            <span class="text-base font-black text-rose-800 font-mono ml-1">৳{{ number_format($totalDueReceivable, 2) }}</span>
        </div>
    </div>

    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-lg flex justify-between">
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)">&times;</button>
        </div>
    @endif

    {{-- Main Grid: Customers list (left) & Statement View (right) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Customer List --}}
        <div class="bg-white rounded-lg border border-slate-200 shadow-xs p-4 space-y-3">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search customer name, phone..." class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg font-medium">

            <div class="space-y-2 overflow-y-auto max-h-[70vh]">
                @foreach($customers as $c)
                    <div 
                        wire:click="$set('selectedCustomerId', {{ $c->id }})"
                        class="p-3 rounded-lg border transition cursor-pointer flex items-center justify-between {{ $selectedCustomerId === $c->id ? 'bg-sky-50 border-sky-500 shadow-xs' : 'bg-slate-50/70 border-slate-200 hover:bg-slate-100' }}"
                    >
                        <div>
                            <div class="font-bold text-xs text-slate-900">{{ $c->name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $c->phone }}</div>
                        </div>
                        <div class="text-right">
                            <div class="font-mono font-bold text-xs {{ $c->current_due > 0 ? 'text-rose-600' : 'text-slate-700' }}">
                                ৳{{ number_format($c->current_due, 2) }}
                            </div>
                            @if($c->current_due > 0)
                                <button 
                                    wire:click.stop="openCollectDueModal({{ $c->id }})"
                                    class="text-[10px] bg-rose-600 hover:bg-rose-700 text-white font-bold px-2 py-0.5 rounded mt-1"
                                >
                                    Collect Due
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="pt-2 border-t border-slate-100">
                {{ $customers->links() }}
            </div>
        </div>

        {{-- Statement / Ledger Detail --}}
        <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 shadow-xs p-5 space-y-4">
            @if($selectedCustomer)
                <div class="flex items-center justify-between pb-3 border-b border-slate-200">
                    <div>
                        <h3 class="font-black text-sm text-slate-900">{{ $selectedCustomer->name }}</h3>
                        <p class="text-xs text-slate-500">Phone: {{ $selectedCustomer->phone }} &bull; Address: {{ $selectedCustomer->address ?: 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-slate-500">Outstanding Balance:</span>
                        <div class="font-black font-mono text-lg {{ $selectedCustomer->current_due > 0 ? 'text-rose-600' : 'text-emerald-700' }}">
                            ৳{{ number_format($selectedCustomer->current_due, 2) }}
                        </div>
                    </div>
                </div>

                <table class="w-full text-left border-collapse text-xs">
                    <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 font-bold border-b border-slate-200">
                        <tr>
                            <th class="py-2.5 px-3">Date</th>
                            <th class="py-2.5 px-2">Type</th>
                            <th class="py-2.5 px-2">Description</th>
                            <th class="py-2.5 px-2 text-right">Debit (+)</th>
                            <th class="py-2.5 px-2 text-right">Credit (-)</th>
                            <th class="py-2.5 px-3 text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($ledgerEntries as $entry)
                            <tr class="hover:bg-slate-50/80">
                                <td class="py-2 px-3 text-slate-500 font-mono text-[11px]">{{ $entry->transaction_date->format('d/m/Y h:i A') }}</td>
                                <td class="py-2 px-2">
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold {{ $entry->transaction_type === 'SALE' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $entry->transaction_type }}
                                    </span>
                                </td>
                                <td class="py-2 px-2 text-slate-600">{{ $entry->note }}</td>
                                <td class="py-2 px-2 text-right font-mono text-slate-800">{{ $entry->debit > 0 ? '৳' . number_format($entry->debit, 2) : '-' }}</td>
                                <td class="py-2 px-2 text-right font-mono text-emerald-700">{{ $entry->credit > 0 ? '৳' . number_format($entry->credit, 2) : '-' }}</td>
                                <td class="py-2 px-3 text-right font-mono font-bold text-slate-900">৳{{ number_format($entry->balance, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400">No ledger transactions recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <div class="py-24 text-center text-slate-400 text-xs">Select a customer from the left to view their detailed double-entry statement.</div>
            @endif
        </div>
    </div>

    {{-- Collect Due Modal --}}
    @if($showPaymentModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-sky-800 px-5 py-3.5 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Collect Customer Due</h3>
                    <button wire:click="$set('showPaymentModal', false)" class="text-white font-bold">&times;</button>
                </div>
                <div class="p-5 space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-slate-700">Amount to Collect (৳) *</label>
                        <input wire:model="collectAmount" type="number" step="0.01" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg font-bold font-mono text-lg text-sky-800">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">Payment Method</label>
                        <select wire:model="paymentMethod" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                            <option value="CASH">Cash</option>
                            <option value="BKASH">bKash</option>
                            <option value="NAGAD">Nagad</option>
                            <option value="BANK_TRANSFER">Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">Receipt / Transaction Note</label>
                        <input wire:model="paymentNote" type="text" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showPaymentModal', false)" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancel</button>
                    <button wire:click="saveDueCollection" class="px-5 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Confirm Collection</button>
                </div>
            </div>
        </div>
    @endif
</div>
