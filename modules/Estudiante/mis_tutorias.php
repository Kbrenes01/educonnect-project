<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\mis_tutorias.php

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

$allowedStatuses = [
    'Todos',
    'Pendiente',
    'Aceptada',
    'Rechazada',
    'Completada',
    'Cancelada'
];

$statusFilter = $_GET['estado'] ?? 'Todos';

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'Todos';
}

$message = "";
$messageType = "";

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'notallowed') {
        $message = "Solo podés abrir tutorías en proceso.";
    } elseif ($_GET['error'] === 'notfound') {
        $message = "No se encontró la tutoría solicitada.";
    } else {
        $message = "No fue posible realizar la acción.";
    }

    $messageType = "error";
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

$tutorias = [];

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

        ta.availableDay,
        ta.startTime,
        ta.endTime
    FROM tutorrequest tr
    INNER JOIN gibbonPerson tutor
        ON tr.tutorID = tutor.gibbonPersonID
    LEFT JOIN tutoravailability ta
        ON tr.tutorAvailabilityID = ta.tutorAvailabilityID
    WHERE tr.studentID = ?
";

$params = [$studentID];
$types = "s";

if ($statusFilter !== 'Todos') {
    $sql .= " AND tr.status = ? ";
    $params[] = $statusFilter;
    $types .= "s";
}

$sql .= " ORDER BY tr.requestedOn DESC ";

$stmt = mysqli_prepare($connection, $sql);

if (!$stmt) {
    mysqli_close($connection);
    die("Error al preparar la consulta.");
}

mysqli_stmt_bind_param($stmt, $types, ...$params);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $tutorias[] = $row;
}

mysqli_stmt_close($stmt);
mysqli_close($connection);

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

function getStatusText($status) {
    if ($status === 'Aceptada') {
        return 'En proceso';
    }

    return $status;
}

function getStatusBadgeStyle($status) {
    switch ($status) {
        case 'Pendiente':
            return "background:#FFF3CD; color:#856404;";
        case 'Aceptada':
            return "background:#D4EDDA; color:#155724;";
        case 'Rechazada':
            return "background:#F8D7DA; color:#721C24;";
        case 'Completada':
            return "background:#D1ECF1; color:#0C5460;";
        case 'Cancelada':
            return "background:#E2E3E5; color:#383D41;";
        default:
            return "background:#F0F0F0; color:#333;";
    }
}

function getFilterUrl($status) {
    return "/educonnect/modules/Estudiante/mis_tutorias.php?estado=" . urlencode($status);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis tutorías - EduConnect</title>
</head>
<body style="margin:0; background:#f4eef9; font-family: sans-serif;">

<?php
$pageSubtitle = 'Mis tutorías';
require_once __DIR__ . '/estudiante_header.php';
?>

    <div style="max-width: 1150px; margin: 25px auto; padding: 0 15px;">

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08); margin-bottom:20px;">
            <h2 style="margin-top:0; color:#741fa2;">Mis tutorías</h2>
            <p style="color:#555; margin-bottom:0;">
                Consultá el estado de tus solicitudes y abrí el espacio privado de las tutorías que ya están en proceso.
            </p>
        </div>

        <?php if ($message): ?>
            <?php
                $bg = $messageType === "error" ? "#F8D7DA" : "#D4EDDA";
                $color = $messageType === "error" ? "#721C24" : "#155724";
            ?>
            <div style="background:<?php echo $bg; ?>; color:<?php echo $color; ?>; padding:12px; border-radius:6px; margin-bottom:18px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:18px 22px; box-shadow:0 3px 7px rgba(0,0,0,0.08); margin-bottom:20px;">
            <strong style="display:block; margin-bottom:12px; color:#333;">Filtrar por estado:</strong>

            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <?php foreach ($allowedStatuses as $status): ?>
                    <?php
                        $active = $statusFilter === $status;
                        $bg = $active ? "#741fa2" : "#eeeeee";
                        $color = $active ? "#ffffff" : "#333333";
                        $text = $status === 'Aceptada' ? 'En proceso' : $status;
                    ?>
                    <a href="<?php echo getFilterUrl($status); ?>"
                       style="background:<?php echo $bg; ?>; color:<?php echo $color; ?>; padding:9px 13px; border-radius:5px; text-decoration:none; font-weight:bold; font-size:14px;">
                        <?php echo htmlspecialchars($text); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08);">

            <?php if (empty($tutorias)): ?>
                <div style="background:#FFF3CD; color:#856404; padding:14px; border-radius:6px;">
                    No tenés tutorías registradas para este filtro.
                </div>
            <?php else: ?>

                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#741fa2; color:white;">
                            <th style="padding:12px; text-align:left;">Tutor</th>
                            <th style="padding:12px; text-align:left;">Materia</th>
                            <th style="padding:12px; text-align:left;">Día</th>
                            <th style="padding:12px; text-align:left;">Horario</th>
                            <th style="padding:12px; text-align:left;">Estado</th>
                            <th style="padding:12px; text-align:left;">Fecha solicitud</th>
                            <th style="padding:12px; text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tutorias as $index => $tutoria): ?>
                            <?php
                                $rowBg = $index % 2 === 0 ? "#ffffff" : "#f7f2fa";
                                $tutorName = trim($tutoria['tutorFirstName'] . ' ' . $tutoria['tutorSurname']);
                                $schedule = formatHour($tutoria['startTime']) . " - " . formatHour($tutoria['endTime']);
                                $statusText = getStatusText($tutoria['status']);
                                $badgeStyle = getStatusBadgeStyle($tutoria['status']);
                            ?>
                            <tr style="background:<?php echo $rowBg; ?>; border-bottom:1px solid #e5e5e5;">
                                <td style="padding:12px;"><?php echo htmlspecialchars($tutorName); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($tutoria['subject']); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($tutoria['availableDay'] ?? 'No definido'); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($schedule); ?></td>
                                <td style="padding:12px;">
                                    <span style="<?php echo $badgeStyle; ?> padding:6px 10px; border-radius:20px; font-weight:bold; font-size:13px;">
                                        <?php echo htmlspecialchars($statusText); ?>
                                    </span>
                                </td>
                                <td style="padding:12px;"><?php echo htmlspecialchars(formatDateTimeValue($tutoria['requestedOn'])); ?></td>
                                <td style="padding:12px; text-align:center;">
                                    <?php if ($tutoria['status'] === 'Aceptada'): ?>
                                        <a href="/educonnect/modules/Estudiante/espacio_tutoria.php?tutorRequestID=<?php echo (int)$tutoria['tutorRequestID']; ?>"
                                           style="background:#741fa2; color:white; padding:8px 14px; border-radius:5px; text-decoration:none; font-weight:bold;">
                                            Abrir
                                        </a>
                                    <?php else: ?>
                                        <span style="color:#777; font-size:13px;">Sin acción</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>
        </div>

        <div style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">
            <a href="/educonnect/modules/Estudiante/estudiante_home.php"
               style="background:#333333; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; font-weight:bold;">
                Volver al inicio
            </a>

            <a href="/educonnect/modules/Estudiante/solicitar_tutoria.php"
               style="background:#741fa2; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; font-weight:bold;">
                Solicitar tutoría
            </a>
        </div>

    </div>

</body>
</html>