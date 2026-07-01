<?php
// C:\xampp\htdocs\educonnect\modules\Tutor\tutor_header.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtenemos los datos del tutor de la sesión
$tutorDisplayName = $_SESSION['tutor_name'] ?? $_SESSION['tutor_username'] ?? 'Tutor';
$pageSubtitle = $pageSubtitle ?? 'Portal del tutor';
?>

<div style="background: linear-gradient(90deg, #6c757d, #343a40); padding: 28px 40px; color: white; display:flex; justify-content:space-between; align-items:center; gap:20px;">
    
    <div>
        <h1 style="margin:0;">EduConnect</h1>
        <p style="margin:6px 0 0 0;"><?php echo htmlspecialchars($pageSubtitle); ?></p>
    </div>

    <div style="position:relative;">
        <button 
            onclick="toggleTutorMenu()" 
            style="display:flex; align-items:center; gap:10px; background:rgba(255,255,255,0.1); color:white; border:1px solid rgba(255,255,255,0.3); padding:10px 14px; border-radius:999px; cursor:pointer; font-weight:bold;"
        >
            <span style="width:32px; height:32px; border-radius:50%; background:white; color:#343a40; display:flex; align-items:center; justify-content:center; font-size:17px;">
                👤
            </span>
            <span><?php echo htmlspecialchars($tutorDisplayName); ?></span>
            <span style="font-size:12px;">▼</span>
        </button>

        <div 
            id="tutorUserMenu" 
            style="display:none; position:absolute; right:0; top:52px; background:white; color:#333; min-width:210px; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.2); overflow:hidden; z-index:1000;"
        >
            <div style="padding:13px 15px; border-bottom:1px solid #eee;">
                <strong style="display:block; color:#343a40;"><?php echo htmlspecialchars($tutorDisplayName); ?></strong>
                <small style="color:#666;">Sesión de tutor</small>
            </div>

            <a href="/educonnect/modules/Tutor/tutor_home.php" style="display:block; padding:12px 15px; color:#333; text-decoration:none; border-bottom:1px solid #eee;">Inicio</a>
            <a href="/educonnect/modules/Tutor/tutor_logout.php" style="display:block; padding:12px 15px; color:#721C24; text-decoration:none; font-weight:bold;">Cerrar sesión</a>
        </div>
    </div>
</div>

<script>
function toggleTutorMenu() {
    const menu = document.getElementById('tutorUserMenu');
    menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
}

document.addEventListener('click', function(event) {
    const menu = document.getElementById('tutorUserMenu');
    if (!event.target.closest('button') && !event.target.closest('#tutorUserMenu')) {
        menu.style.display = 'none';
    }
});
</script>