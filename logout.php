<?php
// logout.php
require_once 'config/global.php';

// Destruir todas las variables de sesión registradas
$_SESSION = array();

// Si se desea destruir la sesión completamente, borramos también la cookie de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Finalmente, destruir la sesión
session_destroy();

// Redirigir al Login
header("Location: " . BASE_URL . "index.php");
exit;
?>