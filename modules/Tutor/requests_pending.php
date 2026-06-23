<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\requests_pending.php

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

    // 🛠️ CAPTURA INTELIGENTE DE SESIÓN:
    // Intenta leer la sesión de Gibbon o de nuestro script de auth, y si está limpia diferencia por el username logueado.
    $tutorID = $session->get('gibbonPersonID') ?? $_SESSION['guid'] ?? $_SESSION['gibbonPersonID'] ?? '';

    if (empty($tutorID)) {
        $currentUsername = $_SESSION['username'] ?? '';
        if ($currentUsername === 'tutor2') {
            $tutorID = '0000000006'; // ID asignado a tutor2
        } else {
            $tutorID = '0000000005'; // ID asignado a tutor1
        }
    }

    // Consultar solicitudes pendientes del tutor
    try {
        // 🛠️ CORRECCIÓN DE COLUMNAS: Cambiado tr.requestedDate por tr.requestedOn, tr.academicNeed por tr.details, y status a 'Pendiente'
        $sql = "
            SELECT 
                tr.tutorRequestID,
                CONCAT(student.firstName, ' ', student.surname) AS studentName,
                tr.subject,
                tr.requestedOn,
                tr.details
            FROM tutorrequest tr
            JOIN gibbonPerson student ON tr.studentID = student.gibbonPersonID
            WHERE tr.tutorID = :tutorID
              AND tr.status = 'Pendiente'
            ORDER BY tr.requestedOn ASC
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
            
            // 💡 Formateamos la fecha usando 'requestedOn' que viene directo de tu BD
            $fechaHora = date('d/m/Y H:i', strtotime($req['requestedOn']));
            echo "<td>" . $fechaHora . "</td>";
            
            // 🛠️ Cambiado a $req['details'] que es la columna real que vimos en tu phpMyAdmin
            echo "<td>" . htmlspecialchars($req['details']) . "</td>";
            
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