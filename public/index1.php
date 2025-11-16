<?php
// public/index1.php

// afficher les erreurs (utile pour tester)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$controllerName = $_GET['controller'] ?? 'sponsor';
$action         = $_GET['action'] ?? 'index';

switch ($controllerName) {
    case 'sponsor':
    default:
        require_once __DIR__ . '/../app/controllers/SponsorController.php';
        $controller = new SponsorController();
        break;
}

if (!method_exists($controller, $action)) {
    die("Action '$action' introuvable dans le contrôleur.");
}

$controller->$action();