<?php
session_start();
require_once __DIR__ . '/../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tutorRequestID = (int)$_POST['tutorRequestID'];
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $archivo = $_FILES['archivoPDF'];
    
    $rutaCarpeta = 'C:/xampp/htdocs/educonnect/uploads/tutor_materials/';
    
    $nombreOriginal = basename($archivo['name']);
    $rutaDestino = $rutaCarpeta . $nombreOriginal;

    if (move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
        $conn = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);
        $stmt = mysqli_prepare($conn, "INSERT INTO tutoria_materiales (tutorRequestID, titulo, descripcion, archivoRuta) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "isss", $tutorRequestID, $titulo, $descripcion, $nombreOriginal);
        
        if (mysqli_stmt_execute($stmt)) {
            header("Location: espacio_tutoria_tutor.php?tutorRequestID=$tutorRequestID&success=1");
        } else {
            echo "Error en Base de Datos: " . mysqli_error($conn);
        }
        mysqli_close($conn);
    } else {
        die("Error: No se pudo mover el archivo. Verifica permisos de escritura.");
    }
    exit();
}
?>