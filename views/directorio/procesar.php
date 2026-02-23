<?php
// views/directorio/procesar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../../config/global.php';
require_once '../../config/db.php';

// Validar sesión
if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: " . BASE_URL);
    exit;
}

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

try {
    $conexion = new Conexion();
    $db = $conexion->conectar();

    /* =======================================================
       1. REGISTRAR PERSONAL
       ======================================================= */
    if ($accion == 'crear') {
        $numero_empleado = trim($_POST['numero_empleado']);
        $nombres = trim($_POST['nombres']);
        $apellidos = trim($_POST['apellidos']);
        $cargo = trim($_POST['cargo']);
        $departamento_id = $_POST['departamento_id']; // Ahora es un ID entero
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);

        $sql = "INSERT INTO personal_directorio (numero_empleado, nombres, apellidos, cargo, departamento_id, telefono, email, estado) 
                VALUES (:num, :nom, :ape, :cargo, :depto, :tel, :email, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':num' => $numero_empleado,
            ':nom' => $nombres,
            ':ape' => $apellidos,
            ':cargo' => $cargo,
            ':depto' => $departamento_id,
            ':tel' => $telefono,
            ':email' => $email
        ]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Registrado!', 'mensaje' => 'El miembro del personal ha sido agregado al directorio.'];
    }

    /* =======================================================
       2. EDITAR PERSONAL
       ======================================================= */ elseif ($accion == 'editar') {
        $id_personal = $_POST['id_personal'];
        $numero_empleado = trim($_POST['numero_empleado']);
        $nombres = trim($_POST['nombres']);
        $apellidos = trim($_POST['apellidos']);
        $cargo = trim($_POST['cargo']);
        $departamento_id = $_POST['departamento_id'];
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);

        $sql = "UPDATE personal_directorio 
                SET numero_empleado = :num, nombres = :nom, apellidos = :ape, cargo = :cargo, 
                    departamento_id = :depto, telefono = :tel, email = :email 
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':num' => $numero_empleado,
            ':nom' => $nombres,
            ':ape' => $apellidos,
            ':cargo' => $cargo,
            ':depto' => $departamento_id,
            ':tel' => $telefono,
            ':email' => $email,
            ':id' => $id_personal
        ]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Actualizado!', 'mensaje' => 'Los datos del personal se actualizaron correctamente.'];
    }

    /* =======================================================
       3. ACTIVAR / DESACTIVAR
       ======================================================= */ elseif ($accion == 'toggle_estado') {
        $id_personal = $_POST['id_personal'];
        $estado_actual = $_POST['estado_actual'];

        $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
        $texto_alerta = ($nuevo_estado == 1) ? 'activado' : 'dado de baja';

        $sql = "UPDATE personal_directorio SET estado = :estado WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_personal]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Estado actualizado', 'mensaje' => "El registro ha sido $texto_alerta con éxito."];
    }

} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error de Base de Datos', 'mensaje' => "Error: " . $e->getMessage()];
}

header("Location: " . BASE_URL . "router.php?modulo=directorio");
exit;