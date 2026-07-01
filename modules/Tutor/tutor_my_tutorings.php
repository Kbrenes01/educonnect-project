<?php
session_start();
if (!isset($_SESSION['is_tutor']) || $_SESSION['is_tutor'] !== true) { 
    header("Location: /educonnect/modules/Tutor/tutor_login.php"); 
    exit(); 
}

$tutorID = $_SESSION['tutor_gibbonPersonID'];
require_once $_SERVER['DOCUMENT_ROOT'] . '/educonnect/config.php';
$connection = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);

$sql = "SELECT tr.tutorRequestID, tr.status, tr.subject, tr.requestedOn, p.officialName, p.preferredName 
        FROM tutorrequest tr
        JOIN gibbonPerson p ON tr.studentID = p.gibbonPersonID
        WHERE tr.tutorID = ? ORDER BY FIELD(tr.status, 'Aceptada', 'En proceso', 'Pendiente')";
$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "s", $tutorID);
mysqli_stmt_execute($stmt);
$tutorias = mysqli_fetch_all(mysqli_stmt_get_result($stmt), MYSQLI_ASSOC);
mysqli_close($connection);
?>

<div style="display: flex; gap: 20px; font-family: sans-serif; padding: 20px; max-width: 1200px; margin: auto;">
    
    <!-- Lado Izquierdo: Tabla (Ahora ocupa todo el ancho) -->
<div style="width: 100%; font-family: sans-serif; padding: 20px; max-width: 1050px; margin: auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="color: #741fa2; margin: 0;">Mis Tutorías</h2>
        <a href="/educonnect/modules/Tutor/tutor_home.php" style="background: #777; color: white; padding: 8px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 14px;">
            ← Volver al Panel
        </a>
    </div>

    <table style="width: 100%; border-collapse: collapse; background: white; border: 1px solid #ddd;">
        <thead>
            <tr style="background: #741fa2; color: white;">
                <th style="padding: 12px; text-align: left;">Estudiante</th>
                <th style="padding: 12px; text-align: left;">Materia</th>
                <th style="padding: 12px; text-align: left;">Estado</th>
                <th style="padding: 12px; text-align: center;">Acción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tutorias as $t): ?>
            <tr style="border-bottom: 1px solid #ddd;">
                <td style="padding: 12px;"><?php echo htmlspecialchars($t['preferredName'] ?: $t['officialName']); ?></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($t['subject']); ?></td>
                <td style="padding: 12px;"><?php echo htmlspecialchars($t['status']); ?></td>
                <td style="padding: 12px; text-align: center;">
                    <!-- CAMBIO: Ahora es un enlace a la nueva página -->
                    <a href="espacio_tutoria_tutor.php?tutorRequestID=<?php echo $t['tutorRequestID']; ?>" 
                       style="background:#741fa2; color:white; padding:6px 12px; text-decoration:none; border-radius:4px; font-weight:bold; display:inline-block;">
                       Abrir
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- He eliminado el <script> y el panel lateral que causaban el bloque rojo -->