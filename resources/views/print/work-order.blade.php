<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orden de Trabajo {{ $workOrder->code }}</title>
    <!-- Incluimos Tailwind temporalmente para estilos rápidos y limpios -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @page {
            size: A4;
            margin: 10mm;
        }
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background-color: white;
            color: black;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        /* Ocultar botones de la interfaz al imprimir */
        @media print {
            .no-print {
                display: none !important;
            }
        }
        .ticket-box {
            border: 2px solid #000;
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .header-title {
            text-transform: uppercase;
            font-weight: 900;
            font-size: 24px;
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px dashed #ccc;
            padding: 6px 0;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gray-100 p-8 flex justify-center" onload="window.print()">

    <div class="bg-white w-full max-w-3xl p-8 shadow-2xl rounded-lg print:shadow-none print:p-0">
        
        <!-- Controles de impresión -->
        <div class="no-print flex justify-end mb-6 space-x-4">
            <button onclick="window.history.back()" class="px-4 py-2 border border-gray-300 rounded text-gray-700 hover:bg-gray-100 font-bold transition">← Volver</button>
            <button onclick="window.print()" class="px-6 py-2 bg-blue-600 text-white rounded font-bold shadow hover:bg-blue-700 transition">🖨️ Imprimir Ahora</button>
        </div>

        <!-- Encabezado del Ticket -->
        <div class="text-center mb-6">
            <h1 class="font-black text-4xl tracking-tighter">HIKMADENT</h1>
            <p class="text-sm font-bold uppercase tracking-widest text-gray-600 mt-1">Laboratorio Dental Especializado</p>
        </div>

        <div class="ticket-box">
            <div class="header-title">Orden de Trabajo: {{ $workOrder->code }}</div>
            
            <div class="grid grid-cols-2 gap-x-12 gap-y-2 text-sm">
                <div class="info-row col-span-2 sm:col-span-1">
                    <span class="info-label">Paciente:</span>
                    <span class="info-value text-lg">{{ $workOrder->patient_name ?? '—' }}</span>
                </div>
                <div class="info-row col-span-2 sm:col-span-1">
                    <span class="info-label">Edad:</span>
                    <span class="info-value">{{ $workOrder->patient_age ? $workOrder->patient_age . ' años' : '—' }}</span>
                </div>
                <div class="info-row col-span-2 sm:col-span-1">
                    <span class="info-label">Doctor(a):</span>
                    <span class="info-value">Dr. {{ $workOrder->doctor_name ?? '—' }}</span>
                </div>
                <div class="info-row col-span-2 sm:col-span-1">
                    <span class="info-label">Consultorio:</span>
                    <span class="info-value">{{ $workOrder->clinic_name ?? '—' }}</span>
                </div>
                
                <div class="col-span-2 my-2 border-t-2 border-black"></div>

                <div class="info-row col-span-2 sm:col-span-1">
                    <span class="info-label">Tipo Protésico:</span>
                    <span class="info-value uppercase bg-gray-200 px-2 py-0.5 rounded">{{ $workOrder->prosthetic_type->label() ?? '—' }}</span>
                </div>
                <div class="info-row col-span-2 sm:col-span-1">
                    <span class="info-label">Color / Tono:</span>
                    <span class="info-value font-bold">{{ $workOrder->color ?? '—' }}</span>
                </div>
                <div class="info-row col-span-2 sm:col-span-1">
                    <span class="info-label">Cantidad:</span>
                    <span class="info-value">{{ $workOrder->quantity ?? 1 }}</span>
                </div>
                <div class="info-row col-span-2 sm:col-span-1">
                    <span class="info-label">Estado Actual:</span>
                    <span class="info-value uppercase">{{ $workOrder->status->label() ?? '—' }}</span>
                </div>

                <div class="col-span-2 my-2 border-t-2 border-black"></div>

                <div class="info-row col-span-2 sm:col-span-1">
                    <span class="info-label">Fecha de Orden:</span>
                    <span class="info-value">{{ $workOrder->order_date ? $workOrder->order_date->format('d/m/Y') : '—' }}</span>
                </div>
                <div class="info-row col-span-2 sm:col-span-1">
                    <span class="info-label text-red-600">Fecha de Entrega:</span>
                    <span class="info-value text-red-600 text-lg">{{ $workOrder->delivery_date ? $workOrder->delivery_date->format('d/m/Y') : '—' }}</span>
                </div>
            </div>
            
            @if($workOrder->specifications)
            <div class="mt-6 border-t pt-4">
                <span class="info-label block mb-2 text-base">Especificaciones Técnicas y Notas:</span>
                <p class="bg-gray-100 p-4 rounded border border-gray-300 font-mono text-sm leading-relaxed">
                    {{ $workOrder->specifications }}
                </p>
            </div>
            @endif

        </div>

        <!-- Códigos de Barras / QR Placeholder -->
        <div class="mt-8 text-center border-t-2 border-dashed border-gray-400 pt-6">
            <p class="text-xs text-gray-500 uppercase tracking-widest mb-2">Comprobante Interno de Laboratorio</p>
            <!-- Un código de barras simple usando CSS para darle el toque profesional -->
            <div class="inline-block font-mono text-4xl" style="font-family: 'Libre Barcode 39', monospace; letter-spacing: -2px;">
                *{{ $workOrder->code }}*
            </div>
            <p class="text-[10px] mt-1 text-gray-400">Escanee este código para trazabilidad rápida en el sistema.</p>
        </div>

        <div class="mt-12 flex justify-between text-xs font-bold text-gray-400 uppercase tracking-wider">
            <span>Emitido: {{ now()->format('d/m/Y H:i') }}</span>
            <span>Firmas de Conformidad: ______________</span>
        </div>

    </div>
</body>
</html>
