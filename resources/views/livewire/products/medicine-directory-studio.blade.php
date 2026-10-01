<div class="space-y-6">
    {{-- Header Banner & Mode Selector --}}
    <div class="bg-gradient-to-r from-teal-700 via-sky-700 to-indigo-800 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="absolute -right-10 -bottom-10 opacity-10 pointer-events-none">
            <svg class="w-80 h-80 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M4.5 10.5C3.67 10.5 3 11.17 3 12s.67 1.5 1.5 1.5h15c.83 0 1.5-.67 1.5-1.5s-.67-1.5-1.5-1.5h-15z"/></svg>
        </div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 relative z-10">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/15 backdrop-blur-md text-xs font-semibold text-emerald-200 mb-2 border border-white/20">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Medex Cloud & DGDA Master Directory v2.4 (Anti-Ban Enabled)
                </div>
                <h1 class="text-2xl font-black tracking-tight">Cloud Medicine Directory & Master Importer</h1>
                <p class="text-sky-100 text-xs mt-1 max-w-2xl">
                    বাংলাদেশের রেজিস্টার্ড সকল ঔষধ ও জেনেরিক সরাসরি সার্চ করুন অথবা Medex থেকে ১-ক্লিকে নিজের ফার্মেসি প্রোডাক্ট ক্যাটালগে ইমপোর্ট করুন।
                </p>
            </div>

            {{-- Mode Switcher Tabs --}}
            <div class="flex items-center bg-black/20 backdrop-blur-md p-1.5 rounded-xl border border-white/20 shrink-0">
                <button 
                    wire:click="$set('activeTab', 'search')" 
                    type="button" 
                    class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'search' ? 'bg-white text-sky-900 shadow-md' : 'text-white/80 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <span>Search & Import</span>
                </button>
                <button 
                    wire:click="$set('activeTab', 'full_sync')" 
                    type="button" 
                    class="px-4 py-2 rounded-lg text-xs font-bold transition flex items-center gap-2 {{ $activeTab === 'full_sync' ? 'bg-emerald-500 text-white shadow-md' : 'text-white/80 hover:text-white' }}">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>⚡ Full Medex Fetcher</span>
                </button>
            </div>
        </div>

        {{-- Real-time Stats Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-5 pt-4 border-t border-white/15">
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                <div class="text-[11px] text-sky-200 font-medium">Catalog Products</div>
                <div class="text-xl font-black text-white mt-0.5">{{ number_format($totalProducts) }} <span class="text-xs font-normal text-sky-200">items</span></div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                <div class="text-[11px] text-sky-200 font-medium">Molecules / Generics</div>
                <div class="text-xl font-black text-emerald-300 mt-0.5">{{ number_format($totalGenerics) }} <span class="text-xs font-normal text-sky-200">generics</span></div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                <div class="text-[11px] text-sky-200 font-medium">Pharma Companies</div>
                <div class="text-xl font-black text-amber-300 mt-0.5">{{ number_format($totalManufacturers) }} <span class="text-xs font-normal text-sky-200">mfg</span></div>
            </div>
            <div class="bg-white/10 backdrop-blur-sm rounded-xl p-3 border border-white/10">
                <div class="text-[11px] text-sky-200 font-medium">Anti-Bot Engine</div>
                <div class="text-xs font-bold text-emerald-300 mt-1 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                    Stealth UA Active
                </div>
            </div>
        </div>
    </div>

    {{-- Feedback Alerts --}}
    @if ($feedbackMessage)
        <div x-data="{ show: true }" x-show="show" class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-sm">✓</div>
                <p class="text-xs font-bold text-emerald-900">{{ $feedbackMessage }}</p>
            </div>
            <button @click="show = false" type="button" class="text-xs font-bold text-emerald-700 hover:text-emerald-900">✕ বন্ধ করুন</button>
        </div>
    @endif

    {{-- =============================================================== --}}
    {{-- TAB 1: SEARCH & 1-CLICK IMPORT                                  --}}
    {{-- =============================================================== --}}
    @if ($activeTab === 'search')
        <div class="space-y-4">
            {{-- Search Bar & Actions --}}
            <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="relative flex-1 w-full">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <input 
                        wire:model.live.debounce.400ms="search" 
                        type="text" 
                        placeholder="Search BD Medicine / Generic (e.g., Napa, Seclo, Maxpro, Paracetamol, Ceevit, Azithromycin)..." 
                        class="w-full pl-11 pr-4 py-3 rounded-xl border border-slate-300 focus:border-sky-500 focus:ring-4 focus:ring-sky-500/10 text-sm font-medium text-slate-800 placeholder-slate-400 shadow-2xs"
                    />
                    <div wire:loading wire:target="search" class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <span class="text-xs font-bold text-sky-600 animate-pulse">Searching Cloud...</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0 w-full md:w-auto justify-between md:justify-end">
                    @if(count($selectedMedicines) > 0)
                        <button 
                            wire:click="importSelected" 
                            type="button" 
                            wire:loading.attr="disabled"
                            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md hover:shadow-lg transition flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            <span>Import Selected ({{ count($selectedMedicines) }})</span>
                        </button>
                    @endif
                </div>
            </div>

            {{-- Quick Search Chips --}}
            <div class="flex items-center gap-2 overflow-x-auto pb-1 text-xs">
                <span class="text-slate-400 font-bold shrink-0">Popular:</span>
                @foreach(['Napa', 'Seclo', 'Maxpro', 'Sergel', 'Ace Plus', 'Ceevit', 'Monas 10', 'Zimax 500', 'Finix 20', 'Almex', 'Telfast', 'Bextram Gold', 'Calbo D'] as $chip)
                    <button 
                        wire:click="$set('search', '{{ $chip }}')" 
                        type="button" 
                        class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-sky-100 hover:text-sky-800 text-slate-700 font-medium transition cursor-pointer shrink-0">
                        {{ $chip }}
                    </button>
                @endforeach
            </div>

            {{-- Results Cards Table --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-xs overflow-hidden">
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <input 
                            wire:model.live="selectAll" 
                            type="checkbox" 
                            id="selectAllBox" 
                            class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer"
                        />
                        <label for="selectAllBox" class="text-xs font-bold text-slate-700 cursor-pointer">
                            Select All ({{ count($searchResults) }} results found)
                        </label>
                    </div>
                    <span class="text-[11px] text-slate-500 font-medium">Live Cloud Match</span>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse ($searchResults as $idx => $item)
                        <div class="p-4 hover:bg-sky-50/40 transition flex flex-col md:flex-row md:items-center justify-between gap-4 {{ !empty($item['is_imported']) ? 'bg-slate-50/70' : '' }}">
                            <div class="flex items-start gap-3.5 flex-1">
                                <input 
                                    wire:model.live="selectedMedicines" 
                                    value="{{ $idx }}" 
                                    type="checkbox" 
                                    class="w-4 h-4 mt-1 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer"
                                />

                                <div class="space-y-1">
                                    <div class="flex items-center flex-wrap gap-2">
                                        <h3 class="text-sm font-bold text-slate-900">{{ $item['name'] }}</h3>
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-100 text-sky-800">{{ $item['dosage_form'] ?? 'Tablet' }}</span>
                                        @if(!empty($item['strength']))
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-700">{{ $item['strength'] }}</span>
                                        @endif
                                        @if(!empty($item['is_imported']))
                                            <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">✓ In Catalog</span>
                                        @endif
                                    </div>

                                    <div class="flex items-center flex-wrap gap-x-4 gap-y-1 text-xs text-slate-600">
                                        <div><span class="text-slate-400">Generic:</span> <strong class="text-indigo-700 font-semibold">{{ $item['generic'] }}</strong></div>
                                        <div><span class="text-slate-400">Mfg:</span> <strong class="text-slate-800">{{ $item['manufacturer'] }}</strong></div>
                                    </div>
                                </div>
                            </div>

                            {{-- Pricing Breakdown & Actions --}}
                            <div class="flex items-center gap-4 shrink-0 self-end md:self-center">
                                <div class="text-right">
                                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Unit MRP</div>
                                    <div class="text-base font-black text-slate-900">৳{{ number_format($item['unit_price'] ?? 0, 2) }} <span class="text-[10px] font-normal text-slate-500">/pc</span></div>
                                    <div class="text-[10px] text-slate-500">
                                        Strip: ৳{{ number_format(($item['unit_price'] ?? 0) * 10, 2) }} | Box: ৳{{ number_format(($item['unit_price'] ?? 0) * 100, 2) }}
                                    </div>
                                </div>

                                <div class="flex items-center gap-2">
                                    <button 
                                        wire:click="openImportModal({{ $idx }})" 
                                        type="button" 
                                        class="px-3 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1 cursor-pointer"
                                        title="Customize Buy/Sale Price before import">
                                        <span>⚙️ Edit</span>
                                    </button>

                                    <button 
                                        wire:click="instantImport({{ $idx }})" 
                                        type="button" 
                                        wire:loading.attr="disabled"
                                        class="px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs hover:shadow transition flex items-center gap-1.5 cursor-pointer">
                                        <span>⚡ 1-Click Import</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-slate-500 space-y-3">
                            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto text-xl font-bold">?</div>
                            <p class="text-sm font-semibold">কোন ঔষধ পাওয়া যায়নি।</p>
                            <p class="text-xs text-slate-400 max-w-sm mx-auto">অনুগ্রহ করে সঠিক ঔষধের নাম অথবা জেনেরিক দিয়ে আবার সার্চ করুন।</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    {{-- =============================================================== --}}
    {{-- TAB 2: FULL MEDEX FETCHER / MASTER CRAWLER                      --}}
    {{-- =============================================================== --}}
    @if ($activeTab === 'full_sync')
        <div class="space-y-6">
            {{-- Master Sync Option Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                {{-- 1. National BD Master Catalog (Instant 1-Click) --}}
                <div class="bg-white p-6 rounded-2xl border border-emerald-200 shadow-xs space-y-4 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-24 h-24 bg-emerald-50 rounded-bl-full -z-0"></div>
                    <div class="relative z-10 space-y-2">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-100 text-emerald-800 text-xs font-black">
                            ⚡ INSTANT • ZERO RATE LIMIT
                        </div>
                        <h3 class="text-lg font-black text-slate-900">1-Click National Master Sync</h3>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            বাংলাদেশের টপ সকল ফার্মাসিউটিক্যালস (Square, Beximco, Incepta, Opsonin, Renata, Eskayef)-এর ভেরিফাইড ওষুধ, জেনেরিক ও মূল্য সরাসরি সিস্টেমে সিঙ্ক করুন। এতে কোন আইপি ব্লক বা ক্লাউডফ্লেয়ার ইস্যু হবে না।
                        </p>
                        <div class="pt-2">
                            <button 
                                wire:click="syncNationalMaster" 
                                type="button" 
                                wire:loading.attr="disabled"
                                class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-md hover:shadow-lg transition flex items-center gap-2 cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                <span>Sync Master Catalog Now</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 2. CLI Background Auto-Crawler Engine --}}
                <div class="bg-slate-900 p-6 rounded-2xl text-slate-100 shadow-md space-y-4 border border-slate-800">
                    <div class="space-y-1">
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-md bg-sky-900/60 text-sky-300 text-[11px] font-mono font-bold">
                            terminal CLI engine
                        </div>
                        <h3 class="text-base font-black text-white">Full Medex Crawl via Terminal (Recommended)</h3>
                        <p class="text-xs text-slate-400">
                            হাজার হাজার ওষুধ স্বয়ংক্রিয়ভাবে ব্যাকগ্রাউন্ডে রেট-লিমিটিং সেইফ মোডে স্ক্র্যাপ করে আনার জন্য টার্মিনালে নিচের কমান্ডটি রান করুন:
                        </p>
                    </div>

                    <div class="bg-black/60 p-3 rounded-xl border border-slate-800 font-mono text-xs text-emerald-400 select-all overflow-x-auto">
                        php artisan medex:sync-all --alpha=all --delay=2
                    </div>

                    <div class="text-[11px] text-slate-400 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-sky-400"></span>
                        Delay 2s নিশ্চিত করে যে Medex কখনো আপনার IP ব্লক করবে না।
                    </div>
                </div>
            </div>

            {{-- Alphabet-by-Alphabet Live Crawler --}}
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-xs space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 pb-4">
                    <div>
                        <h3 class="text-base font-black text-slate-900">Alphabet Live Crawler (A to Z)</h3>
                        <p class="text-xs text-slate-500">যেকোনো অক্ষর সিলেক্ট করে সরাসরি Medex থেকে ব্র্যান্ড পেজ ফেচ করুন।</p>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="text-xs font-bold text-slate-600">Max Pages:</label>
                        <select wire:model.live="crawlPages" class="text-xs font-bold rounded-lg border-slate-300 py-1.5 px-3 focus:ring-sky-500">
                            <option value="1">1 Page (~20 items)</option>
                            <option value="3">3 Pages (~60 items)</option>
                            <option value="5">5 Pages (~100 items)</option>
                            <option value="10">10 Pages (~200 items)</option>
                        </select>
                    </div>
                </div>

                {{-- A-Z Alphabet Buttons --}}
                <div class="flex flex-wrap gap-2 pt-2">
                    @foreach (range('A', 'Z') as $char)
                        <button 
                            wire:click="crawlAlpha('{{ strtolower($char) }}')" 
                            type="button" 
                            wire:loading.attr="disabled"
                            class="w-10 h-10 rounded-xl font-black text-xs transition flex items-center justify-center cursor-pointer border {{ strtolower($char) === $selectedAlpha ? 'bg-sky-600 text-white border-sky-600 shadow-sm' : 'bg-slate-50 hover:bg-sky-100 text-slate-700 border-slate-200' }}">
                            {{ $char }}
                        </button>
                    @endforeach
                </div>

                {{-- Live Crawl Logs Box --}}
                @if (!empty($crawlLogs))
                    <div class="mt-4 p-4 rounded-xl bg-slate-950 text-slate-200 font-mono text-xs space-y-1.5 max-h-64 overflow-y-auto border border-slate-800 shadow-inner">
                        <div class="text-[11px] font-bold text-slate-500 border-b border-slate-800 pb-1 mb-2">LIVE CRAWL OUTPUT</div>
                        @foreach ($crawlLogs as $log)
                            <div class="{{ str_contains($log, '✓') || str_contains($log, '🎉') ? 'text-emerald-400' : (str_contains($log, '⚠️') ? 'text-amber-400' : 'text-slate-300') }}">
                                {{ $log }}
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- =============================================================== --}}
    {{-- CUSTOMIZE & IMPORT MODAL                                        --}}
    {{-- =============================================================== --}}
    @if ($showImportModal && !empty($editingItem))
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-slate-100 space-y-5 animate-in fade-in zoom-in-95 duration-150">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900">⚙️ Customize Pricing & Import</h3>
                        <p class="text-xs text-slate-500">প্রোডাক্ট ইমপোর্ট করার পূর্বে ক্রয় ও বিক্রয় মূল্য নির্ধারণ করুন।</p>
                    </div>
                    <button wire:click="$set('showImportModal', false)" type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold">✕</button>
                </div>

                <div class="bg-sky-50/70 p-3.5 rounded-xl border border-sky-100 space-y-1">
                    <div class="text-sm font-bold text-slate-900">{{ $editingItem['name'] }}</div>
                    <div class="text-xs text-slate-600">Generic: <strong>{{ $editingItem['generic'] }}</strong></div>
                    <div class="text-xs text-slate-600">Company: <strong>{{ $editingItem['manufacturer'] }}</strong></div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Unit MRP / Sale Price (৳)</label>
                        <input 
                            wire:model.live="customMrpPrice" 
                            type="number" 
                            step="0.01" 
                            class="w-full text-sm font-bold rounded-xl border-slate-300 focus:ring-sky-500 focus:border-sky-500"
                        />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Purchase / Cost Price (৳)</label>
                        <input 
                            wire:model.live="customBuyPrice" 
                            type="number" 
                            step="0.01" 
                            class="w-full text-sm font-bold rounded-xl border-slate-300 focus:ring-sky-500 focus:border-sky-500"
                        />
                    </div>
                </div>

                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs text-slate-600 space-y-1">
                    <div class="font-bold text-slate-800">Auto Packaging Calculation:</div>
                    <div>• Strip (10 pcs): MRP ৳{{ number_format($customMrpPrice * 10, 2) }} | Cost ৳{{ number_format($customBuyPrice * 10, 2) }}</div>
                    <div>• Box (100 pcs): MRP ৳{{ number_format($customMrpPrice * 100, 2) }} | Cost ৳{{ number_format($customBuyPrice * 100, 2) }}</div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button 
                        wire:click="$set('showImportModal', false)" 
                        type="button" 
                        class="px-4 py-2 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                        Cancel
                    </button>
                    <button 
                        wire:click="confirmSingleImport" 
                        type="button" 
                        class="px-5 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-md hover:shadow-lg transition cursor-pointer">
                        ✓ Confirm & Save to Catalog
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
