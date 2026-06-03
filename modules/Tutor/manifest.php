<?php
$name        = 'Tutor';
$description = 'Módulo de gestión para tutores académicos';
$entryURL    = 'dashboard.php';
$type        = 'Additional';
$category    = 'EduConnect';
$version = '1.0.01';
$author      = 'EduConnect Team';
$url         = 'https://localhost/educonnect';

$actions = [
    [
        'name'                => 'Tutor Dashboard',
        'precedence'          => '0',
        'category'            => 'Tutor',
        'description'         => 'Panel principal del tutor',
        'URLList'             => 'dashboard.php',
        'entryURL'            => 'dashboard.php',
        'defaultPermissionAdmin'   => 'Y',
        'defaultPermissionTeacher' => 'Y',
        'defaultPermissionStudent' => 'N',
        'defaultPermissionParent'  => 'N',
        'defaultPermissionSupport' => 'N',
        'categoryPermissionStaff'  => 'Y',
        'categoryPermissionStudent'=> 'N',
        'categoryPermissionParent' => 'N',
        'categoryPermissionOther'  => 'N',
    ],
    [
        'name'                => 'Solicitudes Pendientes',
        'precedence'          => '1',
        'category'            => 'Tutor',
        'description'         => 'Ver y gestionar solicitudes de tutoría pendientes',
        'URLList'             => 'requests_pending.php,requests_respond.php',
        'entryURL'            => 'requests_pending.php',
        'defaultPermissionAdmin'   => 'Y',
        'defaultPermissionTeacher' => 'Y',
        'defaultPermissionStudent' => 'N',
        'defaultPermissionParent'  => 'N',
        'defaultPermissionSupport' => 'N',
        'categoryPermissionStaff'  => 'Y',
        'categoryPermissionStudent'=> 'N',
        'categoryPermissionParent' => 'N',
        'categoryPermissionOther'  => 'N',
    ],
];