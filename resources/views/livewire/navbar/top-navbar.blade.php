<div class="relative h-16 px-4 sm:px-6 bg-white border-b border-slate-200 flex items-center justify-between shadow-2xs shrink-0 z-40">
    {{-- Left: Sidebar Toggle & Live Pharmacy Status & POS Shortcut --}}
    <div class="flex items-center space-x-3 sm:space-x-4">
        {{-- Sidebar Toggle Button --}}
        <button 
            @click="$dispatch('toggle-sidebar')"
            type="button" 
            class="p-2 rounded-xl bg-slate-50 hover:bg-sky-50 text-slate-600 hover:text-sky-700 border border-slate-200 hover:border-sky-300 transition cursor-pointer shadow-2xs group flex items-center justify-center"
            title="Toggle Sidebar (Expand / Collapse)"
        >
            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
        </button>

        <div class="flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full {{ $activeShift ? 'bg-emerald-500 animate-pulse' : 'bg-amber-400' }}"></span>
            <span class="text-xs font-bold text-slate-800 hidden sm:inline">
                {{ auth()->user()->branch?->name ?? 'Main Pharmacy' }}
            </span>
            <span class="text-[11px] px-2 py-0.5 rounded-full font-semibold {{ $activeShift ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                {{ $activeShift ? 'Shift Active' : 'Shift Closed' }}
            </span>
        </div>

        <a 
            href="{{ route('pos.terminal') }}" 
            wire:navigate.hover 
            class="hidden md:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-bold shadow-xs transition cursor-pointer"
        >
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
            <span>POS Terminal (<kbd class="text-[10px] bg-white/20 px-1 rounded">Alt+P</kbd>)</span>
        </a>
    </div>

    {{-- Right: Language Switcher, Notification Dropdown & User Profile Dropdown --}}
    <div class="flex items-center space-x-3" x-data="{ showNotifications: false, showUserMenu: false }">
        
        {{-- Language Toggle Switcher --}}
        <div class="flex items-center bg-slate-100 p-0.5 rounded-xl border border-slate-200 shadow-2xs">
            <a 
                href="{{ route('language.switch', 'en') }}" 
                class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 {{ app()->getLocale() == 'en' ? 'bg-white text-sky-700 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}"
                title="Switch to English"
            >
                <span class="text-xs">🇬🇧</span>
                <span>EN</span>
            </a>
            <a 
                href="{{ route('language.switch', 'bn') }}" 
                class="px-2.5 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1 {{ app()->getLocale() == 'bn' ? 'bg-white text-emerald-700 shadow-xs' : 'text-slate-500 hover:text-slate-800' }}"
                title="বাংলায় পরিবর্তন করুন"
            >
                <span class="text-xs">🇧🇩</span>
                <span>বাংলা</span>
            </a>
        </div>
        
        {{-- 1. Notification Dropdown --}}
        <div class="relative">
            <button 
                @click="showNotifications = !showNotifications; showUserMenu = false" 
                type="button" 
                class="relative p-2 rounded-xl bg-slate-50 hover:bg-slate-100 border border-slate-200 text-slate-600 hover:text-sky-700 transition cursor-pointer shadow-2xs"
                title="System Notifications"
            >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
                </svg>

                @if($totalAlerts > 0)
                    <span class="absolute top-1 right-1 flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-rose-500 ring-2 ring-white"></span>
                    </span>
                @endif
            </button>

            {{-- Notification Dropdown Panel --}}
            <div 
                x-show="showNotifications" 
                @click.away="showNotifications = false" 
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50 text-xs divide-y divide-slate-100 ring-1 ring-black/5"
                style="display: none;"
            >
                <div class="p-3.5 bg-slate-100 flex items-center justify-between border-b border-slate-200">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                        <span class="font-bold text-slate-800 text-xs">{{ __('Notifications & System Alerts') }}</span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold whitespace-nowrap {{ $totalAlerts > 0 ? 'bg-rose-100 text-rose-800 border border-rose-200' : 'bg-emerald-100 text-emerald-800 border border-emerald-200' }}">
                        {{ $totalAlerts }} {{ __('Alert(s)') }}
                    </span>
                </div>

                <div class="max-h-80 overflow-y-auto divide-y divide-slate-100">
                    {{-- Low Stock Alert --}}
                    @if($lowStockCount > 0)
                        <a href="{{ route('inventory.reorder-shortage') }}" wire:navigate class="p-3.5 hover:bg-slate-50 flex items-start gap-3 transition block group">
                            <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold text-sm group-hover:scale-105 transition">⚠️</span>
                            <div>
                                <div class="font-bold text-slate-900 group-hover:text-sky-700 transition">{{ $lowStockCount }} {{ __('medicines are in low stock') }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Below minimum threshold. Click to create purchase demand.') }}</div>
                            </div>
                        </a>
                    @endif

                    {{-- Near Expiry Alert --}}
                    @if($nearExpiryCount > 0)
                        <a href="{{ route('inventory.expiry-damage') }}" wire:navigate class="p-3.5 hover:bg-slate-50 flex items-start gap-3 transition block group">
                            <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center shrink-0 font-bold text-sm group-hover:scale-105 transition">⏳</span>
                            <div>
                                <div class="font-bold text-slate-900 group-hover:text-sky-700 transition">{{ $nearExpiryCount }} {{ __('batches expire within 30 days') }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Send supplier return claims to adjust credit balance.') }}</div>
                            </div>
                        </a>
                    @endif

                    {{-- Expired Alert --}}
                    @if($expiredCount > 0)
                        <a href="{{ route('inventory.expiry-damage') }}" wire:navigate class="p-3.5 hover:bg-rose-50/50 flex items-start gap-3 transition block group">
                            <span class="w-8 h-8 rounded-lg bg-rose-100 text-rose-800 flex items-center justify-center shrink-0 font-bold text-sm group-hover:scale-105 transition">🚫</span>
                            <div>
                                <div class="font-bold text-rose-700">{{ $expiredCount }} {{ __('expired batches locked in stock!') }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Sales blocked. Immediate disposal or damage claim required.') }}</div>
                            </div>
                        </a>
                    @endif

                    {{-- Shift Status Alert --}}
                    @if(!$activeShift)
                        <a href="{{ route('accounts.cash-registers') }}" wire:navigate class="p-3.5 hover:bg-slate-50 flex items-start gap-3 transition block group">
                            <span class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0 font-bold text-sm group-hover:scale-105 transition">💼</span>
                            <div>
                                <div class="font-bold text-slate-900 group-hover:text-sky-700 transition">{{ __('Cash register shift is closed') }}</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">{{ __('Open register shift and enter opening float balance.') }}</div>
                            </div>
                        </a>
                    @endif

                    @if($totalAlerts === 0)
                        <div class="p-8 text-center text-slate-400">
                            <div class="text-2xl mb-1.5">🎉</div>
                            <div class="font-bold text-slate-700">{{ __('Everything is running smoothly!') }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ __('No active alerts or urgent system notifications.') }}</div>
                        </div>
                    @endif
                </div>

                <div class="p-2.5 bg-slate-50/80 text-center border-t border-slate-100">
                    <a href="{{ route('dashboard') }}" wire:navigate class="text-[11px] font-bold text-sky-700 hover:text-sky-900 flex items-center justify-center gap-1">
                        <span>{{ __('View Complete Dashboard Analytics') }}</span>
                        <span>&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- 2. User Profile Dropdown --}}
        <div class="relative">
            <button 
                @click="showUserMenu = !showUserMenu; showNotifications = false" 
                type="button" 
                class="flex items-center space-x-2 p-1.5 rounded-xl hover:bg-slate-50 border border-slate-200 transition cursor-pointer shadow-2xs focus:outline-none focus:ring-2 focus:ring-sky-500/20"
            >
                <div class="w-8 h-8 rounded-lg text-white font-bold flex items-center justify-center text-xs shadow-xs" style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);">
                    {{ auth()->user()?->initials() ?? 'JP' }}
                </div>
                <div class="hidden sm:block text-left pr-1">
                    <div class="text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()?->name ?? 'Admin User' }}</div>
                    <div class="text-[10px] text-sky-700 font-semibold leading-tight">{{ auth()->user()?->role ?? 'Pharmacist' }}</div>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
            </button>

            {{-- User Menu Dropdown --}}
            <div 
                x-show="showUserMenu" 
                @click.away="showUserMenu = false" 
                x-transition:enter="transition ease-out duration-150"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="transition ease-in duration-100"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="absolute right-0 top-full mt-2 w-72 sm:w-80 bg-white rounded-2xl shadow-2xl border border-slate-200 overflow-hidden z-50 text-xs divide-y divide-slate-100 ring-1 ring-black/5"
                style="display: none;"
            >
                <div class="p-3.5 bg-slate-100 border-b border-slate-200">
                    <div class="font-bold text-slate-900 text-sm">{{ auth()->user()?->name }}</div>
                    <div class="text-[11px] text-slate-500 font-mono mt-0.5">{{ auth()->user()?->email }}</div>
                </div>

                <div class="p-2 space-y-1">
                    <button 
                        type="button" 
                        wire:click="openProfileModal" 
                        @click="showUserMenu = false"
                        class="w-full text-left px-3 py-2.5 rounded-lg hover:bg-sky-50 hover:text-sky-800 font-semibold transition flex items-center gap-2.5 cursor-pointer text-slate-700"
                    >
                        <div class="w-7 h-7 rounded-lg bg-sky-100 text-sky-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-800">{{ __('Profile & Password') }}</div>
                            <div class="text-[10px] text-slate-400 font-normal">{{ __('Change user name & security credentials') }}</div>
                        </div>
                    </button>

                    <a 
                        href="{{ route('settings.manage') }}" 
                        wire:navigate 
                        @click="showUserMenu = false"
                        class="w-full text-left px-3 py-2.5 rounded-lg hover:bg-sky-50 hover:text-sky-800 font-semibold transition flex items-center gap-2.5 cursor-pointer text-slate-700 block"
                    >
                        <div class="w-7 h-7 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path></svg>
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-800">{{ __('Software Settings') }}</div>
                            <div class="text-[10px] text-slate-400 font-normal">{{ __('Pharmacy, invoice & tax configuration') }}</div>
                        </div>
                    </a>
                </div>

                <div class="p-2">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full text-left px-3 py-2 rounded-lg hover:bg-rose-50 text-rose-600 font-bold transition flex items-center gap-2.5 cursor-pointer">
                            <div class="w-7 h-7 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </div>
                            <span class="text-xs">{{ __('Logout') }}</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- User Profile & Password Change Modal --}}
    @if($showProfileModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-gradient-to-r from-sky-600 to-blue-700 px-6 py-4 text-white flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-base">{{ __('Profile & Password') }}</h3>
                        <p class="text-xs text-sky-100">{{ __('Update user details, email and security password') }}</p>
                    </div>
                    <button wire:click="$set('showProfileModal', false)" class="text-white hover:text-sky-200 font-bold text-xl cursor-pointer">&times;</button>
                </div>

                <form wire:submit="updateProfile" class="p-6 space-y-4">
                    @if($profileSuccess)
                        <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-lg">
                            {{ $profileSuccess }}
                        </div>
                    @endif
                    @if($profileError)
                        <div class="p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-bold rounded-lg">
                            {{ $profileError }}
                        </div>
                    @endif

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">{{ __('User Name') }} <span class="text-rose-500">*</span></label>
                        <input type="text" wire:model="name" class="w-full text-xs font-bold rounded-lg border border-slate-300 py-2 px-3" />
                        @error('name') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-800 mb-1">{{ __('Email Address') }} <span class="text-rose-500">*</span></label>
                        <input type="email" wire:model="email" class="w-full text-xs font-mono rounded-lg border border-slate-300 py-2 px-3" />
                        @error('email') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="pt-3 border-t border-slate-100 space-y-3">
                        <div class="text-xs font-bold text-slate-700">{{ __('Password Change (Optional)') }}</div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Current Password') }}</label>
                            <input type="password" wire:model="currentPassword" placeholder="••••••••" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3" />
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('New Password') }}</label>
                            <input type="password" wire:model="newPassword" placeholder="{{ __('Minimum 6 characters') }}" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3" />
                            @error('newPassword') <span class="text-rose-500 text-[11px] block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-600 mb-1">{{ __('Confirm Password') }}</label>
                            <input type="password" wire:model="newPassword_confirmation" placeholder="{{ __('Re-type new password') }}" class="w-full text-xs rounded-lg border border-slate-300 py-2 px-3" />
                        </div>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end space-x-2">
                        <button type="button" wire:click="$set('showProfileModal', false)" class="px-4 py-2 bg-slate-200 text-slate-700 font-bold rounded-lg text-xs cursor-pointer">
                            {{ __('Close') }}
                        </button>
                        <button type="submit" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold rounded-lg text-xs shadow-md transition cursor-pointer">
                            {{ __('Save Changes') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
