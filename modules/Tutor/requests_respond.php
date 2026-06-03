<?php
// 1. Forzar la carga del núcleo de Gibbon para que reconozca $session y la base de datos
require_once '../../gibbon.php'; 

// El resto de tu código se queda exactamente igual:
$tutorID   = $session->get('gibbonPersonID') ?? '0000000001';
$requestID = $_GET['tutorRequestID'] ?? null;
$action    = $_GET['action'] ?? null;

if (!in_array($action, ['Accepted', 'Rejected']) || !$requestID) {
    $session->setFlash('tutor_error', 'Solicitud inválida.');
} else {
    try {
        $sql = "UPDATE tutorRequest 
                SET status = :action, respondedOn = NOW()
                WHERE tutorRequestID = :requestID 
                  AND tutorID = :tutorID 
                  AND status = 'Pending'";
        $stmt = $connection2->prepare($sql);
        $stmt->execute([
            'action'    => $action,
            'requestID' => $requestID,
            'tutorID'   => $tutorID,
        ]);

        if ($stmt->rowCount() > 0) {
            $msg = ($action === 'Accepted') ? 'Solicitud aceptada correctamente.' : 'Solicitud rechazada.';
            $session->setFlash('tutor_message', $msg);
        } else {
            $session->setFlash('tutor_error', 'No se pudo actualizar la solicitud.');
        }
    } catch (PDOException $e) {
        $session->setFlash('tutor_error', 'Error: ' . $e->getMessage());
    }
}

// Redirección directa al listado
$URL = $container->get('addressFactory')->newInstance()->getURL('/modules/Tutor/requests_pending.php');
header("Location: {$URL}");
exit;