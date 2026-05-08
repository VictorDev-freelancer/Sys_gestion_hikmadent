<?php

namespace App\Livewire\Admin\Catalog;

use Livewire\Component;

use App\Models\CatalogItem;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CatalogForm extends Component
{
    public ?int $itemId = null;
    public string $category = '';
    public string $name = '';
    public float $price_regular = 0;
    public float $price_student = 0;
    public bool $is_active = true;

    protected $rules = [
        'category' => 'required|string|max:100',
        'name' => 'required|string|max:200',
        'price_regular' => 'required|numeric|min:0',
        'price_student' => 'required|numeric|min:0',
        'is_active' => 'boolean',
    ];

    public function mount(?int $id = null)
    {
        if ($id) {
            $this->itemId = $id;
            $item = CatalogItem::findOrFail($id);
            $this->category = $item->category;
            $this->name = $item->name;
            $this->price_regular = (float) $item->price_regular;
            $this->price_student = (float) $item->price_student;
            $this->is_active = $item->is_active;
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'category' => $this->category,
            'name' => $this->name,
            'price_regular' => $this->price_regular,
            'price_student' => $this->price_student,
            'is_active' => $this->is_active,
        ];

        if ($this->itemId) {
            CatalogItem::findOrFail($this->itemId)->update($data);
            session()->flash('message', 'Servicio actualizado correctamente.');
        } else {
            CatalogItem::create($data);
            session()->flash('message', 'Servicio creado correctamente.');
        }

        $this->redirect(route('admin.catalog.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.admin.catalog.catalog-form');
    }
}
