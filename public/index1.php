<?php
// public/index1.php

// Affichage des erreurs (dev)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Connexion PDO unique
require_once __DIR__ . '/../config/database.php'; // crée $pdo

// Déterminer le controller et l'action
$controllerName = $_GET['controller'] ?? 'sponsor';
$actionName     = $_GET['action'] ?? 'front';

$controllerName = strtolower($controllerName);

function abort404(string $message = 'Page introuvable')
{
    http_response_code(404);
    echo '<h1>404</h1><p>' . htmlspecialchars($message) . '</p>';
    exit;
}

switch ($controllerName) {

    case 'sponsor':
        require_once __DIR__ . '/../app/controllers/SponsorController.php';

        if (!class_exists('SponsorController')) {
            abort404('SponsorController non trouvé');
        }

        // On injecte la connexion PDO
        $controller = new SponsorController($pdo);

        if (!method_exists($controller, $actionName)) {
            abort404('Action "' . $actionName . '" inconnue pour SponsorController');
        }

        $controller->$actionName();
        break;

    case 'deal':
        require_once __DIR__ . '/../app/controllers/DealController.php';

        if (!class_exists('DealController')) {
            abort404('DealController non trouvé');
        }

        // On injecte la connexion PDO
        $controller = new DealController($pdo);

        if (!method_exists($controller, $actionName)) {
            abort404('Action "' . $actionName . '" inconnue pour DealController');
        }

        $controller->$actionName();
        break;

    default:
        abort404('Controller "' . $controllerName . '" inconnu');
}