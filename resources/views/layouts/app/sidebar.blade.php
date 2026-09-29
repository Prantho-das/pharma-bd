<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-slate-50 text-slate-800 antialiased font-sans flex">
        {{-- Sidebar --}}
        <aside 
            x-data="{
                searchQuery: '',
                matches(text) {
                    if (!this.searchQuery) return true;
                    return text.toLowerCase().includes(this.searchQuery.toLowerCase().trim());
                },
                hasMatch(items) {
                    if (!this.searchQuery) return true;
                    const q = this.searchQuery.toLowerCase().trim();
                    return items.some(item => item.toLowerCase().includes(q));
                }
            }" 
            class="w-64 bg-white border-r border-slate-200 flex flex-col justify-between shrink-0 min-h-screen text-slate-700 shadow-xs z-30 select-none">
            
            <div class="flex-1 flex flex-col min-h-0">
                {{-- Logo & Brand --}}
                <div class="h-16 px-5 border-b border-sky-700 flex items-center justify-between bg-gradient-to-r from-[#005a9e] via-[#0072bc] to-[#0099e6] text-white shadow-xs shrink-0">
                    <div class="flex items-center space-x-2.5">
                        <div class="w-9 h-9 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center font-black text-sm text-white shadow-inner">
                            Rx
                        </div>
                        <div>
                            <div class="font-black text-sm tracking-tight text-white leading-tight">EXPERT PMS</div>
                            <div class="text-[11px] text-sky-100 font-bold tracking-wider uppercase">Pharmacy Enterprise</div>
                        </div>
                    </div>
                </div>

                {{-- Menu Quick Search Bar --}}
                <div class="p-2.5 border-b border-slate-100 bg-slate-50/70 shrink-0">
                    <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white focus-within:border-sky-500 focus-within:ring-2 focus-within:ring-sky-500/20 transition shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        <input 
                            type="text" 
                            x-model="searchQuery" 
                            placeholder="Search menu & features..." 
                            class="w-full bg-transparent text-xs text-slate-800 placeholder-slate-400 font-normal"
                            style="border: none !important; padding: 0 !important; margin: 0 !important; box-shadow: none !important; outline: none !important; height: auto !important;"
                        />
                        <button 
                            x-show="searchQuery" 
                            @click="searchQuery = ''" 
                            type="button" 
                            class="text-slate-400 hover:text-slate-600 text-xs shrink-0 cursor-pointer font-bold px-1"
                        >
                            ✕
                        </button>
                    </div>
                </div>

                {{-- Navigation Links --}}
                <nav class="flex-1 p-3 space-y-1 text-xs overflow-y-auto max-h-[calc(100vh-11rem)] divide-y divide-slate-100">
                    <div class="space-y-1 pb-2">
                        {{-- 1. Dashboard --}}
                        @can('view_dashboard')
                        <div x-show="matches('Dashboard')">
                            <a href="{{ route('dashboard') }}" wire:navigate.hover class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('dashboard') ? 'bg-sky-600 text-white font-bold shadow-xs' : 'text-slate-700 hover:bg-sky-50 hover:text-sky-800' }}">
                                <svg class="w-4 h-4 shrink-0 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-sky-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                                <span>Dashboard</span>
                            </a>
                        </div>
                        @endcan

                        {{-- 2. Sales --}}
                        @canany(['view_sales', 'create_sales', 'return_sales'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('sales.*', 'pos.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Sales', 'Sale New', 'Sale List', 'Sales Return', 'POS Terminal'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('sales.*', 'pos.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                                    <span>Sales</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('create_sales')
                                    <a x-show="matches('Sales Sale New POS')" href="{{ route('sales.create') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('sales.create') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Sale New (POS)</a>
                                @endcan
                                @can('view_sales')
                                    <a x-show="matches('Sales Sale List Invoices')" href="{{ route('sales.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('sales.index') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Sale List</a>
                                @endcan
                                @can('return_sales')
                                    <a x-show="matches('Sales Return Refund')" href="{{ route('sales.returns') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('sales.returns') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Sales Return</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 3. Purchases --}}
                        @canany(['view_purchases', 'create_purchases', 'return_purchases'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('purchases.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Purchases', 'Purchase New', 'Purchase List', 'Purchase Return', 'Supplier Bill'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('purchases.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span>Purchases</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('create_purchases')
                                    <a x-show="matches('Purchase New Add')" href="{{ route('purchases.create') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('purchases.create') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Purchase New</a>
                                @endcan
                                @can('view_purchases')
                                    <a x-show="matches('Purchase List Invoices')" href="{{ route('purchases.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('purchases.index') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Purchase List</a>
                                @endcan
                                @can('return_purchases')
                                    <a x-show="matches('Purchase Return')" href="{{ route('purchases.returns') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('purchases.returns') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Purchase Return</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 4. Products --}}
                        @canany(['view_products', 'create_products', 'view_expired_products', 'print_barcodes', 'bulk_upload_products', 'manage_categories', 'manage_units', 'manage_medicine_types', 'manage_manufacturers', 'manage_box_sizes'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('products.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Products', 'Medicines', 'All Product', 'Add Product', 'Expired Products', 'Print Labels', 'Barcodes', 'Bulk Upload', 'Category', 'Unit', 'Medicine Types', 'Manufacturers', 'Box Sizes'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('products.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                                    <span>Products</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('view_products')
                                    <a x-show="matches('Products All Product Medicines')" href="{{ route('products.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('products.index') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">All Product</a>
                                @endcan
                                @can('create_products')
                                    <a x-show="matches('Products Add Product Register')" href="{{ route('products.create') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('products.create') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Add Product</a>
                                @endcan
                                @can('view_expired_products')
                                    <a x-show="matches('Products Expired Batches')" href="{{ route('products.expired') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('products.expired') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Expired Products</a>
                                @endcan
                                @can('print_barcodes')
                                    <a x-show="matches('Products Barcodes Print Labels')" href="{{ route('products.barcodes') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('products.barcodes') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Print Labels</a>
                                @endcan
                                @can('bulk_upload_products')
                                    <a x-show="matches('Products Bulk Upload CSV Import')" href="{{ route('products.bulk-upload') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('products.bulk-upload') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Bulk Upload</a>
                                @endcan
                                @can('manage_categories')
                                    <a x-show="matches('Products Category Categories')" href="{{ route('products.categories') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('products.categories') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Category</a>
                                @endcan
                                @can('manage_units')
                                    <a x-show="matches('Products Unit Units Strip Box Piece')" href="{{ route('products.units') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('products.units') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Unit</a>
                                @endcan
                                @can('manage_medicine_types')
                                    <a x-show="matches('Products Medicine Types')" href="{{ route('products.medicine-types') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('products.medicine-types') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Medicine Types</a>
                                @endcan
                                @can('manage_manufacturers')
                                    <a x-show="matches('Products Manufacturers Pharma')" href="{{ route('products.manufacturers') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('products.manufacturers') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Manufacturers</a>
                                @endcan
                                @can('manage_box_sizes')
                                    <a x-show="matches('Products Box Sizes')" href="{{ route('products.box-sizes') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('products.box-sizes') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Box Sizes</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 5. Stock List --}}
                        @canany(['view_stocks', 'manage_reorder', 'manage_expiry_damage', 'view_batches', 'manage_stock_adjustments', 'manage_stock_transfers'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('stocks.*', 'inventory.*', 'batches.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Stocks', 'All Stock', 'Low Stock', 'Batches', 'Adjustments', 'Transfers', 'Reorder', 'Shortage', 'Expiry', 'Damage'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('stocks.*', 'inventory.*', 'batches.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                    <span>Stock List</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('view_stocks')
                                    <a x-show="matches('Stocks All Stock Inventory')" href="{{ route('stocks.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('stocks.index') && !request()->has('alert_qty') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">All Stock</a>
                                @endcan
                                @can('manage_reorder')
                                    <a x-show="matches('Stocks Reorder Shortage Low Stock')" href="{{ route('inventory.reorder-shortage') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('inventory.reorder-shortage') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">⚡ Reorder & Shortage</a>
                                @endcan
                                @can('manage_expiry_damage')
                                    <a x-show="matches('Stocks Near Expiry Damage Disposal')" href="{{ route('inventory.expiry-damage') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('inventory.expiry-damage') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">⏳ Expiry & Damage Claim</a>
                                @endcan
                                @can('view_batches')
                                    <a x-show="matches('Stocks Batches Expiry Table')" href="{{ route('batches.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('batches.index') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Batch Tracking (FEFO)</a>
                                @endcan
                                @can('manage_stock_adjustments')
                                    <a x-show="matches('Stocks Adjustments Damage Waste')" href="{{ route('inventory.stock-adjustments') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('inventory.stock-adjustments') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Stock Adjustment</a>
                                @endcan
                                @can('manage_stock_transfers')
                                    <a x-show="matches('Stocks Transfers Branch Store')" href="{{ route('inventory.transfers') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('inventory.transfers') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Stock Transfer</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 6. Customers --}}
                        @canany(['view_customers', 'manage_customers', 'sms_refill_alerts'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('customers.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Customers', 'All Customers', 'Add Customer', 'Bulk Upload', 'Patient Ledger', 'SMS', 'Refill'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('customers.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span>Customers</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('view_customers')
                                    <a x-show="matches('Customers All Customers')" href="{{ route('customers.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('customers.index') && !request()->has('action') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">All Customers</a>
                                @endcan
                                @can('sms_refill_alerts')
                                    <a x-show="matches('Customers SMS Refill Chronic Patient')" href="{{ route('customers.sms-refill') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('customers.sms-refill') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">📱 SMS & Refill Alerts</a>
                                @endcan
                                @can('manage_customers')
                                    <a x-show="matches('Customers Add Customer Register')" href="{{ route('customers.index') }}?action=create" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->has('action') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Add Customer</a>
                                    <a x-show="matches('Customers Bulk Upload Import')" href="{{ route('customers.index') }}?type=Customer&bulk=1" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->has('bulk') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Bulk Upload</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 7. Suppliers --}}
                        @canany(['view_suppliers', 'manage_suppliers'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('suppliers.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Suppliers', 'All Suppliers', 'Add Supplier', 'Bulk Upload', 'Company Ledger'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('suppliers.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    <span>Suppliers</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('view_suppliers')
                                    <a x-show="matches('Suppliers All Suppliers')" href="{{ route('suppliers.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('suppliers.index') && !request()->has('action') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">All Suppliers</a>
                                @endcan
                                @can('manage_suppliers')
                                    <a x-show="matches('Suppliers Add Supplier Register')" href="{{ route('suppliers.index') }}?action=create" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->has('action') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Add Supplier</a>
                                    <a x-show="matches('Suppliers Bulk Upload Import')" href="{{ route('suppliers.index') }}?type=Supplier&bulk=1" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->has('bulk') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Bulk Upload</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 8. Incomes --}}
                        @canany(['view_incomes', 'manage_incomes', 'manage_income_categories'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('incomes.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Incomes', 'Income', 'Income Category', 'Revenue', 'Earning'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('incomes.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span>Incomes</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('view_incomes')
                                    <a x-show="matches('Incomes Income Entry')" href="{{ route('incomes.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('incomes.index') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Income List</a>
                                @endcan
                                @can('manage_income_categories')
                                    <a x-show="matches('Incomes Income Category')" href="{{ route('incomes.categories') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('incomes.categories') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Income Category</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 9. Expenses --}}
                        @canany(['view_expenses', 'manage_expenses', 'manage_expense_categories'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('expenses.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Expenses', 'Expense', 'Expense Category', 'Cost', 'Bills', 'Petty Cash'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('expenses.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span>Expenses</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('view_expenses')
                                    <a x-show="matches('Expenses Expense Entry List')" href="{{ route('expenses.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('expenses.index') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Expense List</a>
                                @endcan
                                @can('manage_expense_categories')
                                    <a x-show="matches('Expenses Expense Category Heads')" href="{{ route('expenses.categories') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('expenses.categories') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Expense Category</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 10. Tax --}}
                        @can('manage_taxes')
                        <div x-show="matches('Tax VAT Rates Taxes')">
                            <a href="{{ route('taxes.index') }}" wire:navigate.hover class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('taxes.*') ? 'bg-sky-50 text-sky-800 font-bold border-l-4 border-sky-600' : 'text-slate-700 hover:bg-sky-50 hover:text-sky-800' }}">
                                <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"></path></svg>
                                <span>Tax & VAT</span>
                            </a>
                        </div>
                        @endcan

                        {{-- 11. Due List & Cash Register Shifts --}}
                        @canany(['view_dues', 'manage_cash_registers'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('dues.*', 'accounts.cash-registers') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Due List', 'Total Due', 'Cash Due', 'Walk-in Due', 'Customer Due', 'Cash Register', 'Shift', 'Counter'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('dues.*', 'accounts.cash-registers') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    <span>Accounts & Dues</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('view_dues')
                                    <a x-show="matches('Due Total Due Customer')" href="{{ route('dues.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('dues.index') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Total Due</a>
                                    <a x-show="matches('Due Cash Due Walk-in Customer')" href="{{ route('dues.walk') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('dues.walk') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Cash Due</a>
                                @endcan
                                @can('manage_cash_registers')
                                    <a x-show="matches('Shift Cash Register Counter Z-Report')" href="{{ route('accounts.cash-registers') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('accounts.cash-registers') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">💼 Shift & Cash Register</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 13. Profit & Loss List --}}
                        @can('view_profit_loss')
                        <div x-show="matches('Profit Loss LossProfit Revenue Accounts')">
                            <a href="{{ route('loss-profits.index') }}" wire:navigate.hover class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-semibold transition {{ request()->routeIs('loss-profits.*') ? 'bg-sky-50 text-sky-800 font-bold border-l-4 border-sky-600' : 'text-slate-700 hover:bg-sky-50 hover:text-sky-800' }}">
                                <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                                <span>Profit & Loss</span>
                            </a>
                        </div>
                        @endcan

                        {{-- 14. HRM --}}
                        @canany(['view_hrm_departments', 'view_hrm_designations', 'view_hrm_shifts', 'view_hrm_employees', 'view_hrm_leaves', 'view_hrm_holidays', 'view_hrm_attendances', 'view_hrm_payrolls'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('hrm.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['HRM', 'Department', 'Designation', 'Shift', 'Employee', 'Leave Request', 'Holiday', 'Attendance', 'Payroll', 'Salary'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('hrm.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>HRM</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('view_hrm_departments')
                                    <a x-show="matches('HRM Department')" href="{{ route('hrm.departments') }}?tab=departments" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('hrm.departments') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Department</a>
                                @endcan
                                @can('view_hrm_designations')
                                    <a x-show="matches('HRM Designation Post')" href="{{ route('hrm.designations') }}?tab=designations" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('hrm.designations') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Designation</a>
                                @endcan
                                @can('view_hrm_shifts')
                                    <a x-show="matches('HRM Shift Roster')" href="{{ route('hrm.shifts') }}?tab=shifts" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('hrm.shifts') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Shift</a>
                                @endcan
                                @can('view_hrm_employees')
                                    <a x-show="matches('HRM Employee Staff User')" href="{{ route('hrm.employees') }}?tab=employees" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('hrm.employees') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Employee</a>
                                @endcan
                                @can('view_hrm_leaves')
                                    <a x-show="matches('HRM Leave Request Vacation')" href="{{ route('hrm.leaves') }}?tab=leaves" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('hrm.leaves') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Leave Request</a>
                                @endcan
                                @can('view_hrm_holidays')
                                    <a x-show="matches('HRM Holiday Calendar')" href="{{ route('hrm.holidays') }}?tab=holidays" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('hrm.holidays') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Holiday</a>
                                @endcan
                                @can('view_hrm_attendances')
                                    <a x-show="matches('HRM Attendance Punch Clock')" href="{{ route('hrm.attendances') }}?tab=attendances" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('hrm.attendances') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Attendance</a>
                                @endcan
                                @can('view_hrm_payrolls')
                                    <a x-show="matches('HRM Payroll Salary Payslip')" href="{{ route('hrm.payrolls') }}?tab=payrolls" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('hrm.payrolls') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Payroll</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 15. Reports --}}
                        @canany(['view_sales_reports', 'view_purchase_reports', 'view_income_reports', 'view_expense_reports', 'view_stock_reports', 'view_customer_ledger', 'view_supplier_ledger', 'view_due_reports', 'view_loss_profit_reports', 'view_transaction_reports', 'view_expired_reports'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('reports.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Reports', 'Sale Report', 'Sale Return', 'Purchase Report', 'Purchase Return', 'Income Report', 'Expense Report', 'Current Stock', 'Customer Ledger', 'Supplier Ledger', 'Customer Due', 'Supplier Due', 'Loss Profit', 'Transaction', 'Expired Product'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('reports.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span>Reports</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('view_sales_reports')
                                    <a x-show="matches('Reports Sale Sales Invoices')" href="{{ url('/reports/sales') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/sales') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Sale Report</a>
                                    <a x-show="matches('Reports Sale Return Refund')" href="{{ url('/reports/sale-returns') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/sale-returns') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Sale Return</a>
                                @endcan
                                @can('view_purchase_reports')
                                    <a x-show="matches('Reports Purchase Invoices')" href="{{ url('/reports/purchases') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/purchases') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Purchase Report</a>
                                    <a x-show="matches('Reports Purchase Return')" href="{{ url('/reports/purchase-returns') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/purchase-returns') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Purchase Return</a>
                                @endcan
                                @can('view_income_reports')
                                    <a x-show="matches('Reports Income All Income')" href="{{ url('/reports/incomes') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/incomes') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">All Income</a>
                                @endcan
                                @can('view_expense_reports')
                                    <a x-show="matches('Reports Expense All Expense')" href="{{ url('/reports/expenses') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/expenses') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">All Expense</a>
                                @endcan
                                @can('view_stock_reports')
                                    <a x-show="matches('Reports Stock Current Inventory')" href="{{ url('/reports/stocks') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/stocks') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Current Stock</a>
                                @endcan
                                @can('view_customer_ledger')
                                    <a x-show="matches('Reports Customer Ledger Statement')" href="{{ url('/reports/customer-ledger') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/customer-ledger') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Customer Ledger</a>
                                @endcan
                                @can('view_supplier_ledger')
                                    <a x-show="matches('Reports Supplier Ledger Statement')" href="{{ url('/reports/supplier-ledger') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/supplier-ledger') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Supplier Ledger</a>
                                @endcan
                                @can('view_due_reports')
                                    <a x-show="matches('Reports Customer Due')" href="{{ url('/reports/customer-due') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/customer-due') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Customer Due</a>
                                    <a x-show="matches('Reports Supplier Due')" href="{{ url('/reports/supplier-due') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/supplier-due') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Supplier Due</a>
                                @endcan
                                @can('view_loss_profit_reports')
                                    <a x-show="matches('Reports Loss Profit Net Revenue')" href="{{ url('/reports/loss-profit') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/loss-profit') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Loss & Profit</a>
                                @endcan
                                @can('view_transaction_reports')
                                    <a x-show="matches('Reports Transaction Cash Flow')" href="{{ url('/reports/transactions') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/transactions') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Transaction</a>
                                @endcan
                                @can('view_expired_reports')
                                    <a x-show="matches('Reports Expired Product Medicine')" href="{{ url('/reports/expired') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->is('reports/expired') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">Expired Product</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany

                        {{-- 16. Settings & Security --}}
                        @canany(['manage_settings', 'manage_roles_permissions'])
                        <div 
                            x-data="{ open: {{ request()->routeIs('settings.*', 'roles.*') ? 'true' : 'false' }} }" 
                            x-show="hasMatch(['Settings', 'Configuration', 'Pharmacy Setup', 'Roles', 'Permissions', 'Security'])"
                            x-effect="if (searchQuery) open = true;"
                            class="space-y-0.5">
                            <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm font-semibold transition hover:bg-sky-50 hover:text-sky-800 {{ request()->routeIs('settings.*', 'roles.*') ? 'text-sky-800 bg-sky-50 font-bold' : 'text-slate-700' }}">
                                <div class="flex items-center gap-3">
                                    <svg class="w-4 h-4 shrink-0 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>Settings & Access</span>
                                </div>
                                <svg class="w-3.5 h-3.5 text-slate-400 transform transition-transform duration-200" :class="open ? 'rotate-90 text-sky-600' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </button>
                            <div x-show="open" x-collapse class="ml-4 pl-3 border-l-2 border-slate-200 py-1 space-y-0.5">
                                @can('manage_settings')
                                    <a x-show="matches('Settings Configuration Pharmacy Setup')" href="{{ route('settings.manage') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('settings.manage') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">General Settings</a>
                                @endcan
                                @can('manage_roles_permissions')
                                    <a x-show="matches('Roles Permissions Matrix RBAC Access Security')" href="{{ route('roles.index') }}" wire:navigate.hover class="block px-2.5 py-1.5 rounded-md text-xs font-medium text-slate-600 hover:text-sky-700 hover:bg-slate-100 transition {{ request()->routeIs('roles.index') ? 'text-sky-700 font-bold bg-sky-50' : '' }}">🛡️ Roles & Permissions</a>
                                @endcan
                            </div>
                        </div>
                        @endcanany
                    </div>
                </nav>
            </div>

            {{-- User Profile Footer --}}
            <div class="p-3 border-t border-slate-200 bg-slate-50 flex items-center justify-between shrink-0">
                <div class="flex items-center space-x-2.5">
                    <div class="w-8 h-8 rounded-full bg-sky-700 text-white font-bold flex items-center justify-center text-xs shadow-xs">
                        {{ auth()->user()?->initials() ?? 'EP' }}
                    </div>
                    <div class="text-xs">
                        <div class="font-bold text-slate-800 line-clamp-1">{{ auth()->user()?->name ?? 'Admin User' }}</div>
                        <div class="text-[11px] text-sky-700 font-semibold">{{ auth()->user()?->roles->first()?->name ?? auth()->user()?->role ?? 'Super Admin' }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Logout">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                    </button>
                </form>
            </div>
        </aside>

        {{-- Main View Container --}}
        <main class="flex-1 min-h-screen overflow-y-auto bg-slate-50 flex flex-col">
            <livewire:navbar.top-navbar />
            @include('partials.module-tabs')
            <div class="flex-1">
                {{ $slot }}
            </div>
        </main>

        @livewireScripts
    </body>
</html>
