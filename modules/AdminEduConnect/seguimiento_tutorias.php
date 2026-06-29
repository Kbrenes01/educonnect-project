<?php
// C:\xampp\htdocs\educonnect\modules\AdminEduConnect\seguimiento_tutorias.php

/*
    HU-25:
    Administrador consulta tutorías por estado.
    Estados:
    - Pendiente
    - Aceptada => En proceso
    - Rechazada
    - Completada
    - Cancelada

    Si está Cancelada, se muestra acción "Ver motivo".
*/

// Importante: Gibbon espera ejecutarse desde la raíz del proyecto.
$projectRoot = realpath(__DIR__ . '/../../');

if (!$projectRoot) {
    die("No se pudo ubicar la raíz del proyecto.");
}

chdir($projectRoot);

require_once $projectRoot . '/gibbon.php';

$session = $container->get('session');

if (!$session->has('username') || !$session->has('gibbonRoleIDCurrent')) {
    header("Location: /educonnect/index.php");
    exit();
}

$adminPersonID = $session->get('gibbonPersonID');
$adminRoleIDCurrent = $session->get('gibbonRoleIDCurrent');
$adminRoleIDPrimary = $session->get('gibbonRoleIDPrimary');

/*
    En Gibbon:
    001 = Administrator
*/
if ($adminRoleIDCurrent !== '001' && $adminRoleIDPrimary !== '001') {
    die("No tenés permiso para acceder a esta pantalla.");
}

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

function getStatusLabel($status) {
    if ($status === 'Aceptada') {
        return 'En proceso';
    }

    return $status;
}

function getStatusStyle($status) {
    switch ($status) {
        case 'Pendiente':
            return 'background:#fff3cd; color:#856404;';
        case 'Aceptada':
            return 'background:#d4edda; color:#155724;';
        case 'Rechazada':
            return 'background:#f8d7da; color:#721c24;';
        case 'Completada':
            return 'background:#d1ecf1; color:#0c5460;';
        case 'Cancelada':
            return 'background:#e2e3e5; color:#383d41;';
        default:
            return 'background:#eeeeee; color:#333333;';
    }
}

$allowedStatuses = [
    'Todos',
    'Pendiente',
    'Aceptada',
    'Rechazada',
    'Completada',
    'Cancelada'
];

$statusFilter = $_GET['estado'] ?? 'Todos';

if (!in_array($statusFilter, $allowedStatuses, true)) {
    $statusFilter = 'Todos';
}

$tutorias = [];

try {
    $params = [];

    $whereStatus = "";

    if ($statusFilter !== 'Todos') {
        $whereStatus = "WHERE tr.status = :status";
        $params['status'] = $statusFilter;
    }

    $sqlTutorias = "
        SELECT
            tr.tutorRequestID,
            tr.studentID,
            tr.tutorID,
            tr.tutorAvailabilityID,
            tr.subject,
            tr.status,
            tr.requestedOn,

            student.username AS studentUsername,
            student.firstName AS studentFirstName,
            student.surname AS studentSurname,

            tutor.username AS tutorUsername,
            tutor.firstName AS tutorFirstName,
            tutor.surname AS tutorSurname,

            ta.availableDay,
            ta.startTime,
            ta.endTime,

            tc.cancellationID,
            tc.cancellationReason,
            tc.cancelledOn,

            cancelledBy.username AS cancelledByUsername,
            cancelledBy.firstName AS cancelledByFirstName,
            cancelledBy.surname AS cancelledBySurname
        FROM tutorrequest tr
        INNER JOIN gibbonPerson student
            ON tr.studentID = student.gibbonPersonID
        INNER JOIN gibbonPerson tutor
            ON tr.tutorID = tutor.gibbonPersonID
        LEFT JOIN tutoravailability ta
            ON tr.tutorAvailabilityID = ta.tutorAvailabilityID
        LEFT JOIN tutorcancellation tc
            ON tc.tutorRequestID = tr.tutorRequestID
           AND tc.cancellationID = (
                SELECT MAX(tc2.cancellationID)
                FROM tutorcancellation tc2
                WHERE tc2.tutorRequestID = tr.tutorRequestID
           )
        LEFT JOIN gibbonPerson cancelledBy
            ON tc.cancelledByPersonID = cancelledBy.gibbonPersonID
        $whereStatus
        ORDER BY tr.requestedOn DESC, tr.tutorRequestID DESC
    ";

 $stmtTutorias = $connection2->prepare($sqlTutorias);
$stmtTutorias->execute($params);

$tutorias = $stmtTutorias->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error al consultar las tutorías.");
}

$filterLabels = [
    'Todos' => 'Todos',
    'Pendiente' => 'Pendientes',
    'Aceptada' => 'En proceso',
    'Rechazada' => 'Rechazadas',
    'Completada' => 'Completadas',
    'Cancelada' => 'Canceladas'
];

