<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userConnecte = $_SESSION['user'] ?? null;

$rootPath = (basename(dirname($_SERVER['SCRIPT_NAME'])) === 'tp_php') ? '' : '../';

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="<?= $rootPath ?>css/style.css">
    <title>Mon Blog PHP</title>
</head>
<html>
<body>
<style>
    header {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 70px;
        font-size: 110%;
        background-color: rgb(22, 22, 22);
        color: white;
        display:flex;
        align-items: center;
        justify-content: center;
        font-size:200%;
        font-weight:bold;
    }
</style>
<header>
    <a>TP PHP</a>
</header>
