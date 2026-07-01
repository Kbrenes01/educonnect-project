<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\espacio_tutoria_tutor.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['is_tutor']) || $_SESSION['is_tutor'] !== true) {
    header("Location: /educonnect/modules/Tutor/tutor_login.php"); exit();
}

$tutorID = $_SESSION['tutor_gibbonPersonID'] ?? null;
$tutorRequestID = isset($_GET['tutorRequestID']) ? (int) $_GET['tutorRequestID'] : 0;

if (!$tutorID || $tutorRequestID <= 0) {
    header("Location: /educonnect/modules/Tutor/mis_tutorias_tutor.php?error=notfound"); exit();
}

$connection = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);
mysqli_set_charset($connection, "utf8mb4");

$sql = "SELECT tr.*, estudiante.firstName AS estFirstName, estudiante.surname AS estSurname, ta.startTime, ta.endTime
        FROM tutorrequest tr
        INNER JOIN gibbonPerson estudiante ON tr.studentID = estudiante.gibbonPersonID
        LEFT JOIN tutoravailability ta ON tr.tutorAvailabilityID = ta.tutorAvailabilityID
        WHERE tr.tutorRequestID = ? AND tr.tutorID = ? LIMIT 1";

$stmt = mysqli_prepare($connection, $sql);
mysqli_stmt_bind_param($stmt, "ii", $tutorRequestID, $tutorID);
mysqli_stmt_execute($stmt);
$tutoria = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$tutoria) { mysqli_close($connection); header("Location: /educonnect/modules/Tutor/mis_tutorias_tutor.php?error=notfound"); exit(); }

