<?php
// views/layout/sidebar.php

// Detectar qué sección debe estar abierta según el módulo actual
$sec_operaciones = in_array($modulo, ['salidas', 'prestamos', 'entradas', 'solicitudes', 'historial']) ? 'show' : '';
$sec_gestion = in_array($modulo, ['productos', 'proveedores', 'categorias', 'edificios', 'departamentos', 'directorio', 'usuarios', 'formularios']) ? 'show' : '';
$sec_sistema = in_array($modulo, ['reportes', 'auditoria']) ? 'show' : '';
?>
<div id="sidebar">
    <button id="closeSidebar" class="btn border-0 d-md-none position-absolute top-0 end-0 m-3"
        style="color: var(--text-sidebar); background: rgba(255,255,255,0.1); border-radius: 50%;">
        <i class="bi bi-x-lg"></i>
    </button>

    <div class="sidebar-heading text-center fw-bold">
        <i class="bi bi-box-seam me-2"></i> Sistema Integral
    </div>

    <div class="list-group list-group-flush my-3 overflow-auto" style="height: calc(100vh - 140px);">

        <div class="mb-4 px-3 d-md-none border-bottom border-light border-opacity-25 pb-4">
            <div class="text-uppercase small fw-bold opacity-75 mb-3">Apariencia</div>
            <div class="theme-selector-nav justify-content-center" style="background: rgba(0,0,0,0.2);">
                <button class="theme-btn px-3 py-2" data-set-theme="tecnm" title="TecNM"><i
                        class="bi bi-building"></i></button>
                <button class="theme-btn px-3 py-2" data-set-theme="oscuro" title="Oscuro"><i
                        class="bi bi-moon-stars-fill"></i></button>
                <button class="theme-btn px-3 py-2" data-set-theme="pastel" title="Pastel"><i
                        class="bi bi-palette-fill"></i></button>
            </div>
        </div>

        <div id="sidebarAccordion">

            <div class="px-3 mb-1 text-uppercase small fw-bold opacity-75">Principal</div>
            <a href="<?= BASE_URL ?>router.php?modulo=dashboard"
                class="nav-link <?= ($modulo == 'dashboard') ? 'active' : '' ?>">
                <i class="bi bi-speedometer2 me-2"></i> Dashboard
            </a>

            <div class="px-3 mt-4 mb-1 text-uppercase small fw-bold opacity-75">
                <a class="text-decoration-none d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" href="#col_operaciones" style="color: inherit; cursor: pointer;">
                    <span>Operaciones</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
            </div>
            <div id="col_operaciones" class="collapse <?= $sec_operaciones ?>" data-bs-parent="#sidebarAccordion">
                <div class="ps-2 border-start border-light border-opacity-25 ms-4 my-2">
                    <a href="<?= BASE_URL ?>router.php?modulo=salidas"
                        class="nav-link py-2 <?= ($modulo == 'salidas') ? 'active' : '' ?>"><i
                            class="bi bi-box-arrow-right me-2"></i> Salidas Directas</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=prestamos"
                        class="nav-link py-2 <?= ($modulo == 'prestamos') ? 'active' : '' ?>"><i
                            class="bi bi-arrow-left-right me-2"></i> Préstamos</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=entradas"
                        class="nav-link py-2 <?= ($modulo == 'entradas') ? 'active' : '' ?>"><i
                            class="bi bi-box-arrow-in-left me-2"></i> Entradas</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=solicitudes"
                        class="nav-link py-2 <?= ($modulo == 'solicitudes') ? 'active' : '' ?>"><i
                            class="bi bi-globe me-2"></i> Solicitudes Web</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=historial"
                        class="nav-link py-2 <?= ($modulo == 'historial') ? 'active' : '' ?>"><i
                            class="bi bi-clock-history me-2"></i> Historial</a>
                </div>
            </div>

            <div class="px-3 mt-4 mb-1 text-uppercase small fw-bold opacity-75">
                <a class="text-decoration-none d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" href="#col_gestion" style="color: inherit; cursor: pointer;">
                    <span>Gestión</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
            </div>
            <div id="col_gestion" class="collapse <?= $sec_gestion ?>" data-bs-parent="#sidebarAccordion">
                <div class="ps-2 border-start border-light border-opacity-25 ms-4 my-2">
                    <a href="<?= BASE_URL ?>router.php?modulo=productos"
                        class="nav-link py-2 <?= ($modulo == 'productos') ? 'active' : '' ?>"><i
                            class="bi bi-boxes me-2"></i> Productos</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=proveedores"
                        class="nav-link py-2 <?= ($modulo == 'proveedores') ? 'active' : '' ?>"><i
                            class="bi bi-truck me-2"></i> Proveedores</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=categorias"
                        class="nav-link py-2 <?= ($modulo == 'categorias') ? 'active' : '' ?>"><i
                            class="bi bi-tags me-2"></i> Categorías</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=edificios"
                        class="nav-link py-2 <?= ($modulo == 'edificios') ? 'active' : '' ?>"><i
                            class="bi bi-building me-2"></i> Edificios</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=departamentos"
                        class="nav-link py-2 <?= ($modulo == 'departamentos') ? 'active' : '' ?>"><i
                            class="bi bi-door-open me-2"></i> Departamentos</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=directorio"
                        class="nav-link py-2 <?= ($modulo == 'directorio') ? 'active' : '' ?>"><i
                            class="bi bi-person-vcard me-2"></i> Directorio Personal</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=usuarios"
                        class="nav-link py-2 <?= ($modulo == 'usuarios') ? 'active' : '' ?>"><i
                            class="bi bi-people me-2"></i> Usuarios Sistema</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=formularios"
                        class="nav-link py-2 <?= ($modulo == 'formularios') ? 'active' : '' ?>"><i
                            class="bi bi-ui-checks me-2"></i> Formularios</a>
                </div>
            </div>

            <div class="px-3 mt-4 mb-1 text-uppercase small fw-bold opacity-75">
                <a class="text-decoration-none d-flex justify-content-between align-items-center"
                    data-bs-toggle="collapse" href="#col_sistema" style="color: inherit; cursor: pointer;">
                    <span>Sistema</span>
                    <i class="bi bi-chevron-down"></i>
                </a>
            </div>
            <div id="col_sistema" class="collapse <?= $sec_sistema ?>" data-bs-parent="#sidebarAccordion">
                <div class="ps-2 border-start border-light border-opacity-25 ms-4 my-2">
                    <a href="<?= BASE_URL ?>router.php?modulo=reportes"
                        class="nav-link py-2 <?= ($modulo == 'reportes') ? 'active' : '' ?>"><i
                            class="bi bi-file-earmark-bar-graph me-2"></i> Reportes</a>
                    <a href="<?= BASE_URL ?>router.php?modulo=auditoria"
                        class="nav-link py-2 <?= ($modulo == 'auditoria') ? 'active' : '' ?>"><i
                            class="bi bi-eye me-2"></i> Auditoría</a>
                </div>
            </div>

        </div>
        <div class="mt-4 mb-5">
            <a href="<?= BASE_URL ?>logout.php" class="nav-link fw-bold" style="color: #ff6b6b;">
                <i class="bi bi-power me-2"></i> Cerrar Sesión
            </a>
        </div>

    </div>
</div>