<div class="p-6 space-y-6 max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 tracking-tight">Master Catalogs & Medical Directory</h1>
            <p class="text-xs text-slate-500 mt-1">Manage core chemical generics, prescribing doctors, categories, pharmaceutical manufacturers, and packaging units.</p>
        </div>
        <button 
            wire:click="openCreateModal" 
            type="button" 
            class="px-4 py-2.5 bg-gradient-to-r from-sky-600 to-blue-700 hover:from-sky-600 hover:to-blue-700 text-white rounded-lg text-xs font-bold shadow-md transition flex items-center gap-1.5 cursor-pointer self-start sm:self-auto"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            <span>Add New Entry</span>
        </button>
    </div>

    @if($successMessage)
        <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold rounded-lg flex justify-between">
            <span>{{ $successMessage }}</span>
            <button wire:click="$set('successMessage', null)">&times;</button>
        </div>
    @endif

    {{-- Tab Navigation Bar --}}
    <div class="border-b border-slate-200">
        <nav class="flex space-x-6 text-xs font-bold" aria-label="Tabs">
            <button 
                wire:click="setTab('generics')" 
                class="py-3 px-1 border-b-2 transition flex items-center gap-1.5 {{ $activeTab === 'generics' ? 'border-sky-700 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
            >
                <span>🧪 Generics & Molecules</span>
            </button>

            <button 
                wire:click="setTab('doctors')" 
                class="py-3 px-1 border-b-2 transition flex items-center gap-1.5 {{ $activeTab === 'doctors' ? 'border-sky-700 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
            >
                <span>👨‍⚕️ Doctors Directory</span>
            </button>

            <button 
                wire:click="setTab('categories')" 
                class="py-3 px-1 border-b-2 transition flex items-center gap-1.5 {{ $activeTab === 'categories' ? 'border-sky-700 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
            >
                <span>📁 Categories</span>
            </button>

            <button 
                wire:click="setTab('manufacturers')" 
                class="py-3 px-1 border-b-2 transition flex items-center gap-1.5 {{ $activeTab === 'manufacturers' ? 'border-sky-700 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
            >
                <span>🏭 Manufacturers</span>
            </button>

            <button 
                wire:click="setTab('units')" 
                class="py-3 px-1 border-b-2 transition flex items-center gap-1.5 {{ $activeTab === 'units' ? 'border-sky-700 text-sky-800' : 'border-transparent text-slate-500 hover:text-slate-800' }}"
            >
                <span>📦 Packaging Units</span>
            </button>
        </nav>
    </div>

    {{-- Search Toolbar --}}
    <div class="bg-white p-3.5 rounded-lg border border-slate-200/80 shadow-xs">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Search {{ $activeTab }}..." class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-lg font-medium">
    </div>

    {{-- Data Display Grid --}}
    <div class="bg-white rounded-lg border border-slate-200 shadow-xs overflow-hidden">
        <table class="w-full text-left border-collapse text-xs">
            <thead class="bg-slate-50 border-b border-slate-200 text-[11px] uppercase tracking-wider text-slate-500 font-bold">
                <tr>
                    <th class="py-3 px-4">#</th>
                    <th class="py-3 px-3">Name / Title</th>
                    <th class="py-3 px-3">Details / Specifications</th>
                    <th class="py-3 px-3">Created</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($items as $item)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="py-3 px-4 font-mono text-slate-400 text-[11px]">{{ $loop->iteration }}</td>
                        <td class="py-3 px-3 font-bold text-slate-900">
                            {{ $item->name }}
                            @if($activeTab === 'units' && $item->is_base_unit)
                                <span class="bg-sky-100 text-sky-800 text-[9px] font-bold px-1.5 py-0.5 rounded ml-1">BASE ATOMIC</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-600">
                            @if($activeTab === 'generics')
                                <span class="line-clamp-1">{{ $item->indication ?: 'General clinical formulation' }}</span>
                            @elseif($activeTab === 'doctors')
                                <span>{{ $item->degrees }} &bull; {{ $item->hospital_name }} (BMDC: {{ $item->bmdc_reg_no ?: 'N/A' }})</span>
                            @elseif($activeTab === 'categories')
                                <span>{{ $item->description ?: 'N/A' }}</span>
                            @elseif($activeTab === 'manufacturers')
                                <span>{{ $item->contact_person }} ({{ $item->phone ?: 'N/A' }})</span>
                            @elseif($activeTab === 'units')
                                <span class="font-mono">Code: {{ $item->short_code }} &bull; Multiplier: {{ $item->conversion_factor }}</span>
                            @endif
                        </td>
                        <td class="py-3 px-3 text-slate-400 font-mono text-[11px]">
                            {{ $item->created_at?->format('d M, Y') ?? 'N/A' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-12 text-center text-slate-400">No records found. Click "+ Add New Entry" to create one.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-slate-100">
            {{ $items->links() }}
        </div>
    </div>

    {{-- Create / Edit Modal for Current Tab --}}
    @if($showModal)
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white w-full max-w-md rounded-lg shadow-2xl border border-slate-200 overflow-hidden">
                <div class="bg-sky-800 px-5 py-3.5 text-white flex items-center justify-between">
                    <h3 class="text-sm font-bold">Add {{ ucfirst(rtrim($activeTab, 's')) }}</h3>
                    <button wire:click="$set('showModal', false)" class="text-white font-bold">&times;</button>
                </div>

                <div class="p-5 space-y-3 text-xs">
                    @if($activeTab === 'generics')
                        <div>
                            <label class="font-bold text-slate-700">Generic Name *</label>
                            <input wire:model="genericName" type="text" placeholder="e.g. Paracetamol" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                            @error('genericName') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Clinical Indication</label>
                            <textarea wire:model="indication" rows="2" class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg"></textarea>
                        </div>
                    @elseif($activeTab === 'doctors')
                        <div>
                            <label class="font-bold text-slate-700">Doctor Name *</label>
                            <input wire:model="doctorName" type="text" placeholder="Dr. Rafiqul Islam" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                            @error('doctorName') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Degrees</label>
                            <input wire:model="degrees" type="text" placeholder="MBBS, FCPS" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">BMDC Reg No</label>
                            <input wire:model="bmdcRegNo" type="text" placeholder="A-12345" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg font-mono">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Hospital / Chamber</label>
                            <input wire:model="hospitalName" type="text" placeholder="Square Hospital" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                        </div>
                    @elseif($activeTab === 'categories')
                        <div>
                            <label class="font-bold text-slate-700">Category Name *</label>
                            <input wire:model="categoryName" type="text" placeholder="Antibiotics" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                            @error('categoryName') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Description</label>
                            <textarea wire:model="categoryDesc" rows="2" class="w-full py-1.5 px-2 mt-1 border border-slate-300 rounded-lg"></textarea>
                        </div>
                    @elseif($activeTab === 'manufacturers')
                        <div>
                            <label class="font-bold text-slate-700">Manufacturer Name *</label>
                            <input wire:model="manufacturerName" type="text" placeholder="Beximco Pharmaceuticals" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                            @error('manufacturerName') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Contact Person</label>
                            <input wire:model="contactPerson" type="text" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Phone</label>
                            <input wire:model="mfgPhone" type="text" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg font-mono">
                        </div>
                    @elseif($activeTab === 'units')
                        <div>
                            <label class="font-bold text-slate-700">Unit Name *</label>
                            <input wire:model="unitName" type="text" placeholder="Strip / Box / Piece" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg">
                            @error('unitName') <span class="text-rose-500 text-[11px]">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label class="font-bold text-slate-700">Short Code *</label>
                            <input wire:model="shortCode" type="text" placeholder="str / bx / pcs" class="w-full py-2 px-3 mt-1 border border-slate-300 rounded-lg font-mono">
                        </div>
                        <div class="flex items-center space-x-2 pt-2">
                            <input wire:model="isBaseUnit" type="checkbox" id="base_check" class="w-4 h-4 text-sky-600 rounded">
                            <label for="base_check" class="font-bold text-sky-800">Atomic Base Unit (Piece)</label>
                        </div>
                    @endif
                </div>

                <div class="p-4 bg-slate-50 border-t border-slate-200 flex justify-end space-x-2">
                    <button wire:click="$set('showModal', false)" class="px-3 py-1.5 bg-slate-200 text-slate-700 rounded-lg text-xs font-bold">Cancel</button>
                    @if($activeTab === 'generics')
                        <button wire:click="saveGeneric" class="px-5 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Save</button>
                    @elseif($activeTab === 'doctors')
                        <button wire:click="saveDoctor" class="px-5 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Save</button>
                    @elseif($activeTab === 'categories')
                        <button wire:click="saveCategory" class="px-5 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Save</button>
                    @elseif($activeTab === 'manufacturers')
                        <button wire:click="saveManufacturer" class="px-5 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Save</button>
                    @elseif($activeTab === 'units')
                        <button wire:click="saveUnit" class="px-5 py-1.5 bg-sky-700 hover:bg-sky-800 text-white rounded-lg text-xs font-bold">Save</button>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
