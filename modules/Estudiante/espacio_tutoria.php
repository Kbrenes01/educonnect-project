<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\espacio_tutoria.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';

if (!isset($_SESSION['is_student']) || $_SESSION['is_student'] !== true) {
    header("Location: /educonnect/modules/Estudiante/estudiante_login.php");
    exit();
}

$studentID = $_SESSION['student_gibbonPersonID'] ?? null;

if (!$studentID) {
    header("Location: /educonnect/modules/Estudiante/estudiante_login.php");
    exit();
}

$tutorRequestID = isset($_GET['tutorRequestID']) ? (int) $_GET['tutorRequestID'] : 0;
$cancelError = $_GET['cancel_error'] ?? '';

if ($tutorRequestID <= 0) {
    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?error=notfound");
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
        tr.tutorRequestID,
        tr.studentID,
        tr.tutorID,
        tr.tutorAvailabilityID,
        tr.subject,
        tr.details,
        tr.status,
        tr.requestedOn,

        tutor.firstName AS tutorFirstName,
        tutor.surname AS tutorSurname,
        tutor.email AS tutorEmail,

        ta.availableDay,
        ta.startTime,
        ta.endTime,

        tp.educationLevel,
        tp.subjects,
        tp.bio
    FROM tutorrequest tr
    INNER JOIN gibbonPerson tutor
        ON tr.tutorID = tutor.gibbonPersonID
    LEFT JOIN tutoravailability ta
        ON tr.tutorAvailabilityID = ta.tutorAvailabilityID
    LEFT JOIN tutorprofile tp
        ON tr.tutorID = tp.gibbonPersonID
    WHERE tr.tutorRequestID = ?
      AND tr.studentID = ?
    LIMIT 1
";

$stmt = mysqli_prepare($connection, $sql);

if (!$stmt) {
    mysqli_close($connection);
    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?error=notfound");
    exit();
}

mysqli_stmt_bind_param($stmt, "is", $tutorRequestID, $studentID);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$tutoria = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$tutoria) {
    mysqli_close($connection);
    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?error=notfound");
    exit();
}

/*
    HU-16:
    El estudiante solo puede abrir tutorías en estado Aceptada.
    En pantalla se muestra como "En proceso".
*/
if ($tutoria['status'] !== 'Aceptada') {
    mysqli_close($connection);
    header("Location: /educonnect/modules/Estudiante/mis_tutorias.php?error=notallowed");
    exit();
}

/*
    HU-17:
    El estudiante puede consultar los avisos de una tutoría en proceso.
    Solo lectura. No puede crear, editar ni eliminar avisos.
*/
$announcements = [];

$sqlAnnouncements = "
    SELECT
        ta.announcementID,
        ta.tutorRequestID,
        ta.tutorID,
        ta.title,
        ta.message,
        ta.createdOn,

        tutor.firstName AS tutorFirstName,
        tutor.surname AS tutorSurname
    FROM tutorannouncement ta
    INNER JOIN tutorrequest tr
        ON ta.tutorRequestID = tr.tutorRequestID
    INNER JOIN gibbonPerson tutor
        ON ta.tutorID = tutor.gibbonPersonID
    WHERE ta.tutorRequestID = ?
      AND tr.studentID = ?
      AND tr.status = 'Aceptada'
      AND ta.tutorID = tr.tutorID
    ORDER BY ta.createdOn DESC
";

$stmtAnnouncements = mysqli_prepare($connection, $sqlAnnouncements);

if ($stmtAnnouncements) {
    mysqli_stmt_bind_param($stmtAnnouncements, "is", $tutorRequestID, $studentID);
    mysqli_stmt_execute($stmtAnnouncements);

    $resultAnnouncements = mysqli_stmt_get_result($stmtAnnouncements);

    while ($row = mysqli_fetch_assoc($resultAnnouncements)) {
        $announcements[] = $row;
    }

    mysqli_stmt_close($stmtAnnouncements);
}

