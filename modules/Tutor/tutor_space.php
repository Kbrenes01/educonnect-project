<?php
// Espacio privado de tutoría para el Turo
session_start();
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['is_tutor']) || $_SESSION['is_tutor'] !== true || !isset($_GET['id'])) {
    header("Location: /educonnect/modules/Tutor/tutor_my_tutorings.php");
    exit();
}

$requestID = (int)$_GET['id'];
$connection = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);

// Consulta los detalles de la tutoría y el nombre del estudiante
$sql = "SELECT tr.*, p.officialName AS studentName 
        FROM tutorrequest tr
        JOIN gibbonPerson p ON tr.studentID = p.gibbonPersonID
        WHERE tr.tutorRequestID = ? AND tr.tutorID = ?";
$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "is", $requestID, $_SESSION['tutor_gibbonPersonID']);
mysqli_stmt_execute($stmt);
$tutoria = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_close($connection);

if (!$tutoria) { die("Acceso no autorizado o tutoría no encontrada."); }
?>

<!DOCTYPE html>
<html lang="es">
<body style="font-family: sans-serif; background: #f4eef9; padding: 20px;">
    <div style="max-width: 900px; margin: auto; background: white; padding: 25px; border-radius: 8px; border: 1px solid #ddd;">
        <h2 style="color: #741fa2;">Espacio Privado: <?php echo htmlspecialchars($tutoria['subject']); ?></h2>
        
        <div style="background: #f9f9f9; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <p><strong>Estudiante:</strong> <?php echo htmlspecialchars($tutoria['studentName']); ?></p>
            <p><strong>Necesidad académica:</strong> <?php echo htmlspecialchars($tutoria['details']); ?></p>
        </div>

        <!-- Herramientas de Gestión -->
        <div style="display: flex; gap: 20px;">
            <div style="flex: 1;">
                <h4>Gestión de Avisos</h4>
                <textarea style="width: 100%; height: 80px; margin-bottom: 10px;"></textarea>
                <button style="background: #741fa2; color: white; border: none; padding: 10px; width: 100%;">Publicar Aviso</button>
            </div>
            <div style="flex: 1;">
                <h4>Materiales (PDFs)</h4>
                <input type="file" style="margin-bottom: 10px;">
                <button style="background: #2b6cb0; color: white; border: none; padding: 10px; width: 100%;">Subir Material</button>
            </div>
        </div>

        <hr style="margin: 25px 0;">
        <a href="/educonnect/modules/Tutor/tutor_my_tutorings.php" style="background: #333; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;">← Volver a Mis Tutorías</a>
    </div>
</body>
</html>