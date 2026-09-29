<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                Customers Directory
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Maintain patient & customer profiles, contact numbers, credit limits, and outstanding dues.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <button wire:click="$set('tab', 'create')" class="px-4 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-sm transition shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Add Customer
            </button>
            <button wire:click="$set('tab', 'bulk')" class="px-4 py-2 bg-white hover:bg-slate-50 text-slate-700 font-bold rounded-lg text-sm transition border border-slate-300 shadow-sm flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                Bulk Upload
            </button>
        </div>
    </div>

    @if (session('success'))
        <div class="p-4 rounded-lg bg-emerald-50 text-emerald-800 text-sm font-semibold flex items-center gap-2 border border-emerald-200 shadow-sm">
            <svg class="w-5 h-5 flex-shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($tab === 'all')
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
            <div class="p-5 border-b border-slate-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by customer name, phone or email..."
                    class="w-full max-w-md pl-4 pr-4 py-2 text-sm rounded-lg border border-slate-300 bg-white text-slate-900 focus:ring-2 focus:ring-sky-600"
                />
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 text-xs font-semibold uppercase text-slate-600 bg-slate-50">
                            <th class="px-5 py-3.5">Customer Name</th>
                            <th class="px-5 py-3.5">Phone Number</th>
                            <th class="px-5 py-3.5">Address</th>
                            <th class="px-5 py-3.5 text-right">Current Due</th>
                            <th class="px-5 py-3.5 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-800">
                        @forelse ($customers as $c)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-5 py-3.5 font-bold text-slate-900">{{ $c->name }}</td>
                                <td class="px-5 py-3.5 font-mono text-xs text-slate-600">{{ $c->phone }}</td>
                                <td class="px-5 py-3.5 text-xs text-slate-500">{{ $c->address ?: '-' }}</td>
                                <td class="px-5 py-3.5 text-right font-mono font-bold {{ $c->current_due > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                    ৳{{ number_format($c->current_due, 2) }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    <button wire:click="editCustomer({{ $c->id }})" class="px-3 py-1 text-xs bg-sky-50 hover:bg-sky-100 text-sky-800 font-bold rounded-lg transition">
                                        Edit
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400">No customers found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-200">{{ $customers->links() }}</div>
        </div>
    @endif

    @if ($tab === 'create')
        <div class="bg-white rounded-lg p-6 border border-slate-200 shadow-xs max-w-2xl space-y-4">
            <h3 class="font-bold text-base text-slate-900 border-b border-slate-100 pb-2">{{ $editingCustomerId ? 'Edit Customer' : 'Register Customer' }}</h3>
            <form wire:submit="saveCustomer" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Customer Name <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="name" placeholder="Full Name" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 placeholder:text-slate-400 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs" />
                    @error('name') <span class="text-[11px] text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                        <input type="text" wire:model="phone" placeholder="017xxxxxxxx" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 placeholder:text-slate-400 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                        <input type="email" wire:model="email" placeholder="customer@mail.com" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 placeholder:text-slate-400 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Address</label>
                    <input type="text" wire:model="address" placeholder="Address" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 placeholder:text-slate-400 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Opening Due (৳)</label>
                        <input type="number" step="0.01" wire:model="openingBalance" placeholder="0.00" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Credit Limit (৳)</label>
                        <input type="number" step="0.01" wire:model="creditLimit" placeholder="0.00" class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 bg-white text-slate-800 focus:border-sky-600 focus:ring-2 focus:ring-sky-500/15 outline-none shadow-xs" />
                    </div>
                </div>
                <div class="flex gap-2.5 pt-3 border-t border-slate-100">
                    <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs transition shadow-xs cursor-pointer">Save Customer</button>
                    <button type="button" wire:click="$set('tab', 'all')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold rounded-lg text-xs border border-slate-200 cursor-pointer">Cancel</button>
                </div>
            </form>
        </div>
    @endif

    @if ($tab === 'bulk')
        <div class="bg-white rounded-lg p-6 border border-slate-200 shadow-sm max-w-xl space-y-4">
            <h3 class="font-bold text-base text-slate-900">Bulk Upload Customers (CSV)</h3>
            <p class="text-xs text-slate-500">CSV Columns: [Name, Phone, Email, Address, Opening Due]</p>
            <form wire:submit="processBulkUpload" class="space-y-4">
                <input type="file" wire:model="csvFile" class="block w-full text-sm text-slate-600 file:mr-4 file:py-2.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 border border-slate-200 rounded-lg p-2 bg-slate-50" />
                @error('csvFile') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                <button type="submit" class="w-full py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-sm transition shadow-sm">Upload Customers</button>
            </form>
        </div>
    @endif
</div>
