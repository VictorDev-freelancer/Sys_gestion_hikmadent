<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;

#[Layout('layouts.app')]
class UserManagement extends Component
{
    use WithPagination;

    public $name, $email, $password, $role_name, $userId;
    public $isModalOpen = false;
    public $activeTab = 'personal'; // 'personal' o 'logins'

    public function switchTab($tab)
    {
        $this->activeTab = $tab;
        $this->resetPage();
        $this->closeModal();
    }

    public function render()
    {
        $roles = Role::all();
        
        // Filtrar roles según la pestaña activa para el modal
        $modalRoles = $roles;
        if ($this->activeTab === 'personal') {
            $modalRoles = $roles->whereNotIn('name', ['Super usuario', 'Administración']);
        } else {
            $modalRoles = $roles->whereIn('name', ['Super usuario', 'Administración']);
        }

        // Cargar usuarios paginados según la pestaña
        if ($this->activeTab === 'personal') {
            $users = User::whereDoesntHave('roles', function($q) {
                $q->whereIn('name', ['Super usuario', 'Administración']);
            })->with('roles')->paginate(10);
        } else {
            $users = User::whereHas('roles', function($q) {
                $q->whereIn('name', ['Super usuario', 'Administración']);
            })->with('roles')->paginate(10);
        }

        return view('livewire.admin.user-management', [
            'users' => $users,
            'roles' => $roles,
            'modalRoles' => $modalRoles,
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
        if ($this->activeTab === 'personal') {
            // Validaciones para Personal / Colaborador
            $this->validate([
                'name' => 'required|string|max:255',
                'role_name' => 'required|string|exists:roles,name',
            ]);

            if ($this->userId) {
                $user = User::findOrFail($this->userId);
                $user->update([
                    'name' => $this->name,
                ]);
            } else {
                // Generar email único y contraseña aleatoria segura por debajo
                $normalizedName = Str::ascii($this->name);
                $dotName = str_replace(' ', '.', trim($normalizedName));
                $slug = strtolower(preg_replace('/[^a-zA-Z0-9.]/', '', $dotName));
                $dummyEmail = $slug . '_' . time() . '@hikmadent.com';
                $dummyPassword = Hash::make(Str::random(16));

                $user = User::create([
                    'name' => $this->name,
                    'email' => $dummyEmail,
                    'password' => $dummyPassword,
                ]);
            }

            // Sincronizar el rol del área correspondiente
            $role = Role::findByName($this->role_name);
            $user->syncRoles([$role]);

            session()->flash('message', $this->userId ? 'El colaborador ha sido actualizado de manera exitosa.' : 'El nuevo colaborador ha sido registrado en el sistema.');
        } else {
            // Validaciones para Logins de Acceso
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

            // Sincronizar rol administrativo
            $role = Role::findByName($this->role_name);
            $user->syncRoles([$role]);

            session()->flash('message', $this->userId ? 'El login administrativo ha sido modificado existosamente.' : 'El nuevo acceso administrativo fue registrado.');
        }

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
        session()->flash('message', $this->activeTab === 'personal' ? 'El colaborador ha sido removido del sistema.' : 'El login administrativo ha sido eliminado.');
    }
}
