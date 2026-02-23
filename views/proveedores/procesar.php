<?php
// views/proveedores/procesar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../../config/global.php';
require_once '../../config/db.php';

if (!isset($_SESSION['usuario_id']) || $_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: " . BASE_URL);
    exit;
}

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

try {
    $conexion = new Conexion();
    $db = $conexion->conectar();

    /* =======================================================
       1. CREAR PROVEEDOR
       ======================================================= */
    if ($accion == 'crear') {
        $rfc = trim($_POST['rfc']);
        $razon_social = trim($_POST['razon_social']);
        $nombre_contacto = trim($_POST['nombre_contacto']);
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);
        $direccion = trim($_POST['direccion']);

        $sql = "INSERT INTO proveedores (rfc, razon_social, nombre_contacto, telefono, email, direccion, estado) 
                VALUES (:rfc, :rsocial, :contacto, :tel, :email, :dir, 1)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':rfc' => empty($rfc) ? null : $rfc, // El RFC puede ser opcional, pero si va, debe ser único
            ':rsocial' => $razon_social,
            ':contacto' => $nombre_contacto,
            ':tel' => $telefono,
            ':email' => $email,
            ':dir' => $direccion
        ]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Registrado!', 'mensaje' => 'El proveedor se ha guardado en el catálogo.'];
    }

    /* =======================================================
       2. EDITAR PROVEEDOR
       ======================================================= */ elseif ($accion == 'editar') {
        $id_proveedor = $_POST['id_proveedor'];
        $rfc = trim($_POST['rfc']);
        $razon_social = trim($_POST['razon_social']);
        $nombre_contacto = trim($_POST['nombre_contacto']);
        $telefono = trim($_POST['telefono']);
        $email = trim($_POST['email']);
        $direccion = trim($_POST['direccion']);

        $sql = "UPDATE proveedores 
                SET rfc = :rfc, razon_social = :rsocial, nombre_contacto = :contacto, 
                    telefono = :tel, email = :email, direccion = :dir 
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':rfc' => empty($rfc) ? null : $rfc,
            ':rsocial' => $razon_social,
            ':contacto' => $nombre_contacto,
            ':tel' => $telefono,
            ':email' => $email,
            ':dir' => $direccion,
            ':id' => $id_proveedor
        ]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Actualizado!', 'mensaje' => 'Datos del proveedor actualizados correctamente.'];
    }

    /* =======================================================
       3. ACTIVAR / DESACTIVAR
       ======================================================= */ elseif ($accion == 'toggle_estado') {
        $id_proveedor = $_POST['id_proveedor'];
        $estado_actual = $_POST['estado_actual'];
        $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
        $texto_alerta = ($nuevo_estado == 1) ? 'activado' : 'desactivado';

        $sql = "UPDATE proveedores SET estado = :estado WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':estado' => $nuevo_estado, ':id' => $id_proveedor]);

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Estado actualizado', 'mensaje' => "El proveedor fue $texto_alerta con éxito."];
    }

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        $_SESSION['alerta'] = ['tipo' => 'warning', 'titulo' => 'RFC Duplicado', 'mensaje' => 'Ya existe un proveedor registrado con ese RFC.'];
    } else {
        $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error de Base de Datos', 'mensaje' => $e->getMessage()];
    }
}

header("Location: " . BASE_URL . "router.php?modulo=proveedores");
exit;