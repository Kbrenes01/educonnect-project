<?php
// C:\xampp\htdocs\educonnect\modules\AdminEduConnect\completar_estudiante.php

/*
    HU-26:
    Completar perfil de estudiante EduConnect.

    Flujo:
    1. El administrador crea primero el usuario desde Gibbon con rol Student.
    2. Esta pantalla lista estudiantes de Gibbon que aún NO tienen studentprofile.
    3. El administrador completa nivel educativo, grado, materia principal y estado.
    4. Se inserta el registro en studentprofile.
*/

ini_set('display_errors', 1);
error_reporting(E_ALL);

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

function isAdminRole($roleID) {
    return (string)$roleID === '001' || (int)$roleID === 1;
}

$adminRoleIDCurrent = $session->get('gibbonRoleIDCurrent');
$adminRoleIDPrimary = $session->get('gibbonRoleIDPrimary');

if (!isAdminRole($adminRoleIDCurrent) && !isAdminRole($adminRoleIDPrimary)) {
    die("No tenés permiso para acceder a esta pantalla.");
}

function getFullName($user) {
    $fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['surname'] ?? ''));

    if ($fullName === '') {
        $fullName = $user['preferredName'] ?? 'No definido';
    }

    return $fullName;
}

function getGibbonStatusLabel($status) {
    if ($status === 'Full') {
        return 'Activo';
    }

    if (!$status) {
        return 'No definido';
    }

    return $status;
}

function redirectToPage($params = []) {
    $url = "/educonnect/modules/AdminEduConnect/completar_estudiante.php";

    if (!empty($params)) {
        $url .= "?" . http_build_query($params);
    }

    header("Location: " . $url);
    exit();
}

/*
    PROCESAR FORMULARIO
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['formAction'] ?? '') === 'completeStudentProfile') {
    $gibbonPersonID = trim($_POST['studentToComplete'] ?? '');
    $educationLevel = trim($_POST['educationLevel'] ?? '');
    $gradeLevel = trim($_POST['gradeLevel'] ?? '');
    $mainSubject = trim($_POST['mainSubject'] ?? '');
    $isActive = trim($_POST['isActive'] ?? '');

    if (
        $gibbonPersonID === '' ||
        $educationLevel === '' ||
        $gradeLevel === '' ||
        $mainSubject === '' ||
        $isActive === ''
    ) {
        redirectToPage([
            'studentToComplete' => $gibbonPersonID,
            'error' => 'required'
        ]);
    }

    if (!in_array($educationLevel, ['Primaria', 'Secundaria'], true)) {
        redirectToPage([
            'studentToComplete' => $gibbonPersonID,
            'error' => 'required'
        ]);
    }

    if (!in_array($isActive, ['Y', 'N'], true)) {
        redirectToPage([
            'studentToComplete' => $gibbonPersonID,
            'error' => 'required'
        ]);
    }

    if (mb_strlen($gradeLevel) > 50) {
        $gradeLevel = mb_substr($gradeLevel, 0, 50);
    }

    if (mb_strlen($mainSubject) > 100) {
        $mainSubject = mb_substr($mainSubject, 0, 100);
    }

    try {
        $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $sqlValidate = "
            SELECT
                p.gibbonPersonID,
                p.username,
                p.gibbonRoleIDPrimary,
                sp.studentProfileID
            FROM gibbonPerson p
            LEFT JOIN studentprofile sp
                ON p.gibbonPersonID = sp.gibbonPersonID
            WHERE p.gibbonPersonID = :gibbonPersonID
              AND p.gibbonRoleIDPrimary = 3
            LIMIT 1
        ";

        $stmtValidate = $connection2->prepare($sqlValidate);
        $stmtValidate->execute([
            'gibbonPersonID' => (int)$gibbonPersonID
        ]);

        $student = $stmtValidate->fetch(PDO::FETCH_ASSOC);

        if (!$student) {
            redirectToPage([
                'error' => 'invalid'
            ]);
        }

        if (!empty($student['studentProfileID'])) {
            redirectToPage([
                'error' => 'exists'
            ]);
        }

        $sqlInsert = "
            INSERT INTO studentprofile
            (
                gibbonPersonID,
                educationLevel,
                gradeLevel,
                mainSubject,
                isActive
            )
            VALUES
            (
                :gibbonPersonID,
                :educationLevel,
                :gradeLevel,
                :mainSubject,
                :isActive
            )
        ";

        $stmtInsert = $connection2->prepare($sqlInsert);
        $stmtInsert->execute([
            'gibbonPersonID' => (int)$gibbonPersonID,
            'educationLevel' => $educationLevel,
            'gradeLevel' => $gradeLevel,
            'mainSubject' => $mainSubject,
            'isActive' => $isActive
        ]);

        redirectToPage([
            'success' => 'created'
        ]);

    } catch (Exception $e) {
        echo "<h2>Error al guardar el perfil del estudiante</h2>";
        echo "<p>Detalle técnico:</p>";
        echo "<pre>";
        echo htmlspecialchars($e->getMessage());
        echo "</pre>";
        exit();
    }
}

/*
    CARGA DE DATOS
*/
$selectedStudent = null;
$pendingStudents = [];

