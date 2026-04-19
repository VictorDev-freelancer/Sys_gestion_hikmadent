<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * [FASE 1] Tabla `work_orders` — Órdenes de Trabajo.
 *
 * Modelo central del sistema con todos los campos especificados
 * en el prompt: Fecha, Dr.(a), Paciente, Edad, Tipo protésico,
 * Especificaciones, Color, Cantidad, TPD responsable, etc.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();

            // Código único auto-generado: OT-YYYYMMDD-0001
            $table->string('code', 20)->unique();

            // Relaciones principales
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_tpd_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('current_area_id')->nullable()->constrained('areas')->nullOnDelete();

            // Datos de la ficha de trabajo (del prompt)
            $table->string('doctor_name');                        // Dr.(a) - Consultorio
            $table->string('clinic_name')->nullable();            // Consultorio
            $table->string('patient_name');                       // Paciente
            $table->unsignedSmallInteger('patient_age')->nullable(); // Edad
            $table->string('prosthetic_type', 30);                // Tipo de trabajo protésico (Enum)
            $table->text('specifications')->nullable();           // Especificaciones
            $table->string('color', 50)->nullable();              // Color
            $table->unsignedSmallInteger('quantity')->default(1); // Cantidad
            $table->string('final_work_type')->nullable();        // Tipo de trabajo final

            // Estados
            $table->string('status', 20)->default('draft');       // WorkOrderStatus Enum
            $table->string('kanban_status', 20)->default('pending'); // KanbanStatus Enum
            $table->string('priority', 10)->default('normal');    // Priority Enum

            // Fechas del prompt
            $table->date('order_date')->nullable();               // Fecha de la orden
            $table->date('technical_send_date')->nullable();      // Fecha de Envío técnico
            $table->date('clinic_delivery_date')->nullable();     // Fecha de Entrega en clínica
            $table->date('delivery_date')->nullable();            // Fecha de entrega final

            $table->timestamps();

            // Índices para consultas frecuentes
            $table->index('status');
            $table->index('kanban_status');
            $table->index('priority');
            $table->index('order_date');
            $table->index(['status', 'current_area_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
