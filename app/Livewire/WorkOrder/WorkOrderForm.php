<?php

namespace App\Livewire\WorkOrder;

use App\Enums\Priority;
use App\Enums\ProstheticType;
use App\Models\Area;
use App\Models\Client;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;

/**
 * Livewire WorkOrderForm
 *
 * [SOLID - SRP] Formulario de creación/edición de Órdenes de Trabajo.
 * Registra todos los campos del prompt: Dr.(a), Paciente, Edad,
 * Tipo protésico, Especificaciones, Color, etc.
 */
#[Layout('layouts.app')]
class WorkOrderForm extends Component
{
    // Identificador para edición
    public ?int $workOrderId = null;

    // Datos del cliente
    public string $client_name = '';
    public string $client_phone = '';
    public string $client_email = '';

    // Datos de la orden
    #[Rule('required|string|max:255')]
    public string $doctor_name = '';

    public string $clinic_name = '';

    #[Rule('required|string|max:255')]
    public string $patient_name = '';

    public ?int $patient_age = null;

    #[Rule('required|in:regular,student')]
    public string $client_type = 'regular';

    #[Rule('required|exists:catalog_items,id')]
    public ?int $catalog_item_id = null;

    public float $unit_price = 0;
    public float $total_price = 0;

    public string $specifications = '';
    public string $color = '';

    #[Rule('required|integer|min:1')]
    public int $quantity = 1;

    public string $final_work_type = '';

    #[Rule('required|string')]
    public string $priority = 'normal';

    // Fechas
    public ?string $order_date = null;
    public ?string $technical_send_date = null;
    public ?string $clinic_delivery_date = null;
    public ?string $delivery_date = null;

    // Asignaciones
    public ?int $assigned_tpd_id = null;
    // Trabajos extras
    public array $extra_works = [];

    // Ruta Planificada
    public array $planned_route = [];

    public function updatedClientType()
    {
        $this->calculatePrice();
    }

    public function updatedCatalogItemId()
    {
        $this->calculatePrice();
    }

    public function updatedQuantity()
    {
        $this->calculatePrice();
    }

    public function updatedExtraWorks()
    {
        $this->calculatePrice();
    }

    public function addExtraWork(): void
    {
        $this->extra_works[] = [
            'description' => '',
            'price' => 0
        ];
    }

    public function removeExtraWork(int $index): void
    {
        unset($this->extra_works[$index]);
        $this->extra_works = array_values($this->extra_works);
        $this->calculatePrice();
    }

    private function calculatePrice()
    {
        $baseTotal = 0;
        if ($this->catalog_item_id) {
            $item = \App\Models\CatalogItem::find($this->catalog_item_id);
            if ($item) {
                $this->unit_price = $this->client_type === 'student' ? $item->price_student : $item->price_regular;
                $baseTotal = $this->unit_price * $this->quantity;
            }
        } else {
            $this->unit_price = 0;
        }

        $extrasTotal = 0;
        foreach ($this->extra_works as $extra) {
            $extrasTotal += floatval($extra['price'] ?? 0);
        }

        $this->total_price = $baseTotal + $extrasTotal;
    }

    public function mount(?int $workOrderId = null): void
    {
        $this->order_date = now()->format('Y-m-d');

        if ($workOrderId) {
            $this->workOrderId = $workOrderId;
            $workOrder = WorkOrder::with('client')->findOrFail($workOrderId);
            $this->fillFromWorkOrder($workOrder);
        } else {
            // Iniciar con un paso vacío por defecto
            $this->addRouteStep();
        }
    }

    public function addRouteStep(): void
    {
        $this->planned_route[] = [
            'area_id' => '',
            'technician_id' => ''
        ];
    }

    public function removeRouteStep(int $index): void
    {
        unset($this->planned_route[$index]);
        $this->planned_route = array_values($this->planned_route);
    }

    private function fillFromWorkOrder(WorkOrder $wo): void
    {
        $this->doctor_name           = $wo->doctor_name;
        $this->clinic_name           = $wo->clinic_name ?? '';
        $this->patient_name          = $wo->patient_name;
        $this->patient_age           = $wo->patient_age;
        $this->client_type           = $wo->client_type ?? 'regular';
        $this->catalog_item_id       = $wo->catalog_item_id;
        $this->unit_price            = (float) $wo->unit_price;
        $this->total_price           = (float) $wo->total_price;
        $this->specifications        = $wo->specifications ?? '';
        $this->color                 = $wo->color ?? '';
        $this->quantity              = $wo->quantity;
        $this->final_work_type       = $wo->final_work_type ?? '';
        $this->priority              = $wo->priority->value;
        $this->order_date            = $wo->order_date?->format('Y-m-d');
        $this->technical_send_date   = $wo->technical_send_date?->format('Y-m-d');
        $this->clinic_delivery_date  = $wo->clinic_delivery_date?->format('Y-m-d');
        $this->delivery_date         = $wo->delivery_date?->format('Y-m-d');
        $this->assigned_tpd_id       = $wo->assigned_tpd_id;
        $this->planned_route         = $wo->planned_route ?? [];
        $this->extra_works           = $wo->extra_works ?? [];

        if ($wo->client) {
            $this->client_name  = $wo->client->name;
            $this->client_phone = $wo->client->phone ?? '';
            $this->client_email = $wo->client->email ?? '';
        }
    }

