<?php
// COMENTAMOS LA SEGURIDAD TEMPORALMENTE PARA LA PRUEBA DEL SPRINT
// if (isActionAccessible($guid, $connection2, '/modules/Tutor/requests_pending.php') == false) {
//     $page->addError(__('You do not have access to this action.'));
// } else {

    // Título de la página
    $page->breadcrumbs->add(__('Solicitudes Pendientes'));

    // Capturar y mostrar mensajes Flash guardados en el controlador
    if ($session->has('tutor_message')) {
        $page->addMessage($session->getFlash('tutor_message'));
    }
    if ($session->has('tutor_error')) {
        $page->addError($session->getFlash('tutor_error'));
    }

    // Como no estamos pasando por el login estricto del módulo, si $tutorID da vacío, lo forzamos a 1 para la prueba
    $tutorID = $session->get('gibbonPersonID') ?? '0000000001';

    // Consultar solicitudes pendientes del tutor
    try {
        $sql = "
            SELECT 
                tr.tutorRequestID,
                CONCAT(student.firstName, ' ', student.surname) AS studentName,
                tr.subject,
                tr.requestedDate,
                tr.requestedTime,
                tr.academicNeed
            FROM tutorRequest tr
            JOIN gibbonPerson student ON tr.studentID = student.gibbonPersonID
            WHERE tr.tutorID = :tutorID
              AND tr.status = 'Pending'
            ORDER BY tr.requestedDate ASC, tr.requestedTime ASC
        ";
        $result = $connection2->prepare($sql);
        $result->execute(['tutorID' => $tutorID]);
        $requests = $result->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $page->addError('Error al cargar las solicitudes: ' . $e->getMessage());
        $requests = [];
    }

    if (empty($requests)) {
        echo "<div class='warning'><p>No tienes solicitudes pendientes en este momento.</p></div>";
    } else {
        echo "<table class='fullWidth colorOdd' cellspacing='0'>";
        echo "<tr class='head'>
                <th>Estudiante</th>
                <th>Materia</th>
                <th>Fecha y Hora</th>
                <th>Necesidad Académica</th>
                <th>Acciones</th>
              </tr>";

        foreach ($requests as $req) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($req['studentName']) . "</td>";
            echo "<td>" . htmlspecialchars($req['subject']) . "</td>";
            echo "<td>" . date('d/m/Y', strtotime($req['requestedDate'])) . " " . date('H:i', strtotime($req['requestedTime'])) . "</td>";
            echo "<td>" . htmlspecialchars($req['academicNeed']) . "</td>";
            
            // Apuntamos directamente al archivo físico para la prueba rápida
            echo "<td>
                    <a href='modules/Tutor/requests_respond.php?tutorRequestID={$req['tutorRequestID']}&action=Accepted' 
                       class='button success' 
                       onclick=\"return confirm('¿Aceptar esta solicitud?')\">Aceptar</a>
                    &nbsp;
                    <a href='modules/Tutor/requests_respond.php?tutorRequestID={$req['tutorRequestID']}&action=Rejected' 
                       class='button danger'
                       onclick=\"return confirm('¿Rechazar esta solicitud?')\">Rechazar</a>
                  </td>";
            echo "</tr>";
        }
        echo "</table>";
    }

// } // Cierre del else de seguridad comentado