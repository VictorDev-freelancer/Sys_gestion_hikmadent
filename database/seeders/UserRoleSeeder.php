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
 * Este seeder reemplaza la mala práctica del "Código Espagueti" aplicando 
 * estrictamente el Principio de Responsabilidad Única (SRP de SOLID).
 * 
 * En lugar de usar un método complejo `run()` de 80 líneas que hace todo, 
 * delegamos cada tarea en su propia función privada atómica e independiente.
 * Esto hace el código:
 * 1. Altamente testeable.
 * 2. Escalable (fácil de modificar sin romper lo demás).
 * 3. Auto-documentado.
 */
class UserRoleSeeder extends Seeder
{
    /**
     * [Clean Code]: Evitar los "Magic Numbers/Strings". 
     * Centralizamos la contraseña temporal en una constante en lugar de 
     * escribir 'Hikmadent123+' flotando repetitivamente por todo el código.
     */
    private const DEFAULT_PASSWORD = 'Hikmadent123+';

    /**
     * Función principal. 
     * Actúa simplemente como "Director de Orquesta" llamando a las funciones
     * delegadas sin ensuciarse manejando los datos crudos.
     */
    public function run(): void
    {
        $this->initializeRoles();
        $this->registerUsersAndAssignRoles();
    }

    /**
     * [SRP]: Responsabilidad #1 -> Orquestar y Persistir los Roles base del sistema.
     * Iteramos sobre un arreglo controlado y nos aseguramos usando `firstOrCreate`
     * de no duplicar información en caso el seeder se corra múltiples veces por error.
     */
    private function initializeRoles(): void
    {
        $roles = [
            'Administración',
            'Super usuario',
            'Fresado, impresión',
            'Adaptación',
            'Cerámica',
            'Inyectado y cerámica',
            'Diseño',
            'Yeso'
        ];

        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }
    }

    /**
     * [SRP]: Responsabilidad #2 -> Delegar el registro y enlazado de Usuarios.
     * Lee la lista estructurada de datos y ejecuta la capa de lógica para
     * emparejar un Usuario (User) con su respectiva Autorización (Role).
     */
    private function registerUsersAndAssignRoles(): void
    {
        $usersData = $this->getUsersData();

        foreach ($usersData as $userData) {
            // Delega la persistencia al método especializado createUser()
            $user = $this->createUser($userData['name']);
            
            // Usamos el helper de Spatie para adjuntar el permiso al usuario
            $user->assignRole($userData['role']);
        }
    }

    /**
     * [SRP]: Responsabilidad #3 -> Proveer Repositorio Estático.
     * [Clean Code]: Extraer los datos crudos del flujo permite mantener 
     * las rutinas iterativas (los ciclos foreach) limpias y breves.
     */
    private function getUsersData(): array
    {
        return [
            ['name' => 'Zully Manrique', 'role' => 'Administración'],
            ['name' => 'Betty Vasquez', 'role' => 'Administración'],
            ['name' => 'Gianella Ocmin', 'role' => 'Administración'],
            ['name' => 'Ricardo Sangama', 'role' => 'Administración'],
            ['name' => 'Wilfredo Chuquizuta', 'role' => 'Super usuario'],
            ['name' => 'Marck Chuquizuta', 'role' => 'Fresado, impresión'],
            ['name' => 'Elvis Andrade', 'role' => 'Adaptación'],
            ['name' => 'Prudencia Tacuchi', 'role' => 'Cerámica'],
            ['name' => 'Carlos Calderón', 'role' => 'Cerámica'],
            ['name' => 'Morelia Alburqueque', 'role' => 'Cerámica'],
            ['name' => 'Melany Cuchuñaupa', 'role' => 'Inyectado y cerámica'],
            ['name' => 'Luis Tarrillo', 'role' => 'Diseño'],
            ['name' => 'Janeth Astopillo', 'role' => 'Diseño'],
            ['name' => 'Aldair Medina', 'role' => 'Diseño'],
            ['name' => 'Erick Esquivel', 'role' => 'Yeso'],
        ];
    }

    /**
     * [SRP]: Responsabilidad #4 -> Inyección Directa en DB de los Usuarios.
     * Este método ignora todo el contexto, su única labor es recibir un String 
     * y devolver un modelo 'User'. Esto aísla el manejo de `Hash` de la contraseña.
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
     * [SRP]: Responsabilidad #5 -> Lógica de Negocio de Estructura de Strings.
     * [Clean Code]: Se aísla cualquier manipulación algorítmica. Automáticamente
     * convierte "Carlos Calderón" en -> "carlos.calderon@hikmadent.com".
     */
    private function generateEmailFromName(string $fullName): string
    {
        // 1. Quitar tildes (Str::ascii remueve acentos: Calderón -> Calderon)
        $normalizedName = Str::ascii($fullName);
        
        // 2. Reemplazar espacios por puntos. (Carlos Calderon -> Carlos.Calderon)
        $dotSeparatedName = str_replace(' ', '.', trim($normalizedName));
        
        // 3. Convertir todo a minúsculas y añadir el dominio.
        return strtolower($dotSeparatedName) . '@hikmadent.com';
    }
}
