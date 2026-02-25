<?php
// views/layout/header.php

// Asegurarnos de que el usuario está logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

// Rescatar el tema preferido de la base de datos (por defecto 'tecnm')
$tema_actual = isset($_SESSION['tema']) ? $_SESSION['tema'] : 'tecnm';
?>
<!DOCTYPE html>
<html lang="es" data-theme="<?= $tema_actual ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel | <?= NOMBRE_SISTEMA ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        const savedTheme = localStorage.getItem('loginTheme') || '<?= $tema_actual ?>';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

</head>

<body>

    <div class="blob uno"></div>
    <div class="blob dos"></div>

    <div id="wrapper">