$selectedStudentID = $_GET['studentToComplete'] ?? null;
$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

try {
    $connection2->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sqlPendingStudents = "
        SELECT
            p.gibbonPersonID,
            p.username,
            p.firstName,
            p.surname,
            p.preferredName,
            p.status,
            p.gibbonRoleIDPrimary,
            r.name AS roleName
        FROM gibbonPerson p
        LEFT JOIN gibbonRole r
            ON p.gibbonRoleIDPrimary = r.gibbonRoleID
        LEFT JOIN studentprofile sp
            ON p.gibbonPersonID = sp.gibbonPersonID
        WHERE p.gibbonRoleIDPrimary = 3
          AND sp.studentProfileID IS NULL
        ORDER BY p.surname, p.firstName, p.username
    ";

    $stmtPendingStudents = $connection2->prepare($sqlPendingStudents);
    $stmtPendingStudents->execute();

    $pendingStudents = $stmtPendingStudents->fetchAll(PDO::FETCH_ASSOC);

    if ($selectedStudentID !== null && $selectedStudentID !== '') {
        $sqlSelectedStudent = "
            SELECT
                p.gibbonPersonID,
                p.username,
                p.firstName,
                p.surname,
                p.preferredName,
                p.status,
                p.gibbonRoleIDPrimary,
                r.name AS roleName,
                sp.studentProfileID
            FROM gibbonPerson p
            LEFT JOIN gibbonRole r
                ON p.gibbonRoleIDPrimary = r.gibbonRoleID
            LEFT JOIN studentprofile sp
                ON p.gibbonPersonID = sp.gibbonPersonID
            WHERE p.gibbonPersonID = :gibbonPersonID
              AND p.gibbonRoleIDPrimary = 3
            LIMIT 1
        ";

        $stmtSelectedStudent = $connection2->prepare($sqlSelectedStudent);
        $stmtSelectedStudent->execute([
            'gibbonPersonID' => (int)$selectedStudentID
        ]);

        $selectedStudent = $stmtSelectedStudent->fetch(PDO::FETCH_ASSOC);

        if (!$selectedStudent || !empty($selectedStudent['studentProfileID'])) {
            $selectedStudent = null;
        }
    }

} catch (Exception $e) {
    echo "<h2>Error al consultar estudiantes pendientes</h2>";
    echo "<p>Detalle técnico:</p>";
    echo "<pre>";
    echo htmlspecialchars($e->getMessage());
    echo "</pre>";
    exit();
}

