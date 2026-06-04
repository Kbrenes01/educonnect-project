<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\estudiante_home.php

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
    echo "<div style='background:#F8D7DA; color:#721C24; padding:12px; border-radius:5px; margin:20px;'>Error al conectar con la base de datos.</div>";
    exit();
}

mysqli_set_charset($connection, "utf8mb4");

$student = null;
$summary = [
    'pendientes' => 0,
    'enProceso' => 0,
    'completadas' => 0,
    'canceladas' => 0,
];

$sqlStudent = "
    SELECT
        p.gibbonPersonID,
        p.username,
        p.firstName,
        p.surname,
        sp.educationLevel,
        sp.gradeLevel,
        sp.mainSubject
    FROM gibbonPerson p
    INNER JOIN studentprofile sp
        ON p.gibbonPersonID = sp.gibbonPersonID
    WHERE p.gibbonPersonID = ?
      AND sp.isActive = 'Y'
    LIMIT 1
";

$stmtStudent = mysqli_prepare($connection, $sqlStudent);
mysqli_stmt_bind_param($stmtStudent, "s", $gibbonPersonID);
mysqli_stmt_execute($stmtStudent);

$resultStudent = mysqli_stmt_get_result($stmtStudent);
$student = mysqli_fetch_assoc($resultStudent);

mysqli_stmt_close($stmtStudent);

if (!$student) {
    mysqli_close($connection);
    echo "<div style='background:#FFF3CD; color:#856404; padding:12px; border-radius:5px; margin:20px;'>No se encontró el perfil del estudiante.</div>";
    exit();
}

$sqlSummary = "
    SELECT
        SUM(CASE WHEN status = 'Pendiente' THEN 1 ELSE 0 END) AS pendientes,
        SUM(CASE WHEN status = 'Aceptada' THEN 1 ELSE 0 END) AS enProceso,
        SUM(CASE WHEN status = 'Completada' THEN 1 ELSE 0 END) AS completadas,
        SUM(CASE WHEN status = 'Cancelada' THEN 1 ELSE 0 END) AS canceladas
    FROM tutorrequest
    WHERE studentID = ?
";

$stmtSummary = mysqli_prepare($connection, $sqlSummary);
mysqli_stmt_bind_param($stmtSummary, "s", $gibbonPersonID);
mysqli_stmt_execute($stmtSummary);

$resultSummary = mysqli_stmt_get_result($stmtSummary);
$rowSummary = mysqli_fetch_assoc($resultSummary);

mysqli_stmt_close($stmtSummary);

if ($rowSummary) {
    $summary['pendientes'] = (int) ($rowSummary['pendientes'] ?? 0);
    $summary['enProceso'] = (int) ($rowSummary['enProceso'] ?? 0);
    $summary['completadas'] = (int) ($rowSummary['completadas'] ?? 0);
    $summary['canceladas'] = (int) ($rowSummary['canceladas'] ?? 0);
}

mysqli_close($connection);

$studentName = trim($student['firstName'] . ' ' . $student['surname']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>EduConnect - Estudiante</title>
</head>
<body style="margin:0; background:#f4eef9; font-family: sans-serif;">

    <div style="background: linear-gradient(90deg, #9d5ce5, #741fa2); padding: 28px 40px; color: white;">
        <h1 style="margin:0;">EduConnect</h1>
        <p style="margin:6px 0 0 0;">Portal del estudiante</p>
    </div>

    <div style="max-width: 1050px; margin: 25px auto; padding: 0 15px;">

        <div style="background: #ffffff; border: 1px solid #dddddd; border-radius: 8px; padding: 24px; box-shadow: 0 3px 7px rgba(0,0,0,0.08); margin-bottom: 20px;">
            <h2 style="margin-top: 0; color: #741fa2;">Bienvenido/a, <?php echo htmlspecialchars($studentName); ?></h2>
            <p style="color: #555; margin-bottom: 0;">
                Desde este portal podés solicitar tutorías, consultar tus solicitudes y abrir tus tutorías en proceso.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 20px; margin-bottom: 20px;">

            <div style="background: #ffffff; border: 1px solid #dddddd; border-radius: 8px; padding: 22px; box-shadow: 0 3px 7px rgba(0,0,0,0.08);">
                <h3 style="margin-top: 0; color: #333;">Datos del estudiante</h3>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px; font-weight: bold; color: #555;">Nombre:</td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($studentName); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; font-weight: bold; color: #555;">Usuario:</td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($student['username']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; font-weight: bold; color: #555;">Nivel educativo:</td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($student['educationLevel']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; font-weight: bold; color: #555;">Grado:</td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($student['gradeLevel']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px; font-weight: bold; color: #555;">Materia principal:</td>
                        <td style="padding: 8px;"><?php echo htmlspecialchars($student['mainSubject'] ?? 'No indicada'); ?></td>
                    </tr>
                </table>
            </div>

            <div style="background: #ffffff; border: 1px solid #dddddd; border-radius: 8px; padding: 22px; box-shadow: 0 3px 7px rgba(0,0,0,0.08);">
                <h3 style="margin-top: 0; color: #333;">Resumen personal</h3>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="background: #f7f2fa; border-radius: 6px; padding: 14px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #741fa2;"><?php echo $summary['pendientes']; ?></div>
                        <div style="font-size: 13px; color: #555;">Pendientes</div>
                    </div>

                    <div style="background: #f7f2fa; border-radius: 6px; padding: 14px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #741fa2;"><?php echo $summary['enProceso']; ?></div>
                        <div style="font-size: 13px; color: #555;">En proceso</div>
                    </div>

                    <div style="background: #f7f2fa; border-radius: 6px; padding: 14px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #741fa2;"><?php echo $summary['completadas']; ?></div>
                        <div style="font-size: 13px; color: #555;">Completadas</div>
                    </div>

                    <div style="background: #f7f2fa; border-radius: 6px; padding: 14px; text-align: center;">
                        <div style="font-size: 24px; font-weight: bold; color: #741fa2;"><?php echo $summary['canceladas']; ?></div>
                        <div style="font-size: 13px; color: #555;">Canceladas</div>
                    </div>
                </div>
            </div>

        </div>

        <div style="background: #ffffff; border: 1px solid #dddddd; border-radius: 8px; padding: 22px; box-shadow: 0 3px 7px rgba(0,0,0,0.08);">
            <h3 style="margin-top: 0; color: #333;">Opciones del estudiante</h3>

            <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                <a href="/educonnect/modules/Estudiante/solicitar_tutoria.php"
                   style="background: #741fa2; color: #ffffff; padding: 12px 18px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                    Solicitar tutoría
                </a>

                <a href="/educonnect/modules/Estudiante/mis_tutorias.php"
                   style="background: #333333; color: #ffffff; padding: 12px 18px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                    Mis tutorías
                </a>

                <a href="/educonnect/modules/Estudiante/estudiante_logout.php"
                   style="background: #777777; color: #ffffff; padding: 12px 18px; border-radius: 5px; text-decoration: none; font-weight: bold;">
                    Cerrar sesión
                </a>
            </div>
        </div>
    </div>

</body>
</html>