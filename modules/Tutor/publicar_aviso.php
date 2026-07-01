<?php
//Este archivo guarda el aviso asociado únicamente al tutorRequestID.
// C:\xampp\htdocs\educonnect\modules\Tutor\publicar_aviso.php
session_start();
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['is_tutor'])) {
    $tutorRequestID = (int)$_POST['tutorRequestID'];
    $titulo = $_POST['titulo'];
    $mensaje = $_POST['mensaje'];

    $connection = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);
    
    // Verificar conexión
    if (!$connection) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    $sql = "INSERT INTO tutoria_avisos (tutorRequestID, titulo, mensaje) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($connection, $sql);
    
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "iss", $tutorRequestID, $titulo, $mensaje);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        
        // Redirigir siempre de vuelta
        header("Location: espacio_tutoria_tutor.php?tutorRequestID=" . $tutorRequestID . "&success=aviso_publicado");
    } else {
        echo "Error en la consulta: " . mysqli_error($connection);
    }

    mysqli_close($connection);
    exit();
} else {
    // Si alguien intenta entrar directamente sin POST
    header("Location: mis_tutorias_tutor.php");
    exit();
}
?>