<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\estudiante_logout.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

unset($_SESSION['is_student']);
unset($_SESSION['student_gibbonPersonID']);
unset($_SESSION['student_username']);
unset($_SESSION['student_name']);

header("Location: /educonnect/modules/Estudiante/estudiante_login.php");
exit();