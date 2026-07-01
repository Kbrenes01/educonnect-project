<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\tutor_home.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🛡️ CONTROL DE ACCESO
if (!isset($_SESSION['is_tutor']) || $_SESSION['is_tutor'] !== true || empty($_SESSION['tutor_gibbonPersonID'])) {
    echo "<div style='background: #fee2e2; color: #991b1b; padding: 20px; border-radius: 6px; border: 1px solid #fca5a5; font-family: sans-serif; margin: 20px;'>";
    echo "<h3>Acceso Restringido</h3>";
    echo "<p>No se detectó una sesión activa de Tutor. Por favor, inicia sesión de nuevo.</p>";
    echo "</div>";
    return; 
}

$tutorID = $_SESSION['tutor_gibbonPersonID'];
// 🚨 ACTIVAR VISUALIZACIÓN DE ERRORES (Ponelo para ver qué falla)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$tutorID = $_SESSION['tutor_gibbonPersonID'];

// Intentar cargar config.php dinámicamente usando la raíz del servidor de XAMPP
$configPath = $_SERVER['DOCUMENT_ROOT'] . '/educonnect/config.php';

if (file_exists($configPath)) {
    require_once $configPath;
} else {
    // Si falla, intenta la ruta relativa original
    require_once __DIR__ . '/../../config.php';
}

$connection = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);
mysqli_set_charset($connection, "utf8mb4");

// 1. Datos del Tutor
$tutorFullName = $_SESSION['tutor_name'] ?? 'Docente Académico';
$tutorUser = $_SESSION['tutor_username'] ?? 'tutor_logged';
$tutorEmail = 'N/A';

$sqlTutor = "SELECT email FROM gibbonPerson WHERE gibbonPersonID = ? LIMIT 1";
$stmtTutor = mysqli_prepare($connection, $sqlTutor);
if ($stmtTutor) {
    mysqli_stmt_bind_param($stmtTutor, "s", $tutorID);
    mysqli_stmt_execute($stmtTutor);
    $resTutor = mysqli_stmt_get_result($stmtTutor);
    if ($tutorData = mysqli_fetch_assoc($resTutor)) {
        $tutorEmail = $tutorData['email'] ?? 'N/A';
    }
    mysqli_stmt_close($stmtTutor);
}

// 2. Conteo de métricas
$countPendientes = 0;
$countAceptadas = 0;

$sqlCount = "SELECT status, COUNT(*) as total FROM tutorrequest WHERE tutorID = ? GROUP BY status";
$stmtCount = mysqli_prepare($connection, $sqlCount);
if ($stmtCount) {
    mysqli_stmt_bind_param($stmtCount, "s", $tutorID);
    mysqli_stmt_execute($stmtCount);
    $resCount = mysqli_stmt_get_result($stmtCount);
    while ($row = mysqli_fetch_assoc($resCount)) {
        if ($row['status'] === 'Pendiente') $countPendientes = $row['total'];
        if ($row['status'] === 'Accepted' || $row['status'] === 'Aceptada') $countAceptadas = $row['total'];
    }
    mysqli_stmt_close($stmtCount);
}
mysqli_close($connection);
?>

