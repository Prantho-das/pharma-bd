<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Batch Inventory & FEFO Expiry Matrix</h1>
        <p class="text-xs text-slate-500 mt-1">Real-time batch tracking with automated First-Expired, First-Out (FEFO) scheduling and early expiry warnings.</p>
    </div>

    {{-- Metrics Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div wire:click="$set('expiryFilter', 'active')" class="bg-white p-4 rounded-lg border border-slate-200 shadow-xs cursor-pointer hover:border-sky-500 transition {{ $expiryFilter === 'active' ? 'ring-2 ring-sky-500' : '' }}">
            <div class="text-xs font-bold text-slate-500">Active In-Stock Batches</div>
            <div class="text-2xl font-black text-sky-800 font-mono mt-1">{{ $totalActiveBatches }}</div>
        </div>

        <div wire:click="$set('expiryFilter', 'near_expiry')" class="bg-white p-4 rounded-lg border border-slate-200 shadow-xs cursor-pointer hover:border-amber-500 transition {{ $expiryFilter === 'near_expiry' ? 'ring-2 ring-amber-500 bg-amber-50/20' : '' }}">
            <div class="text-xs font-bold text-amber-700">Near Expiry (&le; 90 Days)</div>
            <div class="text-2xl font-black text-amber-600 font-mono mt-1">{{ $totalNearExpiry }}</div>
        </div>

        <div wire:click="$set('expiryFilter', 'expired')" class="bg-white p-4 rounded-lg border border-slate-200 shadow-xs cursor-pointer hover:border-rose-500 transition {{ $expiryFilter === 'expired' ? 'ring-2 ring-rose-500 bg-rose-50/20' : '' }}">
            <div class="text-xs font-bold text-rose-700">Expired Batches (Locked)</div>
            <div class="text-2xl font-black text-rose-600 font-mono mt-1">{{ $totalExpired }}</div>
        </div>
    </div>

    {{-- Search Toolbar --}}
    <div class="bg-white p-4 rounded-lg border border-slate-200/80 shadow-xs grid grid-cols-1 md:grid-cols-3 gap-3">
        <div class="md:col-span-2">
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search batch number, medicine name, SKU..." class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg font-medium">
        </div>
        <div>
            <select wire:model.live="expiryFilter" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg font-medium">
                <option value="all">All Batches (FEFO Sorted)</option>
                <option value="active">Active In-Stock Batches</option>
                <option value="near_expiry">Near Expiry Warning (&le; 90d)</option>
                <option value="expired">Expired Batches</option>
            </select>
        </div>
    </div>

    {{-- Batches Table --}}
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                <tr>
                    <th class="py-3 px-4">Batch Number</th>
                    <th class="py-3 px-3">Medicine</th>
                    <th class="py-3 px-3">Expiry Date</th>
                    <th class="py-3 px-3 text-center">Remaining Stock</th>
                    <th class="py-3 px-3 text-right">Buy Rate (Base)</th>
                    <th class="py-3 px-3 text-right">MRP (Base)</th>
                    <th class="py-3 px-3 text-center">FEFO Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($batches as $b)
                    @php
                        $isExp = $b->isExpired();
                        $isNear = $b->isNearExpiry();
                        $daysLeft = now()->diffInDays($b->expiry_date, false);
                    @endphp
                    <tr class="hover:bg-slate-50/80 transition {{ $isExp ? 'bg-rose-50/40' : ($isNear ? 'bg-amber-50/40' : '') }}">
                        <td class="py-3 px-4 font-mono font-bold text-slate-900">
                            {{ $b->batch_number }}
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-slate-900">{{ $b->product?->name }}</div>
                            <div class="text-[11px] text-sky-700">{{ $b->product?->generic?->name }}</div>
                        </td>
                        <td class="py-3 px-3">
                            <div class="font-mono font-bold {{ $isExp ? 'text-rose-700' : ($isNear ? 'text-amber-700' : 'text-slate-800') }}">
                                {{ $b->expiry_date->format('d M, Y') }}
                            </div>
                            <div class="text-[10px] text-slate-400">
                                @if($isExp)
                                    Expired {{ abs($daysLeft) }} days ago
                                @else
                                    {{ $daysLeft }} days remaining
                                @endif
                            </div>
                        </td>
                        <td class="py-3 px-3 text-center font-mono font-bold text-slate-800">
                            {{ $b->current_stock }} / {{ $b->initial_stock }} {{ $b->baseUnit?->name ?? 'pcs' }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono text-slate-600">
                            ৳{{ number_format($b->purchase_rate, 2) }}
                        </td>
                        <td class="py-3 px-3 text-right font-mono font-bold text-sky-800">
                            ৳{{ number_format($b->mrp_rate, 2) }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            @if($isExp)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-600 text-white">LOCKED EXPIRED</span>
                            @elseif($isNear)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500 text-white">NEAR EXPIRY</span>
                            @elseif($b->current_stock > 0)
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">FEFO ACTIVE</span>
                            @else
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-500">EXHAUSTED</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400 text-xs">No batches match the filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $batches->links() }}
        </div>
    </div>
</div>
