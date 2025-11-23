<?php
// public/index1.php

// Point d'entrée unique (Front Controller)

// Pour l'affichage des erreurs en développement (à désactiver en prod)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Déterminer le controller et l'action à partir de l'URL
// Exemple : index1.php?controller=sponsor&action=index
$controllerName = $_GET['controller'] ?? 'sponsor';
$actionName     = $_GET['action'] ?? 'front';   // ou 'index' selon ce que tu préfères par défaut

// Normaliser le nom (en minuscule)
$controllerName = strtolower($controllerName);

// Fonction utilitaire pour arrêter proprement en cas d'erreur
function abort404(string $message = 'Page introuvable')
{
    http_response_code(404);
    echo '<h1>404</h1><p>' . htmlspecialchars($message) . '</p>';
    exit;
}

// Router principal
switch ($controllerName) {

    case 'sponsor':
        // Charger le contrôleur Sponsor
        require_once __DIR__ . '/../app/controllers/SponsorController.php';

        if (!class_exists('SponsorController')) {
            abort404('SponsorController non trouvé');
        }

        $controller = new SponsorController();

        // Si l'action n'existe pas, 404
        if (!method_exists($controller, $actionName)) {
            abort404('Action "' . $actionName . '" inconnue pour SponsorController');
        }

        // Appeler l'action
        $controller->$actionName();
        break;


    case 'deal':
        // Charger le contrôleur Deal
        require_once __DIR__ . '/../app/controllers/DealController.php';

        if (!class_exists('DealController')) {
            abort404('DealController non trouvé');
        }

        $controller = new DealController();

        if (!method_exists($controller, $actionName)) {
            abort404('Action "' . $actionName . '" inconnue pour DealController');
        }

        $controller->$actionName();
        break;


    default:
        abort404('Controller "' . $controllerName . '" inconnu');
}