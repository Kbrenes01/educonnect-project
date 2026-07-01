<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\upload_material.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🛡️ 1. Control de acceso
if (!isset($_SESSION['is_tutor']) || $_SESSION['is_tutor'] !== true || empty($_SESSION['tutor_gibbonPersonID'])) {
    die("Acceso denegado.");
}

$tutorID = $_SESSION['tutor_gibbonPersonID'];

// 📥 2. Validar petición POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /educonnect/modules/Tutor/requests_pending.php");
    exit();
}

$title = trim($_POST['material_title'] ?? '');

if ($title === '' || !isset($_FILES['material_file'])) {
    header("Location: /educonnect/modules/Tutor/requests_pending.php?status=upload_error_missing");
    exit();
}

$file = $_FILES['material_file'];

// 🔍 3. Validar archivo
if ($file['error'] !== UPLOAD_ERR_OK) {
    header("Location: /educonnect/modules/Tutor/requests_pending.php?status=upload_error_file");
    exit();
}

$fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if ($fileExtension !== 'pdf') {
    header("Location: /educonnect/modules/Tutor/requests_pending.php?status=upload_error_type");
    exit();
}

// 📁 4. RUTA MODIFICADA: Apuntando a la carpeta de tu amigo (tutor_materials)
$targetDir = __DIR__ . '/../../uploads/tutor_materials/';

// Por si las moscas, si el pull no creó la carpeta físicamente, la creamos
if (!file_exists($targetDir)) {
    mkdir($targetDir, 0777, true);
}

// Limpiar el nombre del archivo
$safeTitle = preg_replace('/[^a-zA-Z0-9_-]/', '_', $title);
$newFileName = time() . '_' . $safeTitle . '.pdf';
$targetFilePath = $targetDir . $newFileName;

// 🚚 5. Mover el archivo temporal a la carpeta oficial de tutor_materials
if (move_uploaded_files_fallback($file['tmp_name'], $targetFilePath)) {
    
    // 🗄️ 6. Guardar en la Base de Datos
    require_once __DIR__ . '/../../config.php';
    $connection = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);
    
    if ($connection) {
        mysqli_set_charset($connection, "utf8mb4");
        
        // Guardamos la ruta relativa apuntando a tutor_materials
        $relativeUrl = "/educonnect/uploads/tutor_materials/" . $newFileName;
        
        $sql = "INSERT INTO tutormaterial (tutorID, title, filePath, uploadedOn) VALUES (?, ?, ?, NOW())";
        $stmt = mysqli_prepare($connection, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $tutorID, $title, $relativeUrl);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
        mysqli_close($connection);
    }
    
    header("Location: /educonnect/modules/Tutor/requests_pending.php?status=upload_success");
    exit();
} else {
    header("Location: /educonnect/modules/Tutor/requests_pending.php?status=upload_move_error");
    exit();
}

function move_uploaded_files_fallback($tmp, $dest) {
    if (move_uploaded_file($tmp, $dest)) {
        return true;
    }
    return copy($tmp, $dest);
}