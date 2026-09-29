<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-black text-slate-900 tracking-tight">Stock Adjustment & Damage Reconcile</h1>
        <p class="text-xs text-slate-500 mt-1">Record physical audit discrepancies, damaged ampoules, expired batch removals, or manual stock corrections.</p>
    </div>

    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-lg flex justify-between">
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)">&times;</button>
        </div>
    @endif
    @if($errorMessage)
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-lg flex justify-between">
            <span>{{ $errorMessage }}</span>
            <button wire:click="$set('errorMessage', null)">&times;</button>
        </div>
    @endif

    <form wire:submit.prevent="saveAdjustment">
        <div class="bg-white rounded-lg border border-slate-200 shadow-xs p-5 space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Adjustment Reason *</label>
                    <select wire:model="reason" class="w-full py-2 px-3 border border-slate-300 rounded-lg font-bold">
                        <option value="DAMAGE">Damaged / Broken in Store</option>
                        <option value="EXPIRED">Expired Batch Disposal</option>
                        <option value="AUDIT_DISCREPANCY">Physical Count Discrepancy</option>
                        <option value="THEFT">Pilferage / Loss</option>
                        <option value="CORRECTION">Manual Inventory Correction</option>
                    </select>
                </div>
                <div>
                    <label class="font-bold text-slate-700 block mb-1">Notes / Reason Details</label>
                    <input wire:model="notes" type="text" placeholder="Audit on counter shelf 3..." class="w-full py-2 px-3 border border-slate-300 rounded-lg">
                </div>
            </div>

            {{-- Items line rows --}}
            <div class="space-y-3 pt-2 border-t border-slate-100">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-xs text-slate-800">Batch Items to Adjust</h3>
                    <button wire:click="addRow" type="button" class="text-xs text-sky-700 font-bold hover:underline">+ Add Medicine</button>
                </div>

                @foreach($items as $i => $item)
                    <div class="p-3 bg-slate-50 border border-slate-200 rounded-lg grid grid-cols-1 md:grid-cols-5 gap-3 text-xs items-center">
                        <div class="md:col-span-2">
                            <label class="font-bold text-slate-700 block mb-1">Medicine *</label>
                            <select 
                                wire:change="productSelected({{ $i }}, $event.target.value)"
                                class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg"
                            >
                                <option value="">-- Select Product --</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}" {{ $item['product_id'] == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Batch Number *</label>
                            <select wire:model="items.{{ $i }}.batch_id" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg font-mono">
                                <option value="">-- Batch --</option>
                                @if(!empty($item['product_id']))
                                    @php
                                        $batches = \App\Models\Batch::where('product_id', $item['product_id'])->get();
                                    @endphp
                                    @foreach($batches as $b)
                                        <option value="{{ $b->id }}">{{ $b->batch_number }} ({{ $b->current_stock }} pcs)</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="font-bold text-slate-700 block mb-1">Quantity *</label>
                            <input wire:model="items.{{ $i }}.quantity" type="number" min="1" class="w-full py-1.5 px-2 bg-white border border-slate-300 rounded-lg font-bold font-mono">
                        </div>

                        <div class="flex items-center justify-between pt-4">
                            <select wire:model="items.{{ $i }}.adjustment_type" class="py-1.5 px-2 bg-white border border-slate-300 rounded-lg font-bold text-rose-700">
                                <option value="DEDUCTION">Deduction (-)</option>
                                <option value="ADDITION">Addition (+)</option>
                            </select>
                            <button wire:click="removeRow({{ $i }})" type="button" class="text-rose-500 hover:text-rose-700 font-bold ml-2">&times;</button>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end pt-3 border-t border-slate-100">
                <button type="submit" class="px-6 py-2.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold shadow-md transition">
                    Execute Stock Adjustment
                </button>
            </div>
        </div>
    </form>
</div>
