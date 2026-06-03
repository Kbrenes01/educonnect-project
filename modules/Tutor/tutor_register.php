<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\tutor_register.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$pageTitle = "Registro de Nuevo Tutor";
$title = "Registro de Nuevo Tutor";
$_GET['address'] = "Home > Portal de Tutores > Registro";

$msg = "";
if (isset($_GET['success'])) {
    $msg = "<div style='background: #D4EDDA; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center;'>¡Tutor registrado con éxito! Ya puedes iniciar sesión.</div>";
}
if (isset($_GET['error']) && $_GET['error'] == 'exists') {
    $msg = "<div style='background: #F8D7DA; color: #721C24; padding: 10px; border-radius: 4px; margin-bottom: 15px; text-align: center;'>El nombre de usuario ya se encuentra registrado.</div>";
}
?>

<div class="tutor-register-wrapper" style="max-width: 500px; margin: 30px auto; padding: 30px; border: 1px solid #dcdcdc; background: #ffffff; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); font-family: sans-serif;">
    <h3 style="color: #4a148c; margin-top: 0; text-align: center;">Formulario de Registro: Tutor</h3>
    
    <?php echo $msg; ?>

    <form action="./modules/Tutor/tutor_register_process.php" method="POST">
        <!-- Nombre Completo -->
        <div style="margin-bottom: 12px;">
            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Nombre Completo:</label>
            <input type="text" name="name" placeholder="Ej: Carlos Mora" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <!-- Usuario -->
        <div style="margin-bottom: 12px;">
            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Usuario:</label>
            <input type="text" name="username" placeholder="Ej: carlos.mora" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <!-- Contraseña -->
        <div style="margin-bottom: 12px;">
            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Contraseña Temporal:</label>
            <input type="password" name="password" placeholder="Ej: Carlos123" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <!-- Materia que atiende -->
        <div style="margin-bottom: 12px;">
            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Materia que atiende:</label>
            <input type="text" name="subject" placeholder="Ej: Matemática" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
        </div>

        <!-- Nivel educativo que atiende -->
        <div style="margin-bottom: 12px;">
            <label style="display: block; margin-bottom: 4px; font-weight: bold; font-size: 14px;">Nivel que atiende:</label>
            <select name="level" required style="width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="Primaria">Primaria</option>
                <option value="Secundaria">Secundaria</option>
                <option value="Primaria / Secundaria" selected>Primaria / Secundaria</option>
            </select>
        </div>

        <!-- Horarios de Disponibilidad Básica -->
        <div style="background: #f9f9f9; padding: 10px; border-radius: 4px; margin-bottom: 15px; border: 1px dashed #ccc;">
            <strong style="display: block; margin-bottom: 8px; color: #4a148c; font-size: 14px;">Disponibilidad Inicial</strong>
            
            <div style="margin-bottom: 8px;">
                <label style="font-size: 13px; font-weight: bold;">Día disponible:</label>
                <select name="available_day" style="width: 100%; padding: 6px; margin-top: 2px;">
                    <option value="Lunes">Lunes</option>
                    <option value="Martes">Martes</option>
                    <option value="Miércoles">Miércoles</option>
                    <option value="Jueves">Jueves</option>
                    <option value="Viernes" selected>Viernes</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px;">
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: bold;">Hora Inicio:</label>
                    <input type="time" name="time_start" value="14:00" style="width: 100%; padding: 5px;">
                </div>
                <div style="flex: 1;">
                    <label style="font-size: 13px; font-weight: bold;">Hora Fin:</label>
                    <input type="time" name="time_end" value="15:00" style="width: 100%; padding: 5px;">
                </div>
            </div>
        </div>

        <button type="submit" style="width: 100%; background: #28a745; color: white; padding: 12px; border: none; border-radius: 4px; font-size: 15px; font-weight: bold; cursor: pointer;">
            Registrar y Crear Cuenta
        </button>
    </form>
    
    <div style="text-align: center; margin-top: 15px;">
        <a href="./index.php?q=/modules/Tutor/tutor_login.php" style="color: #666; text-decoration: none; font-size: 13px;">&laquo; Volver al Login</a>
    </div>
</div>