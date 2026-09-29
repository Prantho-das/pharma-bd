<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2">
                <span>কাস্টমার এসএমএস ও ক্রনিক রিফিল রিমাইন্ডার (SMS & Refill Hub)</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">নিয়মিত ডায়াবেটিস/প্রেশারের রোগীদের ওষুধ শেষের পূর্বে রিফিল এসএমএস ও বকেয়া টাকা আদায়ের নোটিফিকেশন সিস্টেম।</p>
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
                <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">বকেয়া থাকা মোট কাস্টমার</div>
                <div class="text-2xl font-black text-slate-900 mt-1">{{ $totalDueCustomersCount }} জন</div>
                <div class="text-[10px] text-slate-400 mt-0.5">রিমাইন্ডার এসএমএস পাঠানো সম্ভব</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold text-lg">📱</div>
        </div>

        <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-xs flex items-center justify-between">
            <div>
                <div class="text-[11px] text-slate-500 font-bold uppercase tracking-wider">মোট বকেয়া পাওনা (Total Due Balance)</div>
                <div class="text-2xl font-black text-rose-600 font-mono mt-1">৳{{ number_format($totalDueAmount, 2) }}</div>
                <div class="text-[10px] text-rose-500 mt-0.5">কাস্টমার লেজারে ট্র্যাকিং হচ্ছে</div>
            </div>
            <div class="w-10 h-10 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-lg">💰</div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex items-center space-x-2 border-b border-slate-200 pb-2">
        <button 
            type="button" 
            wire:click="$set('activeTab', 'refills')" 
            class="px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer {{ $activeTab === 'refills' ? 'bg-sky-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            💊 ক্রনিক প্রেসক্রিপশন রিফিল (Refill Reminders)
        </button>
        <button 
            type="button" 
            wire:click="$set('activeTab', 'dues')" 
            class="px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer {{ $activeTab === 'dues' ? 'bg-indigo-600 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            🔔 বাকি পরিশোধের এসএমএস (Due Collection SMS)
        </button>
        <button 
            type="button" 
            wire:click="$set('activeTab', 'custom_sms')" 
            class="px-4 py-2 rounded-lg text-xs font-bold transition cursor-pointer {{ $activeTab === 'custom_sms' ? 'bg-slate-800 text-white shadow-xs' : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200' }}">
            ✉️ কাস্টম এসএমএস কম্পোজার (Broadcast SMS)
        </button>
    </div>

    {{-- Tab 1: Refill Candidates --}}
    @if($activeTab === 'refills')
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-700 border-b border-slate-200 font-bold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="p-3">রোগী / কাস্টমার (Customer)</th>
                        <th class="p-3">ওষুধের নাম (Medicine)</th>
                        <th class="p-3 text-center">সর্বশেষ ক্রয় তারিখ (Last Purchased)</th>
                        <th class="p-3 text-center">পরিমাণ (Purchased Qty)</th>
                        <th class="p-3 text-center">সম্ভাব্য শেষ হওয়ার তারিখ (Refill Due)</th>
                        <th class="p-3 text-center w-36">এসএমএস পাঠান (Action)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($refillCandidates as $cItem)
                        @php
                            $pDate = $cItem->sale?->sale_date;
                            $daysAgo = $pDate ? (int)\Carbon\Carbon::now()->diffInDays($pDate) : 0;
                            $cust = $cItem->sale?->customer;
                        @endphp
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3">
                                <div class="font-bold text-slate-900 text-sm">{{ $cust?->name ?? 'Walk-in' }}</div>
                                <div class="text-[11px] text-sky-700 font-mono">{{ $cust?->phone ?? 'N/A' }}</div>
                            </td>
                            <td class="p-3">
                                <div class="font-bold text-slate-800">{{ $cItem->product?->name }}</div>
                            </td>
                            <td class="p-3 text-center text-slate-600 font-mono">
                                {{ $pDate ? $pDate->format('d M, Y') : 'N/A' }} ({{ $daysAgo }} দিন আগে)
                            </td>
                            <td class="p-3 text-center font-bold text-slate-800">
                                {{ $cItem->quantity }} {{ $cItem->unit?->name ?? 'pcs' }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                    নিকটবর্তী রিফিল আবশ্যক
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                @if($cust && $cust->phone)
                                    <button 
                                        type="button" 
                                        wire:click="sendRefillReminder({{ $cItem->sale_id }}, '{{ addslashes($cust->name) }}', '{{ $cust->phone }}', '{{ addslashes($cItem->product?->name ?? 'Medicine') }}')"
                                        class="px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs transition cursor-pointer shadow-xs"
                                    >
                                        Send Refill SMS
                                    </button>
                                @else
                                    <span class="text-slate-400 text-[11px]">No Mobile Number</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="p-12 text-center text-slate-400">
                                বর্তমানে কোনো রিফিল রিমাইন্ডার পেন্ডিং নেই।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $refillCandidates->links() }}
            </div>
        </div>
    @endif

    {{-- Tab 2: Due Customers --}}
    @if($activeTab === 'dues')
        <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-xs">
            <div class="p-3 bg-slate-50 border-b border-slate-200">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Search customer name or phone..." 
                    class="w-full max-w-sm text-xs rounded-lg border border-slate-300 py-1.5 px-3 bg-white" 
                />
            </div>
            <table class="w-full text-left text-xs border-collapse">
                <thead class="bg-slate-50 text-slate-700 border-b border-slate-200 font-bold text-[11px] uppercase tracking-wider">
                    <tr>
                        <th class="p-3">কাস্টমার নাম (Customer Name)</th>
                        <th class="p-3">মোবাইল নম্বর (Phone)</th>
                        <th class="p-3">ঠিকানা (Address)</th>
                        <th class="p-3 text-right">বকেয়া পরিমাণ (Due ৳)</th>
                        <th class="p-3 text-center w-36">রিমাইন্ডার (Action)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($dueCustomers as $cust)
                        <tr class="hover:bg-slate-50/80 transition">
                            <td class="p-3 font-bold text-slate-900 text-sm">
                                {{ $cust->name }}
                            </td>
                            <td class="p-3 font-mono font-bold text-sky-700">
                                {{ $cust->phone }}
                            </td>
                            <td class="p-3 text-slate-500 text-[11px]">
                                {{ $cust->address ?? 'Dhaka, Bangladesh' }}
                            </td>
                            <td class="p-3 text-right font-mono font-black text-rose-600 text-sm">
                                ৳{{ number_format($cust->current_due, 2) }}
                            </td>
                            <td class="p-3 text-center">
                                <button 
                                    type="button" 
                                    wire:click="sendDueReminder({{ $cust->id }})" 
                                    class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition cursor-pointer shadow-xs"
                                >
                                    Send Due SMS
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-12 text-center text-slate-400">
                                আলহামদুলিল্লাহ! কোনো কাস্টমারের বকেয়া বাকি নেই।
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="p-3 border-t border-slate-200">
                {{ $dueCustomers->links() }}
            </div>
        </div>
    @endif

    {{-- Tab 3: Custom SMS Composer --}}
    @if($activeTab === 'custom_sms')
        <div class="bg-white p-6 rounded-lg border border-slate-200 shadow-xs max-w-2xl space-y-4">
            <h3 class="font-bold text-sm text-slate-900 border-b border-slate-100 pb-3">বাল্ক ও কাস্টম এসএমএস কম্পোজার (SMS Gateway Composer)</h3>

            <div>
                <label class="block text-xs font-bold text-slate-800 mb-1">প্রাপক নির্বাচন (Select Recipient)</label>
                <select wire:model.live="smsRecipientType" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 bg-white">
                    <option value="all_dues">সকল বকেয়া থাকা কাস্টমার (All Due Customers)</option>
                    <option value="specific_customer">নির্দিষ্ট কাস্টমার (Specific Customer)</option>
                    <option value="custom_number">সরাসরি মোবাইল নম্বর লিখুন (Custom Number)</option>
                </select>
            </div>

            @if($smsRecipientType === 'specific_customer')
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">কাস্টমার বাছাই করুন</label>
                    <select wire:model="selectedCustomerId" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3 bg-white">
                        <option value="">-- কাস্টমার সিলেক্ট করুন --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                </div>
            @endif

            @if($smsRecipientType === 'custom_number')
                <div>
                    <label class="block text-xs font-bold text-slate-800 mb-1">মোবাইল নম্বর (e.g. 017xxxxxxxx)</label>
                    <input type="text" wire:model="customPhone" placeholder="01712345678" class="w-full text-xs font-mono font-bold rounded-lg border border-slate-300 py-2 px-3 bg-white" />
                </div>
            @endif

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-bold text-slate-800">এসএমএস বার্তা (SMS Text Body)</label>
                    <span class="text-[11px] text-slate-400 font-mono">{{ strlen($smsMessage) }}/480 Characters</span>
                </div>
                <textarea 
                    wire:model="smsMessage" 
                    rows="4" 
                    placeholder="প্রিয় গ্রাহক, আপনার প্রেসক্রিপশন ও বকেয়া সংক্রান্ত বার্তা লিখুন..." 
                    class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3"
                ></textarea>
                @error('smsMessage') <span class="text-rose-500 text-[11px] font-semibold mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <div class="text-[11px] text-slate-400">
                    গেgateway: গ্রামীণফোন, রবি, বাংলালিংক ও টেলিটকে সাপোর্ট করবে।
                </div>
                <button 
                    type="button" 
                    wire:click="sendCustomSms" 
                    class="px-5 py-2.5 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800 text-white rounded-lg text-xs font-bold shadow-md transition cursor-pointer"
                >
                    Send SMS Campaign
                </button>
            </div>
        </div>
    @endif
</div>
