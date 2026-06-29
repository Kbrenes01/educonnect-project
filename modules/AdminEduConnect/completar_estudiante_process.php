<?php
// C:\xampp\htdocs\educonnect\modules\AdminEduConnect\completar_estudiante_process.php

$projectRoot = realpath(__DIR__ . '/../../');

if (!$projectRoot) {
    die("No se pudo ubicar la raíz del proyecto.");
}

chdir($projectRoot);

require_once $projectRoot . '/gibbon.php';
require_once $projectRoot . '/config.php';

$session = $container->get('session');

if (!$session->has('username') || !$session->has('gibbonRoleIDCurrent')) {
    header("Location: /educonnect/index.php");
    exit();
}

$adminRoleIDCurrent = $session->get('gibbonRoleIDCurrent');
$adminRoleIDPrimary = $session->get('gibbonRoleIDPrimary');

if ($adminRoleIDCurrent !== '001' && $adminRoleIDPrimary !== '001') {
    die("No tenés permiso para realizar esta acción.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /educonnect/modules/AdminEduConnect/completar_estudiante.php");
    exit();
}

$gibbonPersonID = trim($_POST['gibbonPersonID'] ?? '');
$educationLevel = trim($_POST['educationLevel'] ?? '');
$gradeLevel = trim($_POST['gradeLevel'] ?? '');
$mainSubject = trim($_POST['mainSubject'] ?? '');
$isActive = trim($_POST['isActive'] ?? '');

if (
    $gibbonPersonID === '' ||
    $educationLevel === '' ||
    $gradeLevel === '' ||
    $mainSubject === '' ||
    $isActive === ''
) {
    header("Location: /educonnect/modules/AdminEduConnect/completar_estudiante.php?gibbonPersonID=" . urlencode($gibbonPersonID) . "&error=required");
    exit();
}

if (!in_array($educationLevel, ['Primaria', 'Secundaria'], true)) {
    header("Location: /educonnect/modules/AdminEduConnect/completar_estudiante.php?gibbonPersonID=" . urlencode($gibbonPersonID) . "&error=required");
    exit();
}

if (!in_array($isActive, ['Y', 'N'], true)) {
    header("Location: /educonnect/modules/AdminEduConnect/completar_estudiante.php?gibbonPersonID=" . urlencode($gibbonPersonID) . "&error=required");
    exit();
}

$gibbonPersonIDInt = (int) $gibbonPersonID;

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

// Validar que exista, sea Student y no tenga studentprofile.
$sqlValidate = "
    SELECT
        p.gibbonPersonID,
        p.username,
        p.gibbonRoleIDPrimary,
        sp.studentProfileID
    FROM gibbonPerson p
    LEFT JOIN studentprofile sp
        ON p.gibbonPersonID = sp.gibbonPersonID
    WHERE p.gibbonPersonID = ?
      AND p.gibbonRoleIDPrimary = 3
    LIMIT 1
";

$stmtValidate = mysqli_prepare($connection, $sqlValidate);

if (!$stmtValidate) {
    die("Error preparando validación: " . mysqli_error($connection));
}

mysqli_stmt_bind_param($stmtValidate, "i", $gibbonPersonIDInt);
mysqli_stmt_execute($stmtValidate);

$resultValidate = mysqli_stmt_get_result($stmtValidate);
$student = mysqli_fetch_assoc($resultValidate);

mysqli_stmt_close($stmtValidate);

if (!$student) {
    mysqli_close($connection);
    header("Location: /educonnect/modules/AdminEduConnect/completar_estudiante.php?error=invalid");
    exit();
}

if (!empty($student['studentProfileID'])) {
    mysqli_close($connection);
    header("Location: /educonnect/modules/AdminEduConnect/completar_estudiante.php?error=exists");
    exit();
}

// Insertar en studentprofile.
$sqlInsert = "
    INSERT INTO studentprofile
    (
        gibbonPersonID,
        educationLevel,
        gradeLevel,
        mainSubject,
        isActive
    )
    VALUES
    (?, ?, ?, ?, ?)
";

$stmtInsert = mysqli_prepare($connection, $sqlInsert);

if (!$stmtInsert) {
    die("Error preparando INSERT: " . mysqli_error($connection));
}

mysqli_stmt_bind_param(
    $stmtInsert,
    "issss",
    $gibbonPersonIDInt,
    $educationLevel,
    $gradeLevel,
    $mainSubject,
    $isActive
);

$inserted = mysqli_stmt_execute($stmtInsert);

if (!$inserted) {
    die("Error ejecutando INSERT: " . mysqli_stmt_error($stmtInsert));
}

mysqli_stmt_close($stmtInsert);
mysqli_close($connection);

header("Location: /educonnect/modules/AdminEduConnect/completar_estudiante.php?success=created");
exit();