/*
    HU-18:
    El estudiante puede consultar, previsualizar y descargar los materiales PDF
    de una tutoría en proceso.
    Solo lectura. No puede subir, editar ni eliminar PDFs.
*/
$pdfMaterials = [];

$sqlPdfMaterials = "
    SELECT
        tm.pdfMaterialID,
        tm.tutorRequestID,
        tm.tutorID,
        tm.title,
        tm.fileName,
        tm.filePath,
        tm.description,
        tm.uploadedOn,
        tm.isActive,

        tutor.firstName AS tutorFirstName,
        tutor.surname AS tutorSurname
    FROM tutorpdfmaterial tm
    INNER JOIN tutorrequest tr
        ON tm.tutorRequestID = tr.tutorRequestID
    INNER JOIN gibbonPerson tutor
        ON tm.tutorID = tutor.gibbonPersonID
    WHERE tm.tutorRequestID = ?
      AND tr.studentID = ?
      AND tr.status = 'Aceptada'
      AND tm.tutorID = tr.tutorID
      AND tm.isActive = 'Y'
    ORDER BY tm.uploadedOn DESC
";

$stmtPdfMaterials = mysqli_prepare($connection, $sqlPdfMaterials);

if ($stmtPdfMaterials) {
    mysqli_stmt_bind_param($stmtPdfMaterials, "is", $tutorRequestID, $studentID);
    mysqli_stmt_execute($stmtPdfMaterials);

    $resultPdfMaterials = mysqli_stmt_get_result($stmtPdfMaterials);

    while ($row = mysqli_fetch_assoc($resultPdfMaterials)) {
        $pdfMaterials[] = $row;
    }

    mysqli_stmt_close($stmtPdfMaterials);
}

mysqli_close($connection);

function formatHour($time) {
    if (!$time) {
        return "No definido";
    }

    return date('H:i', strtotime($time));
}

function formatDateTimeValue($dateTime) {
    if (!$dateTime) {
        return "No definido";
    }

    return date('d/m/Y H:i', strtotime($dateTime));
}

