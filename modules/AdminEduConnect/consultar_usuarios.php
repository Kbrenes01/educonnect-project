<?php
// C:\xampp\htdocs\educonnect\modules\AdminEduConnect\consultar_usuarios.php

/*
    HU-28:
    Administrador consulta usuarios registrados.
    Filtros:
    - Todos
    - Estudiantes
    - Tutores
*/

// Gibbon espera ejecutarse desde la raíz del proyecto.
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

$adminRoleIDCurrent = $session->get('gibbonRoleIDCurrent');
$adminRoleIDPrimary = $session->get('gibbonRoleIDPrimary');

/*
    En Gibbon:
    001 = Administrator
*/
if ($adminRoleIDCurrent !== '001' && $adminRoleIDPrimary !== '001') {
    die("No tenés permiso para acceder a esta pantalla.");
}

function getRoleLabel($roleID, $roleName) {
    if ($roleID === '003') {
        return 'Estudiante';
    }

    if ($roleID === '002') {
        return 'Tutor';
    }

    return $roleName ?: 'No definido';
}

function getUserStatusLabel($status) {
    if ($status === 'Full') {
        return 'Activo';
    }

    if (!$status) {
        return 'No definido';
    }

    return $status;
}

function getUserStatusStyle($status) {
    if ($status === 'Full') {
        return 'background:#d4edda; color:#155724;';
    }

    return 'background:#e2e3e5; color:#383d41;';
}

function getProfileStatusLabel($roleID, $studentProfileID, $tutorProfileID) {
    if ($roleID === '003') {
        return $studentProfileID ? 'Perfil estudiante completo' : 'Perfil estudiante pendiente';
    }

    if ($roleID === '002') {
        return $tutorProfileID ? 'Perfil tutor completo' : 'Perfil tutor pendiente';
    }

    return 'No aplica';
}

function getProfileStatusStyle($roleID, $studentProfileID, $tutorProfileID) {
    if ($roleID === '003') {
        return $studentProfileID
            ? 'background:#d4edda; color:#155724;'
            : 'background:#fff3cd; color:#856404;';
    }

    if ($roleID === '002') {
        return $tutorProfileID
            ? 'background:#d4edda; color:#155724;'
            : 'background:#fff3cd; color:#856404;';
    }

    return 'background:#e2e3e5; color:#383d41;';
}

$allowedFilters = [
    'todos',
    'estudiantes',
    'tutores'
];

$roleFilter = $_GET['rol'] ?? 'todos';

if (!in_array($roleFilter, $allowedFilters, true)) {
    $roleFilter = 'todos';
}

$users = [];

try {
    $params = [];

    $whereRole = "WHERE p.gibbonRoleIDPrimary IN ('002', '003')";

    if ($roleFilter === 'estudiantes') {
        $whereRole = "WHERE p.gibbonRoleIDPrimary = :roleID";
        $params['roleID'] = '003';
    } elseif ($roleFilter === 'tutores') {
        $whereRole = "WHERE p.gibbonRoleIDPrimary = :roleID";
        $params['roleID'] = '002';
    }

    $sqlUsers = "
        SELECT
            p.gibbonPersonID,
            p.username,
            p.firstName,
            p.surname,
            p.preferredName,
            p.status,
            p.gibbonRoleIDPrimary,

            r.name AS roleName,

            sp.studentProfileID,
            sp.educationLevel AS studentEducationLevel,
            sp.gradeLevel,
            sp.mainSubject,
            sp.isActive AS studentIsActive,

            tp.tutorProfileID,
            tp.subjects AS tutorSubjects,
            tp.educationLevel AS tutorEducationLevel,
            tp.isActive AS tutorIsActive
        FROM gibbonPerson p
        LEFT JOIN gibbonRole r
            ON p.gibbonRoleIDPrimary = r.gibbonRoleID
        LEFT JOIN studentprofile sp
            ON p.gibbonPersonID = sp.gibbonPersonID
        LEFT JOIN tutorprofile tp
            ON p.gibbonPersonID = tp.gibbonPersonID
        $whereRole
        ORDER BY p.gibbonRoleIDPrimary, p.surname, p.firstName, p.username
    ";

    $stmtUsers = $connection2->prepare($sqlUsers);
    $stmtUsers->execute($params);

    $users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    die("Error al consultar los usuarios.");
}

