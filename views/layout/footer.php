<?php
// views/layout/footer.php
?>
</div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

<script>
    $(document).ready(function () {

        // ==========================================
        // 1. LÓGICA DEL MENÚ LATERAL (MÓVILES)
        // ==========================================

        // Abrir menú con el botón hamburguesa
        $('#sidebarToggle').on('click', function (e) {
            e.preventDefault();
            $('#sidebar').addClass('toggled');
            $('#sidebarOverlay').fadeIn(200); // Muestra el fondo oscuro
        });

        // Cerrar menú haciendo clic en la 'X' o en el fondo oscuro
        $('#sidebarOverlay, #closeSidebar').on('click', function () {
            $('#sidebar').removeClass('toggled');
            $('#sidebarOverlay').fadeOut(200);
        });

        // Si están en un celular, ocultar el menú al elegir una opción
        if ($(window).width() <= 768) {
            $('#sidebar .nav-link').on('click', function () {
                $('#sidebar').removeClass('toggled');
                $('#sidebarOverlay').fadeOut(200);
            });
        }

        // ==========================================
        // 2. LÓGICA DE CAMBIO DE TEMAS EN VIVO
        // ==========================================

        const htmlElement = document.documentElement;
        // Seleccionamos los botones de la Navbar y los de Móviles
        const themeButtons = document.querySelectorAll('.theme-btn');

        const currentTheme = htmlElement.getAttribute('data-theme');
        updateActiveNavButton(currentTheme);

        themeButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault(); // Evita que la página salte hacia arriba
                const selectedTheme = btn.getAttribute('data-set-theme');

                htmlElement.setAttribute('data-theme', selectedTheme);
                localStorage.setItem('loginTheme', selectedTheme);
                updateActiveNavButton(selectedTheme);

                if (typeof Chart !== 'undefined' && document.getElementById('graficaMovimientos')) {
                    setTimeout(() => location.reload(), 300);
                }
            });
        });

        function updateActiveNavButton(themeName) {
            themeButtons.forEach(btn => btn.classList.remove('active'));
            // Ilumina todos los botones correspondientes
            document.querySelectorAll(`[data-set-theme="${themeName}"]`).forEach(el => el.classList.add('active'));
        }

    });
</script>

</body>

</html>