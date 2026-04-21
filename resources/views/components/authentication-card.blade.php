<div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0" style="background: linear-gradient(180deg, #081220, #0c1e33, #0a1628);">
    <div>
        {{ $logo }}
    </div>

    <div class="w-full sm:max-w-md mt-6 px-6 py-4 shadow-xl overflow-hidden sm:rounded-2xl border" style="background: rgba(15, 31, 53, 0.7); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border-color: rgba(50, 184, 212, 0.15);">
        {{ $slot }}
    </div>

    <p class="mt-6 text-xs" style="color: rgba(143, 163, 188, 0.5);">
        <span style="color: rgba(50, 184, 212, 0.4); font-weight: 600;">HIKMADENT</span> · Sistema privado de uso interno
    </p>
</div>
