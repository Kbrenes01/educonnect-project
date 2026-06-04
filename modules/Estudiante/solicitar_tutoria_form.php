<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\solicitar_tutoria_form.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['is_student']) || $_SESSION['is_student'] !== true) {
    header("Location: /educonnect/modules/Estudiante/estudiante_login.php");
    exit();
}

$tutorAvailabilityID = isset($_GET['tutorAvailabilityID']) ? (int) $_GET['tutorAvailabilityID'] : 0;

if ($tutorAvailabilityID <= 0) {
    header("Location: /educonnect/modules/Estudiante/solicitar_tutoria.php?error=notavailable");
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
    WHERE ta.tutorAvailabilityID = ?
      AND ta.status = 'Disponible'
      AND tp.isActive = 'Y'
    LIMIT 1
";

$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "i", $tutorAvailabilityID);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$availability = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);
mysqli_close($connection);

if (!$availability) {
    header("Location: /educonnect/modules/Estudiante/solicitar_tutoria.php?error=notavailable");
    exit();
}

function formatHour($time) {
    return date('H:i', strtotime($time));
}

$tutorName = trim($availability['firstName'] . ' ' . $availability['surname']);
$schedule = formatHour($availability['startTime']) . " - " . formatHour($availability['endTime']);

// Como subjects puede venir como "Matemática, Física", usamos la primera como materia principal de esta solicitud.
$subjectsArray = explode(',', $availability['subjects']);
$mainSubject = trim($subjectsArray[0]);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Formulario de solicitud - EduConnect</title>
</head>
<body style="margin:0; background:#f4eef9; font-family: sans-serif;">

<?php
$pageSubtitle = 'Formulario de solicitud de tutoría';
require_once __DIR__ . '/estudiante_header.php';
?>

    <div style="max-width: 800px; margin: 25px auto; padding: 0 15px;">

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:24px; box-shadow:0 3px 7px rgba(0,0,0,0.08);">

            <h2 style="margin-top:0; color:#741fa2;">Confirmar solicitud</h2>
            <p style="color:#555;">
                Revisá los datos de la tutoría seleccionada e indicá tu necesidad académica.
            </p>

            <form action="/educonnect/modules/Estudiante/solicitar_tutoria_process.php" method="POST">

                <input type="hidden" name="tutorAvailabilityID" value="<?php echo (int)$availability['tutorAvailabilityID']; ?>">

                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-bottom:15px;">

                    <div>
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Tutor:</label>
                        <input type="text" value="<?php echo htmlspecialchars($tutorName); ?>" readonly
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; background:#f5f5f5; box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Materia:</label>
                        <input type="text" value="<?php echo htmlspecialchars($mainSubject); ?>" readonly
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; background:#f5f5f5; box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Nivel:</label>
                        <input type="text" value="<?php echo htmlspecialchars($availability['educationLevel']); ?>" readonly
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; background:#f5f5f5; box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Día:</label>
                        <input type="text" value="<?php echo htmlspecialchars($availability['availableDay']); ?>" readonly
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; background:#f5f5f5; box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Horario:</label>
                        <input type="text" value="<?php echo htmlspecialchars($schedule); ?>" readonly
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; background:#f5f5f5; box-sizing:border-box;">
                    </div>

                    <div>
                        <label style="display:block; font-weight:bold; margin-bottom:5px;">Estado:</label>
                        <input type="text" value="Disponible" readonly
                               style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; background:#f5f5f5; box-sizing:border-box;">
                    </div>

                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block; font-weight:bold; margin-bottom:5px;">
                        Necesidad académica:
                    </label>
                    <textarea name="details" required rows="6"
                              placeholder="Ejemplo: Necesito ayuda con fracciones, resolución de problemas y ejercicios para el examen."
                              style="width:100%; padding:10px; border:1px solid #ccc; border-radius:4px; box-sizing:border-box; resize:vertical;"></textarea>
                    <small style="color:#666;">Este campo es obligatorio.</small>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap;">
                    <button type="submit"
                            style="background:#741fa2; color:white; padding:12px 18px; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">
                        Enviar solicitud
                    </button>

                    <a href="/educonnect/modules/Estudiante/solicitar_tutoria.php"
                       style="background:#777777; color:white; padding:12px 18px; border-radius:5px; text-decoration:none; font-weight:bold;">
                        Cancelar
                    </a>
                </div>

            </form>

        </div>

    </div>

</body>
</html>