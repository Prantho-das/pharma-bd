<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-white">
<head>
    @include('partials.head')
</head>
<body class="h-full bg-white text-slate-900 antialiased font-sans">
    <div class="min-h-screen flex">
        
        <!-- Left 50%: Clean Photo Banner -->
        <div class="relative hidden lg:block lg:w-1/2 bg-slate-900">
            <img 
                src="{{ asset('images/pharmacy_login_banner.jpg') }}" 
                alt="Pharmacy" 
                class="absolute inset-0 h-full w-full object-cover"
            />
        </div>

        <!-- Right 50%: Clean Simple Login Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-8 sm:p-12 md:p-16 xl:p-24 bg-white">
            <div class="w-full max-w-sm space-y-6">
                
                <!-- Logo & Heading -->
                <div class="space-y-2">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="h-9 w-9 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                            Rx
                        </div>
                        <span class="text-xl font-bold tracking-tight text-slate-900">
                            {{ config('app.name', 'Pharmacy Expert') }}
                        </span>
                    </div>

                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900">
                        Welcome back
                    </h1>
                    <p class="text-sm text-slate-500">
                        Please enter your details to sign in.
                    </p>
                </div>

                <!-- Session Status Alert -->
                @if (session('status'))
                    <div class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-medium">
                        {{ session('status') }}
                    </div>
                @endif

                <!-- Validation Errors Alert -->
                @if ($errors->any())
                    <div class="p-3.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs">
                        <ul class="list-disc list-inside space-y-1 font-medium">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Form -->
                <form method="POST" action="{{ route('login.store') }}" class="space-y-4.5" x-data="{ show: false }">
                    @csrf

                    <!-- Email Field -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-sm font-medium text-slate-700">
                            Email address
                        </label>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            autocomplete="email" 
                            required 
                            autofocus 
                            value="{{ old('email') }}" 
                            placeholder="admin@example.com"
                            class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition"
                        />
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm font-medium text-slate-700">
                                Password
                            </label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700 hover:underline" wire:navigate>
                                    Forgot password?
                                </a>
                            @endif
                        </div>
                        <div class="relative">
                            <input 
                                id="password" 
                                name="password" 
                                :type="show ? 'text' : 'password'" 
                                autocomplete="current-password" 
                                required 
                                placeholder="••••••••"
                                class="w-full px-3.5 pr-10 py-2.5 bg-white border border-slate-300 rounded-lg text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600 transition"
                            />
                            <button 
                                type="button" 
                                @click="show = !show"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer"
                                tabindex="-1"
                            >
                                <svg x-show="!show" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" class="w-4 h-4" style="display: none;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium text-slate-600 select-none">
                            <input 
                                type="checkbox" 
                                name="remember" 
                                id="remember"
                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                {{ old('remember') ? 'checked' : '' }}
                            />
                            <span>Remember for 30 days</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button 
                        type="submit" 
                        class="w-full py-2.5 px-4 rounded-lg bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-white text-sm font-semibold shadow-xs transition duration-150 cursor-pointer"
                    >
                        Sign in
                    </button>
                </form>

                <!-- Footer -->
                <div class="pt-6 border-t border-slate-100 text-center text-xs text-slate-400">
                    &copy; {{ date('Y') }} {{ config('app.name', 'Pharmacy Expert') }}. All rights reserved.
                </div>

            </div>
        </div>

    </div>

    @fluxScripts
</body>
</html>
