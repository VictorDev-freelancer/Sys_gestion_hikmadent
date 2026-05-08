<?php

namespace App\Livewire\Admin\Catalog;

use Livewire\Component;

use App\Models\CatalogItem;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class CatalogList extends Component
{
    public $search = '';

    public function deleteItem(int $id)
    {
        CatalogItem::findOrFail($id)->delete();
        session()->flash('message', 'Servicio eliminado correctamente.');
    }

    public function toggleActive(int $id)
    {
        $item = CatalogItem::findOrFail($id);
        $item->update(['is_active' => !$item->is_active]);
    }

    public function render()
    {
        $items = CatalogItem::where('name', 'like', "%{$this->search}%")
            ->orWhere('category', 'like', "%{$this->search}%")
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.catalog.catalog-list', [
            'items' => $items
        ]);
    }
}
