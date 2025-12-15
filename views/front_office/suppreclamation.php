<?php
require_once '../../config/config.php';
session_start();
require_once '../../controllers/ReclamationController.php';  // Assurez-vous que le chemin est correct pour inclure votre fichier

// Créer une instance de UserController
$userController1 = new ReclamationController($pdo);

$userId = $_GET['id'];

$userController1->suppreclamation($userId);

header('Location: index.php?section=reclamations');

exit;
?>