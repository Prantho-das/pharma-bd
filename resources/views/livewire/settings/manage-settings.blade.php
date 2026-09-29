<div class="py-6 space-y-6 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {{-- Card Container --}}
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        {{-- Header --}}
        <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white">
            <div>
                <h2 class="text-base font-bold text-slate-900 tracking-tight flex items-center gap-2">
                    <svg class="w-5 h-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span>General Pharmacy Settings</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Manage invoice headers, taxes, currency and business details</p>
            </div>

            <div class="flex items-center gap-3">
                @if (session('success'))
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200 shadow-2xs">
                        ✓ {{ session('success') }}
                    </span>
                @endif

                <button 
                    type="submit" 
                    form="settings-form"
                    wire:loading.attr="disabled"
                    class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Save Settings</span>
                </button>
            </div>
        </div>

        <form id="settings-form" wire:submit="saveSettings" class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                
                {{-- Row 1: Business Category & Company Name --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Business Category</label>
                    <div class="flex items-center gap-2.5">
                        <select wire:model="businessCategory" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                            <option value="Home Care Store">Home Care Store</option>
                            <option value="Retail Pharmacy">Retail Pharmacy</option>
                            <option value="Hospital Pharmacy">Hospital Pharmacy</option>
                            <option value="Wholesale Pharmacy">Wholesale Pharmacy</option>
                            <option value="Community Pharmacy">Community Pharmacy</option>
                        </select>
                        <div class="w-6 shrink-0"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Company / Business Name</label>
                    <div class="flex items-center gap-2.5">
                        <input type="text" wire:model="companyName" placeholder="e.g. Acnoo Pharmacy" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" />
                        <input type="checkbox" wire:model="showCompanyName" title="Show on Invoice" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 shrink-0 cursor-pointer" />
                    </div>
                </div>

                {{-- Row 2: Phone & Email --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Phone Number</label>
                    <div class="flex items-center gap-2.5">
                        <input type="text" wire:model="phone" placeholder="01712022529" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" />
                        <input type="checkbox" wire:model="showPhone" title="Show on Invoice" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 shrink-0 cursor-pointer" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email</label>
                    <div class="flex items-center gap-2.5">
                        <input type="email" wire:model="email" placeholder="acnoo@gmail.com" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" />
                        <input type="checkbox" wire:model="showEmail" title="Show on Invoice" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 shrink-0 cursor-pointer" />
                    </div>
                </div>

                {{-- Row 3: Address & Sale Rounding Option --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Address</label>
                    <div class="flex items-center gap-2.5">
                        <input type="text" wire:model="address" placeholder="Dhaka, Bangladesh" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" />
                        <input type="checkbox" wire:model="showAddress" title="Show on Invoice" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 shrink-0 cursor-pointer" />
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Sale Rounding Option</label>
                    <div class="flex items-center gap-2.5">
                        <select wire:model="saleRoundingOption" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                            <option value="None">None</option>
                            <option value="Round to Nearest 0.50">Round to nearest (0.50)</option>
                            <option value="Round Up (Ceil)">Round Up (Ceil)</option>
                            <option value="Round Down (Floor)">Round Down (Floor)</option>
                        </select>
                        <div class="w-6 shrink-0"></div>
                    </div>
                </div>

                {{-- Row 4: TAX/GST Title & TAX/GST Number --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">TAX/GST Title</label>
                    <div class="flex items-center gap-2.5">
                        <input type="text" wire:model="taxGstTitle" placeholder="e.g. GST or VAT" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" />
                        <div class="w-6 shrink-0"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">TAX/GST Number</label>
                    <div class="flex items-center gap-2.5">
                        <input type="text" wire:model="taxGstNumber" placeholder="1234" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" />
                        <input type="checkbox" wire:model="showTaxGstNumber" title="Show on Invoice" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 shrink-0 cursor-pointer" />
                    </div>
                </div>

                {{-- Row 5: Product Profit Option & Post-Sale Message --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Product Profit Option</label>
                    <div class="flex items-center gap-2.5">
                        <select wire:model="productProfitOption" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20">
                            <option value="Markup (Profit based on purchase price) • Default">Markup (Profit based on purchase price) • Default</option>
                            <option value="Margin (Profit based on selling price)">Margin (Profit based on selling price)</option>
                        </select>
                        <div class="w-6 shrink-0"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Post-Sale Message</label>
                    <div class="flex items-center gap-2.5">
                        <input type="text" wire:model="postSaleMessage" placeholder="Enter Message" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" />
                        <input type="checkbox" wire:model="showPostSaleMessage" title="Show on Invoice" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 shrink-0 cursor-pointer" />
                    </div>
                </div>

                {{-- Row 6: Invoice Note Label & Invoice Note --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Invoice Note Label</label>
                    <div class="flex items-center gap-2.5">
                        <input type="text" wire:model="invoiceNoteLabel" placeholder="Enter Label" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" />
                        <div class="w-6 shrink-0"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Invoice Note</label>
                    <div class="flex items-center gap-2.5">
                        <input type="text" wire:model="invoiceNote" placeholder="Enter Note" class="flex-1 w-full text-xs font-normal text-slate-800 bg-white border border-slate-300 rounded-lg px-3 py-2 focus:border-sky-500 focus:ring-2 focus:ring-sky-500/20" />
                        <input type="checkbox" wire:model="showInvoiceNote" title="Show on Invoice" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 shrink-0 cursor-pointer" />
                    </div>
                </div>

                {{-- Row 7: A4 Page Invoice Logo & Thermal Invoice Logo --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">A4 Page Invoice Logo</label>
                    <div class="flex items-center gap-2.5">
                        <label class="flex-1 border-2 border-dashed border-slate-200 hover:border-sky-400 rounded-lg p-4 bg-slate-50/50 flex flex-col items-center justify-center cursor-pointer transition min-h-[95px] relative">
                            <input type="file" wire:model="a4InvoiceLogo" accept="image/*" class="hidden" />
                            @if ($a4InvoiceLogo)
                                <img src="{{ $a4InvoiceLogo->temporaryUrl() }}" class="h-10 object-contain" />
                            @elseif ($existingA4Logo)
                                <img src="{{ asset('storage/' . $existingA4Logo) }}" class="h-10 object-contain" />
                            @else
                                <div class="flex items-center gap-2 text-slate-700 font-bold text-sm">
                                    <span class="text-amber-500 text-lg">💊</span>
                                    <span>Acnoo Pharmacy</span>
                                </div>
                                <span class="text-[10px] text-slate-400 mt-0.5">Click to upload A4 Header Logo</span>
                            @endif
                        </label>
                        <div class="w-6 shrink-0"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Thermal Invoice Logo</label>
                    <div class="flex items-center gap-2.5">
                        <label class="flex-1 border-2 border-dashed border-slate-200 hover:border-sky-400 rounded-lg p-4 bg-slate-50/50 flex flex-col items-center justify-center cursor-pointer transition min-h-[95px] relative">
                            <input type="file" wire:model="thermalInvoiceLogo" accept="image/*" class="hidden" />
                            @if ($thermalInvoiceLogo)
                                <img src="{{ $thermalInvoiceLogo->temporaryUrl() }}" class="h-10 object-contain" />
                            @elseif ($existingThermalLogo)
                                <img src="{{ asset('storage/' . $existingThermalLogo) }}" class="h-10 object-contain" />
                            @else
                                <div class="w-8 h-8 rounded-lg bg-emerald-50 border border-emerald-200 flex items-center justify-center text-emerald-600 font-bold text-sm">
                                    +
                                </div>
                                <span class="text-[10px] text-slate-400 mt-0.5">Click to upload POS Logo</span>
                            @endif
                        </label>
                        <div class="w-6 shrink-0"></div>
                    </div>
                </div>

                {{-- Row 8: Invoice Scanner Logo --}}
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Invoice Scanner Logo</label>
                    <div class="flex items-center gap-2.5">
                        <label class="flex-1 border-2 border-dashed border-slate-200 hover:border-sky-400 rounded-lg p-4 bg-slate-50/50 flex flex-col items-center justify-center cursor-pointer transition min-h-[95px] relative">
                            <input type="file" wire:model="invoiceScannerLogo" accept="image/*" class="hidden" />
                            @if ($invoiceScannerLogo)
                                <img src="{{ $invoiceScannerLogo->temporaryUrl() }}" class="h-10 object-contain" />
                            @elseif ($existingScannerLogo)
                                <img src="{{ asset('storage/' . $existingScannerLogo) }}" class="h-10 object-contain" />
                            @else
                                <div class="text-xl">
                                    📱
                                </div>
                                <span class="text-[10px] text-slate-400 mt-0.5">Click to upload QR / Scanner Logo</span>
                            @endif
                        </label>
                        <input type="checkbox" wire:model="showScannerLogo" title="Show Scanner Logo" class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 shrink-0 cursor-pointer" />
                    </div>
                </div>

            </div>

            {{-- Submit Action --}}
            <div class="pt-6 border-t border-slate-100 flex items-center justify-center gap-3">
                <button 
                    type="submit" 
                    wire:loading.attr="disabled"
                    class="px-8 py-2.5 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-sm transition shadow-xs flex items-center gap-2 cursor-pointer">
                    <svg wire:loading.remove class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <svg wire:loading class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span>Save Settings</span>
                </button>
            </div>
        </form>
    </div>
</div>
