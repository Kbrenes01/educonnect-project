<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\cancelar_tutoria_estudiante_process.php

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php");
    exit();
}

$tutorRequestID = isset($_POST['tutorRequestID']) ? (int) $_POST['tutorRequestID'] : 0;
$cancellationReason = trim($_POST['cancellationReason'] ?? '');

if ($tutorRequestID <= 0) {
    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?error=notfound");
    exit();
}

if ($cancellationReason === '') {
    header("Location: /educonnect/modules/Estudiante/espacio_tutoria.php?tutorRequestID=" . urlencode($tutorRequestID) . "&cancel_error=reason");
    exit();
}

if (mb_strlen($cancellationReason) > 500) {
    $cancellationReason = mb_substr($cancellationReason, 0, 500);
}

$connection = mysqli_connect(
    $databaseServer,
    $databaseUsername,
    $databasePassword,
    $databaseName
);

if (!$connection) {
    header("Location: /educonnect/modules/Estudiante/espacio_tutoria.php?tutorRequestID=" . urlencode($tutorRequestID) . "&cancel_error=system");
    exit();
}

mysqli_set_charset($connection, "utf8mb4");

try {
    mysqli_begin_transaction($connection);

    $sqlTutoria = "
        SELECT
            tutorRequestID,
            studentID,
            tutorID,
            tutorAvailabilityID,
            status
        FROM tutorrequest
        WHERE tutorRequestID = ?
          AND studentID = ?
          AND status = 'Aceptada'
        LIMIT 1
        FOR UPDATE
    ";

    $stmtTutoria = mysqli_prepare($connection, $sqlTutoria);

    if (!$stmtTutoria) {
        throw new Exception("No se pudo preparar la consulta de tutoría.");
    }

    mysqli_stmt_bind_param($stmtTutoria, "is", $tutorRequestID, $studentID);
    mysqli_stmt_execute($stmtTutoria);

    $resultTutoria = mysqli_stmt_get_result($stmtTutoria);
    $tutoria = mysqli_fetch_assoc($resultTutoria);

    mysqli_stmt_close($stmtTutoria);

    if (!$tutoria) {
        mysqli_rollback($connection);
        mysqli_close($connection);

        header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?error=notallowed");
        exit();
    }

    $sqlInsertCancellation = "
        INSERT INTO tutorcancellation
        (tutorRequestID, cancelledByPersonID, cancellationReason, cancelledOn)
        VALUES (?, ?, ?, NOW())
    ";

    $stmtInsertCancellation = mysqli_prepare($connection, $sqlInsertCancellation);

    if (!$stmtInsertCancellation) {
        throw new Exception("No se pudo preparar el registro de cancelación.");
    }

    mysqli_stmt_bind_param(
        $stmtInsertCancellation,
        "iss",
        $tutorRequestID,
        $studentID,
        $cancellationReason
    );

    mysqli_stmt_execute($stmtInsertCancellation);
    mysqli_stmt_close($stmtInsertCancellation);

    $sqlUpdateRequest = "
        UPDATE tutorrequest
        SET status = 'Cancelada'
        WHERE tutorRequestID = ?
          AND studentID = ?
          AND status = 'Aceptada'
    ";

    $stmtUpdateRequest = mysqli_prepare($connection, $sqlUpdateRequest);

    if (!$stmtUpdateRequest) {
        throw new Exception("No se pudo preparar la actualización de la tutoría.");
    }

    mysqli_stmt_bind_param($stmtUpdateRequest, "is", $tutorRequestID, $studentID);
    mysqli_stmt_execute($stmtUpdateRequest);

    if (mysqli_stmt_affected_rows($stmtUpdateRequest) <= 0) {
        mysqli_stmt_close($stmtUpdateRequest);
        throw new Exception("No se pudo actualizar el estado de la tutoría.");
    }

    mysqli_stmt_close($stmtUpdateRequest);

    if (!empty($tutoria['tutorAvailabilityID'])) {
        $tutorAvailabilityID = (int) $tutoria['tutorAvailabilityID'];

        $sqlUpdateAvailability = "
            UPDATE tutoravailability
            SET status = 'Disponible'
            WHERE tutorAvailabilityID = ?
        ";

        $stmtUpdateAvailability = mysqli_prepare($connection, $sqlUpdateAvailability);

        if (!$stmtUpdateAvailability) {
            throw new Exception("No se pudo preparar la actualización de disponibilidad.");
        }

        mysqli_stmt_bind_param($stmtUpdateAvailability, "i", $tutorAvailabilityID);
        mysqli_stmt_execute($stmtUpdateAvailability);
        mysqli_stmt_close($stmtUpdateAvailability);
    }

    mysqli_commit($connection);
    mysqli_close($connection);

    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?cancelled=success");
    exit();

} catch (Exception $e) {
    mysqli_rollback($connection);
    mysqli_close($connection);

    header("Location: /educonnect/modules/Estudiante/espacio_tutoria.php?tutorRequestID=" . urlencode($tutorRequestID) . "&cancel_error=system");
    exit();
}