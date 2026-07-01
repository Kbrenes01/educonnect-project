<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/educonnect/config.php';
$id = $_GET['id'];
$conn = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);

// Obtener datos básicos
$sql = "SELECT tr.*, p.officialName FROM tutorrequest tr JOIN gibbonPerson p ON tr.studentID = p.gibbonPersonID WHERE tr.tutorRequestID = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "s", $id);
mysqli_stmt_execute($stmt);
$t = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// --- LÓGICA DE PROCESAMIENTO (Avisos y Estados) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_status'])) {
        $newStatus = $_POST['status'];
        $upd = mysqli_prepare($conn, "UPDATE tutorrequest SET status = ? WHERE tutorRequestID = ?");
        mysqli_stmt_bind_param($upd, "ss", $newStatus, $id);
        mysqli_stmt_execute($upd);
        echo "<p style='color:green;'>Estado actualizado.</p>";
    }
}
?>

<div style="font-size: 14px;">
    <h3 style="color: #741fa2;"><?php echo $t['subject']; ?></h3>
    <p><strong>Estudiante:</strong> <?php echo $t['officialName']; ?></p>
    
    <!-- 1. Gestión de Estados -->
    <form method="POST" onsubmit="event.preventDefault(); updateStatus(this)">
        <input type="hidden" name="update_status" value="1">
        <select name="status" style="width: 100%; padding: 5px;">
            <option value="Aceptada" <?php if($t['status']=='Aceptada') echo 'selected'; ?>>En proceso</option>
            <option value="Completada" <?php if($t['status']=='Completada') echo 'selected'; ?>>Completada</option>
            <option value="Cancelada" <?php if($t['status']=='Cancelada') echo 'selected'; ?>>Cancelada</option>
        </select>
        <button type="submit" style="width: 100%; margin-top:5px; background: #741fa2; color:white; border:none; padding:5px;">Guardar Estado</button>
    </form>

    <hr>

    <!-- 2. Avisos -->
    <h4>Avisos</h4>
    <textarea id="aviso-txt" placeholder="Escribe un aviso..." style="width: 100%; height: 50px;"></textarea>
    <button onclick="alert('Aviso guardado')" style="width: 100%; background: #4a5568; color:white; border:none; padding:5px;">Publicar Aviso</button>

    <hr>

    <!-- 3. Materiales PDF -->
    <h4>Materiales</h4>
    <input type="file" accept="application/pdf" style="width: 100%;">
    <button style="width: 100%; background: #2b6cb0; color:white; border:none; padding:5px; margin-top:5px;">Subir PDF</button>
</div>

<script>
function updateStatus(form) {
    let formData = new FormData(form);
    fetch('tutor_details_ajax.php?id=<?php echo $id; ?>', { method: 'POST', body: formData })
        .then(() => alert('Estado actualizado'));
}
</script>