function formatHour($time) { return $time ? date('H:i', strtotime($time)) : "No definido"; }
$estudianteName = trim($tutoria['estFirstName'] . ' ' . $tutoria['estSurname']);
$schedule = formatHour($tutoria['startTime']) . " - " . formatHour($tutoria['endTime']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Espacio de Tutoría - EduConnect</title>
</head>
<body style="margin:0; background:#f4eef9; font-family: sans-serif;">

    <?php require_once __DIR__ . '/tutor_header.php'; ?>

      <div style="max-width: 1050px; margin: 25px auto; padding: 0 15px;">

        <div style="background:#ffffff; padding:24px; border-radius:8px; margin-bottom:20px;">

            <h2 style="color:#741fa2;">Gestión de Tutoría en Proceso</h2>

            <p>Espacio administrativo para el tutor. Aquí puedes gestionar avisos y materiales para tu estudiante.</p>

        </div>

        <div style="background:#ffffff; padding:22px; border-radius:8px;">
            <h3>Datos del estudiante</h3>
            <p><strong>Estudiante:</strong> <?php echo htmlspecialchars($estudianteName); ?></p>
            <p><strong>Materia:</strong> <?php echo htmlspecialchars($tutoria['subject']); ?></p>
            <p><strong>Horario:</strong> <?php echo htmlspecialchars($schedule); ?></p>
            <hr>
            <p><strong>Necesidad:</strong></p>
            <p style="background:#f9f9f9; padding:10px; border-left:4px solid #741fa2;"><?php echo nl2br(htmlspecialchars($tutoria['details'])); ?></p>
        </div>

        <!-- Acciones de Estado -->
        <div style="margin: 20px 0; display: flex; gap: 10px;">
                    <!-- Botón Marcar como Completada con confirmación -->
<form action="actualizar_estado.php" method="POST" onsubmit="return confirm('¿Estás seguro de que deseas marcar esta tutoría como Completada? Esta acción cerrará formalmente la tutoría.');">
    <input type="hidden" name="tutorRequestID" value="<?php echo $tutorRequestID; ?>">
    <input type="hidden" name="nuevo_estado" value="Completada">
    <button type="submit" style="background:#28a745; color:white; padding:10px; border:none; border-radius:4px; cursor:pointer;">
        Marcar como Completada
    </button>
</form>

            <button onclick="document.getElementById('modalCancelacion').style.display='block'" style="background:#dc3545; color:white; padding:10px; border:none; cursor:pointer; border-radius:4px;">Cancelar Tutoría</button>
        </div>


        <div style="margin-top:20px;">
    <button type="button" 
        onclick="document.getElementById('modalAviso').style.display='block'" 
        style="background:#741fa2; color:white; padding:12px 20px; border:none; border-radius:8px; cursor:pointer; font-weight:bold; position:relative; z-index:100;">
    Crear nuevo aviso
</button>
            <button type="button" 
        onclick="abrirModal('modalPDF')" 
        style="background:#333; color:white; padding:10px; border:none; cursor:pointer;">
    Subir nuevo PDF
</button>
        </div>
    </div>

 <!-- Modal Crear Aviso Mejorado -->
<div id="modalAviso" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:3000; backdrop-filter: blur(3px);">
    <div style="background:white; width:450px; margin:80px auto; padding:30px; border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.3); position:relative;">
        
        <!-- Botón de Cerrar (X) -->
        <button onclick="document.getElementById('modalAviso').style.display='none'" 
                style="position:absolute; top:15px; right:15px; background:transparent; border:none; font-size:20px; cursor:pointer; color:#888;">
            &times;
        </button>

        <h2 style="margin-top:0; color:#741fa2;">Nuevo Aviso</h2>
        <p style="color:#666; font-size: 14px;">Comparte una indicación importante con tu estudiante.</p>

        <form action="publicar_aviso.php" method="POST">
            <input type="hidden" name="tutorRequestID" value="<?php echo $tutorRequestID; ?>">
            
            <input type="text" name="titulo" placeholder="Título del aviso" required 
                   style="width:100%; padding:12px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px; box-sizing:border-box;">
            
            <textarea name="mensaje" placeholder="Escribe aquí el mensaje..." required 
                      style="width:100%; height:120px; padding:12px; margin-bottom:20px; border:1px solid #ddd; border-radius:8px; resize:none; box-sizing:border-box;"></textarea>
            
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('modalAviso').style.display='none'" 
                        style="padding:10px 20px; border:1px solid #ddd; background:#f9f9f9; border-radius:8px; cursor:pointer;">
                    Cancelar
                </button>
                <button type="submit" 
                        style="padding:10px 20px; background:#741fa2; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">
                    Publicar Aviso
                </button>
            </div>
        </form>
    </div>
</div>

    <!-- Modal Cancelación -->
  <div id="modalCancelacion" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:3000; backdrop-filter: blur(3px);">
    <div style="background:white; width:450px; margin:80px auto; padding:30px; border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.3); position:relative;">
        
        <button type="button" onclick="document.getElementById('modalCancelacion').style.display='none'" 
                style="position:absolute; top:15px; right:15px; background:transparent; border:none; font-size:20px; cursor:pointer; color:#888;">&times;</button>

        <h2 style="margin-top:0; color:#d9534f;">Cancelar Tutoría</h2>
        <p style="color:#666; font-size: 14px;">Indica el motivo de la cancelación. Esta acción liberará el horario del tutor.</p>

        <form action="cancelar_tutoria.php" method="POST">
            <input type="hidden" name="tutorRequestID" value="<?php echo $tutorRequestID; ?>">
            <textarea name="motivo" required placeholder="Escribe el motivo aquí..."
                      style="width:100%; height:100px; padding:12px; margin-bottom:20px; border:1px solid #ddd; border-radius:8px; resize:none; box-sizing:border-box;"></textarea>
            
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('modalCancelacion').style.display='none'" 
                        style="padding:10px 20px; border:1px solid #ddd; background:#f9f9f9; border-radius:8px; cursor:pointer;">Cerrar</button>
                <button type="submit" 
                        style="padding:10px 20px; background:#d9534f; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">Confirmar Cancelación</button>
            </div>
        </form>
    </div>
</div>


<?php
// Consulta para traer los avisos de esta tutoría
$avisosStmt = mysqli_prepare($connection, "SELECT * FROM tutoria_avisos WHERE tutorRequestID = ? ORDER BY fechaPublicacion DESC");
mysqli_stmt_bind_param($avisosStmt, "i", $tutorRequestID);
mysqli_stmt_execute($avisosStmt);
$avisos = mysqli_stmt_get_result($avisosStmt);
?>


<!-- Modal Ver Aviso -->
<div id="modalVerAviso" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:3000; backdrop-filter: blur(3px);">
    <div style="background:white; width:400px; margin:100px auto; padding:25px; border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.3); position:relative;">
        <button type="button" onclick="document.getElementById('modalVerAviso').style.display='none'" 
                style="position:absolute; top:15px; right:15px; background:transparent; border:none; font-size:20px; cursor:pointer; color:#888;">&times;</button>
        
        <h3 id="modalTitulo" style="color:#741fa2; margin-top:0;"></h3>
        <p id="modalMensaje" style="color:#555; line-height:1.5;"></p>
        
        <div style="text-align:right; margin-top:20px;">
            <button onclick="document.getElementById('modalVerAviso').style.display='none'" 
                    style="padding:8px 20px; background:#eee; border:none; border-radius:8px; cursor:pointer;">Cerrar</button>
        </div>
    </div>
</div>


<div id="modalPDF" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:3000; backdrop-filter: blur(3px);">
    <div style="background:white; width:450px; margin:80px auto; padding:30px; border-radius:15px; box-shadow:0 10px 25px rgba(0,0,0,0.3); position:relative;">
        
        <button type="button" onclick="document.getElementById('modalPDF').style.display='none'" 
                style="position:absolute; top:15px; right:15px; background:transparent; border:none; font-size:20px; cursor:pointer; color:#888;">&times;</button>

        <h2 style="margin-top:0; color:#333;">Subir Material PDF</h2>
        <p style="color:#666; font-size: 14px;">Comparte recursos académicos con tu estudiante.</p>

        <form action="subir_material.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="tutorRequestID" value="<?php echo $tutorRequestID; ?>">
            <input type="text" name="titulo" placeholder="Título del material" required 
                   style="width:100%; padding:12px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px; box-sizing:border-box;">
            <textarea name="descripcion" placeholder="Descripción breve" 
                      style="width:100%; height:80px; padding:12px; margin-bottom:15px; border:1px solid #ddd; border-radius:8px; resize:none; box-sizing:border-box;"></textarea>
            <input type="file" name="archivoPDF" accept=".pdf" required style="margin-bottom:20px; width:100%;">
            
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button type="button" onclick="document.getElementById('modalPDF').style.display='none'" 
                        style="padding:10px 20px; border:1px solid #ddd; background:#f9f9f9; border-radius:8px; cursor:pointer;">Cancelar</button>
                <button type="submit" 
                        style="padding:10px 20px; background:#333; color:white; border:none; border-radius:8px; cursor:pointer; font-weight:bold;">Subir PDF</button>
            </div>
        </form>
    </div>
</div>
<style>
    .tabla-recursos { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
    .tabla-recursos th { background: #f8f9fa; padding: 12px; text-align: left; border-bottom: 2px solid #eee; }
    .tabla-recursos td { padding: 12px; border-bottom: 1px solid #eee; }
    .btn-accion { padding: 5px 12px; border-radius: 4px; border: 1px solid #ccc; background: #fff; cursor: pointer; font-size: 12px; }
    .btn-eliminar { color: #dc3545; text-decoration: none; font-size: 12px; border: 1px solid #dc3545; padding: 4px 8px; border-radius: 4px; margin-left: 10px; }
</style>

<h3>Avisos Publicados</h3>
<table class="tabla-recursos">
    <?php while ($av = mysqli_fetch_assoc($avisos)): ?>
        <tr>
            <td><strong><?php echo htmlspecialchars($av['titulo']); ?></strong><br><small style="color:#888;"><?php echo $av['fechaPublicacion']; ?></small></td>
            <td style="text-align:right;">
                <button class="btn-accion" onclick="verAviso('<?php echo htmlspecialchars($av['titulo'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($av['mensaje'], ENT_QUOTES); ?>')">Ver detalles</button>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<h3 style="margin-top:30px;">Materiales Disponibles</h3>
<table class="tabla-recursos">
    <?php 
    $res = mysqli_query($connection, "SELECT * FROM tutoria_materiales WHERE tutorRequestID = $tutorRequestID");
    while ($mat = mysqli_fetch_assoc($res)): ?>
        <tr>
            <td>
                <a href="/educonnect/uploads/tutor_materials/<?php echo $mat['archivoRuta']; ?>" target="_blank" style="color:#741fa2; font-weight:bold; text-decoration:none;">
                    📄 <?php echo htmlspecialchars($mat['titulo']); ?>
                </a>
            </td>
            <td style="text-align:right;">
                <a href="eliminar_material.php?id=<?php echo $mat['materialID']; ?>&tutorRequestID=<?php echo $tutorRequestID; ?>" 
                   class="btn-eliminar" onclick="return confirm('¿Seguro que deseas eliminar este archivo?')">Eliminar</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<script>
// Función para abrir modales
function abrirModal(id) {
    document.getElementById(id).style.display = 'block';
}

// Función para cerrar modales (si se hace clic fuera del contenido)
window.onclick = function(event) {
    var modales = ['modalAviso', 'modalCancelacion', 'modalPDF'];
    modales.forEach(function(id) {
        var modal = document.getElementById(id);
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });
}


function verAviso(titulo, mensaje) {
    document.getElementById('modalTitulo').innerText = titulo;
    document.getElementById('modalMensaje').innerText = mensaje;
    document.getElementById('modalVerAviso').style.display = 'block';
}

</script>
</body>
</html>