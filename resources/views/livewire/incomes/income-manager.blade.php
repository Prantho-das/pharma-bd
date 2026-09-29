<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Incomes & Revenue Receipts
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Log miscellaneous receipts, doctor room rent, commissions, and category breakdowns.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-lg bg-emerald-50 text-emerald-800 text-sm font-semibold flex items-center gap-2 border border-emerald-200 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($tab === 'incomes')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-6 border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-base text-slate-900 border-b border-slate-200 pb-3">New Income Voucher</h3>
                <form wire:submit="saveIncome" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Income Title <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="title" placeholder="e.g. Diagnostic Center Referral Commission" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600" />
                        @error('title') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Category</label>
                        <select wire:model="categoryId" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600">
                            <option value="">-- Select Category --</option>
                            @foreach ($categories as $cat) <option value="{{ $cat->id }}">{{ $cat->name }}</option> @endforeach
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Amount (৳) <span class="text-red-500">*</span></label>
                            <input type="number" step="0.01" wire:model="amount" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600 font-bold" />
                            @error('amount') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Date <span class="text-red-500">*</span></label>
                            <input type="date" wire:model="date" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600" />
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Payment Method</label>
                        <select wire:model="paymentMethod" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600">
                            <option value="CASH">CASH</option>
                            <option value="BANK">BANK</option>
                            <option value="MFS">MFS (bKash/Nagad)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Note</label>
                        <input type="text" wire:model="note" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600" />
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-sm transition shadow-sm">
                        Record Income
                    </button>
                </form>
            </div>

            <div class="lg:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 text-sm">
                    Income Logs
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 text-xs font-semibold uppercase text-slate-600 bg-slate-50">
                                <th class="px-5 py-3.5">Date</th>
                                <th class="px-5 py-3.5">Title</th>
                                <th class="px-5 py-3.5">Category</th>
                                <th class="px-5 py-3.5">Method</th>
                                <th class="px-5 py-3.5 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-800">
                            @forelse ($incomes as $inc)
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="px-5 py-3.5 text-xs text-slate-500">{{ $inc->date->format('d/m/Y') }}</td>
                                    <td class="px-5 py-3.5 font-bold text-slate-900">{{ $inc->title }}</td>
                                    <td class="px-5 py-3.5 text-xs text-slate-600 font-medium">{{ $inc->category?->name ?? 'General' }}</td>
                                    <td class="px-5 py-3.5 text-xs font-mono font-bold">{{ $inc->payment_method }}</td>
                                    <td class="px-5 py-3.5 text-right font-mono font-bold text-emerald-600">৳{{ number_format($inc->amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No income records found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4 border-t border-slate-200">{{ $incomes->links() }}</div>
            </div>
        </div>
    @endif

    @if ($tab === 'categories')
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-lg p-6 border border-slate-200 shadow-sm space-y-4">
                <h3 class="font-bold text-base text-slate-900">Add Income Category</h3>
                <form wire:submit="saveCategory" class="space-y-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Category Name</label>
                        <input type="text" wire:model="categoryName" placeholder="e.g. Diagnostic Referral" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600" />
                        @error('categoryName') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-slate-600 mb-1">Description</label>
                        <input type="text" wire:model="categoryDescription" class="w-full px-3.5 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600" />
                    </div>
                    <button type="submit" class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs transition shadow-sm">
                        Add Category
                    </button>
                </form>
            </div>

            <div class="md:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                <div class="p-4 border-b border-slate-200 bg-slate-50 font-bold text-slate-800 text-sm">Income Categories List</div>
                <div class="p-4 divide-y divide-slate-100">
                    @foreach ($categories as $cat)
                        <div class="py-2.5 flex justify-between items-center text-sm">
                            <span class="font-bold text-slate-900">{{ $cat->name }}</span>
                            <span class="text-xs text-slate-500">{{ $cat->description ?: '-' }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
