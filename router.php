<?php
// router.php

// Modo Debug temporal
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/global.php';
require_once __DIR__ . '/config/db.php';

// Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Capturar el módulo. Por defecto es 'dashboard'
$modulo = isset($_GET['modulo']) ? $_GET['modulo'] : 'dashboard';

// 1. CARGAMOS LA CABECERA
require_once __DIR__ . '/views/layout/header.php';

// Filtro oscuro para móviles (Overlay)
echo '<div class="sidebar-overlay" id="sidebarOverlay"></div>';

// 2. CARGAMOS EL MENÚ LATERAL ESTÁTICO
require_once __DIR__ . '/views/layout/sidebar.php';

?>

<div id="page-content-wrapper" class="w-100">

    <nav
        class="navbar navbar-expand-lg custom-navbar shadow-sm px-4 py-3 d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center">
            <button id="sidebarToggle" class="btn d-md-none me-3 p-1 border-0" style="background: transparent;">
                <i class="bi bi-list fs-2" style="color: var(--text-main);"></i>
            </button>

            <h5 class="m-0 fw-bold text-uppercase" style="color: var(--text-main); letter-spacing: 1px;">
                <?= htmlspecialchars($modulo) ?>
            </h5>
        </div>

        <div class="d-flex align-items-center">
            <div class="theme-selector-nav me-4 d-none d-sm-flex shadow-sm">
                <button class="theme-btn" data-set-theme="tecnm" title="Tema TecNM"><i
                        class="bi bi-building"></i></button>
                <button class="theme-btn" data-set-theme="oscuro" title="Modo Oscuro"><i
                        class="bi bi-moon-stars-fill"></i></button>
                <button class="theme-btn" data-set-theme="pastel" title="Modo Pastel"><i
                        class="bi bi-palette-fill"></i></button>
            </div>

            <span class="d-none d-md-block fw-medium" style="color: var(--text-main);">
                <i class="bi bi-person-circle me-1" style="color: var(--accent-color);"></i>
                <?= htmlspecialchars($_SESSION['nombre']); ?>
                <small style="color: var(--text-muted);">(<?= $_SESSION['rol_id'] == 1 ? 'Admin' : 'Usuario' ?>)</small>
            </span>
        </div>
    </nav>

    <div class="module-container">
        <div class="container-fluid p-1 px-md-3">

            <?php
            // Sistema de Enrutamiento
            $ruta_archivo = __DIR__ . "/views/" . $modulo . "/index.php";

            if ($modulo == 'dashboard') {
                $ruta_archivo = __DIR__ . "/views/dashboard.php";
            }

            if (file_exists($ruta_archivo)) {
                require_once $ruta_archivo;
            } else {
                echo "
                <div class='card border-0 text-center mx-auto shadow-sm' style='max-width: 500px; margin-top: 10vh;'>
                    <div class='card-body py-5'>
                        <div class='mb-4 d-inline-flex align-items-center justify-content-center' style='width: 80px; height: 80px; border-radius: 50%; background: rgba(255,255,255,0.1); border: 1px solid var(--glass-border-panel);'>
                            <i class='bi bi-tools display-4' style='color: var(--accent-color);'></i>
                        </div>
                        <h3 class='fw-bold mb-3' style='color: var(--text-main);'>Módulo en Construcción</h3>
                        <p style='color: var(--text-muted); font-size: 1.1rem;'>El módulo <strong>" . htmlspecialchars($modulo) . "</strong> aún no ha sido programado en esta versión 3.</p>
                        <a href='" . BASE_URL . "router.php?modulo=dashboard' class='btn rounded-pill px-4 py-2 mt-4 fw-bold' style='background: var(--glass-sidebar); color: var(--text-sidebar); border: 1px solid var(--glass-border-sidebar); transition: all 0.3s;' onmouseover=\"this.style.transform='translateY(-3px)'\" onmouseout=\"this.style.transform='translateY(0)'\">
                            <i class='bi bi-arrow-left me-2'></i>Volver al Inicio
                        </a>
                    </div>
                </div>";
            }
            ?>

        </div>
    </div>
</div>

<?php
// 3. CARGAMOS EL PIE DE PÁGINA
require_once __DIR__ . '/views/layout/footer.php';
?>