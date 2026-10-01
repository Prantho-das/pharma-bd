<div class="p-4 sm:p-6 max-w-7xl mx-auto space-y-4">
    {{-- Top Header & Co-Pilot Status --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-slate-900 via-slate-800 to-sky-950 p-5 rounded-3xl text-white shadow-lg border border-slate-700/60">
        <div class="flex items-center gap-4">
            <div class="relative">
                <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-sky-500 to-indigo-600 flex items-center justify-center shadow-lg shadow-sky-500/30 ring-2 ring-white/20">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.38-1 1.72V7h3a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-8a3 3 0 0 1 3-3h3V5.72A2 2 0 0 1 10 4a2 2 0 0 1 2-2m-3 9a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-6 5h6v1.5H9V16z"/>
                    </svg>
                </div>
                <span class="absolute -bottom-1 -right-1 flex h-3.5 w-3.5">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3.5 w-3.5 bg-emerald-500 ring-2 ring-slate-900"></span>
                </span>
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-black tracking-tight text-white flex items-center gap-2">
                        Pharmacy Sidekick
                        <span class="text-[10px] uppercase font-extrabold tracking-widest px-2 py-0.5 rounded-full bg-gradient-to-r from-sky-400 to-indigo-400 text-slate-950 shadow-xs">
                            AI Co-Pilot
                        </span>
                    </h1>
                </div>
                <p class="text-xs text-slate-300 mt-0.5 flex items-center gap-2">
                    <span class="flex items-center gap-1 text-emerald-400 font-semibold">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        {{ strtoupper($aiProvider) }}: {{ $aiModel }}
                    </span>
                    <span>•</span>
                    <span class="text-slate-400">9 Live Real-Time Database Tools Connected</span>
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2 self-start sm:self-auto">
            @if(count($messages) > 0)
                <button 
                    wire:click="clearChat" 
                    type="button" 
                    class="px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/20 text-white text-xs font-bold transition flex items-center gap-1.5 backdrop-blur-md cursor-pointer border border-white/10">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    <span>{{ __('New Chat') }}</span>
                </button>
            @endif

            <button 
                wire:click="$set('showSettingsModal', true)" 
                type="button" 
                class="px-3.5 py-2 rounded-xl bg-sky-600 hover:bg-sky-500 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-md shadow-sky-600/30 cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>{{ __('AI Settings & Model') }}</span>
            </button>
        </div>
    </div>

    {{-- System Feedback Alerts --}}
    @if ($feedbackMessage)
        <div x-data="{ show: true }" x-show="show" class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs font-bold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <span>✓</span>
                <span>{{ $feedbackMessage }}</span>
            </div>
            <button @click="show = false" type="button" class="text-emerald-700 hover:text-emerald-900">&times;</button>
        </div>
    @endif

    {{-- Chat & Sidekick Main Workspace --}}
    <div class="bg-white rounded-3xl border border-slate-200/90 shadow-sm flex flex-col justify-between overflow-hidden min-h-[600px] divide-y divide-slate-100">
        
        {{-- Messages Scrollable Viewport --}}
        <div class="p-6 sm:p-8 flex-1 overflow-y-auto space-y-6 max-h-[calc(100vh-22rem)]" id="sidekickChatBox">
            @if(count($messages) === 0)
                {{-- Sidekick Welcome Hero --}}
                <div class="text-center max-w-2xl mx-auto space-y-6 py-8">
                    <div class="w-20 h-20 rounded-3xl bg-gradient-to-tr from-sky-100 via-indigo-50 to-blue-100 text-sky-600 flex items-center justify-center mx-auto shadow-inner border border-sky-200/60 ring-8 ring-sky-50/50">
                        <svg class="w-10 h-10" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2a2 2 0 0 1 2 2c0 .74-.4 1.38-1 1.72V7h3a3 3 0 0 1 3 3v8a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3v-8a3 3 0 0 1 3-3h3V5.72A2 2 0 0 1 10 4a2 2 0 0 1 2-2m-3 9a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3m-6 5h6v1.5H9V16z"/>
                        </svg>
                    </div>

                    <div class="space-y-2">
                        <h2 class="text-2xl font-black text-slate-900 tracking-tight">How can Sidekick help your pharmacy today?</h2>
                        <p class="text-xs text-slate-500 max-w-lg mx-auto">
                            Equipped with high-accuracy database tools to fetch sales, check stock levels, alert expiring batches, audit receivables, and calculate profit margins.
                        </p>
                    </div>

                    {{-- Quick Action Chips (Shopify Sidekick Style) --}}
                    <div class="pt-2 space-y-3">
                        <div class="flex items-center justify-center gap-1.5 text-xs font-bold text-slate-600">
                            <span>⚡</span>
                            <span>{{ __('Click a quick question or type below') }}</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                            @foreach ($suggestedQuestions as $q)
                                <button 
                                    wire:click="selectSuggestedQuestion('{{ $q }}')" 
                                    type="button" 
                                    class="p-4 rounded-2xl bg-slate-50/80 hover:bg-sky-50/70 border border-slate-200 hover:border-sky-300 text-xs font-semibold text-slate-800 transition shadow-2xs hover:shadow-md text-left flex items-center justify-between group cursor-pointer">
                                    <div class="flex items-center gap-2.5">
                                        <span class="p-1 rounded-lg bg-white shadow-2xs border border-slate-200 text-sky-600 group-hover:scale-110 transition-transform">💬</span>
                                        <span>{{ __($q) }}</span>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-400 group-hover:text-sky-600 transition shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                {{-- Active Chat Thread --}}
                <div class="space-y-6 max-w-4xl mx-auto">
                    @foreach ($messages as $msg)
                        @if ($msg['sender'] === 'user')
                            {{-- User Query Bubble --}}
                            <div class="flex items-end justify-end gap-3">
                                <div class="space-y-1 text-right max-w-xl">
                                    <div class="bg-gradient-to-r from-sky-600 to-blue-700 text-white p-4 rounded-3xl rounded-br-none text-xs font-bold shadow-md inline-block text-left leading-relaxed">
                                        {{ $msg['text'] }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 pr-1">{{ $msg['time'] ?? '' }}</div>
                                </div>
                                <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 shadow-inner">
                                    👤
                                </div>
                            </div>
                        @else
                            {{-- Sidekick AI Response Card --}}
                            <div class="flex items-start gap-3.5">
                                <div class="w-9 h-9 rounded-2xl bg-gradient-to-tr from-sky-600 to-indigo-600 text-white flex items-center justify-center font-black text-sm shrink-0 shadow-md ring-2 ring-sky-100">
                                    Rx
                                </div>

                                <div class="space-y-2 flex-1 max-w-3xl">
                                    {{-- Tool Calling Execution Pill --}}
                                    @if(!empty($msg['tool_used']))
                                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 border border-slate-200 text-[11px] font-bold text-slate-700 shadow-2xs">
                                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Tool: <code class="text-sky-700 font-mono">{{ $msg['tool_used'] }}</code></span>
                                            @if(!empty($msg['execution_time']))
                                                <span class="text-slate-400">({{ $msg['execution_time'] }}ms)</span>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Main Message Card --}}
                                    <div class="bg-slate-50/90 border border-slate-200/90 p-5 rounded-3xl rounded-tl-none shadow-xs text-xs text-slate-800 space-y-3 prose prose-xs max-w-none">
                                        <div class="whitespace-pre-line leading-relaxed font-sans">
                                            {!! Str::markdown($msg['text']) !!}
                                        </div>

                                        {{-- Quick Interactive Context Shortcuts --}}
                                        <div class="pt-3 border-t border-slate-200/80 flex flex-wrap items-center gap-2 not-prose">
                                            <a href="{{ route('pos.terminal') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-sky-50 text-slate-700 hover:text-sky-700 border border-slate-200 text-[11px] font-bold shadow-2xs transition">
                                                <span>⚡</span>
                                                <span>{{ __('POS Terminal') }}</span>
                                            </a>
                                            <a href="{{ route('purchases.create') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-sky-50 text-slate-700 hover:text-sky-700 border border-slate-200 text-[11px] font-bold shadow-2xs transition">
                                                <span>🛒</span>
                                                <span>{{ __('New Purchase') }}</span>
                                            </a>
                                            <a href="{{ route('inventory.reorder-shortage') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-sky-50 text-slate-700 hover:text-sky-700 border border-slate-200 text-[11px] font-bold shadow-2xs transition">
                                                <span>📦</span>
                                                <span>{{ __('Reorder Shortage') }}</span>
                                            </a>
                                            <a href="{{ route('reports.index') }}" wire:navigate class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white hover:bg-sky-50 text-slate-700 hover:text-sky-700 border border-slate-200 text-[11px] font-bold shadow-2xs transition">
                                                <span>📊</span>
                                                <span>{{ __('Reports') }}</span>
                                            </a>
                                        </div>
                                    </div>

                                    <div class="text-[10px] text-slate-400 pl-2">{{ $msg['time'] ?? '' }}</div>
                                </div>
                            </div>
                        @endif
                    @endforeach

                    {{-- Live Thinking Indicator --}}
                    @if($isThinking)
                        <div class="flex items-start gap-3.5 animate-pulse">
                            <div class="w-9 h-9 rounded-2xl bg-sky-600 text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-md">
                                Rx
                            </div>
                            <div class="bg-slate-100 border border-slate-200 p-4 rounded-3xl rounded-tl-none text-xs text-slate-600 flex items-center gap-3">
                                <div class="flex gap-1">
                                    <span class="w-2 h-2 rounded-full bg-sky-600 animate-bounce" style="animation-delay: 0ms"></span>
                                    <span class="w-2 h-2 rounded-full bg-sky-600 animate-bounce" style="animation-delay: 150ms"></span>
                                    <span class="w-2 h-2 rounded-full bg-sky-600 animate-bounce" style="animation-delay: 300ms"></span>
                                </div>
                                <span class="font-bold text-sky-800">Sidekick is querying database tools & analyzing live metrics...</span>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        </div>

        {{-- Bottom Input Prompt Bar --}}
        <div class="p-4 sm:p-5 bg-slate-50/80">
            <form wire:submit.prevent="sendMessage" class="max-w-4xl mx-auto">
                <div class="flex items-center gap-2 p-2 bg-white rounded-2xl border-2 border-slate-200 focus-within:border-sky-500 focus-within:ring-4 focus-within:ring-sky-500/10 shadow-sm transition">
                    <input 
                        wire:model="inputPrompt" 
                        type="text" 
                        placeholder="{{ __('Ask Sidekick: e.g. What is today\'s total sales, or check stock for Napa Extra...') }}" 
                        class="flex-1 px-3 py-2 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none bg-transparent"
                        autofocus
                    />

                    <button 
                        type="submit" 
                        wire:loading.attr="disabled"
                        class="p-2.5 sm:px-5 sm:py-2.5 rounded-xl bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-700 hover:to-blue-800 text-white font-bold text-xs sm:text-sm transition flex items-center gap-1.5 shadow-md shadow-sky-600/30 cursor-pointer disabled:opacity-50">
                        <span wire:loading.remove>{{ __('Ask Sidekick') }}</span>
                        <span wire:loading>{{ __('Analyzing...') }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </button>
                </div>
                <div class="flex items-center justify-between px-2 pt-2 text-[11px] text-slate-400">
                    <span>⚡ Sidekick performs tool-calling queries directly on live pharmacy inventory.</span>
                    <span>Pro-tip: Press Enter to submit.</span>
                </div>
            </form>
        </div>
    </div>

    {{-- AI Settings & Provider Modal --}}
    @if ($showSettingsModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-lg rounded-3xl shadow-2xl border border-slate-200 overflow-hidden space-y-0">
                <div class="p-5 bg-gradient-to-r from-slate-900 to-sky-950 text-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center font-bold">⚙️</div>
                        <div>
                            <h3 class="text-sm font-black">{{ __('AI Model Setup & Providers') }}</h3>
                            <p class="text-[11px] text-slate-300">{{ __('Configure Cloud LLM or use Built-in Offline Analytics') }}</p>
                        </div>
                    </div>
                    <button wire:click="$set('showSettingsModal', false)" class="text-white hover:text-rose-400 text-lg font-bold cursor-pointer">&times;</button>
                </div>

                <div class="p-6 space-y-4 text-xs">
                    {{-- Provider Select --}}
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">{{ __('AI Provider Engine') }}</label>
                        <select wire:model.live="aiProvider" class="w-full py-2.5 px-3 border border-slate-300 rounded-xl font-bold text-slate-800 focus:ring-2 focus:ring-sky-500">
                            <option value="groq">⚡ Groq (Ultra Fast Llama 3.3 70B) - Recommended</option>
                            <option value="gemini">✨ Google Gemini (2.5 Flash / 2.5 Pro)</option>
                            <option value="openai">🤖 OpenAI (GPT-4o / GPT-4o-mini)</option>
                            <option value="deepseek">🐋 DeepSeek (V3 / R1)</option>
                            <option value="local">💻 Built-in Offline Local Smart Engine (Zero API Key)</option>
                        </select>
                    </div>

                    {{-- API Key Input --}}
                    @if($aiProvider !== 'local')
                        <div x-data="{ showKey: false }">
                            <label class="block font-bold text-slate-700 mb-1.5">
                                {{ strtoupper($aiProvider) }} API Key <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    wire:model="aiApiKey" 
                                    :type="showKey ? 'text' : 'password'" 
                                    placeholder="Enter your {{ $aiProvider }} API Key" 
                                    class="w-full py-2 px-3 pr-14 border border-slate-300 rounded-xl font-mono text-xs focus:ring-2 focus:ring-sky-500"
                                />
                                <button 
                                    @click="showKey = !showKey" 
                                    type="button" 
                                    class="absolute right-2 top-2 text-[11px] font-bold text-slate-500 hover:text-slate-800 px-1.5 py-0.5 rounded cursor-pointer">
                                    <span x-text="showKey ? 'Hide' : 'Show'"></span>
                                </button>
                            </div>
                        </div>

                        {{-- Model Select --}}
                        <div>
                            <label class="block font-bold text-slate-700 mb-1.5">{{ __('Model Name') }}</label>
                            <select wire:model="aiModel" class="w-full py-2 px-3 border border-slate-300 rounded-xl font-mono text-xs focus:ring-2 focus:ring-sky-500">
                                @if($aiProvider === 'groq')
                                    <option value="llama-3.3-70b-versatile">llama-3.3-70b-versatile (Fastest & Smart)</option>
                                    <option value="llama-3.1-8b-instant">llama-3.1-8b-instant</option>
                                    <option value="mixtral-8x7b-32768">mixtral-8x7b-32768</option>
                                @elseif($aiProvider === 'gemini')
                                    <option value="gemini-2.5-flash">gemini-2.5-flash (Fast & Recommended)</option>
                                    <option value="gemini-2.5-pro">gemini-2.5-pro (Advanced Reasoning)</option>
                                    <option value="gemini-2.0-flash">gemini-2.0-flash</option>
                                    <option value="gemini-2.0-flash-lite">gemini-2.0-flash-lite</option>
                                @elseif($aiProvider === 'openai')
                                    <option value="gpt-4o-mini">gpt-4o-mini (Cost Effective)</option>
                                    <option value="gpt-4o">gpt-4o</option>
                                @elseif($aiProvider === 'deepseek')
                                    <option value="deepseek-chat">deepseek-chat (DeepSeek V3)</option>
                                    <option value="deepseek-reasoner">deepseek-reasoner (R1)</option>
                                @endif
                            </select>
                        </div>
                    @endif

                    {{-- Temperature Slider --}}
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <label class="font-bold text-slate-700">Creativity / Temperature ({{ $aiTemperature }})</label>
                            <span class="text-[10px] text-slate-400">0.0 = Strict Facts, 1.0 = Creative</span>
                        </div>
                        <input wire:model="aiTemperature" type="range" min="0" max="1" step="0.1" class="w-full cursor-pointer accent-sky-600">
                    </div>

                    {{-- Live Test Connection Status --}}
                    @if($testConnectionStatus)
                        <div class="p-3 rounded-xl bg-slate-100 border border-slate-200 text-xs font-bold text-slate-800">
                            {{ $testConnectionStatus }}
                        </div>
                    @endif
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <button 
                        wire:click="testConnection" 
                        type="button" 
                        class="px-3.5 py-2 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-800 font-bold text-xs transition cursor-pointer">
                        🔍 {{ __('Test Connection') }}
                    </button>

                    <div class="flex items-center gap-2">
                        <button wire:click="$set('showSettingsModal', false)" type="button" class="px-3.5 py-2 rounded-xl bg-slate-100 text-slate-600 font-bold text-xs">{{ __('Cancel') }}</button>
                        <button wire:click="saveSettings" type="button" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs shadow-md transition cursor-pointer">✓ {{ __('Save AI Settings') }}</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
