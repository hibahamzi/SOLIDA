<?php
require_once '../../Controllers/ReclamationController.php';  // Assurez-vous que le chemin est correct pour inclure votre fichier

// Créer une instance de UserController
$userController1 = new ReclamationController();

$userId = $_GET['id'];

$userController1->suppreclamation($userId);

header('Location: listeReclamation.php');

exit;
?>