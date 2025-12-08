<?php
// solida/config.php - Connexion à la base de données 'solida'

$servername = "localhost";
$username = "root";  // À modifier si vous avez un autre utilisateur
$password = "";      // À modifier si vous avez un mot de passe
$dbname = "solida";  // Nom de votre base de données

// Créer la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérifier la connexion
if ($conn->connect_error) {
    die("La connexion à la base de données a échoué: " . $conn->connect_error);
}

// Définir l'encodage pour supporter l'UTF-8
$conn->set_charset("utf8mb4");
?>