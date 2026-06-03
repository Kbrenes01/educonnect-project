<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\tutor_login.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configurar los títulos del apartado para cambiar "Application Form"
$pageTitle = "Portal de Tutores Académicos";
$title = "Portal de Tutores Académicos";
$_GET['address'] = "Home > Portal de Tutores";

// Redirigir si ya está logueado como tutor
if (isset($_SESSION['is_tutor']) && $_SESSION['is_tutor'] === true) {
    header("Location: ./index.php?q=/modules/Tutor/requests_pending.php");
    exit();
}

$errorMsg = "";
if (isset($_GET['error'])) {
    if ($_GET['error'] == 'credentials') {
        $errorMsg = "Usuario o contraseña incorrectos.";
    } elseif ($_GET['error'] == 'role') {
        $errorMsg = "Acceso denegado. Este portal es exclusivo para Tutores.";
    }
}
?>

<div class="tutor-login-wrapper" style="max-width: 420px; margin: 40px auto; padding: 30px; border: 1px solid #dcdcdc; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: sans-serif;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #741fa2; margin-bottom: 5px;">EduConnect</h2>
        <strong style="color: #555;">Módulo de Acceso: Tutores</strong>
    </div>

    <?php if ($errorMsg): ?>
        <div style="background-color: #F8D7DA; color: #721C24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px;">
            <?php echo $errorMsg; ?>
        </div>
    <?php endif; ?>
    
    <form action="./modules/Tutor/auth_tutor.php" method="POST">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Usuario o Correo:</label>
            <input type="text" name="tutor_user" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>
        
        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">Contraseña:</label>
            <input type="password" name="tutor_password" required style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>
        
        <button type="submit" style="width: 100%; background: #741fa2; color: white; padding: 12px; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer;">
            Ingresar al Sistema
        </button>
    </form>
    
    <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eeeeee;">
        <p style="color: #666; font-size: 14px; margin-bottom: 12px;">¿No tienes una cuenta de tutor activa?</p>
        <a href="./index.php?q=/modules/Tutor/tutor_register.php" style="display: block; background: #28a745; color: white; padding: 11px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 14px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); transition: background 0.2s;">
            Registrarse como Tutor
        </a>
    </div>
    
    <div style="text-align: center; margin-top: 20px;">
        <a href="./index.php" style="color: #666; text-decoration: none; font-size: 13px;">&laquo; Volver al Inicio Público</a>
    </div>
</div>