<?php
// C:\xampp\htdocs\educonnect\modules\Estudiante\estudiante_login.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['is_student']) && $_SESSION['is_student'] === true) {
    header("Location: /educonnect/modules/Estudiante/estudiante_home.php");
    exit();
}

$errorMsg = "";

if (isset($_GET['error'])) {
    if ($_GET['error'] === 'credentials') {
        $errorMsg = "Usuario o contraseña incorrectos.";
    } elseif ($_GET['error'] === 'role') {
        $errorMsg = "Acceso denegado. Este portal es exclusivo para estudiantes.";
    } elseif ($_GET['error'] === 'profile') {
        $errorMsg = "Su cuenta aún no tiene habilitado el acceso al módulo de estudiantes. Comuníquese con administración para completar el proceso de registro.";
    } elseif ($_GET['error'] === 'db') {
        $errorMsg = "Ocurrió un error al conectar con la base de datos.";
    }
}
?>

<div class="student-login-wrapper" style="max-width: 420px; margin: 40px auto; padding: 30px; border: 1px solid #dcdcdc; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: sans-serif;">
    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: #741fa2; margin-bottom: 5px;">EduConnect</h2>
        <strong style="color: #555;">Módulo de Acceso: Estudiantes</strong>
    </div>

    <?php if ($errorMsg): ?>
        <div style="background-color: #F8D7DA; color: #721C24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center; font-size: 14px;">
            <?php echo htmlspecialchars($errorMsg); ?>
        </div>
    <?php endif; ?>

    <form action="/educonnect/modules/Estudiante/auth_estudiante.php" method="POST">
        <div style="margin-bottom: 15px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">
                Usuario o Correo:
            </label>
            <input 
                type="text" 
                name="student_user" 
                required 
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"
            >
        </div>

        <div style="margin-bottom: 20px;">
            <label style="display: block; margin-bottom: 5px; font-weight: bold; color: #333;">
                Contraseña:
            </label>
            <input 
                type="password" 
                name="student_password" 
                required 
                style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;"
            >
        </div>

        <button 
            type="submit" 
            style="width: 100%; background: #741fa2; color: white; padding: 12px; border: none; border-radius: 4px; font-size: 16px; font-weight: bold; cursor: pointer;"
        >
            Ingresar al Sistema
        </button>
    </form>

    <div style="text-align: center; margin-top: 20px; padding-top: 20px; border-top: 1px solid #eeeeee;">
        <p style="color: #666; font-size: 14px; margin-bottom: 0;">
            Las cuentas de estudiante son creadas por administración.
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="/educonnect/index.php" style="color: #666; text-decoration: none; font-size: 13px;">
            &laquo; Volver al Inicio Público
        </a>
    </div>
</div>