$currentAdminName = $session->get('preferredName') ?: $session->get('username');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Seguimiento de tutorías - EduConnect</title>

    <style>
        body {
            margin: 0;
            background: #f4eef9;
            font-family: sans-serif;
        }

        .top-bar {
            background: #741fa2;
            color: white;
            padding: 18px 30px;
            box-shadow: 0 3px 7px rgba(0,0,0,0.15);
        }

        .top-bar h1 {
            margin: 0;
            font-size: 24px;
        }

        .top-bar p {
            margin: 6px 0 0 0;
            color: #f1dff8;
            font-size: 14px;
        }

        .container {
            max-width: 1180px;
            margin: 25px auto;
            padding: 0 15px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #dddddd;
            border-radius: 8px;
            padding: 22px;
            box-shadow: 0 3px 7px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .card h2,
        .card h3 {
            margin-top: 0;
            color: #333333;
        }

        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 12px;
        }

        .filter-button {
            padding: 10px 14px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            display: inline-block;
        }

        .filter-active {
            background: #741fa2;
            color: white;
        }

        .filter-inactive {
            background: #eeeeee;
            color: #333333;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 960px;
        }

        th {
            background: #741fa2;
            color: white;
            padding: 12px;
            text-align: left;
            font-size: 14px;
        }

        td {
            padding: 11px 12px;
            border-bottom: 1px solid #eeeeee;
            font-size: 14px;
            vertical-align: middle;
        }

        tr:nth-child(even) {
            background: #f7f2fa;
        }

        .status-badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
        }

        .reason-button {
            background: #333333;
            color: white;
            padding: 8px 11px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 12px;
            cursor: pointer;
        }

        .empty-message {
            background: #f7f2fa;
            border-left: 5px solid #741fa2;
            padding: 15px;
            border-radius: 8px;
            color: #444444;
            font-size: 14px;
        }

        .back-button {
            background: #333333;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
        }

        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(0,0,0,0.68);
            padding: 25px;
            box-sizing: border-box;
        }

        .modal {
            background: white;
            max-width: 620px;
            margin: 70px auto 0 auto;
            border-radius: 8px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.35);
            overflow: hidden;
        }

        .modal-header {
            background: #f7f2fa;
            border-bottom: 1px solid #dddddd;
            padding: 18px 20px;
        }

        .modal-header h3 {
            margin: 0;
            color: #741fa2;
        }

        .modal-body {
            padding: 20px;
        }

        .reason-box {
            background: #f7f2fa;
            border-left: 5px solid #741fa2;
            padding: 14px;
            border-radius: 6px;
            color: #333333;
            line-height: 1.5;
            margin-top: 10px;
            white-space: pre-wrap;
        }

        .modal-info {
            color: #555555;
            font-size: 14px;
            line-height: 1.6;
        }

        .modal-actions {
            padding: 0 20px 20px 20px;
            display: flex;
            justify-content: flex-end;
        }

        .close-button {
            background: #741fa2;
            color: white;
            padding: 10px 14px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
        }

        @media (max-width: 800px) {
            .top-bar {
                padding: 16px;
            }

            .modal-overlay {
                padding: 10px;
            }

            .modal {
                margin-top: 35px;
            }
        }
    </style>
</head>

