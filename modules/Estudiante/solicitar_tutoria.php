<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\solicitar_tutoria.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['is_student']) || $_SESSION['is_student'] !== true) {
    header("Location: /educonnect/modules/Estudiante/estudiante_login.php");
    exit();
}

$gibbonPersonID = $_SESSION['student_gibbonPersonID'] ?? null;

if (!$gibbonPersonID) {
    header("Location: /educonnect/modules/Estudiante/estudiante_login.php");
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

$message = "";
$messageType = "";

if (isset($_GET['success'])) {
    $message = "Solicitud enviada correctamente. El tutor podrá verla como pendiente.";
    $messageType = "success";
}

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'notavailable') {
        $message = "Ese horario ya no está disponible. Seleccioná otro horario.";
    } elseif ($_GET['error'] === 'empty') {
        $message = "Debés ingresar la necesidad académica.";
    } elseif ($_GET['error'] === 'db') {
        $message = "Ocurrió un error al procesar la solicitud.";
    } else {
        $message = "No fue posible completar la acción.";
    }

    $messageType = "error";
}

$sql = "
    SELECT
        ta.tutorAvailabilityID,
        ta.gibbonPersonID AS tutorID,
        ta.availableDay,
        ta.startTime,
        ta.endTime,
        ta.status,
        tp.subjects,
        tp.educationLevel,
        p.firstName,
        p.surname
    FROM tutoravailability ta
    INNER JOIN tutorprofile tp
        ON ta.gibbonPersonID = tp.gibbonPersonID
    INNER JOIN gibbonPerson p
        ON ta.gibbonPersonID = p.gibbonPersonID
    WHERE ta.status = 'Disponible'
      AND tp.isActive = 'Y'
    ORDER BY 
        FIELD(ta.availableDay, 'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'),
        ta.startTime ASC
";

$result = mysqli_query($connection, $sql);

$availabilities = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $availabilities[] = $row;
    }
}

mysqli_close($connection);

function formatHour($time) {
    return date('H:i', strtotime($time));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitar tutoría - EduConnect</title>
</head>
<body style="margin:0; background:#f4eef9; font-family: sans-serif;">

<?php
$pageSubtitle = 'Solicitar tutoría';
require_once __DIR__ . '/estudiante_header.php';
?>

    <div style="max-width: 1100px; margin: 25px auto; padding: 0 15px;">

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08); margin-bottom:20px;">
            <h2 style="margin-top:0; color:#741fa2;">Tutores disponibles</h2>
            <p style="color:#555; margin-bottom:0;">
                Seleccioná un tutor disponible según la materia, nivel, día y horario que mejor se ajuste a tu necesidad académica.
            </p>
        </div>

        <?php if ($message): ?>
            <?php
                $bg = $messageType === "success" ? "#D4EDDA" : "#F8D7DA";
                $color = $messageType === "success" ? "#155724" : "#721C24";
            ?>
            <div style="background:<?php echo $bg; ?>; color:<?php echo $color; ?>; padding:12px; border-radius:6px; margin-bottom:18px;">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08);">

            <?php if (empty($availabilities)): ?>
                <div style="background:#FFF3CD; color:#856404; padding:14px; border-radius:6px;">
                    No hay tutores disponibles actualmente.
                </div>
            <?php else: ?>

                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="background:#741fa2; color:white;">
                            <th style="padding:12px; text-align:left;">Tutor</th>
                            <th style="padding:12px; text-align:left;">Materia</th>
                            <th style="padding:12px; text-align:left;">Nivel</th>
                            <th style="padding:12px; text-align:left;">Día</th>
                            <th style="padding:12px; text-align:left;">Horario</th>
                            <th style="padding:12px; text-align:center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($availabilities as $index => $availability): ?>
                            <?php
                                $rowBg = $index % 2 === 0 ? "#ffffff" : "#f7f2fa";
                                $tutorName = trim($availability['firstName'] . ' ' . $availability['surname']);
                                $schedule = formatHour($availability['startTime']) . " - " . formatHour($availability['endTime']);
                            ?>
                            <tr style="background:<?php echo $rowBg; ?>; border-bottom:1px solid #e5e5e5;">
                                <td style="padding:12px;"><?php echo htmlspecialchars($tutorName); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($availability['subjects']); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($availability['educationLevel']); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($availability['availableDay']); ?></td>
                                <td style="padding:12px;"><?php echo htmlspecialchars($schedule); ?></td>
                                <td style="padding:12px; text-align:center;">
                                    <a href="/educonnect/modules/Estudiante/solicitar_tutoria_form.php?tutorAvailabilityID=<?php echo (int)$availability['tutorAvailabilityID']; ?>"
                                       style="background:#741fa2; color:white; padding:8px 14px; border-radius:5px; text-decoration:none; font-weight:bold;">
                                        Solicitar
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php endif; ?>
        </div>

        <div style="margin-top:20px; display:flex; gap:12px;">
            <a href="/educonnect/modules/Estudiante/estudiante_home.php"
               style="background:#333333; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; font-weight:bold;">
                Volver al inicio
            </a>

            <a href="/educonnect/modules/Estudiante/mis_tutorias.php"
               style="background:#777777; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; font-weight:bold;">
                Mis tutorías
            </a>
        </div>

    </div>

</body>
</html>