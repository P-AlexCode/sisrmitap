<?php
// views/layout/sidebar.php

// Detectar qué sección debe estar abierta según el módulo actual
$sec_operaciones = in_array($modulo, ['salidas', 'prestamos', 'entradas', 'solicitudes', 'historial']) ? 'show' : '';
$sec_gestion = in_array($modulo, ['productos', 'proveedores', 'categorias', 'unidades', 'edificios', 'departamentos', 'directorio', 'usuarios', 'formularios']) ? 'show' : '';
$sec_sistema = in_array($modulo, ['reportes', 'auditoria']) ? 'show' : '';
?>

<style>
    /* Estilos Premium Exclusivos para el Sidebar Glassmorphism */
    #sidebar {
        display: flex;
        flex-direction: column;
        padding: 1.5rem 0;
    }

    .sidebar-brand {
        font-size: 1.3rem;
        font-weight: 800;
        padding: 0 1.5rem 1.5rem;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--text-sidebar);
        border-bottom: 1px solid var(--glass-border-sidebar);
        margin-bottom: 1rem;
        letter-spacing: 0.5px;
    }

    .sidebar-brand .logo-icon {
        background: var(--accent-color);
        color: #fff;
        width: 38px;
        height: 38px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        font-size: 1.3rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    .sidebar-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: rgba(255, 255, 255, 0.5);
        padding: 0.5rem 1.5rem;
        margin-top: 0.8rem;
        font-weight: 700;
    }

    [data-theme="pastel"] .sidebar-label {
        color: rgba(0, 0, 0, 0.4);
    }

    .nav-custom-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1.2rem;
        color: var(--text-sidebar);
        text-decoration: none;
        font-size: 0.95rem;
        font-weight: 500;
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        margin: 0.2rem 1rem;
        border-radius: 14px;
        opacity: 0.85;
    }

    .nav-custom-link i:not(.bi-chevron-down) {
        font-size: 1.2rem;
        width: 30px;
        margin-right: 5px;
        transition: transform 0.3s ease;
    }

    .nav-custom-link .bi-chevron-down {
        transition: transform 0.3s ease;
        font-size: 0.85rem;
        opacity: 0.7;
    }

    .nav-custom-link[aria-expanded="true"] .bi-chevron-down {
        transform: rotate(180deg);
    }

    .nav-custom-link:hover {
        background: var(--hover-sidebar);
        opacity: 1;
        transform: translateX(4px);
    }

    .nav-custom-link.active {
        background: var(--active-sidebar);
        color: #fff;
        opacity: 1;
        box-shadow: inset 0 0 0 1px var(--glass-border-sidebar);
        font-weight: 600;
    }

    [data-theme="pastel"] .nav-custom-link.active {
        color: var(--accent-color);
    }

    .nav-custom-link.active i:not(.bi-chevron-down) {
        color: var(--accent-color);
        transform: scale(1.1);
    }

    .sidebar-submenu {
        background: rgba(0, 0, 0, 0.1);
        margin: 0.2rem 1rem;
        border-radius: 14px;
        overflow: hidden;
        padding: 0.3rem 0;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    [data-theme="oscuro"] .sidebar-submenu {
        background: rgba(0, 0, 0, 0.2);
    }

    [data-theme="pastel"] .sidebar-submenu {
        background: rgba(255, 255, 255, 0.3);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }

    .sidebar-submenu .nav-custom-link {
        margin: 0;
        border-radius: 0;
        padding: 0.6rem 1rem 0.6rem 3rem;
        font-size: 0.85rem;
        font-weight: 400;
        box-shadow: none !important;
        background: transparent !important;
    }

    .sidebar-submenu .nav-custom-link:hover {
        background: rgba(255, 255, 255, 0.05) !important;
        transform: translateX(4px);
    }

    [data-theme="pastel"] .sidebar-submenu .nav-custom-link:hover {
        background: rgba(255, 255, 255, 0.4) !important;
    }

    .sidebar-submenu .nav-custom-link.active {
        color: var(--accent-color);
        font-weight: 600;
        box-shadow: inset 4px 0 0 var(--accent-color) !important;
        background: rgba(255, 255, 255, 0.05) !important;
        opacity: 1;
    }

    /* SCROLLBAR DE CRISTAL EXCLUSIVO PARA EL SIDEBAR */
    .sidebar-scroll::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-scroll::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    .sidebar-scroll::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.4);
    }
</style>

