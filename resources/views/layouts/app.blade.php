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
            // Pure JS implementation that is immune to Livewire/Alpine loading errors
            (function() {
                var tooltips = {};

                function renderCalendar(id) {
                    var el = document.getElementById(id);
                    if (el && el.dataset.init !== '1') {
                        el.dataset.init = '1';
                        var eventsData = JSON.parse(el.getAttribute('data-events') || '[]');
                        
                        var cal = new FullCalendar.Calendar(el, {
                            initialView: 'dayGridMonth',
                            locale: 'es',
                            firstDay: 1,
                            height: 'auto',
                            events: eventsData,
                            headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
                            buttonText: { today: 'Hoy', month: 'Mensual', week: 'Semanal', day: 'Diario' },
                            dayMaxEvents: 3,
                            moreLinkText: function(n) { return '+' + n + ' más'; },
                            eventDidMount: function(info) {
                                info.el.addEventListener('mouseenter', function() {
                                    var p = info.event.extendedProps;
                                    var tt = document.createElement('div');
                                    tt.className = 'fc-event-tooltip';
                                    tt.style.position = 'fixed';
                                    tt.style.zIndex = '9999';
                                    tt.style.background = 'white';
                                    tt.style.border = '1px solid #e5e7eb';
                                    tt.style.borderRadius = '12px';
                                    tt.style.padding = '14px 16px';
                                    tt.style.boxShadow = '0 20px 25px -5px rgba(0,0,0,0.12)';
                                    tt.style.pointerEvents = 'none';
                                    tt.style.minWidth = '240px';
                                    tt.style.maxWidth = '300px';

                                    var html = '<b style="color:#4f46e5">' + p.code + '</b>';
                                    if (p.isDelayed) html += ' <span style="color:red;font-size:11px">⚠️ RETRASADA</span>';
                                    html += '<br><small>Paciente: ' + (p.patient||'—') + '<br>Doctor: Dr. ' + (p.doctor||'—');
                                    if (p.area) html += '<br>Área: ' + p.area;
                                    if (p.technician) html += '<br>Responsable: ' + p.technician;
                                    if (p.status) html += '<br>Estado: ' + p.status;
                                    if (p.priority) html += '<br>Prioridad: ' + p.priority;
                                    if (p.deliveryDate) html += '<br>Entrega: ' + p.deliveryDate;
                                    html += '</small>';

                                    tt.innerHTML = html;
                                    document.body.appendChild(tt);
                                    tooltips[info.event.id || p.code] = tt;
                                    
                                    var r = info.el.getBoundingClientRect();
                                    tt.style.top = (r.bottom + 8) + 'px';
                                    tt.style.left = Math.min(r.left, window.innerWidth - 320) + 'px';
                                });
                                info.el.addEventListener('mouseleave', function() {
                                    var p = info.event.extendedProps;
                                    var tt = tooltips[info.event.id || p.code];
                                    if (tt) { tt.remove(); delete tooltips[info.event.id || p.code]; }
                                });
                            },
                            eventClick: function(info) { 
                                info.jsEvent.preventDefault(); 
                                if(info.event.url) window.location.href = info.event.url; // Fallback to normal navigation if Livewire is dead
                            },
                        });
                        cal.render();
                    }
                }

                // Continuously watch for calendar elements appearing in the DOM
                setInterval(function() {
                    if (typeof FullCalendar !== 'undefined') {
                        renderCalendar('admin-calendar');
                        renderCalendar('area-calendar');
                    }
                }, 200);

                // Clean up orphaned tooltips on scroll
                window.addEventListener('scroll', function() {
                    var els = document.querySelectorAll('.fc-event-tooltip');
                    els.forEach(function(el) { el.remove(); });
                    tooltips = {};
                }, true);
            })();
        </script>
    </body>
</html>
