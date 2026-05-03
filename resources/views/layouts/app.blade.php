<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- FullCalendar v6 CDN -->
        <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>

        <!-- Styles -->
        @livewireStyles
    </head>
    <body class="font-sans antialiased">
        <x-banner />

        <div class="min-h-screen bg-gray-100">
            @livewire('navigation-menu')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>

        @stack('modals')

        @stack('scripts')

        @livewireScripts

        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('fullcalendar', (eventsData) => ({
                    calendar: null,
                    tooltip: null,
                    init() {
                        this.calendar = new FullCalendar.Calendar(this.$el, {
                            initialView: 'dayGridMonth',
                            locale: 'es',
                            firstDay: 1,
                            height: 'auto',
                            events: eventsData,
                            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
                            buttonText: { today: 'Hoy', month: 'Mensual', week: 'Semanal', day: 'Diario' },
                            dayMaxEvents: 3,
                            moreLinkText: function(n) { return '+' + n + ' más'; },
                            eventDidMount: (info) => {
                                info.el.addEventListener('mouseenter', () => {
                                    var p = info.event.extendedProps;
                                    this.tooltip = document.createElement('div');
                                    this.tooltip.className = 'fc-event-tooltip';
                                    this.tooltip.style.position = 'fixed';
                                    this.tooltip.style.zIndex = '9999';
                                    this.tooltip.style.background = 'white';
                                    this.tooltip.style.border = '1px solid #e5e7eb';
                                    this.tooltip.style.borderRadius = '12px';
                                    this.tooltip.style.padding = '14px 16px';
                                    this.tooltip.style.boxShadow = '0 20px 25px -5px rgba(0,0,0,0.12)';
                                    this.tooltip.style.pointerEvents = 'none';
                                    this.tooltip.style.minWidth = '240px';
                                    this.tooltip.style.maxWidth = '300px';

                                    var html = '<b style="color:#4f46e5">' + p.code + '</b>';
                                    if (p.isDelayed) html += ' <span style="color:red;font-size:11px">⚠️ RETRASADA</span>';
                                    html += '<br><small>Paciente: ' + (p.patient||'—') + '<br>Doctor: Dr. ' + (p.doctor||'—');
                                    if (p.area) html += '<br>Área: ' + p.area;
                                    if (p.technician) html += '<br>Responsable: ' + p.technician;
                                    if (p.status) html += '<br>Estado: ' + p.status;
                                    if (p.priority) html += '<br>Prioridad: ' + p.priority;
                                    if (p.deliveryDate) html += '<br>Entrega: ' + p.deliveryDate;
                                    html += '</small>';

                                    this.tooltip.innerHTML = html;
                                    document.body.appendChild(this.tooltip);
                                    
                                    var r = info.el.getBoundingClientRect();
                                    this.tooltip.style.top = (r.bottom + 8) + 'px';
                                    this.tooltip.style.left = Math.min(r.left, window.innerWidth - 320) + 'px';
                                });
                                info.el.addEventListener('mouseleave', () => {
                                    if (this.tooltip) { this.tooltip.remove(); this.tooltip = null; }
                                });
                            },
                            eventClick: function(info) { 
                                info.jsEvent.preventDefault(); 
                                if(info.event.url) Livewire.navigate(info.event.url); 
                            },
                        });
                        this.calendar.render();
                    },
                    destroy() {
                        if (this.calendar) {
                            this.calendar.destroy();
                        }
                        if (this.tooltip) {
                            this.tooltip.remove();
                        }
                    }
                }));
            });
        </script>
    </body>
</html>
