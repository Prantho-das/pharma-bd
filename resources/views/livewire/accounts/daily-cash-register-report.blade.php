<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Counter Shifts & Z-Report Reconciliation</h1>
            <p class="text-xs text-slate-500 mt-1">Manage cashier shifts, track physical vs expected cash drawer float, and print daily closing Z-Reports.</p>
        </div>
        <div>
            @if($activeShift)
                <button 
                    wire:click="$set('showCloseShiftModal', true)" 
                    type="button" 
                    class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                    <span>Close Shift & Z-Report</span>
                </button>
            @else
                <button 
                    wire:click="$set('showOpenShiftModal', true)" 
                    type="button" 
                    class="px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-600 hover:to-blue-700 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path></svg>
                    <span>Open New Shift Float</span>
                </button>
            @endif
        </div>
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

    {{-- Active Shift Card --}}
    @if($activeShift)
        <div class="bg-gradient-to-br from-sky-600 to-blue-700 text-white rounded-lg p-6 shadow-lg space-y-4">
            <div class="flex items-center justify-between border-b border-white/15 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-3 h-3 bg-emerald-400 rounded-full animate-pulse"></span>
                    <h3 class="font-bold text-sm">Active Counter Shift in Progress</h3>
                </div>
                <div class="text-xs text-sky-200">
                    Opened at: <strong class="text-white">{{ $activeShift->opened_at->format('d M, Y h:i A') }}</strong>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                <div class="bg-white/10 p-3 rounded-lg border border-white/10">
                    <div class="text-[11px] text-sky-200 font-medium">Opening Float</div>
                    <div class="text-xl font-bold font-mono mt-1">৳{{ number_format($activeShiftTotals['opening_cash'] ?? 0, 2) }}</div>
                </div>

                <div class="bg-white/10 p-3 rounded-lg border border-white/10">
                    <div class="text-[11px] text-sky-200 font-medium">Cash Sales</div>
                    <div class="text-xl font-bold font-mono mt-1">৳{{ number_format($activeShiftTotals['cash_sales'] ?? 0, 2) }}</div>
                </div>

                <div class="bg-white/10 p-3 rounded-lg border border-white/10">
                    <div class="text-[11px] text-sky-200 font-medium">Digital MFS (bKash/Nagad)</div>
                    <div class="text-xl font-bold font-mono mt-1">৳{{ number_format($activeShiftTotals['mfs_sales'] ?? 0, 2) }}</div>
                </div>

                <div class="bg-white/10 p-3 rounded-lg border border-white/10">
                    <div class="text-[11px] text-sky-200 font-medium">Card POS Sales</div>
                    <div class="text-xl font-bold font-mono mt-1">৳{{ number_format($activeShiftTotals['card_sales'] ?? 0, 2) }}</div>
                </div>

                <div class="bg-emerald-500/30 p-3 rounded-lg border border-emerald-400/40">
                    <div class="text-[11px] text-emerald-200 font-bold">Expected Drawer Cash</div>
                    <div class="text-xl font-black font-mono mt-1">৳{{ number_format($activeShiftTotals['expected_closing_cash'] ?? 0, 2) }}</div>
                </div>
            </div>
        </div>
    @else
        <div class="bg-slate-50 border border-slate-200 p-8 rounded-lg text-center space-y-3">
            <div class="w-12 h-12 bg-slate-200 text-slate-500 rounded-full flex items-center justify-center mx-auto">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
            </div>
            <h3 class="font-bold text-slate-800 text-sm">No Active Register Shift</h3>
            <p class="text-xs text-slate-500 max-w-sm mx-auto">Please enter your opening drawer cash float to start counter sales and shift tracking.</p>
            <button 
                wire:click="$set('showOpenShiftModal', true)" 
                type="button" 
                class="px-5 py-2 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold shadow-md transition"
            >
                Open Register Shift Now
            </button>
        </div>
    @endif

    {{-- Shift History Table --}}
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 bg-slate-50 border-b border-slate-200">
            <h3 class="text-xs font-black uppercase tracking-wider text-slate-600">Register Shift Logs & Z-Reports</h3>
        </div>
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                <tr>
                    <th class="py-3 px-4">Opened At</th>
                    <th class="py-3 px-3">Closed At</th>
                    <th class="py-3 px-3">Cashier</th>
                    <th class="py-3 px-3 text-right">Opening Float</th>
                    <th class="py-3 px-3 text-right">Cash Sales</th>
                    <th class="py-3 px-3 text-right">Physical Cash</th>
                    <th class="py-3 px-3 text-right">Difference</th>
                    <th class="py-3 px-3 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($pastShifts as $shift)
                    <tr class="hover:bg-slate-50/80">
                        <td class="py-3 px-4 text-slate-700 font-mono">{{ $shift->opened_at->format('d/m/Y h:i A') }}</td>
                        <td class="py-3 px-3 text-slate-500 font-mono">{{ $shift->closed_at ? $shift->closed_at->format('d/m/Y h:i A') : 'Current Shift' }}</td>
                        <td class="py-3 px-3 font-bold text-slate-800">{{ $shift->user?->name }}</td>
                        <td class="py-3 px-3 text-right font-mono text-slate-800">৳{{ number_format($shift->opening_cash, 2) }}</td>
                        <td class="py-3 px-3 text-right font-mono text-emerald-700 font-bold">৳{{ number_format($shift->total_cash_sales, 2) }}</td>
                        <td class="py-3 px-3 text-right font-mono text-slate-900 font-bold">{{ $shift->closing_cash !== null ? '৳' . number_format($shift->closing_cash, 2) : '-' }}</td>
                        <td class="py-3 px-3 text-right font-mono font-bold {{ $shift->difference < 0 ? 'text-rose-600' : ($shift->difference > 0 ? 'text-emerald-600' : 'text-slate-500') }}">
                            {{ $shift->difference != 0 ? ($shift->difference > 0 ? '+' : '') . '৳' . number_format($shift->difference, 2) : '৳0.00' }}
                        </td>
                        <td class="py-3 px-3 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold {{ $shift->status === 'OPEN' ? 'bg-emerald-100 text-emerald-800 animate-pulse' : 'bg-slate-100 text-slate-600' }}">
                                {{ $shift->status }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-400">No shift records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $pastShifts->links() }}
        </div>
    </div>

    {{-- Modal: Open Shift --}}
    @if($showOpenShiftModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-sky-800 px-5 py-3.5 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Open Register Shift</h3>
                    <button wire:click="$set('showOpenShiftModal', false)" class="text-white font-bold">&times;</button>
                </div>
                <div class="p-5 space-y-3 text-xs">
                    <div>
                        <label class="font-bold text-slate-700">Starting Cash Float (৳) *</label>
                        <input wire:model="openingCash" type="number" step="0.01" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg font-bold font-mono text-lg text-sky-800">
                    </div>
                    <div>
                        <label class="font-bold text-slate-700">Opening Notes</label>
                        <input wire:model="openNote" type="text" placeholder="Shift 1 Morning Counter" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showOpenShiftModal', false)" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancel</button>
                    <button wire:click="openShift" class="px-5 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Start Shift</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal: Close Shift & Z-Report --}}
    @if($showCloseShiftModal && $activeShift)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-lg rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-rose-700 px-5 py-3.5 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Close Shift & Print Z-Report</h3>
                    <button wire:click="$set('showCloseShiftModal', false)" class="text-white font-bold">&times;</button>
                </div>
                <div class="p-5 space-y-3 text-xs">
                    <div class="bg-slate-50 p-3 rounded-lg border border-slate-200 space-y-1">
                        <div class="flex justify-between"><span>Expected Physical Cash:</span> <strong class="font-mono text-slate-900">৳{{ number_format($activeShiftTotals['expected_closing_cash'] ?? 0, 2) }}</strong></div>
                        <div class="flex justify-between"><span>Digital (MFS & Card):</span> <strong class="font-mono text-slate-900">৳{{ number_format(($activeShiftTotals['mfs_sales'] ?? 0) + ($activeShiftTotals['card_sales'] ?? 0), 2) }}</strong></div>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700">Actual Physical Cash Count in Drawer (৳) *</label>
                        <input wire:model="physicalClosingCash" type="number" step="0.01" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg font-bold font-mono text-lg text-slate-900">
                    </div>

                    <div>
                        <label class="font-bold text-slate-700">Closing Notes / Discrepancy Reason</label>
                        <textarea wire:model="closeNote" rows="2" class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg"></textarea>
                    </div>
                </div>
                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showCloseShiftModal', false)" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancel</button>
                    <button wire:click="closeShift" class="px-5 py-1.5 bg-rose-700 hover:bg-rose-800 text-white rounded-lg text-xs font-bold">Lock Register & Close</button>
                </div>
            </div>
        </div>
    @endif
</div>
