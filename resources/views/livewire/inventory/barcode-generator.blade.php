<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #printable-label-area, #printable-label-area * {
                visibility: visible;
            }
            #printable-label-area {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                margin: 0;
                padding: 0;
                background: white !important;
                color: black !important;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>

    <!-- Header -->
    <div class="no-print flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 flex items-center gap-2">
                <svg class="w-7 h-7 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                </svg>
                Barcode & Shelf Label Generator
            </h1>
            <p class="text-sm text-slate-500 mt-1">
                Generate high-density thermal barcode stickers (50x25mm / 38x25mm) and A4 label sheets with MRP, Batch No, and Expiry details.
            </p>
        </div>

        @if ($batch)
            <button 
                type="button" 
                onclick="window.print()"
                class="px-5 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-sm transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24-1.077-.44-2.18-.592-3.3m11.744 0c-.152 1.12-.352 2.223-.592 3.3m-10.56 0A23.447 23.447 0 0 0 3 14.25v3.75a2.25 2.25 0 0 0 2.25 2.25h13.5A2.25 2.25 0 0 0 21 18V14.25c-1.026.16-2.072.26-3.13.3m-10.56 0A23.003 23.003 0 0 1 12 14.25c2.052 0 4.043-.223 5.92-.647M12 9V3m0 0 3 3m-3-3-3 3" />
                </svg>
                Print {{ $labelCount }} Label(s)
            </button>
        @endif
    </div>

    <!-- Configuration Grid -->
    <div class="no-print grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Medicine Batch Lookup -->
        <div class="lg:col-span-2 bg-white rounded-lg p-6 border border-slate-200 shadow-sm space-y-4">
            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600">
                Search Medicine or Batch
            </label>
            <div class="relative">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search by medicine name, SKU or barcode..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 text-sm focus:ring-2 focus:ring-sky-600"
                />
                <svg class="w-4 h-4 absolute left-3.5 top-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </div>

            @if (!empty($searchResults))
                <div class="border border-slate-200 rounded-lg max-h-56 overflow-y-auto divide-y divide-slate-100">
                    @foreach ($searchResults as $res)
                        <button 
                            type="button" 
                            wire:click="selectBatch({{ $res['id'] }})"
                            class="w-full px-4 py-2.5 text-left hover:bg-sky-50 flex items-center justify-between text-sm transition-colors">
                            <div>
                                <span class="font-bold text-slate-900">{{ $res['product']['name'] }}</span>
                                <span class="text-xs text-slate-500 block">Batch: <span class="font-mono font-bold text-slate-700">{{ $res['batch_number'] }}</span> • Exp: {{ \Carbon\Carbon::parse($res['expiry_date'])->format('d/m/Y') }}</span>
                            </div>
                            <span class="text-xs font-mono font-bold text-sky-700">MRP: ৳{{ number_format($res['mrp_rate'], 2) }}</span>
                        </button>
                    @endforeach
                </div>
            @endif

            <!-- Customization Switches -->
            <div class="pt-4 border-t border-slate-100 grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                    <input type="checkbox" wire:model.live="showPharmacyName" class="rounded text-sky-600 focus:ring-sky-500 border-slate-300" />
                    <span>Pharmacy Name</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                    <input type="checkbox" wire:model.live="showGeneric" class="rounded text-sky-600 focus:ring-sky-500 border-slate-300" />
                    <span>Generic Molecule</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                    <input type="checkbox" wire:model.live="showBatchNo" class="rounded text-sky-600 focus:ring-sky-500 border-slate-300" />
                    <span>Batch Number</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                    <input type="checkbox" wire:model.live="showExpiry" class="rounded text-sky-600 focus:ring-sky-500 border-slate-300" />
                    <span>Expiry Date</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                    <input type="checkbox" wire:model.live="showPrice" class="rounded text-sky-600 focus:ring-sky-500 border-slate-300" />
                    <span>MRP Price</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer text-slate-700 font-medium">
                    <input type="checkbox" wire:model.live="showBarcodeText" class="rounded text-sky-600 focus:ring-sky-500 border-slate-300" />
                    <span>Barcode Text</span>
                </label>
            </div>
        </div>

        <!-- Label Sizing & Print Count -->
        <div class="bg-white rounded-lg p-6 border border-slate-200 shadow-sm space-y-4">
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Label Layout Format
                </label>
                <select 
                    wire:model.live="labelSize"
                    class="w-full px-3.5 py-2.5 rounded-lg border border-slate-300 bg-white text-slate-900 text-sm focus:ring-2 focus:ring-sky-600">
                    <option value="50x25">Thermal Roll (50mm x 25mm Standard)</option>
                    <option value="38x25">Thermal Roll (38mm x 25mm Compact)</option>
                    <option value="a4-24">A4 Sheet (24 Labels / Page - 3x8)</option>
                    <option value="a4-30">A4 Sheet (30 Labels / Page - 3x10)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Number of Labels to Print
                </label>
                <input 
                    type="number" 
                    min="1" 
                    max="100" 
                    wire:model.live="labelCount"
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 font-mono text-sm focus:ring-2 focus:ring-sky-600"
                />
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 mb-1.5">
                    Pharmacy Brand Title
                </label>
                <input 
                    type="text" 
                    wire:model.live="pharmacyName"
                    class="w-full px-3.5 py-2 rounded-lg border border-slate-300 bg-white text-slate-900 text-sm focus:ring-2 focus:ring-sky-600 font-bold"
                />
            </div>
        </div>
    </div>

    <!-- Live Preview & Printable Label Sheet Area -->
    <div id="printable-label-area" class="space-y-4">
        <div class="no-print font-bold text-sm text-slate-600 uppercase tracking-wider">
            Live Print Preview ({{ $labelCount }} Stickers)
        </div>

        @if ($batch)
            <div class="flex flex-wrap gap-3 items-start justify-start p-6 bg-slate-50 rounded-lg border border-dashed border-slate-300">
                @for ($i = 0; $i < $labelCount; $i++)
                    <div class="bg-white text-black p-2 border border-slate-300 rounded shadow-sm flex flex-col justify-between" 
                         style="width: {{ $labelSize === '38x25' ? '150px' : '190px' }}; height: 95px; font-family: sans-serif;">
                        
                        <!-- Header -->
                        <div class="text-center leading-tight">
                            @if ($showPharmacyName)
                                <span class="font-black text-[9px] uppercase tracking-wider block text-slate-900">{{ $pharmacyName }}</span>
                            @endif
                            <span class="font-bold text-[11px] truncate block text-black">{{ $batch->product->name }}</span>
                            @if ($showGeneric && $batch->product->generic)
                                <span class="text-[8px] text-slate-600 truncate block">{{ $batch->product->generic->name }}</span>
                            @endif
                        </div>

                        <!-- Barcode Visual SVG -->
                        <div class="flex flex-col items-center justify-center my-0.5">
                            <svg class="w-full h-5" viewBox="0 0 100 20" preserveAspectRatio="none">
                                <rect x="2" width="2" height="20" fill="black"/>
                                <rect x="6" width="3" height="20" fill="black"/>
                                <rect x="12" width="1" height="20" fill="black"/>
                                <rect x="15" width="4" height="20" fill="black"/>
                                <rect x="22" width="2" height="20" fill="black"/>
                                <rect x="26" width="1" height="20" fill="black"/>
                                <rect x="30" width="3" height="20" fill="black"/>
                                <rect x="36" width="2" height="20" fill="black"/>
                                <rect x="40" width="4" height="20" fill="black"/>
                                <rect x="46" width="1" height="20" fill="black"/>
                                <rect x="50" width="3" height="20" fill="black"/>
                                <rect x="55" width="2" height="20" fill="black"/>
                                <rect x="60" width="4" height="20" fill="black"/>
                                <rect x="66" width="2" height="20" fill="black"/>
                                <rect x="70" width="1" height="20" fill="black"/>
                                <rect x="74" width="3" height="20" fill="black"/>
                                <rect x="80" width="2" height="20" fill="black"/>
                                <rect x="85" width="3" height="20" fill="black"/>
                                <rect x="91" width="1" height="20" fill="black"/>
                                <rect x="95" width="2" height="20" fill="black"/>
                            </svg>
                            @if ($showBarcodeText)
                                <span class="text-[8px] font-mono font-bold tracking-widest">{{ $batch->product->barcode ?: $batch->batch_number }}</span>
                            @endif
                        </div>

                        <!-- Footer Details -->
                        <div class="flex justify-between items-end text-[8px] border-t border-slate-200 pt-0.5">
                            <div>
                                @if ($showBatchNo)
                                    <span class="block font-mono">B: {{ $batch->batch_number }}</span>
                                @endif
                                @if ($showExpiry)
                                    <span class="block">Exp: {{ \Carbon\Carbon::parse($batch->expiry_date)->format('m/y') }}</span>
                                @endif
                            </div>
                            @if ($showPrice)
                                <div class="text-right">
                                    <span class="font-black text-[11px]">৳{{ number_format($batch->mrp_rate, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endfor
            </div>
        @else
            <div class="no-print bg-white rounded-lg p-12 border border-slate-200 text-center text-slate-500 shadow-sm">
                Please search and select a medicine batch above to preview labels.
            </div>
        @endif
    </div>
</div>
