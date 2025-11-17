<?php
$servername = "127.0.0.1";   // ou "localhost"
$username = "root";           // ton utilisateur MySQL
$password = "";               // ton mot de passe MySQL
$dbname = "reclamations_db";  // <-- nom correct de la base

// Création de la connexion
$conn = new mysqli($servername, $username, $password, $dbname);

// Vérification de la connexion
if ($conn->connect_error) {
    die("Erreur de connexion à la base de données : " . $conn->connect_error);
}
?>
