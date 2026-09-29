<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Tax & VAT Rates Management
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Configure standard pharmaceutical drug tariffs, VAT, GST, and tax exemptions for POS & billing.
            </p>
        </div>
    </div>

    @if (session('success'))
        <div class="p-3.5 rounded-lg bg-emerald-50 text-emerald-800 text-xs font-semibold flex items-center gap-2 border border-emerald-200 shadow-2xs">
            <svg class="w-4 h-4 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Left: Create / Edit Form --}}
        <div class="bg-white rounded-lg p-5 border border-slate-200 shadow-xs space-y-4">
            <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-2 flex items-center justify-between">
                <span>{{ $editingTaxId ? 'Edit Tax Rule' : 'Add New Tax Rate' }}</span>
                @if ($editingTaxId)
                    <button type="button" wire:click="cancelEdit" class="text-[11px] text-slate-400 hover:text-slate-600">Cancel</button>
                @endif
            </h3>
            <form wire:submit="saveTax" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tax / VAT Name <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="name" placeholder="e.g. Standard Medicine VAT (5%)" class="w-full text-xs" />
                    @error('name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tax Percentage (%) <span class="text-rose-500">*</span></label>
                    <input type="number" step="0.01" wire:model="rate" placeholder="e.g. 5.00" class="w-full text-xs font-mono font-bold" />
                    @error('rate') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center gap-2 pt-1">
                    <input type="checkbox" id="taxStatus" wire:model="isActive" class="rounded text-sky-600 focus:ring-sky-500 border-slate-300" />
                    <label for="taxStatus" class="text-xs text-slate-700 font-medium cursor-pointer">Active in POS & Products</label>
                </div>

                <button type="submit" class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs transition shadow-xs cursor-pointer">
                    {{ $editingTaxId ? 'Update Tax Rule' : 'Save Tax Rate' }}
                </button>
            </form>
        </div>

        {{-- Right: Taxes List --}}
        <div class="md:col-span-2 bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-3.5 border-b border-slate-200 bg-slate-50 flex items-center justify-between">
                <span class="font-bold text-slate-800 text-xs">Configured Tax Rates</span>
                <span class="text-[11px] text-slate-500 font-medium">Total: {{ count($taxes) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500 bg-slate-50/50">
                            <th class="px-4 py-3">Tax Name</th>
                            <th class="px-4 py-3 text-center">Rate (%)</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse ($taxes as $tax)
                            <tr class="hover:bg-sky-50/20 transition">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $tax->name }}</td>
                                <td class="px-4 py-3 text-center font-mono font-bold text-sky-700">{{ number_format($tax->rate, 2) }}%</td>
                                <td class="px-4 py-3 text-center">
                                    <button wire:click="toggleTax({{ $tax->id }})" class="cursor-pointer">
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold {{ $tax->is_active ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                            {{ $tax->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </button>
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <button wire:click="editTax({{ $tax->id }})" class="px-2 py-1 text-[11px] bg-sky-50 hover:bg-sky-100 text-sky-800 font-bold rounded transition cursor-pointer">
                                        Edit
                                    </button>
                                    <button wire:click="deleteTax({{ $tax->id }})" wire:confirm="Are you sure you want to delete this tax rate?" class="px-2 py-1 text-[11px] bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold rounded transition cursor-pointer">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">No tax rates configured.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
