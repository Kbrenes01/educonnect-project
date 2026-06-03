<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\tutor_register_process.php
require_once '../../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = $_POST['password']; 
    $subject = trim($_POST['subject']);
    $level = $_POST['level'];
    $day = $_POST['available_day'];
    $time_start = $_POST['time_start'];
    $time_end = $_POST['time_end'];

    // Conexión manual usando los datos de config.php
    $connection = mysqli_connect($databaseServer, $databaseUsername, $databasePassword, $databaseName);

    if (!$connection) {
        die("Error de conexión: " . mysqli_connect_error());
    }

    // 1. Validar si el nombre de usuario ya existe en gibbonPerson
    $checkQuery = "SELECT gibbonPersonID FROM gibbonPerson WHERE username = ?";
    $stmtCheck = mysqli_prepare($connection, $checkQuery);
    mysqli_stmt_bind_param($stmtCheck, "s", $username);
    mysqli_stmt_execute($stmtCheck);
    mysqli_stmt_store_result($stmtCheck); 

    if (mysqli_stmt_num_rows($stmtCheck) > 0) {
        mysqli_stmt_close($stmtCheck);
        mysqli_close($connection); // CORREGIDO: de mysqli_connect_close a mysqli_close
        header("Location: ../../index.php?q=/modules/Tutor/tutor_register.php&error=exists");
        exit();
    }
    mysqli_stmt_close($stmtCheck);

    // 2. Generar un ID único de 10 dígitos (Formato oficial de Gibbon)
    $gibbonPersonID = str_pad(rand(1, 99999999), 10, '0', STR_PAD_LEFT);

    // Separar nombre y apellido para las columnas firstName y surname
    $parts = explode(' ', $name, 2);
    $firstName = $parts[0];
    $surname = isset($parts[1]) ? $parts[1] : 'Tutor';
    
    // Encriptar la contraseña para proteger 'passwordStrong'
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $dummyEmail = $username . "@educonnect.com";

    // 3. Insertar en gibbonPerson 
    $sqlUser = "INSERT INTO gibbonPerson (gibbonPersonID, username, passwordStrong, firstName, surname, status, email) 
                VALUES (?, ?, ?, ?, ?, 'Active', ?)";
    
    $stmtUser = mysqli_prepare($connection, $sqlUser);
    mysqli_stmt_bind_param($stmtUser, "ssssss", $gibbonPersonID, $username, $hashedPassword, $firstName, $surname, $dummyEmail);
    mysqli_stmt_execute($stmtUser);
    mysqli_stmt_close($stmtUser);

    // 4. Insertar la información detallada en tu tabla 'tutorprofile' usando las nuevas columnas
    $bioDefault = "Tutor especializado en " . $subject;
    $isActive = "Y";
    $createdOn = date('Y-m-d H:i:s');

    $sqlProfile = "INSERT INTO tutorprofile (gibbonPersonID, subjects, educationLevel, bio, availableDay, startTime, endTime, isActive, createdOn) 
                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmtProfile = mysqli_prepare($connection, $sqlProfile);
    mysqli_stmt_bind_param($stmtProfile, "sssssssss", $gibbonPersonID, $subject, $level, $bioDefault, $day, $time_start, $time_end, $isActive, $createdOn);
    mysqli_stmt_execute($stmtProfile);
    mysqli_stmt_close($stmtProfile);

    // CORREGIDO: Aquí estaba el error de la línea 71
    mysqli_close($connection); 

    // Redireccionar con éxito total de vuelta al formulario
    header("Location: ../../index.php?q=/modules/Tutor/tutor_register.php&success=1");
    exit();
} else {
    header("Location: ../../index.php");
    exit();
}