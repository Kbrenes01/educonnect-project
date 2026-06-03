<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\auth_tutor.php
require_once '../../config.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = trim($_POST['tutor_user']);
    $password = $_POST['tutor_password'];

    try {
        $query = "SELECT * FROM gibbonPerson WHERE (username = ? OR email = ?) AND status = 'Active' LIMIT 1";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$usernameOrEmail, $usernameOrEmail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                // '008' suele ser el ID de rol para profesores/tutores en Gibbon. 
                // Verifica en tu BD si este número coincide.
                if ($user['roleIDPrimary'] === '008') { 
                    if (session_status() == PHP_SESSION_NONE) {
                        session_start();
                    }
                    $_SESSION['guid'] = $user['gibbonPersonID'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_tutor'] = true;
                    
                    header("Location: ../../index.php?q=/modules/Tutor/requests_pending.php");
                    exit();
                } else {
                    header("Location: ../../index.php?q=/modules/Tutor/tutor_login.php&error=role");
                    exit();
                }
            }
        }
        header("Location: ../../index.php?q=/modules/Tutor/tutor_login.php&error=credentials");
        exit();
    } catch (PDOException $e) {
        die("Error: " . $e->getMessage());
    }
}