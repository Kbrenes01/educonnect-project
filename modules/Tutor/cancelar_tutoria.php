<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\cancelar_tutoria.php
session_start();
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['is_tutor'])) {
    $tutorRequestID = (int)$_POST['tutorRequestID'];
    $motivo = trim($_POST['motivo']);

    // Validar motivo obligatorio
    if (empty($motivo)) {
        header("Location: espacio_tutoria_tutor.php?tutorRequestID=$tutorRequestID&error=motivo_obligatorio");
        exit();
    }

    $connection = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);

    // 1. Obtener ID de disponibilidad para liberar
    $stmtGet = mysqli_prepare($connection, "SELECT tutorAvailabilityID FROM tutorrequest WHERE tutorRequestID = ? AND tutorID = ?");
    mysqli_stmt_bind_param($stmtGet, "ii", $tutorRequestID, $_SESSION['tutor_gibbonPersonID']);
    mysqli_stmt_execute($stmtGet);
    $res = mysqli_fetch_assoc(mysqli_stmt_get_result($stmtGet));
    $avID = $res['tutorAvailabilityID'] ?? null;

    // 2. Marcar como Cancelada y guardar motivo
    $stmtUpd = mysqli_prepare($connection, "UPDATE tutorrequest SET status = 'Cancelada', motivoCancelacion = ? WHERE tutorRequestID = ?");
    mysqli_stmt_bind_param($stmtUpd, "si", $motivo, $tutorRequestID);
    mysqli_stmt_execute($stmtUpd);

    // 3. Liberar disponibilidad
    if ($avID) {
        $stmtAv = mysqli_prepare($connection, "UPDATE tutoravailability SET status = 'Disponible' WHERE tutorAvailabilityID = ?");
        mysqli_stmt_bind_param($stmtAv, "i", $avID);
        mysqli_stmt_execute($stmtAv);
    }

    mysqli_close($connection);
    header("Location: mis_tutorias_tutor.php?success=cancelada");
    exit();
}
?>