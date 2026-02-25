<?php
// views/dashboard.php
?>
<div class="row mb-4">
    <div class="col-12 px-4">
        <h2 class="fw-bold" style="color: var(--text-main); letter-spacing: -0.5px;">Panel de Control</h2>
        <p style="color: var(--text-muted);">Resumen general del sistema de recursos y servicios.</p>
    </div>
</div>

<div class="row g-4 mb-4 px-3">

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0" style="transition: transform 0.3s ease;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                <div class="mb-3 d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel);">
                    <i class="bi bi-boxes fs-1" style="color: var(--accent-color);"></i>
                </div>
                <h2 class="fw-bold mb-0">1,245</h2>
                <span style="color: var(--text-muted); font-weight: 500; font-size: 0.9rem;">Productos en Stock</span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0" style="transition: transform 0.3s ease;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                <div class="mb-3 d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel);">
                    <i class="bi bi-arrow-left-right fs-1" style="color: #63b3ed;"></i>
                </div>
                <h2 class="fw-bold mb-0">42</h2>
                <span style="color: var(--text-muted); font-weight: 500; font-size: 0.9rem;">Préstamos Activos</span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0" style="transition: transform 0.3s ease;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                <div class="mb-3 d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel);">
                    <i class="bi bi-box-arrow-right fs-1" style="color: #fbd38d;"></i>
                </div>
                <h2 class="fw-bold mb-0">18</h2>
                <span style="color: var(--text-muted); font-weight: 500; font-size: 0.9rem;">Salidas de Hoy</span>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3">
        <div class="card h-100 border-0" style="transition: transform 0.3s ease;"
            onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-center">
                <div class="mb-3 d-flex align-items-center justify-content-center shadow-sm"
                    style="width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel);">
                    <i class="bi bi-exclamation-triangle fs-1" style="color: #fc8181;"></i>
                </div>
                <h2 class="fw-bold mb-0">5</h2>
                <span style="color: var(--text-muted); font-weight: 500; font-size: 0.9rem;">Stock Crítico</span>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 px-3">

    <div class="col-lg-8">
        <div class="card border-0 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="fw-bold m-0" style="color: var(--text-main);">Movimientos Recientes (Semana)</h5>
                    <i class="bi bi-graph-up-arrow text-muted opacity-50 fs-4"></i>
                </div>
                <canvas id="graficaMovimientos" height="100"></canvas>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 h-100">
            <div class="card-body p-4 d-flex flex-column">
                <h5 class="fw-bold mb-4" style="color: var(--text-main);">Accesos Rápidos</h5>

                <div class="d-grid gap-3 mt-auto mb-auto">
                    <a href="<?= BASE_URL ?>router.php?modulo=prestamos" class="btn rounded-pill py-3 fw-bold shadow-sm"
                        style="background: var(--glass-sidebar); border: 1px solid var(--glass-border-sidebar); color: var(--text-sidebar); transition: all 0.3s;"
                        onmouseover="this.style.transform='translateY(-3px)'"
                        onmouseout="this.style.transform='translateY(0)'">
                        <i class="bi bi-plus-circle me-2"></i> Nuevo Préstamo
                    </a>

                    <a href="<?= BASE_URL ?>router.php?modulo=entradas" class="btn rounded-pill py-3 fw-bold shadow-sm"
                        style="background: transparent; border: 1px solid var(--glass-border-panel); color: var(--text-main); transition: all 0.3s;"
                        onmouseover="this.style.background='var(--hover-sidebar)'; this.style.transform='translateY(-3px)'"
                        onmouseout="this.style.background='transparent'; this.style.transform='translateY(0)'">
                        <i class="bi bi-box-arrow-in-left me-2"></i> Registrar Entrada
                    </a>

                    <a href="<?= BASE_URL ?>router.php?modulo=salidas" class="btn rounded-pill py-3 fw-bold shadow-sm"
                        style="background: transparent; border: 1px solid var(--glass-border-panel); color: var(--text-main); transition: all 0.3s;"
                        onmouseover="this.style.background='var(--hover-sidebar)'; this.style.transform='translateY(-3px)'"
                        onmouseout="this.style.background='transparent'; this.style.transform='translateY(0)'">
                        <i class="bi bi-box-arrow-right me-2"></i> Registrar Salida
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Inicializar la gráfica
        const ctx = document.getElementById('graficaMovimientos').getContext('2d');

        // Leer los colores directamente del CSS actual
        const rootStyles = getComputedStyle(document.documentElement);
        const accentColor = rootStyles.getPropertyValue('--accent-color').trim() || '#ff9e1b';
        const textMuted = rootStyles.getPropertyValue('--text-muted').trim() || '#6c757d';

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
                datasets: [{
                    label: 'Salidas de Material',
                    data: [12, 19, 3, 5, 2],
                    borderColor: accentColor,
                    backgroundColor: 'rgba(255, 255, 255, 0.1)', // Fondo de cristal suave para la gráfica
                    borderWidth: 3,
                    tension: 0.4, // Curvas suaves
                    fill: true,
                    pointBackgroundColor: accentColor,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        labels: { color: rootStyles.getPropertyValue('--text-main').trim() }
                    },
                    tooltip: {
                        backgroundColor: rootStyles.getPropertyValue('--glass-sidebar').trim(),
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: rootStyles.getPropertyValue('--glass-border-sidebar').trim(),
                        borderWidth: 1,
                        padding: 10,
                        displayColors: false,
                        cornerRadius: 10
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(150, 150, 150, 0.1)', borderDash: [5, 5] },
                        ticks: { color: textMuted }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: textMuted }
                    }
                }
            }
        });
    });
</script>