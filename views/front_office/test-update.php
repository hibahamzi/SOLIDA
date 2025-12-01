<?php
// Fichier de test pour vérifier l'accès
echo "Test d'accès à UpdateReclamation.php<br>";
echo "Fichier accessible !<br>";
echo "Chemin actuel: " . __DIR__ . "<br>";
echo "ID reçu: " . (isset($_GET['id']) ? $_GET['id'] : 'Aucun ID') . "<br>";

// Test du contrôleur
require_once __DIR__ . '/../../controllers/ReclamationController.php';
echo "Contrôleur chargé avec succès !<br>";

$controller = new ReclamationController();
echo "Instance du contrôleur créée avec succès !<br>";
?>

