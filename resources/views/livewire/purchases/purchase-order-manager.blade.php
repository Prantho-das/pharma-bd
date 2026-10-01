<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Top Header Banner --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-3 h-3 rounded-full bg-indigo-600 animate-pulse"></span>
                <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ __('Purchase Orders (PO)') }}</h1>
            </div>
            <p class="text-xs text-slate-500 mt-1">
                {{ __('Manage supplier purchase orders, track procurement lifecycle, and convert PO to actual GRN.') }}
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('purchases.index') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1.5" wire:navigate>
                {{ __('Purchases / GRN List') }}
            </a>
            <a href="{{ route('purchase-orders.create') }}" class="px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2 cursor-pointer" wire:navigate>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>+ {{ __('New Purchase Order') }}</span>
            </a>
        </div>
    </div>

    {{-- System Flash Alert --}}
    @if (session()->has('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">✓</div>
                <p class="text-xs font-bold text-emerald-900">{{ session('success') }}</p>
            </div>
            <button onclick="this.parentElement.remove()" type="button" class="text-xs font-bold text-emerald-700 hover:text-emerald-900">✕ {{ __('Close') }}</button>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="bg-white p-4 rounded-2xl border border-slate-200 shadow-xs grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
        <div class="lg:col-span-2">
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __('Search PO / Supplier') }}</label>
            <input wire:model.live.debounce.300ms="search" type="text" placeholder="{{ __('Search by PO #, Supplier name or phone...') }}" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20">
        </div>
        <div>
            <label class="block text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1">{{ __('Status Filter') }}</label>
            <select wire:model.live="statusFilter" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:ring-2 focus:ring-indigo-500/20">
                <option value="">{{ __('All Statuses') }}</option>
                <option value="ORDERED">{{ __('ORDERED') }}</option>
                <option value="RECEIVED">{{ __('RECEIVED') }}</option>
                <option value="PARTIALLY_RECEIVED">{{ __('PARTIALLY_RECEIVED') }}</option>
                <option value="DRAFT">{{ __('DRAFT') }}</option>
                <option value="CANCELLED">{{ __('CANCELLED') }}</option>
            </select>
        </div>
    </div>

    {{-- Purchase Orders Table --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 font-bold uppercase tracking-wider border-b border-slate-200">
                    <tr>
                        <th class="py-3.5 px-4">{{ __('PO Number') }}</th>
                        <th class="py-3.5 px-4">{{ __('Supplier') }}</th>
                        <th class="py-3.5 px-4">{{ __('Order Date') }}</th>
                        <th class="py-3.5 px-4">{{ __('Expected Delivery') }}</th>
                        <th class="py-3.5 px-4 text-center">{{ __('Items / Qty') }}</th>
                        <th class="py-3.5 px-4 text-right">{{ __('Grand Total') }}</th>
                        <th class="py-3.5 px-4 text-center">{{ __('Status') }}</th>
                        <th class="py-3.5 px-4 text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse($purchaseOrders as $po)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="py-3.5 px-4 font-black text-indigo-700 font-mono">
                                {{ $po->po_number }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $po->supplier?->name ?? 'N/A' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $po->supplier?->company_name ?: $po->supplier?->phone }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ \Carbon\Carbon::parse($po->order_date)->format('d M, Y') }}
                            </td>
                            <td class="py-3.5 px-4 text-slate-600">
                                {{ $po->expected_delivery_date ? \Carbon\Carbon::parse($po->expected_delivery_date)->format('d M, Y') : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="font-bold text-slate-900">{{ $po->total_items }} {{ __('items') }}</span>
                                <div class="text-[11px] text-slate-500">Qty: {{ number_format($po->total_ordered_qty) }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-right font-black font-mono text-slate-900">
                                ৳{{ number_format($po->grand_total, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                @if($po->status === 'ORDERED')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wide">
                                        {{ __('ORDERED') }}
                                    </span>
                                @elseif($po->status === 'RECEIVED')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wide">
                                        {{ __('RECEIVED') }}
                                    </span>
                                @elseif($po->status === 'CANCELLED')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-50 text-rose-700 border border-rose-200 uppercase tracking-wide">
                                        {{ __('CANCELLED') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-slate-100 text-slate-700 border border-slate-200 uppercase tracking-wide">
                                        {{ $po->status }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                                <button wire:click="viewPo({{ $po->id }})" type="button" class="px-2.5 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition">
                                    👁 {{ __('View') }}
                                </button>

                                @if(in_array($po->status, ['ORDERED', 'DRAFT', 'PARTIALLY_RECEIVED']))
                                    <a href="{{ route('purchases.create', ['from_po' => $po->id]) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black shadow-xs transition" wire:navigate>
                                        📥 {{ __('Receive GRN') }}
                                    </a>
                                    <button wire:click="cancelPo({{ $po->id }})" wire:confirm="{{ __('Are you sure you want to cancel this Purchase Order?') }}" type="button" class="px-2 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-600 rounded-lg text-xs font-bold transition">
                                        ✕
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-slate-400 font-bold">
                                {{ __('No purchase orders found matching your criteria.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($purchaseOrders->hasPages())
            <div class="p-4 border-t border-slate-100">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>

    {{-- View Purchase Order Modal --}}
    @if($showViewModal && $selectedPo)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-3xl rounded-2xl shadow-2xl border border-slate-200 max-h-[90vh] flex flex-col overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                {{-- Modal Header --}}
                <div class="p-4 bg-slate-900 text-white flex justify-between items-center print:hidden">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-0.5 text-[10px] font-black rounded bg-indigo-500 text-white uppercase">{{ $selectedPo->status }}</span>
                        <h3 class="font-black text-sm tracking-wide">{{ __('Purchase Order Details') }} - {{ $selectedPo->po_number }}</h3>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick="window.print()" class="px-2.5 py-1 bg-white/20 hover:bg-white/30 text-white rounded text-xs font-bold transition">
                            🖨 {{ __('Print') }}
                        </button>
                        <button wire:click="closeViewModal" class="text-slate-400 hover:text-white text-lg font-bold">&times;</button>
                    </div>
                </div>

                {{-- Printable Body --}}
                <div class="p-6 overflow-y-auto space-y-6 text-xs text-slate-700">
                    <div class="flex justify-between items-start border-b border-slate-200 pb-4">
                        <div>
                            <h2 class="text-xl font-black text-slate-900">{{ config('app.name', 'Pharmacy Expert') }}</h2>
                            <p class="text-slate-500 text-[11px]">{{ __('Purchase Order Demand Slip') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-black text-sm font-mono text-indigo-700">{{ $selectedPo->po_number }}</p>
                            <p class="text-slate-500 text-[11px]">{{ __('Date') }}: {{ \Carbon\Carbon::parse($selectedPo->order_date)->format('d M, Y') }}</p>
                            @if($selectedPo->expected_delivery_date)
                                <p class="text-slate-500 text-[11px]">{{ __('Expected') }}: {{ \Carbon\Carbon::parse($selectedPo->expected_delivery_date)->format('d M, Y') }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Supplier Info --}}
                    <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                        <div>
                            <span class="text-[10px] font-black uppercase text-slate-400 block mb-0.5">{{ __('Vendor / Supplier') }}</span>
                            <p class="font-bold text-slate-900">{{ $selectedPo->supplier?->name ?? 'N/A' }}</p>
                            <p class="text-slate-500">{{ $selectedPo->supplier?->company_name }}</p>
                            <p class="text-slate-500">{{ $selectedPo->supplier?->phone }}</p>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] font-black uppercase text-slate-400 block mb-0.5">{{ __('Issued By') }}</span>
                            <p class="font-bold text-slate-900">{{ $selectedPo->creator?->name ?? 'System' }}</p>
                            @if($selectedPo->notes)
                                <p class="text-[11px] text-slate-500 mt-1"><span class="font-bold">{{ __('Notes') }}:</span> {{ $selectedPo->notes }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Order Items Table --}}
                    <table class="w-full text-left border-collapse border border-slate-200">
                        <thead class="bg-slate-100 text-slate-700 font-bold uppercase text-[10px]">
                            <tr>
                                <th class="p-2 border border-slate-200">#</th>
                                <th class="p-2 border border-slate-200">{{ __('Product / Medicine') }}</th>
                                <th class="p-2 border border-slate-200 text-center">{{ __('Unit') }}</th>
                                <th class="p-2 border border-slate-200 text-right">{{ __('Qty Ordered') }}</th>
                                <th class="p-2 border border-slate-200 text-right">{{ __('Est. Unit Cost') }}</th>
                                <th class="p-2 border border-slate-200 text-right">{{ __('Total Cost') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @foreach($selectedPo->items as $idx => $item)
                                <tr>
                                    <td class="p-2 border border-slate-200 text-center">{{ $idx + 1 }}</td>
                                    <td class="p-2 border border-slate-200 font-bold text-slate-900">
                                        {{ $item->product?->name ?? 'N/A' }}
                                        <span class="text-[10px] text-slate-400 block">{{ $item->product?->sku }}</span>
                                    </td>
                                    <td class="p-2 border border-slate-200 text-center">{{ $item->unit?->name ?? '-' }}</td>
                                    <td class="p-2 border border-slate-200 text-right font-black font-mono">{{ $item->quantity_ordered }}</td>
                                    <td class="p-2 border border-slate-200 text-right font-mono">৳{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="p-2 border border-slate-200 text-right font-black font-mono">৳{{ number_format($item->total_cost, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-slate-50 font-bold">
                            <tr>
                                <td colspan="5" class="p-2 border border-slate-200 text-right">{{ __('Sub Total') }}:</td>
                                <td class="p-2 border border-slate-200 text-right font-mono">৳{{ number_format($selectedPo->sub_total, 2) }}</td>
                            </tr>
                            @if($selectedPo->discount > 0)
                                <tr>
                                    <td colspan="5" class="p-2 border border-slate-200 text-right text-rose-600">{{ __('Discount') }}:</td>
                                    <td class="p-2 border border-slate-200 text-right font-mono text-rose-600">-৳{{ number_format($selectedPo->discount, 2) }}</td>
                                </tr>
                            @endif
                            @if($selectedPo->tax_amount > 0)
                                <tr>
                                    <td colspan="5" class="p-2 border border-slate-200 text-right text-sky-600">{{ __('Tax / VAT') }}:</td>
                                    <td class="p-2 border border-slate-200 text-right font-mono text-sky-600">+৳{{ number_format($selectedPo->tax_amount, 2) }}</td>
                                </tr>
                            @endif
                            <tr class="bg-indigo-50 font-black text-indigo-950">
                                <td colspan="5" class="p-2.5 border border-slate-200 text-right uppercase">{{ __('Grand Total') }}:</td>
                                <td class="p-2.5 border border-slate-200 text-right font-mono text-sm">৳{{ number_format($selectedPo->grand_total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-between items-center print:hidden">
                    <button wire:click="closeViewModal" type="button" class="px-4 py-2 bg-slate-200 hover:bg-slate-300 text-slate-700 rounded-lg text-xs font-bold transition">
                        {{ __('Close') }}
                    </button>
                    @if(in_array($selectedPo->status, ['ORDERED', 'DRAFT', 'PARTIALLY_RECEIVED']))
                        <a href="{{ route('purchases.create', ['from_po' => $selectedPo->id]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-black shadow-xs transition flex items-center gap-1.5" wire:navigate>
                            📥 {{ __('Receive / Convert to GRN') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
