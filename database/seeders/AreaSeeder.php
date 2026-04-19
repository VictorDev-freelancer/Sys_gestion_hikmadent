<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Area;
use App\Models\AreaStage;

/**
 * Class AreaSeeder
 *
 * [SOLID - OCP] Siembra las 7 áreas operativas de HIKMADENT
 * junto con sus etapas/checklist específicas según el prompt.
 *
 * Cada etapa es un item de checklist: ¿se completó?, ¿quién?, ¿cuándo?
 * NO se suben archivos 3D — solo se verifica cumplimiento.
 */
class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areasData = $this->getAreasData();

        foreach ($areasData as $index => $areaData) {
            $area = Area::firstOrCreate(
                ['slug' => $areaData['slug']],
                [
                    'name'          => $areaData['name'],
                    'display_order' => $index + 1,
                    'color'         => $areaData['color'],
                    'icon'          => $areaData['icon'],
                    'is_active'     => true,
                ]
            );

            foreach ($areaData['stages'] as $stageIndex => $stageName) {
                AreaStage::firstOrCreate(
                    [
                        'area_id' => $area->id,
                        'name'    => $stageName,
                    ],
                    [
                        'display_order'     => $stageIndex + 1,
                        'estimated_minutes' => $areaData['estimated_minutes'][$stageIndex] ?? 30,
                        'is_required'       => true,
                    ]
                );
            }
        }
    }

    /**
     * Datos de las 7 áreas con sus etapas del prompt.
     */
    private function getAreasData(): array
    {
        return [
            /* ---------------------------------------------------------- */
            /* 1. ADMINISTRACIÓN                                           */
            /* ---------------------------------------------------------- */
            [
                'name'  => 'Administración',
                'slug'  => 'administracion',
                'color' => '#6366F1', // Indigo
                'icon'  => 'building-office',
                'stages' => [
                    'Recepción del cliente',
                    'Registro de datos del paciente',
                    'Selección de tipo de trabajo protésico',
                    'Registro de especificaciones y color',
                    'Asignación de TPD responsable',
                    'Generación de Orden de Trabajo',
                    'Asignación a área de servicio',
                    'Seguimiento y control',
                    'Confirmación de entrega final',
                ],
                'estimated_minutes' => [10, 10, 5, 10, 5, 5, 5, 15, 10],
            ],

            /* ---------------------------------------------------------- */
            /* 2. IMPRESIÓN                                                */
            /* ---------------------------------------------------------- */
            [
                'name'  => 'Impresión',
                'slug'  => 'impresion',
                'color' => '#EC4899', // Pink
                'icon'  => 'printer',
                'stages' => [
                    'Recepción del trabajo',
                    'Validación inicial',
                    'Preparación del modelo',
                    'Vaciado del yeso',
                    'Fraguado',
                    'Post-procesado',
                    'Identificación del trabajo',
                    'Priorización de trabajos',
                    'Registro del trabajo',
                    'Transferencia a la siguiente área',
                    'Excepciones del proceso',
                ],
                'estimated_minutes' => [10, 15, 20, 30, 45, 20, 5, 10, 5, 5, 15],
            ],

            /* ---------------------------------------------------------- */
            /* 3. YESO                                                     */
            /* ---------------------------------------------------------- */
            [
                'name'  => 'Yeso',
                'slug'  => 'yeso',
                'color' => '#F59E0B', // Amber
                'icon'  => 'cube',
                'stages' => [
                    'Recepción de archivos',
                    'Organización de trabajos',
                    'Preparación de impresión',
                    'Registro de trabajos impresos',
                    'Proceso de impresión',
                    'Post-procesado — Lavado',
                    'Post-procesado — Fotocurado',
                    'Identificación de piezas',
                    'Control de errores',
                    'Control de insumos y mantenimiento',
                    'Entrega del trabajo',
                ],
                'estimated_minutes' => [10, 10, 15, 5, 60, 20, 15, 10, 15, 10, 5],
            ],

            /* ---------------------------------------------------------- */
            /* 4. DIGITAL                                                  */
            /* ---------------------------------------------------------- */
            [
                'name'  => 'Digital',
                'slug'  => 'digital',
                'color' => '#3B82F6', // Blue
                'icon'  => 'computer-desktop',
                'stages' => [
                    'Ingreso de doctor y paciente',
                    'Escaneo intraoral',
                    'Diseño en Exocad (software externo — verificar cumplimiento)',
                    'Modelo físico',
                ],
                'estimated_minutes' => [10, 30, 120, 30],
            ],

            /* ---------------------------------------------------------- */
            /* 5. FRESADO                                                  */
            /* ---------------------------------------------------------- */
            [
                'name'  => 'Fresado',
                'slug'  => 'fresado',
                'color' => '#10B981', // Emerald
                'icon'  => 'cog-6-tooth',
                'stages' => [
                    'Carga inicial (15 min)',
                    'Selección de impresora (PMMA / Zirconio / General)',
                    'Carga del archivo',
                    'Carga de materia prima (Zirconio, PMMA, EMAX, Resina)',
                    'Proceso de impresión o fresado',
                    'Secado o sintetizado',
                    'Transferencia a Inyectado y Adaptación',
                ],
                'estimated_minutes' => [15, 5, 10, 10, 90, 60, 5],
            ],

            /* ---------------------------------------------------------- */
            /* 6. INYECTADO Y ADAPTACIÓN                                   */
            /* ---------------------------------------------------------- */
            [
                'name'  => 'Inyectado y Adaptación',
                'slug'  => 'inyectado-adaptacion',
                'color' => '#8B5CF6', // Violet
                'icon'  => 'wrench-screwdriver',
                'stages' => [
                    'Encerado de patrón',
                    'Bebederos y revestimiento',
                    'Fraguado',
                    'Eliminación de cera',
                    'Prensado de material',
                    'Enfriamiento',
                    'Arenado',
                    'Escaneo intraoral',
                    'Ajuste y adaptación',
                ],
                'estimated_minutes' => [30, 20, 45, 20, 30, 30, 15, 30, 45],
            ],

            /* ---------------------------------------------------------- */
            /* 7. CERÁMICA                                                 */
            /* ---------------------------------------------------------- */
            [
                'name'  => 'Cerámica',
                'slug'  => 'ceramica',
                'color' => '#EF4444', // Red
                'icon'  => 'fire',
                'stages' => [
                    'Maquillaje — Diseño (estratificado o monolítico)',
                    'Coronas y/o estratificación',
                    'Ajustes de forma o adaptación',
                    'Ajustes de textura o altura',
                    'Revisión de calidad (si falla → regresa a Adaptación)',
                    'Tiempo de finalización (3-4 hrs aprox.)',
                ],
                'estimated_minutes' => [60, 60, 30, 30, 20, 30],
            ],
        ];
    }
}
