<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\descargar_material.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['is_student']) || $_SESSION['is_student'] !== true) {
    header("Location: /educonnect/modules/Estudiante/estudiante_login.php");
    exit();
}

$studentID = $_SESSION['student_gibbonPersonID'] ?? null;

if (!$studentID) {
    header("Location: /educonnect/modules/Estudiante/estudiante_login.php");
    exit();
}

$pdfMaterialID = isset($_GET['pdfMaterialID']) ? (int) $_GET['pdfMaterialID'] : 0;
$mode = $_GET['mode'] ?? 'download';

if ($pdfMaterialID <= 0) {
    die("Material no válido.");
}

if ($mode !== 'preview' && $mode !== 'download') {
    $mode = 'download';
}

$connection = mysqli_connect(
    $databaseServer,
    $databaseUsername,
    $databasePassword,
    $databaseName
);

if (!$connection) {
    die("Error al conectar con la base de datos.");
}

mysqli_set_charset($connection, "utf8mb4");

$sql = "
    SELECT
        tm.pdfMaterialID,
        tm.tutorRequestID,
        tm.tutorID,
        tm.title,
        tm.fileName,
        tm.filePath,
        tm.isActive,

        tr.studentID,
        tr.status
    FROM tutorpdfmaterial tm
    INNER JOIN tutorrequest tr
        ON tm.tutorRequestID = tr.tutorRequestID
    WHERE tm.pdfMaterialID = ?
      AND tr.studentID = ?
      AND tr.status = 'Aceptada'
      AND tm.isActive = 'Y'
      AND tm.tutorID = tr.tutorID
    LIMIT 1
";

$stmt = mysqli_prepare($connection, $sql);

if (!$stmt) {
    mysqli_close($connection);
    die("No se pudo consultar el material.");
}

mysqli_stmt_bind_param($stmt, "is", $pdfMaterialID, $studentID);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$material = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);
mysqli_close($connection);

if (!$material) {
    die("No tenés permiso para acceder a este material o el material no existe.");
}

$relativePath = str_replace("\\", "/", $material['filePath']);
$relativePath = ltrim($relativePath, "/");

if (strpos($relativePath, "..") !== false) {
    die("Ruta de archivo no permitida.");
}

$projectRoot = realpath(__DIR__ . "/../../");
$materialsRoot = realpath($projectRoot . "/uploads/tutor_materials");
$fullPath = realpath($projectRoot . "/" . $relativePath);

if (!$projectRoot || !$materialsRoot || !$fullPath) {
    die("El archivo no existe en el servidor.");
}

if (strpos($fullPath, $materialsRoot) !== 0) {
    die("Ruta de archivo no permitida.");
}

if (!is_file($fullPath)) {
    die("El archivo no existe en el servidor.");
}

$downloadName = basename($material['fileName']);

if (!$downloadName) {
    $downloadName = basename($fullPath);
}

$downloadName = str_replace('"', '', $downloadName);

while (ob_get_level()) {
    ob_end_clean();
}

header("Content-Type: application/pdf");
header("Content-Length: " . filesize($fullPath));
header("Cache-Control: private");
header("Pragma: private");
header("X-Content-Type-Options: nosniff");

if ($mode === 'preview') {
    header("Content-Disposition: inline; filename=\"" . $downloadName . "\"");
} else {
    header("Content-Disposition: attachment; filename=\"" . $downloadName . "\"");
}

readfile($fullPath);
exit();