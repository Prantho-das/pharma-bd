<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>মেয়াদোত্তীর্ণ ও ড্যামেজ ম্যানেজমেন্ট (Near-Expiry & Damage Claims)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">নিকটবর্তী মেয়াদের ওষুধ শনাক্তকরণ, সাপ্লায়ারে এক্সপায়ারি রিটার্ন ক্লেইম এবং নষ্ট ওষুধের ড্যামেজ রাইট-অফ হিসাব।</p>
        </div>

        <div class="flex items-center gap-2">
            <a 
                href="{{ route('purchases.returns') }}" 
                wire:navigate 
                class="px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 15v-1a4 4 0 00-4-4H4m0 0l3-3m-3 3l3 3m5 4v1a3 3 0 003 3h4a3 3 0 003-3V7a3 3 0 00-3-3h-4a3 3 0 00-3 3v1"></path></svg>
                <span>Supplier Return Hub</span>
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-lg flex justify-between items-center shadow-xs">
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)" class="text-emerald-500 font-bold">&times;</button>
        </div>
    @endif
    @if($errorMessage)
        <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-lg flex justify-between items-center shadow-xs">
            <span>{{ $errorMessage }}</span>
            <button wire:click="$set('errorMessage', null)" class="text-rose-500 font-bold">&times;</button>
        </div>
    @endif

    {{-- KPIs --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">নিকটবর্তী মেয়াদের ঝুঁকি (Near Expiry Value)</div>
                <div class="text-2xl font-black text-amber-600 font-mono mt-1">৳{{ number_format($totalNearExpiryAtRisk, 2) }}</div>
                <div class="text-[10px] text-amber-700 mt-0.5">আগামী {{ $expiryDaysThreshold }} দিনের মধ্যে মেয়াদ উত্তীর্ণ হবে</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-lg">⏳</div>
        </div>

        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">মেয়াদোত্তীর্ণ নষ্ট স্টক মূল্য (Expired Value)</div>
                <div class="text-2xl font-black text-rose-600 font-mono mt-1">৳{{ number_format($totalExpiredValue, 2) }}</div>
                <div class="text-[10px] text-rose-600 mt-0.5">বিক্রি বন্ধ ও দ্রুত রাইট-অফ আবশ্যক</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-lg">🚫</div>
        </div>
    </div>

    {{-- Tabs Toolbar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 pb-2">
        <div class="flex items-center space-x-2">
            <button 
                type="button" 
                wire:click="$set('activeTab', 'near_expiry')" 
                class="px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer {{ $activeTab === 'near_expiry' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                ⏳ নিকটবর্তী মেয়াদ (Near Expiry)
            </button>
            <button 
                type="button" 
                wire:click="$set('activeTab', 'expired')" 
                class="px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer {{ $activeTab === 'expired' ? 'bg-rose-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                🚫 মেয়াদোত্তীর্ণ ব্যাচ (Expired)
            </button>
            <button 
                type="button" 
                wire:click="$set('activeTab', 'damage_log')" 
                class="px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer {{ $activeTab === 'damage_log' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
                📋 ড্যামেজ রাইট-অফ লগ (Damage History)
            </button>
        </div>

        @if($activeTab === 'near_expiry')
            <div class="flex items-center gap-2">
                <span class="text-xs text-slate-500 font-semibold">মেয়াদের সময়সীমা:</span>
                <select wire:model.live="expiryDaysThreshold" class="text-xs rounded-lg border border-slate-300 py-1.5 px-2 bg-white">
                    <option value="30">৩০ দিনের মধ্যে (Urgent)</option>
                    <option value="60">৬০ দিনের মধ্যে</option>
                    <option value="90">৯০ দিনের মধ্যে</option>
                </select>
            </div>
        @endif
    </div>

    {{-- Tab 1: Near Expiry Batches --}}
    @if($activeTab === 'near_expiry')
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-700 border-b border-slate-200 font-bold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="p-3">ওষুধ ও কোম্পানি (Medicine)</th>
                        <th class="p-3">ব্যাচ নম্বর (Batch)</th>
                        <th class="p-3 text-center">মেয়াদোত্তীর্ণ তারিখ (Expiry)</th>
                        <th class="p-3 text-center">অবশিষ্ট দিন (Days Left)</th>
                        <th class="p-3 text-center">মজুদ (Stock)</th>
                        <th class="p-3 text-right">ঝুঁকিপূর্ণ মূল্য (At Risk ৳)</th>
                        <th class="p-3 text-center w-40">পদক্ষেপ (Action)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($nearExpiryBatches as $batch)
                        @php
                            $daysLeft = \Carbon\Carbon::now()->diffInDays($batch->expiry_date, false);
                            $riskVal = $batch->current_stock * ((float)$batch->purchase_rate);
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3">
                                <div class="font-bold text-slate-900 text-sm">{{ $batch->product?->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $batch->product?->manufacturer?->name ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3 font-mono font-bold text-slate-800">
                                {{ $batch->batch_number }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold text-amber-700">
                                {{ $batch->expiry_date ? $batch->expiry_date->format('d M, Y') : 'N/A' }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full font-bold text-[10px] {{ $daysLeft <= 30 ? 'bg-rose-100 text-rose-800' : 'bg-amber-100 text-amber-800' }}">
                                    {{ max(0, (int)$daysLeft) }} দিন বাকি
                                </span>
                            </td>
                            <td class="p-3 text-center font-bold text-slate-800">
                                {{ $batch->current_stock }} {{ $batch->product?->baseUnit?->name ?? 'pcs' }}
                            </td>
                            <td class="p-3 text-right font-mono font-bold text-amber-700">
                                ৳{{ number_format($riskVal, 2) }}
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a 
                                        href="{{ route('purchases.returns') }}" 
                                        wire:navigate 
                                        class="px-2.5 py-1 rounded bg-sky-50 text-sky-800 hover:bg-sky-100 font-bold text-[11px] transition cursor-pointer"
                                        title="Return to Supplier"
                                    >
                                        Claim Return
                                    </a>
                                    <button 
                                        type="button" 
                                        wire:click="openDamageModal({{ $batch->id }})" 
                                        class="px-2.5 py-1 rounded bg-rose-50 text-rose-700 hover:bg-rose-100 font-bold text-[11px] transition cursor-pointer"
                                    >
                                        Write-off
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-12 text-center text-slate-400">
                                আগামী {{ $expiryDaysThreshold }} দিনের মধ্যে কোনো ওষুধের মেয়াদ শেষ হওয়ার ঝুঁকি নেই।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $nearExpiryBatches->links() }}
            </div>
        </div>
    @endif

    {{-- Tab 2: Expired Batches --}}
    @if($activeTab === 'expired')
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-rose-50 text-rose-900 border-b border-rose-100 font-bold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="p-3">ওষুধ ও কোম্পানি (Medicine)</th>
                        <th class="p-3">ব্যাচ নম্বর (Batch)</th>
                        <th class="p-3 text-center">মেয়াদ উত্তীর্ণের তারিখ (Expired Date)</th>
                        <th class="p-3 text-center">মজুদ (Stock)</th>
                        <th class="p-3 text-right">ক্ষতিগ্রস্ত মূল্য (Loss Valuation ৳)</th>
                        <th class="p-3 text-center w-36">পদক্ষেপ (Action)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expiredBatches as $batch)
                        @php
                            $expVal = $batch->current_stock * ((float)$batch->purchase_rate);
                        @endphp
                        <tr class="hover:bg-rose-50/30 transition">
                            <td class="p-3">
                                <div class="font-bold text-slate-900 text-sm">{{ $batch->product?->name }}</div>
                                <div class="text-[11px] text-slate-500">{{ $batch->product?->manufacturer?->name ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3 font-mono font-bold text-slate-800">
                                {{ $batch->batch_number }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold text-rose-600">
                                {{ $batch->expiry_date ? $batch->expiry_date->format('d M, Y') : 'N/A' }}
                            </td>
                            <td class="p-3 text-center font-bold text-rose-700">
                                {{ $batch->current_stock }} {{ $batch->product?->baseUnit?->name ?? 'pcs' }}
                            </td>
                            <td class="p-3 text-right font-mono font-black text-rose-600">
                                ৳{{ number_format($expVal, 2) }}
                            </td>
                            <td class="p-3 text-center">
                                <button 
                                    type="button" 
                                    wire:click="openDamageModal({{ $batch->id }})" 
                                    class="px-3 py-1.5 rounded bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs transition cursor-pointer shadow-xs"
                                >
                                    Record Disposal
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400">
                                কোনো মেয়াদোত্তীর্ণ ওষুধ বর্তমানে স্টকে নেই।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $expiredBatches->links() }}
            </div>
        </div>
    @endif

    {{-- Tab 3: Damage & Spoilage Adjustment Logs --}}
    @if($activeTab === 'damage_log')
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-700 border-b border-slate-200 font-bold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="p-3">তারিখ ও সময় (Date)</th>
                        <th class="p-3">ওষুধের নাম (Medicine)</th>
                        <th class="p-3">ব্যাচ (Batch)</th>
                        <th class="p-3 text-center">পরিমাণ (Qty)</th>
                        <th class="p-3">কারণ ও নোট (Reason & Note)</th>
                        <th class="p-3 text-center">অ্যাডজাস্টকারী (User)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($damageLogs as $log)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3 text-slate-600 font-mono text-[11px]">
                                {{ $log->created_at->format('d M, Y h:i A') }}
                            </td>
                            <td class="p-3 font-bold text-slate-900">
                                {{ $log->product?->name }}
                            </td>
                            <td class="p-3 font-mono font-bold text-slate-700">
                                {{ $log->batch?->batch_number ?? 'N/A' }}
                            </td>
                            <td class="p-3 text-center font-mono font-bold text-rose-600">
                                -{{ $log->quantity }} {{ $log->product?->baseUnit?->name ?? 'pcs' }}
                            </td>
                            <td class="p-3 text-slate-700">
                                {{ $log->reason }}
                            </td>
                            <td class="p-3 text-center text-slate-600">
                                {{ $log->user?->name ?? 'Admin' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400">
                                কোনো ড্যামেজ রাইট-অফ রেকর্ড পাওয়া যায়নি।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $damageLogs->links() }}
            </div>
        </div>
    @endif

    {{-- Damage / Write-off Modal --}}
    @if($showDamageModal && $selectedBatch)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-lg rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-rose-700 px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base">Record Damage / Spoilage Write-Off</h3>
                        <p class="text-xs text-rose-100">{{ $selectedBatch->product?->name }} (Batch: {{ $selectedBatch->batch_number }})</p>
                    </div>
                    <button wire:click="$set('showDamageModal', false)" class="text-white hover:text-rose-200 font-bold text-xl cursor-pointer">&times;</button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">রাইট-অফ পরিমাণ (Quantity to Write-Off)</label>
                        <div class="flex items-center gap-2">
                            <input 
                                type="number" 
                                min="1" 
                                max="{{ $selectedBatch->current_stock }}" 
                                wire:model="damageQuantity" 
                                class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 py-2 px-3" 
                            />
                            <span class="text-xs font-bold text-slate-500 whitespace-nowrap">
                                / {{ $selectedBatch->current_stock }} {{ $selectedBatch->product?->baseUnit?->name ?? 'pcs' }} Available
                            </span>
                        </div>
                        @error('damageQuantity') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">নষ্ট হওয়ার কারণ (Disposal Reason)</label>
                        <select wire:model="damageReason" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 bg-white">
                            <option value="Expired & Disposed">মেয়াদোত্তীর্ণ ও বিনষ্টকরণ (Expired & Disposed)</option>
                            <option value="Broken / Physical Damage">ভেঙে যাওয়া / ক্ষতিগ্রস্ত (Broken / Physical Damage)</option>
                            <option value="Temperature Excursion">কোল্ড চেইন নষ্ট / ফ্রিজের সমস্যা (Temperature Excursion)</option>
                            <option value="Leakage / Quality Issue">লিক বা গুণগত সমস্যা (Leakage / Quality Issue)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">অতিরিক্ত বিবরণ (Optional Note)</label>
                        <textarea wire:model="damageNote" rows="2" placeholder="নোট বা রেফারেন্স লিখুন..." class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3"></textarea>
                    </div>

                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-900 font-medium">
                        ⚠️ এই পরিমাণ ওষুধটি বর্তমান স্টক থেকে স্থায়ীভাবে বাদ দেওয়া হবে এবং আর্থিক ক্ষতি হিসেবে রিপোর্ট হবে।
                    </div>
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-end space-x-2">
                    <button wire:click="$set('showDamageModal', false)" type="button" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-lg text-xs cursor-pointer">
                        Cancel
                    </button>
                    <button wire:click="recordDamageWriteOff" type="button" class="px-5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-lg text-xs shadow-md transition cursor-pointer">
                        Confirm Write-off
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