<body>

    <div class="top-bar">
        <h1>Seguimiento de tutorías</h1>
        <p>Supervisión general de tutorías pendientes, en proceso, rechazadas, completadas y canceladas.</p>
    </div>

    <div class="container">

        <div class="card">
            <h2>Panel administrativo</h2>
            <p style="color:#555; margin-bottom:0;">
                Administrador: <strong><?php echo htmlspecialchars($currentAdminName); ?></strong>
            </p>
        </div>

        <div class="card">
            <h3>Filtrar por estado</h3>

            <div class="filters">
                <?php foreach ($filterLabels as $filterValue => $filterText): ?>
                    <a
                        class="filter-button <?php echo $statusFilter === $filterValue ? 'filter-active' : 'filter-inactive'; ?>"
                        href="/educonnect/modules/AdminEduConnect/seguimiento_tutorias.php?estado=<?php echo urlencode($filterValue); ?>">
                        <?php echo htmlspecialchars($filterText); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h3>Lista de tutorías</h3>

            <?php if (empty($tutorias)): ?>
                <div class="empty-message">
                    No hay tutorías registradas para el filtro seleccionado.
                </div>
            <?php else: ?>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Tutor</th>
                                <th>Materia</th>
                                <th>Día</th>
                                <th>Horario</th>
                                <th>Estado</th>
                                <th>Fecha solicitud</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($tutorias as $tutoria): ?>
                                <?php
                                    $studentName = trim(($tutoria['studentFirstName'] ?? '') . ' ' . ($tutoria['studentSurname'] ?? ''));
                                    $tutorName = trim(($tutoria['tutorFirstName'] ?? '') . ' ' . ($tutoria['tutorSurname'] ?? ''));

                                    if ($studentName === '') {
                                        $studentName = $tutoria['studentUsername'] ?? 'No definido';
                                    }

                                    if ($tutorName === '') {
                                        $tutorName = $tutoria['tutorUsername'] ?? 'No definido';
                                    }

                                    $schedule = formatHour($tutoria['startTime']) . " - " . formatHour($tutoria['endTime']);
                                    $status = $tutoria['status'];
                                    $statusLabel = getStatusLabel($status);
                                    $statusStyle = getStatusStyle($status);

                                    $reason = $tutoria['cancellationReason'] ?: 'No hay motivo registrado.';
                                    $cancelledOn = formatDateTimeValue($tutoria['cancelledOn']);

                                    $cancelledByName = trim(($tutoria['cancelledByFirstName'] ?? '') . ' ' . ($tutoria['cancelledBySurname'] ?? ''));

                                    if ($cancelledByName === '') {
                                        $cancelledByName = $tutoria['cancelledByUsername'] ?? 'No definido';
                                    }
                                ?>

                                <tr>
                                    <td><?php echo htmlspecialchars($studentName); ?></td>
                                    <td><?php echo htmlspecialchars($tutorName); ?></td>
                                    <td><?php echo htmlspecialchars($tutoria['subject']); ?></td>
                                    <td><?php echo htmlspecialchars($tutoria['availableDay'] ?? 'No definido'); ?></td>
                                    <td><?php echo htmlspecialchars($schedule); ?></td>
                                    <td>
                                        <span class="status-badge" style="<?php echo htmlspecialchars($statusStyle); ?>">
                                            <?php echo htmlspecialchars($statusLabel); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars(formatDateTimeValue($tutoria['requestedOn'])); ?></td>
                                    <td>
                                        <?php if ($status === 'Cancelada'): ?>
                                            <button
                                                type="button"
                                                class="reason-button"
                                                data-student="<?php echo htmlspecialchars($studentName, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-tutor="<?php echo htmlspecialchars($tutorName, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-subject="<?php echo htmlspecialchars($tutoria['subject'], ENT_QUOTES, 'UTF-8'); ?>"
                                                data-reason="<?php echo htmlspecialchars($reason, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-cancelled-on="<?php echo htmlspecialchars($cancelledOn, ENT_QUOTES, 'UTF-8'); ?>"
                                                data-cancelled-by="<?php echo htmlspecialchars($cancelledByName, ENT_QUOTES, 'UTF-8'); ?>">
                                                Ver motivo
                                            </button>
                                        <?php else: ?>
                                            <span style="color:#777777; font-size:13px;">Sin acción</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>

        <div style="margin-bottom:30px;">
            <a href="/educonnect/index.php" class="back-button">
                Volver al inicio
            </a>
        </div>

    </div>

    <div id="reasonModalOverlay" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <h3>Motivo de cancelación</h3>
            </div>

            <div class="modal-body">
                <div class="modal-info">
                    <strong>Estudiante:</strong> <span id="modalStudent"></span><br>
                    <strong>Tutor:</strong> <span id="modalTutor"></span><br>
                    <strong>Materia:</strong> <span id="modalSubject"></span><br>
                    <strong>Cancelado por:</strong> <span id="modalCancelledBy"></span><br>
                    <strong>Fecha de cancelación:</strong> <span id="modalCancelledOn"></span>
                </div>

                <div class="reason-box" id="modalReason"></div>
            </div>

            <div class="modal-actions">
                <button type="button" class="close-button" onclick="closeReasonModal()">
                    Cerrar
                </button>
            </div>
        </div>
    </div>

    <script>
        function openReasonModal(button) {
            const overlay = document.getElementById('reasonModalOverlay');

            document.getElementById('modalStudent').textContent = button.getAttribute('data-student') || 'No definido';
            document.getElementById('modalTutor').textContent = button.getAttribute('data-tutor') || 'No definido';
            document.getElementById('modalSubject').textContent = button.getAttribute('data-subject') || 'No definido';
            document.getElementById('modalReason').textContent = button.getAttribute('data-reason') || 'No hay motivo registrado.';
            document.getElementById('modalCancelledOn').textContent = button.getAttribute('data-cancelled-on') || 'No definido';
            document.getElementById('modalCancelledBy').textContent = button.getAttribute('data-cancelled-by') || 'No definido';

            overlay.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function closeReasonModal() {
            const overlay = document.getElementById('reasonModalOverlay');

            if (overlay) {
                overlay.style.display = 'none';
            }

            document.body.style.overflow = '';
        }

        document.addEventListener('DOMContentLoaded', function () {
            const reasonButtons = document.querySelectorAll('.reason-button');

            reasonButtons.forEach(function (button) {
                button.addEventListener('click', function () {
                    openReasonModal(button);
                });
            });

            const overlay = document.getElementById('reasonModalOverlay');

            if (overlay) {
                overlay.addEventListener('click', function (event) {
                    if (event.target === overlay) {
                        closeReasonModal();
                    }
                });
            }

            document.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    closeReasonModal();
                }
            });
        });
    </script>

</body>
</html>