<?php

namespace App\Livewire\Taxes;

use App\Models\Tax;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Tax & VAT Configuration - PMS')]
class TaxManager extends Component
{
    public ?int $editingTaxId = null;

    public string $name = '';

    public float $rate = 0.00;

    public bool $isActive = true;

    public function saveTax()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'rate' => 'required|numeric|min:0|max:100',
        ]);

        Tax::updateOrCreate(
            ['id' => $this->editingTaxId],
            [
                'name' => $this->name,
                'rate' => $this->rate,
                'is_active' => $this->isActive,
            ]
        );

        session()->flash('success', $this->editingTaxId ? 'Tax rate updated successfully!' : 'Tax rate configured successfully!');
        $this->reset(['name', 'rate', 'editingTaxId', 'isActive']);
    }

    public function editTax(int $id)
    {
        $tax = Tax::findOrFail($id);
        $this->editingTaxId = $tax->id;
        $this->name = $tax->name;
        $this->rate = (float) $tax->rate;
        $this->isActive = (bool) $tax->is_active;
    }

    public function cancelEdit()
    {
        $this->reset(['name', 'rate', 'editingTaxId', 'isActive']);
    }

    public function deleteTax(int $id)
    {
        Tax::findOrFail($id)->delete();
        session()->flash('success', 'Tax rate removed.');
    }

    public function toggleTax(int $id)
    {
        $tax = Tax::findOrFail($id);
        $tax->update(['is_active' => ! $tax->is_active]);
    }

    public function render()
    {
        return view('livewire.taxes.tax-manager', [
            'taxes' => Tax::orderBy('name')->get(),
        ]);
    }
}