$tutorName = trim($tutoria['tutorFirstName'] . ' ' . $tutoria['tutorSurname']);
$schedule = formatHour($tutoria['startTime']) . " - " . formatHour($tutoria['endTime']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Espacio de tutoría - EduConnect</title>

    <style>
        .private-option-button {
            display: inline-block;
            border: none;
            cursor: pointer;
            font-family: sans-serif;
            font-size: 14px;
        }

        .private-panel {
            display: none;
            margin-top: 22px;
            border-top: 1px solid #e6ddec;
            padding-top: 20px;
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 16px;
        }

        .panel-header h3 {
            margin: 0;
            color: #333333;
            font-size: 18px;
        }

        .panel-header p {
            margin: 6px 0 0 0;
            color: #555555;
            font-size: 14px;
        }

        .panel-count {
            background: #f0e2f7;
            color: #741fa2;
            padding: 7px 12px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 13px;
            white-space: nowrap;
        }

        .panel-order-note {
            color: #666666;
            font-size: 13px;
            margin-top: 8px;
        }

        .announcements-list,
        .materials-list {
            max-height: 390px;
            overflow-y: auto;
            padding-right: 8px;
        }

        .announcement-card,
        .material-card {
            border-radius: 8px;
            padding: 15px 17px;
            margin-bottom: 14px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .announcement-card {
            background: #f7f2fa;
            border-left: 5px solid #741fa2;
        }

        .material-card {
            background: #ffffff;
            border: 1px solid #e2d7ea;
            border-left: 5px solid #333333;
        }

        .announcement-card-header,
        .material-card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 9px;
        }

        .announcement-card-title,
        .material-card-title {
            color: #222222;
            font-weight: bold;
            font-size: 15px;
        }

        .announcement-card-date,
        .material-card-date {
            color: #666666;
            font-size: 12px;
            white-space: nowrap;
        }

        .announcement-card-message,
        .material-card-description {
            margin: 0;
            color: #333333;
            line-height: 1.5;
            font-size: 14px;
        }

        .announcement-card-footer,
        .material-card-footer {
            margin-top: 11px;
            color: #666666;
            font-size: 12px;
        }

        .empty-panel-message {
            background: #f7f2fa;
            border-left: 5px solid #741fa2;
            padding: 15px;
            border-radius: 8px;
            color: #444444;
            font-size: 14px;
        }

        .material-file-name {
            margin-top: 8px;
            color: #666666;
            font-size: 13px;
        }

        .material-actions {
            margin-top: 13px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .download-button,
        .preview-button {
            color: white;
            padding: 9px 13px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            display: inline-block;
            border: none;
            cursor: pointer;
            font-family: sans-serif;
        }

        .preview-button {
            background: #333333;
        }

        .download-button {
            background: #741fa2;
        }

        .download-button:hover,
        .preview-button:hover {
            opacity: 0.9;
        }

        .pdf-modal-overlay,
        .cancel-modal-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            inset: 0;
            background: rgba(0,0,0,0.68);
            padding: 25px;
            box-sizing: border-box;
        }

        .pdf-modal {
            background: #ffffff;
            border-radius: 8px;
            max-width: 1050px;
            height: 90vh;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            box-shadow: 0 8px 25px rgba(0,0,0,0.35);
            overflow: hidden;
        }

        .pdf-modal-header {
            padding: 14px 18px;
            border-bottom: 1px solid #dddddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            background: #f7f2fa;
        }

        .pdf-modal-title {
            font-weight: bold;
            color: #333333;
        }

        .pdf-modal-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
        }

        .modal-download-button {
            background: #741fa2;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
        }

        .modal-close-button {
            background: #777777;
            color: white;
            padding: 8px 12px;
            border-radius: 5px;
            border: none;
            cursor: pointer;
            font-weight: bold;
            font-size: 13px;
        }

        .pdf-frame {
            width: 100%;
            flex: 1;
            border: none;
        }

        .cancel-modal {
            background: #ffffff;
            border-radius: 8px;
            max-width: 620px;
            margin: 45px auto 0 auto;
            box-shadow: 0 8px 25px rgba(0,0,0,0.35);
            overflow: hidden;
        }

        .cancel-modal-header {
            background: #f7f2fa;
            border-bottom: 1px solid #dddddd;
            padding: 18px 20px;
        }

        .cancel-modal-header h3 {
            margin: 0;
            color: #741fa2;
            font-size: 20px;
        }

        .cancel-modal-header p {
            margin: 8px 0 0 0;
            color: #555555;
            font-size: 14px;
            line-height: 1.45;
        }

        .cancel-modal-body {
            padding: 20px;
        }

        .cancel-summary {
            background: #f7f2fa;
            border-left: 5px solid #741fa2;
            padding: 13px 15px;
            border-radius: 6px;
            margin-bottom: 18px;
            color: #333333;
            font-size: 14px;
            line-height: 1.6;
        }

        .cancel-error-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 14px;
        }

        .cancel-textarea {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            font-family: sans-serif;
            resize: vertical;
            min-height: 120px;
        }

        .cancel-modal-actions {
            margin-top: 18px;
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .confirm-cancel-button {
            background: #741fa2;
            color: white;
            padding: 11px 16px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-family: sans-serif;
        }

        .back-cancel-button {
            background: #333333;
            color: white;
            padding: 11px 16px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-family: sans-serif;
        }

        .soft-warning {
            color: #666666;
            font-size: 13px;
            margin-top: 8px;
            line-height: 1.45;
        }

        @media (max-width: 800px) {
            .main-grid {
                grid-template-columns: 1fr !important;
            }

            .panel-header {
                flex-direction: column;
            }

            .announcement-card-header,
            .material-card-header {
                flex-direction: column;
            }

            .pdf-modal-overlay,
            .cancel-modal-overlay {
                padding: 10px;
            }

            .pdf-modal {
                height: 92vh;
            }

            .pdf-modal-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .cancel-modal {
                margin-top: 20px;
            }
        }
    </style>
</head>
<body style="margin:0; background:#f4eef9; font-family: sans-serif;">

<?php
$pageSubtitle = 'Espacio privado de tutoría';
require_once __DIR__ . '/estudiante_header.php';
?>

    <div style="max-width: 1050px; margin: 25px auto; padding: 0 15px;">

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:24px; box-shadow:0 3px 7px rgba(0,0,0,0.08); margin-bottom:20px;">
            <h2 style="margin-top:0; color:#741fa2;">Tutoría en proceso</h2>
            <p style="color:#555; margin-bottom:0;">
                Este es el espacio privado de la tutoría. Desde aquí podés consultar los datos principales y acceder a las opciones disponibles.
            </p>
        </div>

        <div class="main-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:20px; align-items:stretch;">

            <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08);">
                <h3 style="margin-top:0; color:#333;">Datos principales</h3>

                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555; width:42%;">Tutor:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutorName); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Materia:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutoria['subject']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Nivel:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutoria['educationLevel'] ?? 'No definido'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Día:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutoria['availableDay'] ?? 'No definido'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Horario:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($schedule); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Estado:</td>
                        <td style="padding:9px;">
                            <span style="background:#D4EDDA; color:#155724; padding:6px 10px; border-radius:20px; font-weight:bold; font-size:13px;">
                                En proceso
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Fecha de solicitud:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars(formatDateTimeValue($tutoria['requestedOn'])); ?></td>
                    </tr>
                </table>
            </div>

            <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08);">
                <h3 style="margin-top:0; color:#333;">Tutor asignado</h3>

                <table style="width:100%; border-collapse:collapse;">
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555; width:42%;">Nombre:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutorName); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Materias:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutoria['subjects'] ?? $tutoria['subject']); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555;">Nivel:</td>
                        <td style="padding:9px;"><?php echo htmlspecialchars($tutoria['educationLevel'] ?? 'No definido'); ?></td>
                    </tr>
                    <tr>
                        <td style="padding:9px; font-weight:bold; color:#555; vertical-align:top;">Descripción:</td>
                        <td style="padding:9px; line-height:1.45;">
                            <?php echo htmlspecialchars($tutoria['bio'] ?? 'Sin descripción registrada.'); ?>
                        </td>
                    </tr>
                </table>
            </div>

        </div>

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08); margin-bottom:20px;">
            <h3 style="margin-top:0; color:#333;">Necesidad académica indicada</h3>

            <div style="background:#f7f2fa; border-left:5px solid #741fa2; padding:15px; border-radius:5px; color:#333;">
                <?php echo nl2br(htmlspecialchars($tutoria['details'])); ?>
            </div>
        </div>

        <div style="background:#ffffff; border:1px solid #dddddd; border-radius:8px; padding:22px; box-shadow:0 3px 7px rgba(0,0,0,0.08);">
            <h3 style="margin-top:0; color:#333;">Opciones del espacio privado</h3>

            <p style="color:#555;">
                Desde estas opciones podés consultar la información publicada por el tutor para esta tutoría.
            </p>

            <div style="display:flex; gap:15px; flex-wrap:wrap; margin-top:15px;">

                <button type="button"
                        id="announcementsToggleButton"
                        onclick="toggleAnnouncements()"
                        class="private-option-button"
                        style="background:#741fa2; color:white; padding:12px 18px; border-radius:5px; text-decoration:none; font-weight:bold;">
                    Avisos
                </button>

                <button type="button"
                        id="materialsToggleButton"
                        onclick="toggleMaterials()"
                        class="private-option-button"
                        style="background:#333333; color:white; padding:12px 18px; border-radius:5px; text-decoration:none; font-weight:bold;">
                    Materiales PDF
                </button>

                <button type="button"
                        onclick="openStudentCancelModal()"
                        class="private-option-button"
                        style="background:#777777; color:white; padding:12px 18px; border-radius:5px; text-decoration:none; font-weight:bold;">
                    Cancelar tutoría
                </button>

            </div>

            <div id="announcementsPanel" class="private-panel">
                <div class="panel-header">
                    <div>
                        <h3>Avisos de la tutoría</h3>
                        <p>Indicaciones publicadas por el tutor para esta tutoría en proceso.</p>
                        <div class="panel-order-note">Ordenados del más reciente al más antiguo.</div>
                    </div>

                    <span class="panel-count">
                        <?php echo count($announcements); ?> aviso(s)
                    </span>
                </div>

                <?php if (empty($announcements)): ?>
                    <div class="empty-panel-message">
                        No hay avisos publicados para esta tutoría.
                    </div>
                <?php else: ?>
                    <div class="announcements-list">
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="announcement-card">
                                <div class="announcement-card-header">
                                    <div class="announcement-card-title">
                                        <?php echo htmlspecialchars($announcement['title']); ?>
                                    </div>

                                    <div class="announcement-card-date">
                                        <?php echo htmlspecialchars(formatDateTimeValue($announcement['createdOn'])); ?>
                                    </div>
                                </div>

                                <p class="announcement-card-message">
                                    <?php echo nl2br(htmlspecialchars($announcement['message'])); ?>
                                </p>

                                <div class="announcement-card-footer">
                                    Publicado por
                                    <?php
                                        echo htmlspecialchars(
                                            trim($announcement['tutorFirstName'] . ' ' . $announcement['tutorSurname'])
                                        );
                                    ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div id="materialsPanel" class="private-panel">
                <div class="panel-header">
                    <div>
                        <h3>Materiales PDF de la tutoría</h3>
                        <p>Recursos compartidos por el tutor para esta tutoría en proceso.</p>
                        <div class="panel-order-note">Ordenados del más reciente al más antiguo.</div>
                    </div>

                    <span class="panel-count">
                        <?php echo count($pdfMaterials); ?> PDF(s)
                    </span>
                </div>

                <?php if (empty($pdfMaterials)): ?>
                    <div class="empty-panel-message">
                        No hay materiales PDF publicados para esta tutoría.
                    </div>
                <?php else: ?>
                    <div class="materials-list">
                        <?php foreach ($pdfMaterials as $material): ?>
                            <?php
                                $pdfMaterialID = (int) $material['pdfMaterialID'];
                                $previewUrl = "/educonnect/modules/Estudiante/descargar_material.php?pdfMaterialID=" . urlencode($pdfMaterialID) . "&mode=preview";
                                $downloadUrl = "/educonnect/modules/Estudiante/descargar_material.php?pdfMaterialID=" . urlencode($pdfMaterialID) . "&mode=download";
                            ?>

                            <div class="material-card">
                                <div class="material-card-header">
                                    <div class="material-card-title">
                                        📄 <?php echo htmlspecialchars($material['title']); ?>
                                    </div>

                                    <div class="material-card-date">
                                        <?php echo htmlspecialchars(formatDateTimeValue($material['uploadedOn'])); ?>
                                    </div>
                                </div>

                                <p class="material-card-description">
                                    <?php echo nl2br(htmlspecialchars($material['description'] ?? 'Sin descripción registrada.')); ?>
                                </p>

                                <div class="material-file-name">
                                    Archivo:
                                    <strong><?php echo htmlspecialchars($material['fileName']); ?></strong>
                                </div>

                                <div class="material-card-footer">
                                    Subido por
                                    <?php
                                        echo htmlspecialchars(
                                            trim($material['tutorFirstName'] . ' ' . $material['tutorSurname'])
                                        );
                                    ?>
                                </div>

                                <div class="material-actions">
                                    <button type="button"
                                            class="preview-button"
                                            data-preview-url="<?php echo htmlspecialchars($previewUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-download-url="<?php echo htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                            data-title="<?php echo htmlspecialchars($material['title'], ENT_QUOTES, 'UTF-8'); ?>">
                                        Previsualizar PDF
                                    </button>

                                    <a class="download-button"
                                       href="<?php echo htmlspecialchars($downloadUrl, ENT_QUOTES, 'UTF-8'); ?>">
                                        Descargar PDF
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div style="margin-top:20px; display:flex; gap:12px; flex-wrap:wrap;">
            <a href="/educonnect/modules/Estudiante/mis_tutorias.php"
               style="background:#333333; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; font-weight:bold;">
                Volver a Mis tutorías
            </a>

            <a href="/educonnect/modules/Estudiante/estudiante_home.php"
               style="background:#741fa2; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; font-weight:bold;">
                Volver al inicio
            </a>
        </div>

    </div>

    <div id="pdfModalOverlay" class="pdf-modal-overlay">
        <div class="pdf-modal">
            <div class="pdf-modal-header">
                <div id="pdfModalTitle" class="pdf-modal-title">
                    Vista previa del PDF
                </div>

                <div class="pdf-modal-actions">
                    <a id="pdfModalDownloadLink"
                       class="modal-download-button"
                       href="#">
                        Descargar PDF
                    </a>

                    <button type="button"
                            class="modal-close-button"
                            onclick="closePdfPreview()">
                        Cerrar
                    </button>
                </div>
            </div>

            <iframe id="pdfPreviewFrame"
                    class="pdf-frame"
                    src="">
            </iframe>
        </div>
    </div>

    <div id="studentCancelModalOverlay" class="cancel-modal-overlay">
        <div class="cancel-modal">
            <div class="cancel-modal-header">
                <h3>Cancelar tutoría</h3>
                <p>
                    Indicá el motivo por el cual esta tutoría no continuará.
                    La tutoría <strong>terminará</strong> de inmediato y deberá volverla a solicitar.
                </p>
            </div>

            <div class="cancel-modal-body">

                <?php if ($cancelError === 'reason'): ?>
                    <div class="cancel-error-message">
                        Debés ingresar un motivo de cancelación.
                    </div>
                <?php elseif ($cancelError === 'system'): ?>
                    <div class="cancel-error-message">
                        Ocurrió un error al cancelar la tutoría. Intentá nuevamente.
                    </div>
                <?php endif; ?>

                <div class="cancel-summary">
                    <strong>Tutoría:</strong> <?php echo htmlspecialchars($tutoria['subject']); ?><br>
                    <strong>Tutor:</strong> <?php echo htmlspecialchars($tutorName); ?><br>
                    <strong>Horario:</strong> <?php echo htmlspecialchars($tutoria['availableDay'] ?? 'No definido'); ?>,
                    <?php echo htmlspecialchars($schedule); ?>
                </div>

                <form method="POST"
                      action="/educonnect/modules/Estudiante/cancelar_tutoria_estudiante_process.php"
                      onsubmit="return validateStudentCancellationForm();">

                    <input type="hidden"
                           name="tutorRequestID"
                           value="<?php echo htmlspecialchars($tutorRequestID); ?>">

                    <label for="studentCancellationReason" style="display:block; font-weight:bold; color:#555; margin-bottom:8px;">
                        Motivo de cancelación:
                    </label>

                    <textarea
                        id="studentCancellationReason"
                        name="cancellationReason"
                        required
                        rows="6"
                        maxlength="500"
                        class="cancel-textarea"
                        placeholder="Ejemplo: No podré continuar con la tutoría por un cambio de horario."></textarea>

                    <div class="soft-warning">
                        El motivo es obligatorio.
                    </div>

                    <div class="cancel-modal-actions">
                        <button type="submit" class="confirm-cancel-button">
                            Confirmar cancelación
                        </button>

                        <button type="button"
                                class="back-cancel-button"
                                onclick="closeStudentCancelModal()">
                            Regresar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function hidePanel(panelId, buttonId, buttonText) {
            const panel = document.getElementById(panelId);
            const button = document.getElementById(buttonId);

            if (panel) {
                panel.style.display = 'none';
            }

            if (button) {
                button.textContent = buttonText;
            }
        }

        function toggleAnnouncements() {
            const panel = document.getElementById('announcementsPanel');
            const button = document.getElementById('announcementsToggleButton');

            if (!panel || !button) {
                return;
            }

            const isHidden = panel.style.display === '' || panel.style.display === 'none';

            hidePanel('materialsPanel', 'materialsToggleButton', 'Materiales PDF');

            if (isHidden) {
                panel.style.display = 'block';
                button.textContent = 'Ocultar avisos';
                panel.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            } else {
                panel.style.display = 'none';
                button.textContent = 'Avisos';
            }
        }

        function toggleMaterials() {
            const panel = document.getElementById('materialsPanel');
            const button = document.getElementById('materialsToggleButton');

            if (!panel || !button) {
                return;
            }

            const isHidden = panel.style.display === '' || panel.style.display === 'none';

            hidePanel('announcementsPanel', 'announcementsToggleButton', 'Avisos');

            if (isHidden) {
                panel.style.display = 'block';
                button.textContent = 'Ocultar materiales';
                panel.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            } else {
                panel.style.display = 'none';
                button.textContent = 'Materiales PDF';
            }
        }

        function openPdfPreview(previewUrl, downloadUrl, title) {
            const overlay = document.getElementById('pdfModalOverlay');
            const frame = document.getElementById('pdfPreviewFrame');
            const titleElement = document.getElementById('pdfModalTitle');
            const downloadLink = document.getElementById('pdfModalDownloadLink');

            if (!overlay || !frame || !titleElement || !downloadLink) {
                return;
            }

            titleElement.textContent = title || 'Vista previa del PDF';
            downloadLink.href = downloadUrl;
            frame.src = previewUrl;
            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closePdfPreview() {
            const overlay = document.getElementById('pdfModalOverlay');
            const frame = document.getElementById('pdfPreviewFrame');

            if (frame) {
                frame.src = '';
            }

            if (overlay) {
                overlay.style.display = 'none';
            }

            document.body.style.overflow = '';
        }

        function openStudentCancelModal() {
            const overlay = document.getElementById('studentCancelModalOverlay');

            if (!overlay) {
                return;
            }

            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';

            const textarea = document.getElementById('studentCancellationReason');

            if (textarea) {
                setTimeout(function () {
                    textarea.focus();
                }, 100);
            }
        }

        function closeStudentCancelModal() {
            const overlay = document.getElementById('studentCancelModalOverlay');

            if (overlay) {
                overlay.style.display = 'none';
            }

            document.body.style.overflow = '';
        }

        function validateStudentCancellationForm() {
            const textarea = document.getElementById('studentCancellationReason');

            if (!textarea || textarea.value.trim() === '') {
                alert('Debés ingresar un motivo de cancelación.');
                return false;
            }

            return confirm('¿Seguro que querés cancelar esta tutoría?');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const previewButtons = document.querySelectorAll('.preview-button');

            previewButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    const previewUrl = button.getAttribute('data-preview-url');
                    const downloadUrl = button.getAttribute('data-download-url');
                    const title = button.getAttribute('data-title');

                    openPdfPreview(previewUrl, downloadUrl, title);
                });
            });

            const pdfOverlay = document.getElementById('pdfModalOverlay');

            if (pdfOverlay) {
                pdfOverlay.addEventListener('click', function (event) {
                    if (event.target === pdfOverlay) {
                        closePdfPreview();
                    }
                });
            }

            const cancelOverlay = document.getElementById('studentCancelModalOverlay');

            if (cancelOverlay) {
                cancelOverlay.addEventListener('click', function (event) {
                    if (event.target === cancelOverlay) {
                        closeStudentCancelModal();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closePdfPreview();
                    closeStudentCancelModal();
                }
            });

            <?php if ($cancelError === 'reason' || $cancelError === 'system'): ?>
                openStudentCancelModal();
            <?php endif; ?>
        });
    </script>

</body>
</html>