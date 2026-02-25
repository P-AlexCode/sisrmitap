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
            <div class="theme-selector-nav me-3 d-none d-sm-flex shadow-sm">
                <button class="theme-btn" data-set-theme="tecnm" title="Tema TecNM"><i
                        class="bi bi-building"></i></button>
                <button class="theme-btn" data-set-theme="oscuro" title="Modo Oscuro"><i
                        class="bi bi-moon-stars-fill"></i></button>
                <button class="theme-btn" data-set-theme="pastel" title="Modo Pastel"><i
                        class="bi bi-palette-fill"></i></button>
            </div>

            <div class="dropdown">
                <button class="btn dropdown-toggle d-flex align-items-center border-0 p-0 shadow-none" type="button"
                    id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background: transparent;">
                    <div class="rounded-circle d-flex justify-content-center align-items-center me-2 shadow-sm"
                        style="width: 38px; height: 38px; background: rgba(0,0,0,0.05); color: var(--accent-color); border: 1px solid var(--glass-border-panel);">
                        <i class="bi bi-person-fill fs-5"></i>
                    </div>
                    <div class="text-start d-none d-md-block" style="line-height: 1.1;">
                        <span class="fw-bold d-block" style="color: var(--text-main); font-size: 0.9rem;">
                            <?= htmlspecialchars($_SESSION['nombre']); ?>
                        </span>
                        <small style="color: var(--text-muted); font-size: 0.75rem;">
                            <?= $_SESSION['rol_id'] == 1 ? 'Administrador' : 'Almacenista' ?>
                        </small>
                    </div>
                </button>

                <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg mt-2 p-2" aria-labelledby="userDropdown"
                    style="background: var(--glass-panel); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid var(--glass-border-panel); border-radius: 16px; min-width: 220px;">
                    <li class="px-3 py-2 border-bottom border-secondary border-opacity-10 mb-2">
                        <span class="d-block fw-bold"
                            style="color: var(--text-main);"><?= htmlspecialchars($_SESSION['nombre']); ?></span>
                        <small
                            style="color: var(--text-muted);"><?= $_SESSION['email'] ?? 'Usuario del Sistema' ?></small>
                    </li>

                    <li><a class="dropdown-item py-2 fw-bold text-danger rounded d-flex align-items-center"
                            href="<?= BASE_URL ?>logout.php" style="transition: background 0.2s;"
                            onmouseover="this.style.background='rgba(220,53,69,0.1)'"
                            onmouseout="this.style.background='transparent'">
                            <i class="bi bi-power me-2 fs-5"></i> Cerrar Sesión
                        </a></li>
                </ul>
            </div>
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