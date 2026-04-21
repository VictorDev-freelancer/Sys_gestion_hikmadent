<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>HIKMADENT — Sistema de Gestión</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        <style>
            /* ═══════════════════════════════════════════
               HIKMADENT — Welcome Page Styles
               Sistema Cerrado de Gestión Interna
            ═══════════════════════════════════════════ */

            :root {
                --hk-primary: #0e4d6e;
                --hk-primary-light: #1a6d9a;
                --hk-accent: #32b8d4;
                --hk-accent-glow: rgba(50, 184, 212, 0.35);
                --hk-dark: #0a1628;
                --hk-dark-card: #0f1f35;
                --hk-dark-surface: #132841;
                --hk-text: #e4eaf2;
                --hk-text-muted: #8fa3bc;
                --hk-border: rgba(50, 184, 212, 0.15);
                --hk-gradient-start: #081220;
                --hk-gradient-mid: #0c1e33;
                --hk-gradient-end: #0a1628;
            }

            *,
            *::before,
            *::after {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }

            html {
                scroll-behavior: smooth;
            }

            body {
                font-family: 'Inter', system-ui, -apple-system, sans-serif;
                background: var(--hk-dark);
                color: var(--hk-text);
                min-height: 100vh;
                overflow-x: hidden;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                position: relative;
            }

            /* ─── Animated Background ─── */
            .bg-canvas {
                position: fixed;
                inset: 0;
                z-index: 0;
                background:
                    radial-gradient(ellipse 80% 50% at 50% -20%, rgba(14, 77, 110, 0.25), transparent),
                    radial-gradient(ellipse 60% 40% at 80% 100%, rgba(50, 184, 212, 0.1), transparent),
                    radial-gradient(ellipse 50% 60% at 10% 90%, rgba(14, 77, 110, 0.12), transparent),
                    linear-gradient(180deg, var(--hk-gradient-start), var(--hk-gradient-mid), var(--hk-gradient-end));
            }

            /* Floating orbs */
            .orb {
                position: fixed;
                border-radius: 50%;
                filter: blur(80px);
                opacity: 0.3;
                animation: orbFloat 20s ease-in-out infinite;
                z-index: 0;
            }
            .orb--1 {
                width: 400px;
                height: 400px;
                background: var(--hk-primary);
                top: -100px;
                right: -100px;
                animation-delay: 0s;
            }
            .orb--2 {
                width: 300px;
                height: 300px;
                background: var(--hk-accent);
                bottom: -80px;
                left: -80px;
                animation-delay: -7s;
                opacity: 0.15;
            }
            .orb--3 {
                width: 200px;
                height: 200px;
                background: #1a6d9a;
                top: 50%;
                left: 60%;
                animation-delay: -14s;
                opacity: 0.12;
            }

            @keyframes orbFloat {
                0%, 100% { transform: translate(0, 0) scale(1); }
                25%      { transform: translate(30px, -40px) scale(1.05); }
                50%      { transform: translate(-20px, 20px) scale(0.95); }
                75%      { transform: translate(15px, 35px) scale(1.02); }
            }

            /* Subtle grid pattern */
            .bg-grid {
                position: fixed;
                inset: 0;
                z-index: 0;
                background-image:
                    linear-gradient(rgba(50, 184, 212, 0.03) 1px, transparent 1px),
                    linear-gradient(90deg, rgba(50, 184, 212, 0.03) 1px, transparent 1px);
                background-size: 60px 60px;
                mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black 30%, transparent 80%);
                -webkit-mask-image: radial-gradient(ellipse 70% 70% at 50% 50%, black 30%, transparent 80%);
            }

            /* ─── Main Container ─── */
            .welcome-wrapper {
                position: relative;
                z-index: 1;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 2rem 1.5rem;
                width: 100%;
                max-width: 720px;
                margin: 0 auto;
            }

            /* ─── Typographic Logo ─── */
            .brand-logo {
                margin-bottom: 2.5rem;
                animation: fadeSlideUp 1s ease-out;
                text-align: center;
            }

            .brand-logo .brand-name {
                font-family: 'Outfit', sans-serif;
                font-size: 2.8rem;
                font-weight: 800;
                letter-spacing: 0.06em;
                background: linear-gradient(135deg, #ffffff, var(--hk-accent));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                line-height: 1;
            }

            .brand-logo .brand-tagline {
                font-size: 0.7rem;
                font-weight: 500;
                text-transform: uppercase;
                letter-spacing: 0.25em;
                color: var(--hk-text-muted);
                margin-top: 0.4rem;
            }

            /* ─── Glass Card ─── */
            .glass-card {
                background: rgba(15, 31, 53, 0.6);
                backdrop-filter: blur(24px);
                -webkit-backdrop-filter: blur(24px);
                border: 1px solid var(--hk-border);
                border-radius: 24px;
                padding: 3rem 2.5rem;
                width: 100%;
                text-align: center;
                box-shadow:
                    0 4px 24px rgba(0, 0, 0, 0.25),
                    0 0 0 1px rgba(50, 184, 212, 0.05) inset,
                    0 1px 0 rgba(255, 255, 255, 0.03) inset;
                animation: fadeSlideUp 1s ease-out 0.15s both;
                position: relative;
                overflow: hidden;
            }

            /* Shimmer accent line at top of card */
            .glass-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 50%;
                transform: translateX(-50%);
                width: 60%;
                height: 2px;
                background: linear-gradient(90deg, transparent, var(--hk-accent), transparent);
                border-radius: 2px;
                animation: shimmer 3s ease-in-out infinite;
            }

            @keyframes shimmer {
                0%, 100% { opacity: 0.4; width: 40%; }
                50%      { opacity: 1; width: 70%; }
            }

            /* ─── Typography ─── */
            .welcome-badge {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                background: rgba(50, 184, 212, 0.1);
                border: 1px solid rgba(50, 184, 212, 0.2);
                border-radius: 100px;
                padding: 0.4rem 1.2rem;
                font-size: 0.75rem;
                font-weight: 600;
                color: var(--hk-accent);
                text-transform: uppercase;
                letter-spacing: 0.12em;
                margin-bottom: 1.5rem;
                animation: fadeSlideUp 1s ease-out 0.3s both;
            }

            .welcome-badge .badge-dot {
                width: 6px;
                height: 6px;
                border-radius: 50%;
                background: var(--hk-accent);
                animation: blink 2s ease-in-out infinite;
            }

            @keyframes blink {
                0%, 100% { opacity: 1; }
                50%      { opacity: 0.3; }
            }

            .welcome-title {
                font-family: 'Outfit', sans-serif;
                font-size: 2.2rem;
                font-weight: 700;
                line-height: 1.2;
                margin-bottom: 0.75rem;
                background: linear-gradient(135deg, #ffffff, var(--hk-accent));
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
                animation: fadeSlideUp 1s ease-out 0.4s both;
            }

            .welcome-subtitle {
                font-size: 1rem;
                font-weight: 400;
                color: var(--hk-text-muted);
                line-height: 1.65;
                margin-bottom: 2rem;
                max-width: 480px;
                margin-left: auto;
                margin-right: auto;
                animation: fadeSlideUp 1s ease-out 0.5s both;
            }

            .welcome-subtitle strong {
                color: var(--hk-text);
                font-weight: 600;
            }

            /* ─── Divider ─── */
            .divider {
                width: 100%;
                height: 1px;
                background: linear-gradient(90deg, transparent, var(--hk-border), transparent);
                margin: 0.5rem 0 2rem;
                animation: fadeSlideUp 1s ease-out 0.55s both;
            }

            /* ─── Area Badges ─── */
            .areas-section {
                margin-bottom: 2rem;
                animation: fadeSlideUp 1s ease-out 0.6s both;
            }

            .areas-label {
                font-size: 0.7rem;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.15em;
                color: var(--hk-text-muted);
                margin-bottom: 0.85rem;
            }

            .areas-grid {
                display: flex;
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.5rem;
            }

            .area-chip {
                display: inline-flex;
                align-items: center;
                gap: 0.35rem;
                padding: 0.35rem 0.85rem;
                border-radius: 100px;
                font-size: 0.72rem;
                font-weight: 500;
                color: var(--hk-text-muted);
                background: rgba(255, 255, 255, 0.03);
                border: 1px solid rgba(255, 255, 255, 0.06);
                transition: all 0.3s ease;
            }

            .area-chip:hover {
                background: rgba(50, 184, 212, 0.08);
                border-color: rgba(50, 184, 212, 0.25);
                color: var(--hk-accent);
                transform: translateY(-1px);
            }

            .area-chip .chip-icon {
                font-size: 0.85rem;
                line-height: 1;
            }

            /* ─── Auth Buttons ─── */
            .auth-actions {
                display: flex;
                gap: 1rem;
                justify-content: center;
                flex-wrap: wrap;
                animation: fadeSlideUp 1s ease-out 0.7s both;
            }

            .btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
                padding: 0.85rem 2.2rem;
                border-radius: 14px;
                font-family: 'Inter', sans-serif;
                font-size: 0.9rem;
                font-weight: 600;
                text-decoration: none;
                cursor: pointer;
                transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
                border: none;
                outline: none;
                position: relative;
                overflow: hidden;
            }

            .btn--primary {
                background: linear-gradient(135deg, var(--hk-primary-light), var(--hk-accent));
                color: #ffffff;
                box-shadow:
                    0 4px 15px rgba(50, 184, 212, 0.25),
                    0 0 0 1px rgba(50, 184, 212, 0.15) inset;
            }

            .btn--primary:hover {
                transform: translateY(-2px) scale(1.02);
                box-shadow:
                    0 8px 25px rgba(50, 184, 212, 0.35),
                    0 0 0 1px rgba(50, 184, 212, 0.25) inset;
            }

            .btn--primary:active {
                transform: translateY(0) scale(0.98);
            }

            /* Shine effect on primary button */
            .btn--primary::after {
                content: '';
                position: absolute;
                top: -50%;
                left: -60%;
                width: 40%;
                height: 200%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
                transform: skewX(-20deg);
                animation: btnShine 4s ease-in-out infinite;
            }

            @keyframes btnShine {
                0%, 70%, 100% { left: -60%; }
                80%            { left: 120%; }
            }

            .btn--ghost {
                background: rgba(255, 255, 255, 0.04);
                color: var(--hk-text);
                border: 1px solid rgba(255, 255, 255, 0.1);
            }

            .btn--ghost:hover {
                background: rgba(255, 255, 255, 0.08);
                border-color: rgba(50, 184, 212, 0.3);
                transform: translateY(-2px);
            }

            .btn--ghost:active {
                transform: translateY(0);
            }

            .btn-icon {
                font-size: 1.1rem;
                line-height: 1;
            }

            /* ─── Footer ─── */
            .footer-info {
                margin-top: 2.5rem;
                text-align: center;
                animation: fadeSlideUp 1s ease-out 0.85s both;
            }

            .footer-info p {
                font-size: 0.72rem;
                color: rgba(143, 163, 188, 0.5);
                line-height: 1.8;
                letter-spacing: 0.02em;
            }

            .footer-info .footer-brand {
                font-weight: 600;
                color: rgba(50, 184, 212, 0.4);
            }

            /* ─── Animations ─── */
            @keyframes fadeSlideUp {
                from {
                    opacity: 0;
                    transform: translateY(20px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* ─── Responsive ─── */
            @media (max-width: 640px) {
                .welcome-wrapper {
                    padding: 1.5rem 1rem;
                }
                .glass-card {
                    padding: 2rem 1.5rem;
                    border-radius: 20px;
                }
                .logo-container img {
                    width: 220px;
                }
                .welcome-title {
                    font-size: 1.7rem;
                }
                .welcome-subtitle {
                    font-size: 0.9rem;
                }
                .auth-actions {
                    flex-direction: column;
                    align-items: stretch;
                }
                .btn {
                    padding: 0.8rem 1.5rem;
                }
            }

            @media (min-width: 641px) and (max-width: 1024px) {
                .logo-container img {
                    width: 260px;
                }
            }

            /* ─── Clock widget ─── */
            .clock-widget {
                position: fixed;
                top: 1.5rem;
                right: 1.5rem;
                z-index: 10;
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                background: rgba(15, 31, 53, 0.5);
                backdrop-filter: blur(12px);
                border: 1px solid rgba(50, 184, 212, 0.1);
                border-radius: 12px;
                font-size: 0.78rem;
                font-weight: 500;
                color: var(--hk-text-muted);
                animation: fadeSlideUp 1s ease-out 1s both;
            }

            .clock-widget .clock-icon {
                font-size: 0.9rem;
            }

            /* Particle dots */
            .particles {
                position: fixed;
                inset: 0;
                z-index: 0;
                pointer-events: none;
            }

            .particle {
                position: absolute;
                width: 2px;
                height: 2px;
                background: var(--hk-accent);
                border-radius: 50%;
                opacity: 0;
                animation: particleDrift 8s linear infinite;
            }

            @keyframes particleDrift {
                0%   { opacity: 0; transform: translateY(0) scale(0); }
                10%  { opacity: 0.6; transform: scale(1); }
                90%  { opacity: 0.1; }
                100% { opacity: 0; transform: translateY(-100vh) scale(0); }
            }
        </style>
    </head>
    <body>

        <!-- ═══ Animated Background ═══ -->
        <div class="bg-canvas"></div>
        <div class="bg-grid"></div>
        <div class="orb orb--1"></div>
        <div class="orb orb--2"></div>
        <div class="orb orb--3"></div>

        <!-- Particle dots -->
        <div class="particles" id="particles"></div>

        <!-- Clock Widget -->
        <div class="clock-widget">
            <span class="clock-icon">🕐</span>
            <span id="liveClock">--:--:--</span>
        </div>

        <!-- ═══ Main Content ═══ -->
        <div class="welcome-wrapper">

            <!-- Brand Logo -->
            <div class="brand-logo">
                <div class="brand-name">HIKMADENT</div>
                <div class="brand-tagline">Centro Técnico Odontológico</div>
            </div>

            <!-- Glass Card -->
            <div class="glass-card">

                <!-- Badge -->
                <div class="welcome-badge">
                    <span class="badge-dot"></span>
                    Sistema de Gestión Interno
                </div>

                <!-- Title -->
                <h1 class="welcome-title">
                    Bienvenido al Sistema HIKMADENT
                </h1>

                <!-- Subtitle -->
                <p class="welcome-subtitle">
                    Plataforma integral de gestión para el <strong>Centro Técnico Odontológico</strong>.
                    Administra órdenes de trabajo, calendarios por área y el flujo completo
                    del laboratorio dental en un solo lugar.
                </p>

                <!-- Divider -->
                <div class="divider"></div>

                <!-- Areas Preview -->
                <div class="areas-section">
                    <p class="areas-label">Áreas del Laboratorio</p>
                    <div class="areas-grid">
                        <span class="area-chip"><span class="chip-icon">📋</span> Administración</span>
                        <span class="area-chip"><span class="chip-icon">🖨️</span> Impresión</span>
                        <span class="area-chip"><span class="chip-icon">🦷</span> Yeso</span>
                        <span class="area-chip"><span class="chip-icon">💻</span> Digital</span>
                        <span class="area-chip"><span class="chip-icon">⚙️</span> Fresado</span>
                        <span class="area-chip"><span class="chip-icon">🔧</span> Inyectado y Adaptación</span>
                        <span class="area-chip"><span class="chip-icon">🎨</span> Cerámica</span>
                    </div>
                </div>

                <!-- Auth Buttons -->
                @if (Route::has('login'))
                    <div class="auth-actions">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn btn--primary" id="btn-dashboard">
                                <span class="btn-icon">🏠</span>
                                Ir al Panel Principal
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn btn--primary" id="btn-login">
                                <span class="btn-icon">🔐</span>
                                Iniciar Sesión
                            </a>
                        @endauth
                    </div>
                @endif
            </div>

            <!-- Footer -->
            <div class="footer-info">
                <p>
                    <span class="footer-brand">HIKMADENT</span> · Centro Técnico Odontológico<br>
                    Sistema privado de uso interno · © {{ date('Y') }}
                </p>
            </div>
        </div>

        <!-- ═══ Scripts ═══ -->
        <script>
            // ── Live Clock ──
            function updateClock() {
                const now = new Date();
                const h = String(now.getHours()).padStart(2, '0');
                const m = String(now.getMinutes()).padStart(2, '0');
                const s = String(now.getSeconds()).padStart(2, '0');
                document.getElementById('liveClock').textContent = `${h}:${m}:${s}`;
            }
            updateClock();
            setInterval(updateClock, 1000);

            // ── Particle Generator ──
            (function generateParticles() {
                const container = document.getElementById('particles');
                const count = 18;
                for (let i = 0; i < count; i++) {
                    const dot = document.createElement('div');
                    dot.className = 'particle';
                    dot.style.left = Math.random() * 100 + '%';
                    dot.style.bottom = '-5px';
                    dot.style.animationDelay = (Math.random() * 8) + 's';
                    dot.style.animationDuration = (6 + Math.random() * 6) + 's';
                    dot.style.width = (1 + Math.random() * 2) + 'px';
                    dot.style.height = dot.style.width;
                    container.appendChild(dot);
                }
            })();
        </script>
    </body>
</html>
