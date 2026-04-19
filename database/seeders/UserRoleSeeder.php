<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Class UserRoleSeeder
 *
 * [ARQUITECTURA SOLID Y CLEAN CODE]
 * ---------------------------------
 * Roles ALINEADOS con las 7 áreas del prompt de HIKMADENT:
 * Administración, Impresión, Yeso, Digital, Fresado,
 * Inyectado y Adaptación, Cerámica.
 *
 * + Rol adicional: Super usuario (administrador del sistema).
 */
class UserRoleSeeder extends Seeder
{
    /**
     * [Clean Code]: Constante para evitar "Magic Strings".
     */
    private const DEFAULT_PASSWORD = 'Hikmadent123+';

    /**
     * Función principal — Orquestador.
     */
    public function run(): void
    {
        $this->initializeRoles();
        $this->registerUsersAndAssignRoles();
    }

    /**
     * [SRP]: Roles alineados a las 7 áreas del prompt.
     */
    private function initializeRoles(): void
    {
        $roles = [
            'Super usuario',
            'Administración',
            'Impresión',
            'Yeso',
            'Digital',
            'Fresado',
            'Inyectado y Adaptación',
            'Cerámica',
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    /**
     * [SRP]: Registro de usuarios con roles alineados al prompt.
     */
    private function registerUsersAndAssignRoles(): void
    {
        $usersData = $this->getUsersData();

        foreach ($usersData as $userData) {
            $user = $this->createUser($userData['name']);
            $user->syncRoles([$userData['role']]);
        }
    }

    /**
     * [SRP]: Repositorio estático de datos iniciales.
     * Roles actualizados según las áreas del prompt.
     */
    private function getUsersData(): array
    {
        return [
            // Administración
            ['name' => 'Zully Manrique',         'role' => 'Administración'],
            ['name' => 'Betty Vasquez',           'role' => 'Administración'],
            ['name' => 'Gianella Ocmin',          'role' => 'Administración'],
            ['name' => 'Ricardo Sangama',         'role' => 'Administración'],

            // Super usuario
            ['name' => 'Wilfredo Chuquizuta',     'role' => 'Super usuario'],

            // Impresión (antes era "Fresado, impresión")
            ['name' => 'Marck Chuquizuta',        'role' => 'Impresión'],

            // Fresado (separado de Impresión)
            // Nota: Marck también puede operar en Fresado — asignar doble rol si es necesario

            // Inyectado y Adaptación (alineado con el prompt)
            ['name' => 'Elvis Andrade',           'role' => 'Inyectado y Adaptación'],

            // Cerámica
            ['name' => 'Prudencia Tacuchi',       'role' => 'Cerámica'],
            ['name' => 'Carlos Calderón',         'role' => 'Cerámica'],
            ['name' => 'Morelia Alburqueque',      'role' => 'Cerámica'],
            ['name' => 'Melany Cuchuñaupa',        'role' => 'Cerámica'],

            // Digital (antes "Diseño")
            ['name' => 'Luis Tarrillo',           'role' => 'Digital'],
            ['name' => 'Janeth Astopillo',        'role' => 'Digital'],
            ['name' => 'Aldair Medina',           'role' => 'Digital'],

            // Yeso
            ['name' => 'Erick Esquivel',          'role' => 'Yeso'],
        ];
    }

    /**
     * [SRP]: Persistencia de usuario individual.
     */
    private function createUser(string $fullName): User
    {
        return User::firstOrCreate(
            ['email' => $this->generateEmailFromName($fullName)],
            [
                'name'     => $fullName,
                'password' => Hash::make(self::DEFAULT_PASSWORD),
            ]
        );
    }

    /**
     * [SRP]: Generación de email a partir del nombre.
     * "Carlos Calderón" → "carlos.calderon@hikmadent.com"
     */
    private function generateEmailFromName(string $fullName): string
    {
        $normalizedName = Str::ascii($fullName);
        $dotSeparatedName = str_replace(' ', '.', trim($normalizedName));

        return strtolower($dotSeparatedName) . '@hikmadent.com';
    }
}
