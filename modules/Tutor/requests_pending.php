<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\requests_pending.php

// 🚨 FORZAR VISUALIZACIÓN DE ERRORES PARA DESARROLLO
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

// Cargar la configuración de la base de datos dinámicamente
$configPath = $_SERVER['DOCUMENT_ROOT'] . '/educonnect/config.php';
if (file_exists($configPath)) {
    require_once $configPath;
} else {
    require_once __DIR__ . '/../../config.php';
}

$connection = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);
if (!$connection) {
    die("<p>Error de conexión a la base de datos.</p>");
}
mysqli_set_charset($connection, "utf8mb4");

// Procesar acciones de Aceptar o Rechazar si se envían por POST
$msg = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['tutorRequestID'])) {
    $action = $_POST['action'];
    $requestID = $_POST['tutorRequestID'];
    $newStatus = ($action === 'accept') ? 'Accepted' : 'Rejected';
    
    $sqlUpdate = "UPDATE tutorrequest SET status = ? WHERE tutorRequestID = ? AND tutorID = ?";
    $stmtUpdate = mysqli_prepare($connection, $sqlUpdate);
    if ($stmtUpdate) {
        mysqli_stmt_bind_param($stmtUpdate, "sss", $newStatus, $requestID, $tutorID);
        if (mysqli_stmt_execute($stmtUpdate)) {
            $msg = "<div style='background: #dcfce7; color: #166534; padding: 15px; border-radius: 6px; border: 1px solid #bbf7d0; margin-bottom: 20px; font-family: sans-serif;'>Solicitud actualizada correctamente.</div>";
        }
        mysqli_stmt_close($stmtUpdate);
    }
}

// Obtener todas las solicitudes pendientes asignadas a este Tutor
$requests = [];
$sqlRequests = "SELECT tr.tutorRequestID, tr.status, tr.tutorID, p.officialName, p.preferredName 
                FROM tutorrequest tr
                JOIN gibbonPerson p ON tr.studentID = p.gibbonPersonID
                WHERE tr.tutorID = ? AND tr.status = 'Pendiente'";

$stmtReq = mysqli_prepare($connection, $sqlRequests);
if ($stmtReq) {
    mysqli_stmt_bind_param($stmtReq, "s", $tutorID);
    mysqli_stmt_execute($stmtReq);
    $resReq = mysqli_stmt_get_result($stmtReq);
    while ($row = mysqli_fetch_assoc($resReq)) {
        $requests[] = $row;
    }
    mysqli_stmt_close($stmtReq);
}
mysqli_close($connection);
?>

<style>
    .edu-banner { background: #741fa2; color: white; padding: 20px; margin: -20px -32px 24px -32px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); font-family: sans-serif; }
    .edu-card { background: white; border-radius: 8px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; margin-bottom: 24px; font-family: sans-serif; }
    .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
    .table-responsive { width: 100%; overflow-x: auto; }
    .edu-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-family: sans-serif; }
    .edu-table th { background: #741fa2; color: white; text-align: left; padding: 12px; font-size: 14px; }
    .edu-table td { padding: 12px; border-bottom: 1px solid #e2e8f0; font-size: 14px; color: #333; }
    .edu-table tr:hover { background: #f8f5fa; }
    .btn-purple { background: #741fa2; color: white; padding: 10px 16px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-purple:hover { background: #5c1882; }
    .btn-gray { background: #777777; color: white; padding: 10px 16px; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; text-decoration: none; display: inline-block; font-size: 14px; }
    .btn-gray:hover { background: #555555; }
    .btn-success { background: #16a34a; color: white; padding: 8px 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 13px; }
    .btn-success:hover { background: #15803d; }
    .btn-danger { background: #dc2626; color: white; padding: 8px 12px; border: none; border-radius: 4px; font-weight: bold; cursor: pointer; font-size: 13px; }
    .btn-danger:hover { background: #b91c1c; }
    .badge-pending { background: #fef3c7; color: #d97706; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; border: 1px solid #fde68a; }
    .action-forms { display: flex; gap: 8px; margin: 0; }
</style>

<div class="edu-banner">
    <h1 style="margin:0; font-size: 26px; font-weight: bold;">EduConnect</h1>
    <p style="margin:4px 0 0 0; opacity: 0.9; font-size: 15px;">Portal del tutor</p>
</div>

<?php echo $msg; ?>

<div class="edu-card">
    <div class="top-bar">
        <h2 style="color: #741fa2; margin: 0; font-size: 22px;">Gestión de Solicitudes Pendientes</h2>
        <!-- 🛠️ BOTÓN DE ATRÁS CORREGIDO Y COMPLETO -->
        <a href="/educonnect/modules/Tutor/tutor_home.php" class="btn-gray">← Volver al Panel Principal</a>
    </div>
    
    <p style="color: #4a5568; margin-bottom: 20px; font-size: 14px; line-height: 1.5;">
        A continuación se muestran los estudiantes que han solicitado tu tutoría académica. Podés aceptar o rechazar cada caso utilizando las acciones directas de la tabla.
    </p>

    <?php if (empty($requests)): ?>
        <div style="background: #f8fafc; text-align: center; padding: 40px; border-radius: 6px; border: 1px solid #e2e8f0;">
            <p style="margin: 0; color: #64748b; font-size: 16px; font-weight: bold;">No tenés solicitudes pendientes de revisión en este momento.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="edu-table">
                <thead>
                    <tr>
                        <th>ID Solicitud</th>
                        <th>Estudiante</th>
                        <th>Estado</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($requests as $req): ?>
                        <?php 
                            $studentName = !empty($req['preferredName']) ? $req['preferredName'] : $req['officialName'];
                        ?>
                        <tr>
                            <td><strong>#<?php echo htmlspecialchars($req['tutorRequestID']); ?></strong></td>
                            <td><?php echo htmlspecialchars($studentName); ?></td>
                            <td><span class="badge-pending">Pendiente</span></td>
                            <td style="text-align: center;">
                                <div style="display: flex; justify-content: center; gap: 8px;">
                                    <!-- Formulario Aceptar -->
                                    <form action="" method="POST" class="action-forms" onsubmit="return confirm('¿Seguro que deseas aceptar esta tutoría?');">
                                        <input type="hidden" name="tutorRequestID" value="<?php echo htmlspecialchars($req['tutorRequestID']); ?>">
                                        <input type="hidden" name="action" value="accept">
                                        <button type="submit" class="btn-success">✔ Aceptar</button>
                                    </form>
                                    
                                    <!-- Formulario Rechazar -->
                                    <form action="" method="POST" class="action-forms" onsubmit="return confirm('¿Seguro que deseas rechazar esta tutoría?');">
                                        <input type="hidden" name="tutorRequestID" value="<?php echo htmlspecialchars($req['tutorRequestID']); ?>">
                                        <input type="hidden" name="action" value="reject">
                                        <button type="submit" class="btn-danger">✖ Rechazar</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>