<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\espacio_tutoria.php

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

$tutorRequestID = isset($_GET['tutorRequestID']) ? (int) $_GET['tutorRequestID'] : 0;

if ($tutorRequestID <= 0) {
    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?error=notfound");
    exit();
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
        tr.tutorRequestID,
        tr.studentID,
        tr.tutorID,
        tr.tutorAvailabilityID,
        tr.subject,
        tr.details,
        tr.status,
        tr.requestedOn,

        tutor.firstName AS tutorFirstName,
        tutor.surname AS tutorSurname,
        tutor.email AS tutorEmail,

        ta.availableDay,
        ta.startTime,
        ta.endTime,

        tp.educationLevel,
        tp.subjects,
        tp.bio
    FROM tutorrequest tr
    INNER JOIN gibbonPerson tutor
        ON tr.tutorID = tutor.gibbonPersonID
    LEFT JOIN tutoravailability ta
        ON tr.tutorAvailabilityID = ta.tutorAvailabilityID
    LEFT JOIN tutorprofile tp
        ON tr.tutorID = tp.gibbonPersonID
    WHERE tr.tutorRequestID = ?
      AND tr.studentID = ?
    LIMIT 1
";

$stmt = mysqli_prepare($connection, $sql);

if (!$stmt) {
    mysqli_close($connection);
    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?error=notfound");
    exit();
}

mysqli_stmt_bind_param($stmt, "is", $tutorRequestID, $studentID);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$tutoria = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);
mysqli_close($connection);

if (!$tutoria) {
    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?error=notfound");
    exit();
}

/*
    HU-16:
    El estudiante solo puede abrir tutorías en estado Aceptada.
    En pantalla se muestra como "En proceso".
*/
if ($tutoria['status'] !== 'Aceptada') {
    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?error=notallowed");
    exit();
}

function formatHour($time) {
    if (!$time) {
        return "No definido";
    }

    return date('H:i', strtotime($time));
}

function formatDateTimeValue($dateTime) {
    if (!$dateTime) {
        return "No definido";
    }

    return date('d/m/Y H:i', strtotime($dateTime));
}

$tutorName = trim($tutoria['tutorFirstName'] . ' ' . $tutoria['tutorSurname']);
$schedule = formatHour($tutoria['startTime']) . " - " . formatHour($tutoria['endTime']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Espacio de tutoría - EduConnect</title>
</head>
<body style="margin:0; background:#f4eef9; font-family: sans-serif;">

<?php
$pageSubtitle = 'Espacio privado de tutoría';
require_once __DIR__ . '/estudiante_header.php';
?>

    <div style="max-width: 1050px; margin: 25px auto; padding: 0 15px;">

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:24px; box-shadow:0 3px 7px rgba(0,0,0,0.08); margin-bottom:20px;">
            <h2 style="margin-top:0; color:#741fa2;">Tutoría en proceso</h2>
            <p style="color:#555; margin-bottom:0;">
                Este es el espacio privado de la tutoría. Desde aquí podés consultar los datos principales y acceder a las opciones disponibles.
            </p>
        </div>

        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px; align-items:stretch;">

            <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08);">
                <h3 style="margin-top:0; color:#333;">Datos principales</h3>

                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555; width:42%;">Tutor:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutorName); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Materia:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutoria['subject']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Nivel:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutoria['educationLevel'] ?? 'No definido'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Día:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutoria['availableDay'] ?? 'No definido'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Horario:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($schedule); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Estado:</td>
                        <td style="padding:9px;">
                            <span style="background:#D4EDDA; color:#155724; padding:6px 10px; border-radius:20px; font-weight:bold; font-size:13px;">
                                En proceso
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Fecha de solicitud:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars(formatDateTimeValue($tutoria['requestedOn'])); ?></td>
                    </tr>
                </table>
            </div>

            <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08);">
                <h3 style="margin-top:0; color:#333;">Tutor asignado</h3>

                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555; width:42%;">Nombre:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutorName); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Materias:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutoria['subjects'] ?? $tutoria['subject']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Nivel:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutoria['educationLevel'] ?? 'No definido'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555; vertical-align:top;">Descripción:</td>
                        <td style="padding:9px; line-height:1.45;">
                            <?php echo htmlspecialchars($tutoria['bio'] ?? 'Sin descripción registrada.'); ?>
                        </td>
                    </tr>
                </table>
            </div>

        </div>

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08); margin-bottom:20px;">
            <h3 style="margin-top:0; color:#333;">Necesidad académica indicada</h3>

            <div style="background:#f7f2fa; border-left:5px solid #741fa2; padding:15px; border-radius:5px; color:#333;">
                <?php echo nl2br(htmlspecialchars($tutoria['details'])); ?>
            </div>
        </div>

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08);">
            <h3 style="margin-top:0; color:#333;">Opciones del espacio privado</h3>

            <p style="color:#555;">
                Estas opciones quedan visibles para el avance. Su funcionalidad completa se implementará en las siguientes fases.
            </p>

            <div style="display:flex; gap:15px; flex-wrap:wrap; margin-top:15px;">

                <a href="#"
                   onclick="alert('La sección de avisos se implementará en la siguiente fase.'); return false;"
                   style="background:#741fa2; color:white; padding:12px 18px; border-radius:5px; text-decoration:none; font-weight:bold;">
                    Avisos
                </a>

                <a href="#"
                   onclick="alert('La sección de materiales PDF se implementará en la siguiente fase.'); return false;"
                   style="background:#333333; color:white; padding:12px 18px; border-radius:5px; text-decoration:none; font-weight:bold;">
                    Materiales PDF
                </a>

                <a href="#"
                   onclick="alert('La cancelación de tutorías se implementará en la siguiente fase.'); return false;"
                   style="background:#777777; color:white; padding:12px 18px; border-radius:5px; text-decoration:none; font-weight:bold;">
                    Cancelar tutoría
                </a>

            </div>
        </div>

        <div style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">
            <a href="/educonnect/modules/Estudiante/mis_tutorias.php"
               style="background:#333333; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; font-weight:bold;">
                Volver a Mis tutorías
            </a>

            <a href="/educonnect/modules/Estudiante/estudiante_home.php"
               style="background:#741fa2; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; font-weight:bold;">
                Volver al inicio
            </a>
        </div>

    </div>

</body>
</html>