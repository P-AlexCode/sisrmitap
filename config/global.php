<?php
// config/global.php

date_default_timezone_set('America/Hermosillo');

define('BASE_URL', 'https://sisrmitap.palexcode.com//');

define('NOMBRE_SISTEMA', 'Sistema de Recursos Materiales y Servicios');
define('INSTITUCION', 'TecNM Campus Agua Prieta');


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>