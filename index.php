<?php

// Autoload simple
spl_autoload_register(function($class) {
    if (file_exists("controllers/$class.php")) {
        include "controllers/$class.php";
    }
    if (file_exists("models/$class.php")) {
        include "models/$class.php";
    }
});

// Controller w action par défaut
$controllerName = $_GET['controller'] ?? 'ForumController';
$action         = $_GET['action']     ?? 'index';

// Vérifier si le controller existe
if (!file_exists("controllers/$controllerName.php")) {
    die("❌ ERROR: Controller not found : $controllerName");
}

$controller = new $controllerName();

// Vérifier si l'action existe dans le controller
if (!method_exists($controller, $action)) {
    die("❌ ERROR: Action not found : $action");
}

// Exécuter l'action
$controller->$action();
