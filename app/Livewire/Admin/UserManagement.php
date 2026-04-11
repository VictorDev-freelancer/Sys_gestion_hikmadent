<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class UserManagement extends Component
{
    use WithPagination;

    public $name, $email, $password, $role_name, $userId;
    public $isModalOpen = false;

    public function render()
    {
        return view('livewire.admin.user-management', [
            'users' => User::with('roles')->paginate(10),
            'roles' => Role::all(),
        ]);
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->isModalOpen = true;
    }

    public function closeModal()
    {
        $this->isModalOpen = false;
        $this->resetInputFields();
    }

    private function resetInputFields()
    {
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->role_name = '';
        $this->userId = null;
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $this->userId,
            'password' => $this->userId ? 'nullable|min:8' : 'required|min:8',
            'role_name' => 'required|string|exists:roles,name',
        ]);

        $user = User::updateOrCreate(['id' => $this->userId], [
            'name' => $this->name,
            'email' => $this->email,
        ]);

        if (!empty($this->password)) {
            $user->update(['password' => Hash::make($this->password)]);
        }

        // Asignación estricta de 1 rol usando syncRoles de Spatie
        $role = Role::findByName($this->role_name);
        $user->syncRoles([$role]);

        session()->flash('message', $this->userId ? 'El usuario ha sido modificado y actualizado existosamente.' : 'El nuevo miembro fue agregado al sistema.');

        $this->closeModal();
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role_name = $user->roles->first() ? $user->roles->first()->name : '';

        $this->openModal();
    }

    public function delete($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('message', 'El usuario ha sido expulsado del sistema permanentemente.');
    }
}
