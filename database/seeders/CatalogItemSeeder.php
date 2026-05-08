<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CatalogItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $items = [
            // IPS E.MAX
            ['category' => 'IPS E.MAX (Disilicato de litio)', 'name' => 'Corona', 'price_regular' => 360.00, 'price_student' => 250.00],
            ['category' => 'IPS E.MAX (Disilicato de litio)', 'name' => 'Carilla', 'price_regular' => 360.00, 'price_student' => 250.00],
            ['category' => 'IPS E.MAX (Disilicato de litio)', 'name' => 'Fragmentos', 'price_regular' => 360.00, 'price_student' => 250.00],
            ['category' => 'IPS E.MAX (Disilicato de litio)', 'name' => 'Inlay - onlay - overlay', 'price_regular' => 360.00, 'price_student' => 250.00], // from student section

            // METAL CAD-CAM – PORCELANA
            ['category' => 'METAL CAD-CAM – PORCELANA', 'name' => 'Corona Metal- porcelana', 'price_regular' => 180.00, 'price_student' => 125.00],
            ['category' => 'METAL CAD-CAM – PORCELANA', 'name' => 'Hombro Cerámico', 'price_regular' => 40.00, 'price_student' => 20.00],
            ['category' => 'METAL CAD-CAM – PORCELANA', 'name' => 'Encia rosada', 'price_regular' => 20.00, 'price_student' => 20.00],
            ['category' => 'METAL CAD-CAM – PORCELANA', 'name' => 'Apoyo ó fresado de corona para PPR', 'price_regular' => 40.00, 'price_student' => 20.00],
            ['category' => 'METAL CAD-CAM – PORCELANA', 'name' => 'Apoyo simple Metálico', 'price_regular' => 40.00, 'price_student' => 18.00],
            ['category' => 'METAL CAD-CAM – PORCELANA', 'name' => 'Incrustacion metalica', 'price_regular' => 50.00, 'price_student' => 45.00],
            ['category' => 'METAL CAD-CAM – PORCELANA', 'name' => 'Corona completa metalica', 'price_regular' => 70.00, 'price_student' => 65.00],
            ['category' => 'METAL CAD-CAM – PORCELANA', 'name' => 'Attaches (sin matrix)', 'price_regular' => 80.00, 'price_student' => 70.00],
            ['category' => 'METAL CAD-CAM – PORCELANA', 'name' => 'Rompe fuerzas', 'price_regular' => 80.00, 'price_student' => 70.00],

            // SOLDADURA
            ['category' => 'SOLDADURA', 'name' => 'Soldadura Convencional x Pza.', 'price_regular' => 50.00, 'price_student' => 40.00],

            // ZIRCONIO - CAD-CAM
            ['category' => 'ZIRCONIO - CAD-CAM', 'name' => 'Corona con Cerámica CAD-CAM', 'price_regular' => 410.00, 'price_student' => 300.00],
            ['category' => 'ZIRCONIO - CAD-CAM', 'name' => 'Corona monolítica individual CAD-CAM', 'price_regular' => 410.00, 'price_student' => 300.00],
            ['category' => 'ZIRCONIO - CAD-CAM', 'name' => 'Encía rosada por pieza', 'price_regular' => 40.00, 'price_student' => 20.00],

            // ENCERADO
            ['category' => 'ENCERADO', 'name' => 'Encerado manual de Diagnóstico por pza', 'price_regular' => 20.00, 'price_student' => 15.00],
            ['category' => 'ENCERADO', 'name' => 'Encerado de diagnóstico por pza digital + impresion', 'price_regular' => 20.00, 'price_student' => 10.00], // student uses 'digital'
            ['category' => 'ENCERADO', 'name' => 'Encerado de diagnóstico por pza digital', 'price_regular' => 15.00, 'price_student' => 10.00],
            ['category' => 'ENCERADO', 'name' => 'Impresion hemiarcada', 'price_regular' => 35.00, 'price_student' => 35.00],
            ['category' => 'ENCERADO', 'name' => 'Impresión de modelo digital', 'price_regular' => 70.00, 'price_student' => 90.00], // student has 3D sup. e inf.
            ['category' => 'ENCERADO', 'name' => 'Impresion modelo digital con zocalo', 'price_regular' => 90.00, 'price_student' => 90.00],

            // IMPLANTES
            ['category' => 'IMPLANTES', 'name' => 'Corona de zirconio individual (CAD-CAM)', 'price_regular' => 420.00, 'price_student' => 300.00],
            ['category' => 'IMPLANTES', 'name' => 'Corona Metal Porcelana Atornillada CAD CAM CoCr (envía solo tornillo)', 'price_regular' => 250.00, 'price_student' => 250.00],
            ['category' => 'IMPLANTES', 'name' => 'Corona Metal Porcelana Cemento atornillada CAD CAM CoCr (Fresado + Corona)', 'price_regular' => 200.00, 'price_student' => 180.00],
            ['category' => 'IMPLANTES', 'name' => 'Corona Metal porcelana atornillada con calcinable', 'price_regular' => 200.00, 'price_student' => 180.00],
            ['category' => 'IMPLANTES', 'name' => 'Pilar Personalizado CAD CAM CoCr (envía solo tornillo)', 'price_regular' => 180.00, 'price_student' => 150.00],
            ['category' => 'IMPLANTES', 'name' => 'Pilar Personalizado CAD CAM CoCr más opacado', 'price_regular' => 190.00, 'price_student' => 150.00],
            ['category' => 'IMPLANTES', 'name' => 'Pilar Personalizado Colado CrNi (envía UCLA y tornillo) mas opacado', 'price_regular' => 50.00, 'price_student' => 50.00],
            ['category' => 'IMPLANTES', 'name' => 'Pilar Personalizado CAD-CAM Zirconio', 'price_regular' => 110.00, 'price_student' => 150.00],
            ['category' => 'IMPLANTES', 'name' => 'Pilar Personalizado CAD-CAM Zirconio resvestido por ceramica', 'price_regular' => 130.00, 'price_student' => 150.00],

            // IMPLANTES MULTIPLES
            ['category' => 'IMPLANTES MULTIPLES', 'name' => 'Hibrida metalica sobre 6 implantes', 'price_regular' => 3500.00, 'price_student' => 3500.00],
            ['category' => 'IMPLANTES MULTIPLES', 'name' => 'Hibrida de Zr. sobre 6 implantes', 'price_regular' => 5500.00, 'price_student' => 5500.00],

            // CEROMERO
            ['category' => 'CEROMERO', 'name' => 'Inlay - onlay- overlay', 'price_regular' => 150.00, 'price_student' => 110.00],
            ['category' => 'CEROMERO', 'name' => 'Carillas', 'price_regular' => 190.00, 'price_student' => 120.00],
            ['category' => 'CEROMERO', 'name' => 'Corona completa', 'price_regular' => 200.00, 'price_student' => 150.00],

            // MODELOS
            ['category' => 'MODELOS', 'name' => 'Modelos con implantes con gingifast', 'price_regular' => 85.00, 'price_student' => 50.00],
            ['category' => 'MODELOS', 'name' => 'Modelo troquelado', 'price_regular' => 70.00, 'price_student' => 50.00],
            ['category' => 'MODELOS', 'name' => 'Modelo de estudio', 'price_regular' => 30.00, 'price_student' => 30.00],

            // PMMA / RESINAS
            ['category' => 'PMMA', 'name' => 'Coronas PMMA', 'price_regular' => 70.00, 'price_student' => 50.00],
            ['category' => 'PMMA', 'name' => 'Ferula mioreljante', 'price_regular' => 300.00, 'price_student' => 300.00],
            ['category' => 'PMMA', 'name' => 'Resina nanohibrida', 'price_regular' => 250.00, 'price_student' => 250.00],
        ];

        foreach ($items as $item) {
            \App\Models\CatalogItem::firstOrCreate(
                ['name' => $item['name']],
                $item
            );
        }
    }
}