<style>
    .edu-banner { background: #741fa2; color: white; padding: 20px; margin: -20px -32px 24px -32px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
    .edu-card { background: white; border-radius: 8px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 24px; font-family: sans-serif; }
    .grid-container { display: grid; grid-template-columns: 1fr; gap: 24px; margin-bottom: 24px; }
    @media(min-width: 768px) { .grid-container { grid-template-columns: 1fr 1fr; } }
    .metric-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .metric-box { background: #f3e8ff; border-radius: 6px; padding: 15px; text-align: center; border: 1px solid #e9d5ff; }
    .metric-number { font-size: 24px; font-weight: bold; color: #741fa2; margin: 0; }
    .metric-label { font-size: 13px; color: #555; margin-top: 4px; }
    .data-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #f1f5f9; }
    .data-label { font-weight: bold; color: #4a5568; }
    .data-value { color: #1a202c; }
    .nav-buttons { display: flex; gap: 15px; flex-wrap: wrap; }

    .btn-purple { background: #741fa2; color: white; padding: 12px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; font-family: sans-serif; }
    .btn-purple:hover { background: #5c1882; }
    .btn-gray { background: #777777; color: white; padding: 12px 20px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; font-family: sans-serif; }
    .btn-gray:hover { background: #555555; }
</style>

<div class="edu-banner">
    <h1 style="margin:0; font-size: 26px; font-weight: bold;">EduConnect</h1>
    <p style="margin:4px 0 0 0; opacity: 0.9; font-size: 15px;">Portal del tutor</p>
</div>

<div class="edu-card">
    <h2 style="color: #741fa2; margin: 0 0 8px 0; font-size: 22px;">Bienvenido/a, <?php echo htmlspecialchars($tutorFullName); ?></h2>
    <p style="color: #4a5568; margin: 0; font-size: 14px; line-height: 1.5;">
        Desde tu panel de control podés revisar tus estadísticas en tiempo real, responder a nuevas solicitudes y acceder a tus herramientas de trabajo.
    </p>
</div>

<div class="grid-container">
    <div class="edu-card" style="margin-bottom:0;">
        <h3 style="margin-top: 0; margin-bottom: 16px; color: #333; font-size: 16px; border-bottom: 2px solid #741fa2; padding-bottom: 6px; display: inline-block;">Datos del tutor</h3>
        <div class="data-row"><span class="data-label">Nombre:</span><span class="data-value"><?php echo htmlspecialchars($tutorFullName); ?></span></div>
        <div class="data-row"><span class="data-label">Usuario:</span><span class="data-value"><?php echo htmlspecialchars($tutorUser); ?></span></div>
        <div class="data-row"><span class="data-label">Identificación:</span><span class="data-value"><?php echo htmlspecialchars($tutorID); ?></span></div>
        <div class="data-row"><span class="data-label">Contacto:</span><span class="data-value"><?php echo htmlspecialchars($tutorEmail); ?></span></div>
    </div>

    <div class="edu-card" style="margin-bottom:0;">
        <h3 style="margin-top: 0; margin-bottom: 16px; color: #333; font-size: 16px; border-bottom: 2px solid #741fa2; padding-bottom: 6px; display: inline-block;">Resumen de actividad</h3>
        <div class="metric-grid">
            <div class="metric-box">
                <p class="metric-number"><?php echo $countPendientes; ?></p>
                <p class="metric-label">Pendientes</p>
            </div>
            <div class="metric-box" style="background: #ecfdf5; border-color: #a7f3d0;">
                <p class="metric-number" style="color: #059669;"><?php echo $countAceptadas; ?></p>
                <p class="metric-label">En proceso</p>
            </div>
            <div class="metric-box" style="background: #f8fafc; border-color: #e2e8f0;">
                <p class="metric-number" style="color: #64748b;">0</p>
                <p class="metric-label">Completadas</p>
            </div>
            <div class="metric-box" style="background: #fdf2f8; border-color: #fbcfe8;">
                <p class="metric-number" style="color: #db2777;">0</p>
                <p class="metric-label">Canceladas</p>
            </div>
        </div>
    </div>
</div>

<div class="edu-card">
    <h3 style="margin-top: 0; color: #333; font-size: 16px; margin-bottom: 16px;">Módulos de Gestión</h3>
    <div class="nav-buttons">
        <a href="/educonnect/modules/Tutor/requests_pending.php" class="btn-purple">
             Gestionar Solicitudes (<?php echo $countPendientes; ?>)
        </a>
        <a href="/educonnect/modules/Tutor/tutor_my_tutorings.php" class="btn-purple" style="background: #059669;">
             Mis Tutorías
        </a>
        <a href="/educonnect/modules/Tutor/tutor_logout.php" class="btn-gray">
             Cerrar Sesión
        </a>
    </div>
</div>