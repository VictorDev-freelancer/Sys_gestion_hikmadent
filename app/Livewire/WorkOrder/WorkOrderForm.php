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

    #[Rule('required|string')]
    public string $prosthetic_type = '';

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
    public ?int $initial_area_id = null;
    public ?int $area_supervisor_id = null;
    public ?int $area_technician_id = null;

    public function mount(?int $workOrderId = null): void
    {
        $this->order_date = now()->format('Y-m-d');

        if ($workOrderId) {
            $this->workOrderId = $workOrderId;
            $workOrder = WorkOrder::with('client')->findOrFail($workOrderId);
            $this->fillFromWorkOrder($workOrder);
        }
    }

    private function fillFromWorkOrder(WorkOrder $wo): void
    {
        $this->doctor_name           = $wo->doctor_name;
        $this->clinic_name           = $wo->clinic_name ?? '';
        $this->patient_name          = $wo->patient_name;
        $this->patient_age           = $wo->patient_age;
        $this->prosthetic_type       = $wo->prosthetic_type->value;
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
            'prosthetic_type' => 'required|string',
            'quantity'       => 'required|integer|min:1',
            'priority'       => 'required|string',
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

        $data = [
            'client_id'            => $client->id,
            'doctor_name'          => $this->doctor_name,
            'clinic_name'          => $this->clinic_name ?: null,
            'patient_name'         => $this->patient_name,
            'patient_age'          => $this->patient_age,
            'prosthetic_type'      => $this->prosthetic_type,
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
        ];

        if ($this->workOrderId) {
            $workOrder = WorkOrder::findOrFail($this->workOrderId);
            $workOrder->update($data);
            session()->flash('message', 'Orden de Trabajo actualizada exitosamente.');
        } else {
            $workOrder = $service->create($data);

            // Si se seleccionó un área inicial, asignar
            if ($this->initial_area_id) {
                $area = Area::findOrFail($this->initial_area_id);
                $service->assignToArea(
                    $workOrder,
                    $area,
                    $this->area_technician_id,
                    $this->area_supervisor_id,
                );
            }

            session()->flash('message', "Orden {$workOrder->code} creada exitosamente.");
        }

        $this->redirect(route('work-orders.index'), navigate: true);
    }

    public function render()
    {
        return view('livewire.work-order.work-order-form', [
            'prostheticTypes' => ProstheticType::options(),
            'priorities'      => Priority::options(),
            'areas'           => Area::active()->ordered()->get(),
            'technicians'     => User::all(),
        ]);
    }
}
