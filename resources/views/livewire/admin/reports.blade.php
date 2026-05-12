<div>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-4 md:space-y-0">
            <div>
                <h2 class="font-bold text-2xl text-gray-800 leading-tight flex items-center">
                    <svg class="w-7 h-7 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    {{ __('Analíticas y Reportes de Gestión') }}
                </h2>
                <p class="text-xs text-gray-500 mt-1">Monitorea la productividad, finanzas y operaciones del laboratorio dental en tiempo real.</p>
            </div>
            
            {{-- Filtro de Periodo Global --}}
            <div class="bg-white p-1.5 rounded-xl border border-gray-200 shadow-sm flex space-x-1">
                <button wire:click="setPeriod('weekly')" class="px-4 py-2 text-xs font-bold rounded-lg transition-all {{ $period === 'weekly' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                    📅 Vista Semanal
                </button>
                <button wire:click="setPeriod('monthly')" class="px-4 py-2 text-xs font-bold rounded-lg transition-all {{ $period === 'monthly' ? 'bg-indigo-600 text-white shadow-md' : 'text-gray-600 hover:text-indigo-600 hover:bg-indigo-50/50' }}">
                    📊 Vista Mensual
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-8 bg-gray-50/50 min-h-screen font-sans">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- 1. Tarjetas de Indicadores Clave (KPIs) con Máximo Contraste y Claridad --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Facturación Total -->
                <div class="bg-white rounded-2xl shadow-xl p-6 border-l-4 border-indigo-600 transform hover:-translate-y-0.5 transition duration-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-400 text-[10px] font-black uppercase tracking-wider">Facturación Total</p>
                            <h3 class="text-2xl font-black mt-2 text-gray-900 font-mono">S/ {{ number_format($kpis['total_earnings'], 2) }}</h3>
                        </div>
                        <span class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl text-lg font-bold">
                            💵
                        </span>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-gray-400">
                        <span class="font-bold text-indigo-600 mr-1">Histórico</span> de órdenes finalizadas.
                    </div>
                </div>

                <!-- Total de Órdenes -->
                <div class="bg-white rounded-2xl shadow-xl p-6 border-l-4 border-violet-500 transform hover:-translate-y-0.5 transition duration-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-400 text-[10px] font-black uppercase tracking-wider">Órdenes Totales</p>
                            <h3 class="text-2xl font-black mt-2 text-gray-900 font-mono">{{ number_format($kpis['total_orders']) }}</h3>
                        </div>
                        <span class="p-2.5 bg-violet-50 text-violet-600 rounded-xl text-lg font-bold">
                            📦
                        </span>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-gray-400">
                        <span class="font-bold text-violet-500 mr-1">{{ number_format($kpis['completed_orders']) }}</span> completadas con éxito.
                    </div>
                </div>

                <!-- Ticket Promedio -->
                <div class="bg-white rounded-2xl shadow-xl p-6 border-l-4 border-emerald-500 transform hover:-translate-y-0.5 transition duration-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-400 text-[10px] font-black uppercase tracking-wider">Valor Promedio / OT</p>
                            <h3 class="text-2xl font-black mt-2 text-gray-900 font-mono">S/ {{ number_format($kpis['average_ticket'], 2) }}</h3>
                        </div>
                        <span class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl text-lg font-bold">
                            📈
                        </span>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-gray-400">
                        <span class="font-bold text-emerald-500 mr-1">Rendimiento medio</span> por orden.
                    </div>
                </div>

                <!-- Eficiencia de Entrega -->
                <div class="bg-white rounded-2xl shadow-xl p-6 border-l-4 border-orange-500 transform hover:-translate-y-0.5 transition duration-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-gray-400 text-[10px] font-black uppercase tracking-wider">Eficiencia de Entrega</p>
                            <h3 class="text-2xl font-black mt-2 text-gray-900 font-mono">{{ number_format($kpis['on_time_percentage'], 1) }}%</h3>
                        </div>
                        <span class="p-2.5 bg-orange-50 text-orange-600 rounded-xl text-lg font-bold">
                            ⚡
                        </span>
                    </div>
                    <div class="mt-4 flex items-center text-xs text-gray-400">
                        <span class="font-bold text-orange-500 mr-1">Entregas a tiempo</span> en clínica.
                    </div>
                </div>
            </div>

            {{-- 2. Panel Principal de Gráficos de Líneas (Producción e Ingresos) --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Gráfico de Producción -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6" wire:ignore>
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h4 class="font-black text-gray-800 text-base">Volumen de Producción</h4>
                            <p class="text-xs text-gray-400">Órdenes Creadas vs Completadas en el periodo.</p>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <canvas id="productionChart"></canvas>
                    </div>
                </div>

                <!-- Gráfico de Ingresos -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6" wire:ignore>
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h4 class="font-black text-gray-800 text-base">Ingresos y Facturación (S/.)</h4>
                            <p class="text-xs text-gray-400">Evolución de los montos facturados.</p>
                        </div>
                    </div>
                    <div class="h-80 w-full">
                        <canvas id="financialChart"></canvas>
                    </div>
                </div>
            </div>

            {{-- 3. Distribuciones y Métricas Analíticas Detalladas --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Distribución por Trabajo Protésico (Servicios más pedidos) -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 flex flex-col justify-between" wire:ignore>
                    <div>
                        <h4 class="font-black text-gray-800 text-sm uppercase tracking-wider mb-2">Trabajos Más Solicitados</h4>
                        <p class="text-xs text-gray-400 mb-6">Porcentaje de demanda según catálogo dental.</p>
                        <div class="h-56 flex items-center justify-center">
                            <canvas id="catalogChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Productividad por Área Operativa (Kanbans completados) -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 flex flex-col justify-between" wire:ignore>
                    <div>
                        <h4 class="font-black text-gray-800 text-sm uppercase tracking-wider mb-2">Productividad de Áreas</h4>
                        <p class="text-xs text-gray-400 mb-6">Total de etapas y tareas procesadas con éxito.</p>
                        <div class="h-56 flex items-center justify-center">
                            <canvas id="areasChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Distribución por Tipo de Cliente (Estudiantes vs Clínica/Natural) -->
                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 p-6 flex flex-col justify-between" wire:ignore>
                    <div>
                        <h4 class="font-black text-gray-800 text-sm uppercase tracking-wider mb-2">Segmentación de Clientes</h4>
                        <p class="text-xs text-gray-400 mb-6">Proporción de volumen de trabajo por perfil.</p>
                        <div class="h-56 flex items-center justify-center">
                            <canvas id="clientChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. Sección de Exportación de Datos (ETL) --}}
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-indigo-600 px-6 py-4 flex items-center justify-between">
                    <h3 class="text-base font-bold text-white flex items-center">
                        📥 Exportador de Reportes Analíticos (ETL)
                    </h3>
                </div>
                <div class="p-6 md:p-8">
                    <p class="text-xs text-gray-500 mb-6">
                        Selecciona un rango de fechas y descarga un conjunto de datos crudos formateado en CSV para análisis externo avanzado en Excel o PowerBI.
                    </p>
                    <form wire:submit="downloadReport" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="startDate" class="block text-xs font-bold text-gray-700 mb-1.5">Fecha de Inicio *</label>
                                <input type="date" wire:model="startDate" id="startDate" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-sm">
                                @error('startDate') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="endDate" class="block text-xs font-bold text-gray-700 mb-1.5">Fecha de Fin *</label>
                                <input type="date" wire:model="endDate" id="endDate" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full text-sm">
                                @error('endDate') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="areaId" class="block text-xs font-bold text-gray-700 mb-1.5">Área Operativa (Opcional)</label>
                                <select wire:model="areaId" id="areaId" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm w-full bg-white text-sm">
                                    <option value="">-- Todas las Áreas --</option>
                                    @foreach($areas as $area)
                                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end pt-4 border-t border-gray-100">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-5 rounded-lg text-xs shadow-md transition duration-150 ease-in-out flex items-center">
                                <svg wire:loading.remove wire:target="downloadReport" class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                <svg wire:loading wire:target="downloadReport" class="animate-spin -ml-0.5 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Descargar Reporte CSV
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    {{-- SCRIPTS DE CHART.JS CON MÁXIMA REACTIVIDAD DE EVENTOS NATIVOS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let productionChart, financialChart;

            // Datos de inicialización desde Laravel
            const initialData = @json($initialChartData);
            const catalogData = @json($catalogDistribution);
            const areaData = @json($areaProductivity);
            const clientData = @json($clientTypeDistribution);

            // ─── 1. GRÁFICO DE PRODUCCIÓN (Creadas vs Completadas) ───
            const ctxProd = document.getElementById('productionChart').getContext('2d');
            productionChart = new Chart(ctxProd, {
                type: 'line',
                data: {
                    labels: initialData.labels,
                    datasets: [
                        {
                            label: 'Órdenes Creadas',
                            data: initialData.created,
                            borderColor: '#4f46e5',
                            backgroundColor: 'rgba(79, 70, 229, 0.05)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.3
                        },
                        {
                            label: 'Órdenes Completadas',
                            data: initialData.completed,
                            borderColor: '#10b981',
                            backgroundColor: 'rgba(16, 185, 129, 0.05)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top', labels: { boxWidth: 12, font: { weight: 'bold', size: 11 } } }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // ─── 2. GRÁFICO FINANCIERO (Ingresos) ───
            const ctxFin = document.getElementById('financialChart').getContext('2d');
            financialChart = new Chart(ctxFin, {
                type: 'bar',
                data: {
                    labels: initialData.labels,
                    datasets: [{
                        label: 'Facturación (S/.)',
                        data: initialData.earnings,
                        backgroundColor: 'rgba(139, 92, 246, 0.85)',
                        hoverBackgroundColor: '#8b5cf6',
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                        x: { grid: { display: false } }
                    }
                }
            });

            // ─── 3. DISTRIBUCIÓN DE TRABAJO PROTÉSICO ───
            const ctxCat = document.getElementById('catalogChart').getContext('2d');
            new Chart(ctxCat, {
                type: 'doughnut',
                data: {
                    labels: catalogData.map(c => c.label),
                    datasets: [{
                        data: catalogData.map(c => c.total),
                        backgroundColor: ['#4f46e5', '#8b5cf6', '#10b981', '#f59e0b', '#ec4899'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 10, weight: 'bold' } } }
                    },
                    cutout: '65%'
                }
            });

            // ─── 4. PRODUCTIVIDAD DE ÁREAS ───
            const ctxAreas = document.getElementById('areasChart').getContext('2d');
            new Chart(ctxAreas, {
                type: 'bar',
                data: {
                    labels: areaData.map(a => a.label),
                    datasets: [{
                        data: areaData.map(a => a.total),
                        backgroundColor: '#6366f1',
                        borderRadius: 4
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, grid: { color: '#f3f4f6' } },
                        y: { grid: { display: false } }
                    }
                }
            });

            // ─── 5. SEGMENTACIÓN DE CLIENTES ───
            const ctxCli = document.getElementById('clientChart').getContext('2d');
            new Chart(ctxCli, {
                type: 'pie',
                data: {
                    labels: clientData.map(c => c.label),
                    datasets: [{
                        data: clientData.map(c => c.total),
                        backgroundColor: ['#10b981', '#f59e0b'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10, weight: 'bold' } } }
                    }
                }
            });

            // ─── ESCUCHAR ACTUALIZACIONES REACTIVAS NATIVAS DE WINDOW ───
            window.addEventListener('charts-updated', (event) => {
                const data = event.detail.chartData;

                // Actualizar Producción
                productionChart.data.labels = data.labels;
                productionChart.data.datasets[0].data = data.created;
                productionChart.data.datasets[1].data = data.completed;
                productionChart.update();

                // Actualizar Financiero
                financialChart.data.labels = data.labels;
                financialChart.data.datasets[0].data = data.earnings;
                financialChart.update();
            });
        });
    </script>
</div>