<div id="sidebar">

    <button id="closeSidebar" class="btn border-0 d-md-none position-absolute top-0 end-0 m-2"
        style="color: var(--text-sidebar); background: rgba(255,255,255,0.1); border-radius: 50%; z-index: 10;">
        <i class="bi bi-x-lg"></i>
    </button>

    <div class="sidebar-brand">
        <div class="logo-icon"><i class="bi bi-box-seam"></i></div>
        <span>Inventario V3</span>
    </div>

    <div class="sidebar-scroll overflow-auto d-flex flex-column h-100 pb-3"
        style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.2) transparent;">

        <div class="mb-3 px-3 d-md-none border-bottom border-light border-opacity-10 pb-3">
            <div class="sidebar-label px-0 mb-2 mt-0">Apariencia</div>
            <div class="theme-selector-nav justify-content-center w-100" style="background: rgba(0,0,0,0.15);">
                <button class="theme-btn flex-fill py-2" data-set-theme="tecnm" title="TecNM"><i
                        class="bi bi-building"></i></button>
                <button class="theme-btn flex-fill py-2" data-set-theme="oscuro" title="Oscuro"><i
                        class="bi bi-moon-stars-fill"></i></button>
                <button class="theme-btn flex-fill py-2" data-set-theme="pastel" title="Pastel"><i
                        class="bi bi-palette-fill"></i></button>
            </div>
        </div>

        <div id="sidebarAccordion">

            <div class="sidebar-label">Principal</div>
            <a href="<?= BASE_URL ?>router.php?modulo=dashboard"
                class="nav-custom-link <?= ($modulo == 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-grid-1x2"></i> Panel de Control
            </a>

            <div class="sidebar-label">Inventario y Cajas</div>
            <a class="nav-custom-link d-flex justify-content-between <?= $sec_operaciones ? 'active' : '' ?>"
                data-bs-toggle="collapse" href="#col_operaciones"
                aria-expanded="<?= $sec_operaciones ? 'true' : 'false' ?>" style="cursor: pointer;">
                <div><i class="bi bi-arrow-left-right"></i> Operaciones</div>
                <i class="bi bi-chevron-down m-0 w-auto"></i>
            </a>
            <div id="col_operaciones" class="collapse <?= $sec_operaciones ?>" data-bs-parent="#sidebarAccordion">
                <div class="sidebar-submenu">
                    <a href="<?= BASE_URL ?>router.php?modulo=salidas"
                        class="nav-custom-link <?= ($modulo == 'salidas') ? 'active' : '' ?>">Salidas Directas</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=prestamos"
                        class="nav-custom-link <?= ($modulo == 'prestamos') ? 'active' : '' ?>">Préstamos (Vales)</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=entradas"
                        class="nav-custom-link <?= ($modulo == 'entradas') ? 'active' : '' ?>">Recepción / Compras</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=solicitudes"
                        class="nav-custom-link <?= ($modulo == 'solicitudes') ? 'active' : '' ?>">Peticiones Web</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=historial"
                        class="nav-custom-link <?= ($modulo == 'historial') ? 'active' : '' ?>">Kárdex (Historial)</a>
                </div>
            </div>

            <div class="sidebar-label">Catálogos Base</div>
            <a class="nav-custom-link d-flex justify-content-between <?= $sec_gestion ? 'active' : '' ?>"
                data-bs-toggle="collapse" href="#col_gestion" aria-expanded="<?= $sec_gestion ? 'true' : 'false' ?>"
                style="cursor: pointer;">
                <div><i class="bi bi-database"></i> Gestión de Datos</div>
                <i class="bi bi-chevron-down m-0 w-auto"></i>
            </a>
            <div id="col_gestion" class="collapse <?= $sec_gestion ?>" data-bs-parent="#sidebarAccordion">
                <div class="sidebar-submenu">
                    <a href="<?= BASE_URL ?>router.php?modulo=productos"
                        class="nav-custom-link <?= ($modulo == 'productos') ? 'active' : '' ?>">Artículos</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=categorias"
                        class="nav-custom-link <?= ($modulo == 'categorias') ? 'active' : '' ?>">Categorías</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=unidades"
                        class="nav-custom-link <?= ($modulo == 'unidades') ? 'active' : '' ?>">Unidades de Medida</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=proveedores"
                        class="nav-custom-link <?= ($modulo == 'proveedores') ? 'active' : '' ?>">Proveedores</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=edificios"
                        class="nav-custom-link <?= ($modulo == 'edificios') ? 'active' : '' ?>">Edificios</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=departamentos"
                        class="nav-custom-link <?= ($modulo == 'departamentos') ? 'active' : '' ?>">Departamentos</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=directorio"
                        class="nav-custom-link <?= ($modulo == 'directorio') ? 'active' : '' ?>">Directorio Personal</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=usuarios"
                        class="nav-custom-link <?= ($modulo == 'usuarios') ? 'active' : '' ?>">Staff y Usuarios</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=formularios"
                        class="nav-custom-link <?= ($modulo == 'formularios') ? 'active' : '' ?>">Formularios
                        Públicos</a>
                </div>
            </div>

            <div class="sidebar-label">Mantenimiento</div>
            <a class="nav-custom-link d-flex justify-content-between <?= $sec_sistema ? 'active' : '' ?>"
                data-bs-toggle="collapse" href="#col_sistema" aria-expanded="<?= $sec_sistema ? 'true' : 'false' ?>"
                style="cursor: pointer;">
                <div><i class="bi bi-sliders"></i> Sistema</div>
                <i class="bi bi-chevron-down m-0 w-auto"></i>
            </a>
            <div id="col_sistema" class="collapse <?= $sec_sistema ?>" data-bs-parent="#sidebarAccordion">
                <div class="sidebar-submenu">
                    <a href="<?= BASE_URL ?>router.php?modulo=reportes"
                        class="nav-custom-link <?= ($modulo == 'reportes') ? 'active' : '' ?>">Reportes PDF/Excel</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=auditoria"
                        class="nav-custom-link <?= ($modulo == 'auditoria') ? 'active' : '' ?>">Log de Auditoría</a>
                </div>
            </div>

        </div>

    </div>
</div>