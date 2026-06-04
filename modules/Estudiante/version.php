<?php

$name        = 'Estudiante';
$description = 'Módulo de gestión para estudiantes en EduConnect';
$entryURL    = 'estudiante_login.php';
$type        = 'Additional';
$category    = 'EduConnect';
$version     = '1.0.00';
$author      = 'EduConnect Team';
$url         = 'http://localhost/educonnect';

$actions = [
    [
        'name'                       => 'Portal Estudiante',
        'precedence'                 => '0',
        'category'                   => 'Estudiante',
        'description'                => 'Portal principal del estudiante',
        'URLList'                    => 'estudiante_login.php,auth_estudiante.php,estudiante_home.php',
        'entryURL'                   => 'estudiante_login.php',
        'defaultPermissionAdmin'     => 'Y',
        'defaultPermissionTeacher'   => 'N',
        'defaultPermissionStudent'   => 'Y',
        'defaultPermissionParent'    => 'N',
        'defaultPermissionSupport'   => 'N',
        'categoryPermissionStaff'    => 'N',
        'categoryPermissionStudent'  => 'Y',
        'categoryPermissionParent'   => 'N',
        'categoryPermissionOther'    => 'N',
    ],
];