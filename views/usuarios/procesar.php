<?php
// views/usuarios/procesar.php

// Iniciar sesión si no está iniciada (depende de tu global.php, pero lo aseguramos)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/global.php';
require_once '../../config/db.php';

// Seguridad: Si no hay sesión o no enviaron datos por POST, los pateamos al inicio
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: " . BASE_URL);
    exit;
}

// Capturamos la acción a realizar
$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

try {
    $conexion = new Conexion();
    $db = $conexion->conectar();

    /* =======================================================
       1. CREAR USUARIO
       ======================================================= */
    if ($accion == 'crear') {
        $nombre = trim($_POST['nombre']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $rol_id = $_POST['rol_id'];

        // Encriptar contraseña (Bcrypt es el estándar de oro en PHP)
        $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (nombre, username, email, password_hash, rol_id, estado) 
                VALUES (:nombre, :username, :email, :password_hash, :rol_id, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nombre' => $nombre,
            ':username' => $username,
            ':email' => $email,
            ':password_hash' => $password_hash,
            ':rol_id' => $rol_id
        ]);

        // Guardamos una alerta de éxito en la sesión
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Creado!', 'mensaje' => 'El usuario ha sido registrado exitosamente.'];
    }

    /* =======================================================
       2. EDITAR USUARIO
       ======================================================= */ elseif ($accion == 'editar') {
        $id_usuario = $_POST['id_usuario'];
        $nombre = trim($_POST['nombre']);
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $rol_id = $_POST['rol_id'];

        // Si escribieron algo en la contraseña, la actualizamos. Si no, la dejamos igual.
        if (!empty($_POST['password'])) {
            $password_hash = password_hash($_POST['password'], PASSWORD_BCRYPT);
            $sql = "UPDATE usuarios SET nombre = :nombre, username = :username, email = :email, rol_id = :rol_id, password_hash = :password_hash WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':nombre' => $nombre, ':username' => $username, ':email' => $email, ':rol_id' => $rol_id, ':password_hash' => $password_hash, ':id' => $id_usuario]);
        } else {
            $sql = "UPDATE usuarios SET nombre = :nombre, username = :username, email = :email, rol_id = :rol_id WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':nombre' => $nombre, ':username' => $username, ':email' => $email, ':rol_id' => $rol_id, ':id' => $id_usuario]);
        }

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Actualizado!', 'mensaje' => 'Los datos del usuario se guardaron correctamente.'];
    }

    /* =======================================================
       3. DESACTIVAR / ACTIVAR (Eliminado lógico)
       ======================================================= */ elseif ($accion == 'toggle_estado') {
        $id_usuario = $_POST['id_usuario'];
        $estado_actual = $_POST['estado_actual'];

        // Invertimos el estado (si era 1 pasa a 0, si era 0 pasa a 1)
        $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
        $texto_alerta = ($nuevo_estado == 1) ? 'activado' : 'desactivado';

        // Evitar que el usuario se desactive a sí mismo por error
        if ($id_usuario == $_SESSION['usuario_id']) {
            $_SESSION['alerta'] = ['tipo' => 'warning', 'titulo' => 'Acción denegada', 'mensaje' => 'No puedes desactivar tu propio usuario mientras estás en sesión.'];
        } else {
            $sql = "UPDATE usuarios SET estado = :estado WHERE id = :id";
            $stmt = $db->prepare($sql);
            $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_usuario]);

            $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Estado actualizado', 'mensaje' => "El usuario ha sido $texto_alerta con éxito."];
        }
    }

} catch (PDOException $e) {
    // Si la base de datos falla (ej. correo duplicado), atrapamos el error para no romper la pantalla
    $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error de Base de Datos', 'mensaje' => $e->getMessage()];
}

// Redirigimos de vuelta a la pantalla de usuarios
header("Location: " . BASE_URL . "router.php?modulo=usuarios");
exit;