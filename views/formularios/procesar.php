<?php
// views/formularios/procesar.php

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
       1. CREAR FORMULARIO
       ======================================================= */
    if ($accion == 'crear') {
        $titulo = trim($_POST['titulo']);
        $descripcion = trim($_POST['descripcion']);
        $accion_posterior = $_POST['accion_posterior'];

        $sql = "INSERT INTO formularios (titulo, descripcion, accion_posterior, estado, fecha_apertura) 
                VALUES (:tit, :desc, :acc, 'activo', NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([':tit' => $titulo, ':desc' => $descripcion, ':acc' => $accion_posterior]);

        $nuevo_id = $db->lastInsertId();

        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => '¡Formulario Creado!', 'mensaje' => 'Ahora puedes agregarle preguntas.'];
        // Lo mandamos directo al diseñador
        header("Location: " . BASE_URL . "router.php?modulo=formularios_diseno&id=" . $nuevo_id);
        exit;
    }

    /* =======================================================
       2. CAMBIAR ESTADO (Activar / Cerrar)
       ======================================================= */ elseif ($accion == 'toggle_estado') {
        $id_form = $_POST['id_formulario'];
        $estado_actual = $_POST['estado_actual'];

        if ($estado_actual == 'activo') {
            // Lo cerramos
            $db->prepare("UPDATE formularios SET estado = 'inactivo' WHERE id = ?")->execute([$id_form]);
            $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Formulario Cerrado', 'mensaje' => 'Ya no se aceptarán más respuestas.'];
        } else {
            // Al reabrirlo, actualizamos la fecha para un nuevo ciclo
            $db->prepare("UPDATE formularios SET estado = 'activo', fecha_apertura = NOW() WHERE id = ?")->execute([$id_form]);
            $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Nuevo Ciclo Abierto', 'mensaje' => 'El formulario está activo y listo para recibir respuestas.'];
        }
    }

    /* =======================================================
       3. ELIMINAR FORMULARIO
       ======================================================= */ elseif ($accion == 'eliminar') {
        $id_form = $_POST['id_formulario'];
        $db->prepare("DELETE FROM formularios WHERE id = ?")->execute([$id_form]);
        $_SESSION['alerta'] = ['tipo' => 'success', 'titulo' => 'Eliminado', 'mensaje' => 'El formulario y sus respuestas fueron borrados.'];
    }

} catch (PDOException $e) {
    $_SESSION['alerta'] = ['tipo' => 'error', 'titulo' => 'Error', 'mensaje' => $e->getMessage()];
}

header("Location: " . BASE_URL . "router.php?modulo=formularios");
exit;