    public function save(): void
    {
        $this->validate([
            'doctor_name'    => 'required|string|max:255',
            'patient_name'   => 'required|string|max:255',
            'client_type'    => 'required|in:regular,student',
            'catalog_item_id'=> 'required|exists:catalog_items,id',
            'quantity'       => 'required|integer|min:1',
            'priority'       => 'required|string',
            'planned_route.*.area_id' => 'required_with:planned_route.*.technician_id',
            'extra_works.*.description' => 'required|string|max:255',
            'extra_works.*.price'       => 'required|numeric|min:0',
        ]);

        $service = app(WorkOrderService::class);

        // Crear o encontrar el cliente
        $client = Client::firstOrCreate(
            ['name' => $this->client_name ?: $this->doctor_name],
            [
                'phone'       => $this->client_phone ?: null,
                'email'       => $this->client_email ?: null,
                'doctor_name' => $this->doctor_name,
                'clinic_name' => $this->clinic_name ?: null,
            ]
        );

        // Limpiar pasos vacíos en la ruta planificada
        $cleanRoute = array_values(array_filter($this->planned_route, fn($step) => !empty($step['area_id'])));

        // Limpiar trabajos extras vacíos
        $cleanExtras = array_values(array_filter($this->extra_works, fn($extra) => !empty(trim($extra['description']))));

        $data = [
            'client_id'            => $client->id,
            'client_type'          => $this->client_type,
            'doctor_name'          => $this->doctor_name,
            'clinic_name'          => $this->clinic_name ?: null,
            'patient_name'         => $this->patient_name,
            'patient_age'          => $this->patient_age,
            'catalog_item_id'      => $this->catalog_item_id,
            'unit_price'           => $this->unit_price,
            'total_price'          => $this->total_price,
            'prosthetic_type'      => 'otro', // Fallback para evitar errores con ENUM en BD
            'specifications'       => $this->specifications ?: null,
            'color'                => $this->color ?: null,
            'quantity'             => $this->quantity,
            'final_work_type'      => $this->final_work_type ?: null,
            'priority'             => $this->priority,
            'order_date'           => $this->order_date,
            'technical_send_date'  => $this->technical_send_date ?: null,
            'clinic_delivery_date' => $this->clinic_delivery_date ?: null,
            'delivery_date'        => $this->delivery_date ?: null,
            'assigned_tpd_id'      => $this->assigned_tpd_id,
            'planned_route'        => empty($cleanRoute) ? null : $cleanRoute,
            'extra_works'          => empty($cleanExtras) ? null : $cleanExtras,
        ];

        if ($this->workOrderId) {
            $workOrder = WorkOrder::findOrFail($this->workOrderId);
            $workOrder->update($data);
            session()->flash('message', 'Orden de Trabajo actualizada exitosamente.');
        } else {
            $workOrder = $service->create($data);

            // Si se definió una ruta planificada, transferir al primer paso automáticamente
            if (!empty($cleanRoute)) {
                $firstStep = $cleanRoute[0];
                $area = Area::findOrFail($firstStep['area_id']);
                $techId = !empty($firstStep['technician_id']) ? $firstStep['technician_id'] : null;
                
                $service->assignToArea(
                    $workOrder,
                    $area,
                    $techId,
                    null // Supervisor (puede ser manejado por el servicio o null por ahora)
                );
            }

            session()->flash('message', "Orden {$workOrder->code} creada exitosamente.");
        }

        $this->redirect(route('work-orders.index'), navigate: true);
    }

    public function render()
    {
        $catalogItems = \App\Models\CatalogItem::where('is_active', true)
            ->orderBy('category')
            ->orderBy('name')
            ->get()
            ->groupBy('category');

        return view('livewire.work-order.work-order-form', [
            'prostheticTypes' => ProstheticType::options(),
            'priorities'      => Priority::options(),
            'areas'           => Area::active()->ordered()->get(),
            'technicians'     => User::all(),
            'catalogItems'    => $catalogItems,
        ]);
    }
}
