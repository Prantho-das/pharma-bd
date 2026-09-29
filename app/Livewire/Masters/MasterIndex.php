<?php

namespace App\Livewire\Masters;

use App\Models\Category;
use App\Models\Doctor;
use App\Models\Generic;
use App\Models\Manufacturer;
use App\Models\Unit;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Master Catalogs & Medical Directory - PMS')]
class MasterIndex extends Component
{
    use WithPagination;

    #[Url(as: 'tab')]
    public string $activeTab = 'generics'; // generics, doctors, categories, manufacturers, units

    public string $search = '';

    // Modal state
    public bool $showModal = false;

    public ?int $editingId = null;

    // Generic Fields
    public string $genericName = '';

    public string $indication = '';

    public string $sideEffects = '';

    // Doctor Fields
    public string $doctorName = '';

    public string $degrees = '';

    public string $bmdcRegNo = '';

    public string $hospitalName = '';

    public string $doctorPhone = '';

    // Category Fields
    public string $categoryName = '';

    public string $categoryDesc = '';

    // Manufacturer Fields
    public string $manufacturerName = '';

    public string $contactPerson = '';

    public string $mfgPhone = '';

    public string $mfgAddress = '';

    // Unit Fields
    public string $unitName = '';

    public string $shortCode = '';

    public float $conversionFactor = 1.00;

    public bool $isBaseUnit = false;

    public ?string $successMessage = null;

    public function setTab(string $tab)
    {
        $this->activeTab = $tab;
        $this->search = '';
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->reset([
            'editingId', 'genericName', 'indication', 'sideEffects',
            'doctorName', 'degrees', 'bmdcRegNo', 'hospitalName', 'doctorPhone',
            'categoryName', 'categoryDesc',
            'manufacturerName', 'contactPerson', 'mfgPhone', 'mfgAddress',
            'unitName', 'shortCode', 'conversionFactor', 'isBaseUnit',
        ]);
        $this->showModal = true;
    }

    public function saveGeneric()
    {
        $this->validate(['genericName' => 'required|string|min:2']);
        Generic::updateOrCreate(
            ['id' => $this->editingId],
            ['name' => $this->genericName, 'indication' => $this->indication, 'side_effects' => $this->sideEffects]
        );
        $this->showModal = false;
        $this->successMessage = 'Generic formulation saved successfully!';
    }

    public function saveDoctor()
    {
        $this->validate(['doctorName' => 'required|string|min:2']);
        Doctor::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->doctorName,
                'degrees' => $this->degrees,
                'bmdc_reg_no' => $this->bmdcRegNo,
                'hospital_name' => $this->hospitalName,
                'phone' => $this->doctorPhone,
            ]
        );
        $this->showModal = false;
        $this->successMessage = 'Doctor saved successfully!';
    }

    public function saveCategory()
    {
        $this->validate(['categoryName' => 'required|string|min:2']);
        Category::updateOrCreate(
            ['id' => $this->editingId],
            ['name' => $this->categoryName, 'description' => $this->categoryDesc]
        );
        $this->showModal = false;
        $this->successMessage = 'Category saved successfully!';
    }

    public function saveManufacturer()
    {
        $this->validate(['manufacturerName' => 'required|string|min:2']);
        Manufacturer::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->manufacturerName,
                'contact_person' => $this->contactPerson,
                'phone' => $this->mfgPhone,
                'address' => $this->mfgAddress,
            ]
        );
        $this->showModal = false;
        $this->successMessage = 'Pharmaceutical manufacturer saved successfully!';
    }

    public function saveUnit()
    {
        $this->validate(['unitName' => 'required|string|min:2', 'shortCode' => 'required|string']);
        Unit::updateOrCreate(
            ['id' => $this->editingId],
            [
                'name' => $this->unitName,
                'short_code' => $this->shortCode,
                'conversion_factor' => $this->conversionFactor,
                'is_base_unit' => $this->isBaseUnit,
            ]
        );
        $this->showModal = false;
        $this->successMessage = 'Packaging unit saved successfully!';
    }

    public function render()
    {
        $term = '%'.trim($this->search).'%';

        $data = match ($this->activeTab) {
            'doctors' => Doctor::where('name', 'like', $term)->orWhere('hospital_name', 'like', $term)->latest('id')->paginate(12),
            'categories' => Category::where('name', 'like', $term)->latest('id')->paginate(12),
            'manufacturers' => Manufacturer::where('name', 'like', $term)->latest('id')->paginate(12),
            'units' => Unit::where('name', 'like', $term)->latest('id')->paginate(12),
            default => Generic::where('name', 'like', $term)->latest('id')->paginate(12),
        };

        return view('livewire.masters.master-index', [
            'items' => $data,
        ]);
    }
}
