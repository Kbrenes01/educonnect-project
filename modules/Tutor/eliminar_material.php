<?php
require_once __DIR__ . '/../../config.php';
$id = (int)$_GET['id'];
$trID = (int)$_GET['tutorRequestID'];

$conn = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);
$res = mysqli_query($conn, "SELECT archivoRuta FROM tutoria_materiales WHERE materialID = $id");
$mat = mysqli_fetch_assoc($res);

// Borrar archivo físico y registro BD
unlink(__DIR__ . '/../../uploads/tutor_materials/' . $mat['archivoRuta']);
mysqli_query($conn, "DELETE FROM tutoria_materiales WHERE materialID = $id");

header("Location: espacio_tutoria_tutor.php?tutorRequestID=$trID");
?>