$currentAdminName = $session->get('preferredName') ?: $session->get('username');
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Completar estudiante - EduConnect</title>

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
            max-width: 1120px;
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

        .info-box {
            background: #f7f2fa;
            border-left: 5px solid #741fa2;
            padding: 15px;
            border-radius: 8px;
            color: #444444;
            font-size: 14px;
            line-height: 1.5;
        }

        .success-box {
            background: #d4edda;
            border-left: 5px solid #155724;
            padding: 15px;
            border-radius: 8px;
            color: #155724;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .error-box {
            background: #f8d7da;
            border-left: 5px solid #721c24;
            padding: 15px;
            border-radius: 8px;
            color: #721c24;
            font-size: 14px;
            margin-bottom: 20px;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 760px;
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

        .action-button {
            background: #741fa2;
            color: white;
            padding: 9px 12px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 13px;
            display: inline-block;
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

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        label {
            font-weight: bold;
            color: #333333;
            margin-bottom: 6px;
            font-size: 14px;
        }

        input,
        select {
            padding: 10px;
            border: 1px solid #cccccc;
            border-radius: 5px;
            font-size: 14px;
        }

        input[readonly] {
            background: #eeeeee;
            color: #555555;
        }

        .submit-button {
            background: #741fa2;
            color: white;
            padding: 11px 16px;
            border: none;
            border-radius: 5px;
            font-weight: bold;
            cursor: pointer;
            font-size: 14px;
        }

        .cancel-button {
            background: #666666;
            color: white;
            padding: 11px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 14px;
            display: inline-block;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .empty-message {
            background: #f7f2fa;
            border-left: 5px solid #741fa2;
            padding: 15px;
            border-radius: 8px;
            color: #444444;
            font-size: 14px;
        }

        @media (max-width: 800px) {
            .form-grid {
                grid-template-columns: 1fr;
            }

            .top-bar {
                padding: 16px;
            }
        }
    </style>
</head>

<body>

    <div class="top-bar">
        <h1>Completar perfil de estudiante</h1>
        <p>Registro de información académica requerida por EduConnect.</p>
    </div>

    <div class="container">

        <?php if ($success === 'created'): ?>
            <div class="success-box">
                El perfil del estudiante fue completado correctamente.
            </div>
        <?php endif; ?>

        <?php if ($error === 'required'): ?>
            <div class="error-box">
                No se pudo guardar. Todos los campos obligatorios deben estar completos.
            </div>
        <?php elseif ($error === 'exists'): ?>
            <div class="error-box">
                Este estudiante ya tiene un perfil registrado en EduConnect.
            </div>
        <?php elseif ($error === 'invalid'): ?>
            <div class="error-box">
                El estudiante seleccionado no es válido.
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Panel administrativo</h2>
            <p style="color:#555; margin-bottom:0;">
                Administrador: <strong><?php echo htmlspecialchars($currentAdminName); ?></strong>
            </p>
        </div>

        <div class="card">
            <h3>¿Para qué sirve este formulario?</h3>

            <div class="info-box">
                Llene este formulario para completar el proceso de registro del estudiante. Esto es necesario para que el estudiante pueda usar el sistema de tutorías, ver tutores disponibles y
                solicitar apoyo.
            </div>
        </div>

        <?php if ($selectedStudent): ?>
            <?php $selectedName = getFullName($selectedStudent); ?>

            <div class="card">
                <h3>Completar datos del estudiante</h3>

                <form method="POST" action="/educonnect/modules/AdminEduConnect/completar_estudiante.php">
                    <input type="hidden" name="formAction" value="completeStudentProfile">
                    <input type="hidden" name="studentToComplete" value="<?php echo htmlspecialchars($selectedStudent['gibbonPersonID']); ?>">

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="fullName">Nombre completo</label>
                            <input id="fullName" name="fullNameReadonly" type="text" value="<?php echo htmlspecialchars($selectedName); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="username">Usuario</label>
                            <input id="username" name="usernameReadonly" type="text" value="<?php echo htmlspecialchars($selectedStudent['username']); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="roleName">Rol</label>
                            <input id="roleName" name="roleNameReadonly" type="text" value="Estudiante" readonly>
                        </div>

                        <div class="form-group">
                            <label for="gibbonStatus">Estado en Gibbon</label>
                            <input id="gibbonStatus" name="gibbonStatusReadonly" type="text" value="<?php echo htmlspecialchars(getGibbonStatusLabel($selectedStudent['status'])); ?>" readonly>
                        </div>

                        <div class="form-group">
                            <label for="educationLevel">Nivel educativo *</label>
                            <select name="educationLevel" id="educationLevel" required>
                                <option value="">Seleccione...</option>
                                <option value="Primaria">Primaria</option>
                                <option value="Secundaria">Secundaria</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="gradeLevel">Grado *</label>
                            <input type="text" name="gradeLevel" id="gradeLevel" maxlength="50" placeholder="Ej: Sétimo, Octavo, Undécimo" required>
                        </div>

                        <div class="form-group">
                            <label for="mainSubject">Materia de apoyo principal *</label>
                            <input type="text" name="mainSubject" id="mainSubject" maxlength="100" placeholder="Ej: Matemática, Inglés, Español" required>
                        </div>

                        <div class="form-group">
                            <label for="isActive">Estado en EduConnect *</label>
                            <select name="isActive" id="isActive" required>
                                <option value="">Seleccione...</option>
                                <option value="Y">Activo</option>
                                <option value="N">Inactivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="actions">
                        <button type="submit" class="submit-button">
                            Guardar perfil estudiante
                        </button>

                        <a hx-boost="false" href="/educonnect/modules/AdminEduConnect/completar_estudiante.php" class="cancel-button">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <div class="card">
            <h3>Estudiantes pendientes de completar</h3>

            <?php if (empty($pendingStudents)): ?>
                <div class="empty-message">
                    No hay estudiantes pendientes. Todos los estudiantes ya tienen el proceso de registro completo en EduConnect.
                </div>
            <?php else: ?>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre completo</th>
                                <th>Usuario</th>
                                <th>Rol</th>
                                <th>Estado Gibbon</th>
                                <th>Acción</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($pendingStudents as $student): ?>
                                <?php $studentName = getFullName($student); ?>

                                <tr>
                                    <td><?php echo htmlspecialchars($studentName); ?></td>
                                    <td><?php echo htmlspecialchars($student['username']); ?></td>
                                    <td>Estudiante</td>
                                    <td><?php echo htmlspecialchars(getGibbonStatusLabel($student['status'])); ?></td>
                                    <td>
                                        <a
                                            hx-boost="false"
                                            class="action-button"
                                            href="/educonnect/modules/AdminEduConnect/completar_estudiante.php?studentToComplete=<?php echo urlencode($student['gibbonPersonID']); ?>">
                                            Completar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div style="margin-bottom:30px;">
            <a hx-boost="false" href="/educonnect/index.php" class="back-button">
                Volver al inicio
            </a>
        </div>

    </div>

</body>
</html>