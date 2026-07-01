<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\tutor_logout.php

//  Forzar visualización de errores por si algo más falla
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🧹 Limpiar las variables de sesión del Tutor
$_SESSION['is_tutor'] = null;
$_SESSION['tutor_gibbonPersonID'] = null;
$_SESSION['tutor_username'] = null;
$_SESSION['tutor_name'] = null;

unset($_SESSION['is_tutor']);
unset($_SESSION['tutor_gibbonPersonID']);
unset($_SESSION['tutor_username']);
unset($_SESSION['tutor_name']);

//  Redirección por PHP (Método estándar)
if (!headers_sent()) {
    header("Location: /educonnect/modules/Tutor/tutor_login.php");
    exit();
}

// Salvavidas en JavaScript si las cabeceras ya se enviaron y falló el header superior
echo "<script>window.location.href='/educonnect/modules/Tutor/tutor_login.php';</script>";
exit();