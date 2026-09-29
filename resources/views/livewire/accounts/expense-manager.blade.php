<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Pharmacy Expenses & Cash Payouts</h1>
            <p class="text-xs text-slate-500 mt-1">Record utility bills, shop expenses, staff allowances, and packaging purchases.</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="bg-rose-50 border border-rose-200 px-4 py-2 rounded-lg">
                <span class="text-xs font-bold text-rose-700">Total Expenses:</span>
                <span class="text-base font-black text-rose-800 font-mono ml-1">৳{{ number_format($totalExpenseAmount, 2) }}</span>
            </div>
            <button 
                wire:click="openCreateModal" 
                type="button" 
                class="px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-600 hover:to-blue-700 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>Record Expense</span>
            </button>
        </div>
    </div>

    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-lg flex justify-between">
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)">&times;</button>
        </div>
    @endif

    {{-- Filter Toolbar --}}
    <div class="bg-white p-4 rounded-lg border border-slate-200/80 shadow-xs grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="md:col-span-2">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search expense description..." class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg font-medium">
        </div>
        <div>
            <select wire:model.live="selectedCategory" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg font-medium">
                <option value="">All Categories</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Expenses Table --}}
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                <tr>
                    <th class="py-3 px-4">Date</th>
                    <th class="py-3 px-3">Title / Description</th>
                    <th class="py-3 px-3">Category</th>
                    <th class="py-3 px-3">Payment Method</th>
                    <th class="py-3 px-3">Recorded By</th>
                    <th class="py-3 px-4 text-right">Amount (৳)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-mono text-slate-600">{{ $exp->date->format('d M, Y') }}</td>
                        <td class="py-3 px-3 font-bold text-slate-800">
                            {{ $exp->title }}
                            @if($exp->note)
                                <div class="text-[11px] text-slate-400 font-normal">{{ $exp->note }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-3">
                            <span class="bg-slate-100 text-slate-700 px-2 py-0.5 rounded text-[11px] font-medium">{{ $exp->category?->name }}</span>
                        </td>
                        <td class="py-3 px-3 font-mono font-bold text-slate-700">{{ $exp->payment_method }}</td>
                        <td class="py-3 px-3 text-slate-500">{{ $exp->user?->name }}</td>
                        <td class="py-3 px-4 text-right font-mono font-bold text-rose-700 text-sm">৳{{ number_format($exp->amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">No expense vouchers recorded.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $expenses->links() }}
        </div>
    </div>

    {{-- Modal: Record Expense --}}
    @if($showModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-sky-800 px-5 py-3.5 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Record Expense Voucher</h3>
                    <button wire:click="$set('showModal', false)" class="text-white font-bold">&times;</button>
                </div>
                <div class="p-5 space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-slate-700">Expense Title *</label>
                        <input wire:model="title" type="text" placeholder="e.g. Electricity Bill or Carry Bag" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                        @error('title') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="font-bold text-slate-700">Expense Category *</label>
                            <button wire:click="$set('showCategoryModal', true)" type="button" class="text-[11px] text-sky-700 font-bold hover:underline">+ New Category</button>
                        </div>
                        <select wire:model="category_id" class="w-full py-2 px-3 border border-slate-300 rounded-lg">
                            <option value="">-- Select Category --</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="font-bold text-slate-700">Amount (৳) *</label>
                            <input wire:model="amount" type="number" step="0.01" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg font-bold font-mono text-slate-900">
                            @error('amount') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Payment Date</label>
                            <input wire:model="date" type="date" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                        </div>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700">Payment Source</label>
                        <select wire:model="payment_method" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                            <option value="CASH">Cash (Drawer Payout)</option>
                            <option value="BANK">Bank Transfer</option>
                            <option value="MFS">MFS (bKash/Nagad)</option>
                        </select>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700">Notes / Remarks</label>
                        <input wire:model="note" type="text" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showModal', false)" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancel</button>
                    <button wire:click="saveExpense" class="px-5 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Save Voucher</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Quick Add Category Modal --}}
    @if($showCategoryModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-sm rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-sky-800 px-5 py-3.5 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Add Expense Category</h3>
                    <button wire:click="$set('showCategoryModal', false)" class="text-white font-bold">&times;</button>
                </div>
                <div class="p-5 space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-slate-700">Category Name *</label>
                        <input wire:model="newCategoryName" type="text" placeholder="e.g. Shop Utilities" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">Description</label>
                        <input wire:model="newCategoryDesc" type="text" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showCategoryModal', false)" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancel</button>
                    <button wire:click="saveCategory" class="px-4 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Save Category</button>
                </div>
            </div>
        </div>
    @endif
</div>