$filterLabels = [
    'todos' => 'Todos',
    'estudiantes' => 'Estudiantes',
    'tutores' => 'Tutores'
];

$currentAdminName = $session->get('preferredName') ?: $session->get('username');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de usuarios - EduConnect</title>

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
            min-width: 900px;
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

        .badge {
            padding: 6px 10px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
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

        @media (max-width: 800px) {
            .top-bar {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

    <div class="top-bar">
        <h1>Consulta de usuarios</h1>
        <p>Visualización de estudiantes y tutores registrados en EduConnect.</p>
    </div>

    <div class="container">

        <div class="card">
            <h2>Panel administrativo</h2>
            <p style="color:#555; margin-bottom:0;">
                Administrador: <strong><?php echo htmlspecialchars($currentAdminName); ?></strong>
            </p>
        </div>

        <div class="card">
            <h3>Filtrar por rol</h3>

            <div class="filters">
                <?php foreach ($filterLabels as $filterValue => $filterText): ?>
                    <a
                        class="filter-button <?php echo $roleFilter === $filterValue ? 'filter-active' : 'filter-inactive'; ?>"
                        href="/educonnect/modules/AdminEduConnect/consultar_usuarios.php?rol=<?php echo urlencode($filterValue); ?>">
                        <?php echo htmlspecialchars($filterText); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card">
            <h3>Lista de usuarios</h3>

            <?php if (empty($users)): ?>
                <div class="empty-message">
                    No hay usuarios registrados para el filtro seleccionado.
                </div>
            <?php else: ?>

                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado</th>
                                <th>Perfil EduConnect</th>
                                <th>Detalle</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <?php
                                    $fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['surname'] ?? ''));

                                    if ($fullName === '') {
                                        $fullName = $user['preferredName'] ?: 'No definido';
                                    }

                                    $roleID = $user['gibbonRoleIDPrimary'];
                                    $roleLabel = getRoleLabel($roleID, $user['roleName']);
                                    $statusLabel = getUserStatusLabel($user['status']);
                                    $statusStyle = getUserStatusStyle($user['status']);

                                    $profileStatusLabel = getProfileStatusLabel(
                                        $roleID,
                                        $user['studentProfileID'],
                                        $user['tutorProfileID']
                                    );

                                    $profileStatusStyle = getProfileStatusStyle(
                                        $roleID,
                                        $user['studentProfileID'],
                                        $user['tutorProfileID']
                                    );

                                    if ($roleID === '003') {
                                        $detailParts = [];

                                        if (!empty($user['studentEducationLevel'])) {
                                            $detailParts[] = 'Nivel: ' . $user['studentEducationLevel'];
                                        }

                                        if (!empty($user['gradeLevel'])) {
                                            $detailParts[] = 'Grado: ' . $user['gradeLevel'];
                                        }

                                        if (!empty($user['mainSubject'])) {
                                            $detailParts[] = 'Materia principal: ' . $user['mainSubject'];
                                        }

                                        $detail = !empty($detailParts)
                                            ? implode(' | ', $detailParts)
                                            : 'Sin perfil de estudiante registrado.';
                                    } elseif ($roleID === '002') {
                                        $detailParts = [];

                                        if (!empty($user['tutorEducationLevel'])) {
                                            $detailParts[] = 'Nivel: ' . $user['tutorEducationLevel'];
                                        }

                                        if (!empty($user['tutorSubjects'])) {
                                            $detailParts[] = 'Materias: ' . $user['tutorSubjects'];
                                        }

                                        $detail = !empty($detailParts)
                                            ? implode(' | ', $detailParts)
                                            : 'Sin perfil de tutor registrado.';
                                    } else {
                                        $detail = 'No aplica.';
                                    }
                                ?>

                                <tr>
                                    <td><?php echo htmlspecialchars($fullName); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($roleLabel); ?></td>
                                    <td>
                                        <span class="badge" style="<?php echo htmlspecialchars($statusStyle); ?>">
                                            <?php echo htmlspecialchars($statusLabel); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge" style="<?php echo htmlspecialchars($profileStatusStyle); ?>">
                                            <?php echo htmlspecialchars($profileStatusLabel); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($detail); ?></td>
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

</body>
</html> 