<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\solicitar_tutoria_process.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['is_student']) || $_SESSION['is_student'] !== true) {
    header("Location: /educonnect/modules/Estudiante/estudiante_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /educonnect/modules/Estudiante/solicitar_tutoria.php");
    exit();
}

$studentID = $_SESSION['student_gibbonPersonID'] ?? null;
$tutorAvailabilityID = isset($_POST['tutorAvailabilityID']) ? (int) $_POST['tutorAvailabilityID'] : 0;
$details = trim($_POST['details'] ?? '');

if (!$studentID || $tutorAvailabilityID <= 0) {
    header("Location: /educonnect/modules/Estudiante/solicitar_tutoria.php?error=notavailable");
    exit();
}

if ($details === '') {
    header("Location: /educonnect/modules/Estudiante/solicitar_tutoria_form.php?tutorAvailabilityID={$tutorAvailabilityID}&error=empty");
    exit();
}

$connection = mysqli_connect(
    $databaseServer,
    $databaseUsername,
    $databasePassword,
    $databaseName
);

if (!$connection) {
    header("Location: /educonnect/modules/Estudiante/solicitar_tutoria.php?error=db");
    exit();
}

mysqli_set_charset($connection, "utf8mb4");

try {
    mysqli_begin_transaction($connection);

    /*
        Bloqueamos la disponibilidad mientras se procesa.
        Esto evita que dos estudiantes tomen el mismo horario al mismo tiempo.
    */
    $sqlAvailability = "
        SELECT
            ta.tutorAvailabilityID,
            ta.gibbonPersonID AS tutorID,
            ta.status,
            tp.subjects
        FROM tutoravailability ta
        INNER JOIN tutorprofile tp
            ON ta.gibbonPersonID = tp.gibbonPersonID
        WHERE ta.tutorAvailabilityID = ?
          AND ta.status = 'Disponible'
          AND tp.isActive = 'Y'
        LIMIT 1
        FOR UPDATE
    ";

    $stmtAvailability = mysqli_prepare($connection, $sqlAvailability);
    mysqli_stmt_bind_param($stmtAvailability, "i", $tutorAvailabilityID);
    mysqli_stmt_execute($stmtAvailability);

    $availabilityResult = mysqli_stmt_get_result($stmtAvailability);
    $availability = mysqli_fetch_assoc($availabilityResult);

    mysqli_stmt_close($stmtAvailability);

    if (!$availability) {
        mysqli_rollback($connection);
        mysqli_close($connection);
        header("Location: /educonnect/modules/Estudiante/solicitar_tutoria.php?error=notavailable");
        exit();
    }

    $tutorID = $availability['tutorID'];

    // Si el tutor tiene "Matemática, Física", usamos la primera materia como materia principal solicitada.
    $subjectsArray = explode(',', $availability['subjects']);
    $subject = trim($subjectsArray[0]);

    if ($subject === '') {
        $subject = $availability['subjects'];
    }

    /*
        Crear solicitud.
        Esta es la tabla que Kevin leerá desde el módulo Tutor.
    */
    $sqlInsert = "
        INSERT INTO tutorrequest
        (studentID, tutorID, tutorAvailabilityID, subject, details, status)
        VALUES
        (?, ?, ?, ?, ?, 'Pendiente')
    ";

    $stmtInsert = mysqli_prepare($connection, $sqlInsert);
    mysqli_stmt_bind_param(
        $stmtInsert,
        "ssiss",
        $studentID,
        $tutorID,
        $tutorAvailabilityID,
        $subject,
        $details
    );

    mysqli_stmt_execute($stmtInsert);
    mysqli_stmt_close($stmtInsert);

    /*
        La disponibilidad pasa a Pendiente.
        Kevin luego la puede cambiar:
        - A Ocupado si acepta.
        - A Disponible si rechaza.
    */
    $sqlUpdateAvailability = "
        UPDATE tutoravailability
        SET status = 'Pendiente'
        WHERE tutorAvailabilityID = ?
    ";

    $stmtUpdate = mysqli_prepare($connection, $sqlUpdateAvailability);
    mysqli_stmt_bind_param($stmtUpdate, "i", $tutorAvailabilityID);
    mysqli_stmt_execute($stmtUpdate);
    mysqli_stmt_close($stmtUpdate);

    mysqli_commit($connection);
    mysqli_close($connection);

    header("Location: /educonnect/modules/Estudiante/solicitar_tutoria.php?success=1");
    exit();

} catch (Throwable $e) {
    mysqli_rollback($connection);
    mysqli_close($connection);

    header("Location: /educonnect/modules/Estudiante/solicitar_tutoria.php?error=db");
    exit();
}