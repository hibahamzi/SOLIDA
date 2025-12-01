<?php
// config/database.php

$host = 'localhost';

// IMPORTANT : mets ici EXACTEMENT le nom de la base
// où tu vois tes anciens sponsors dans phpMyAdmin.
$db   = 'sponsor'; // à changer si ta base réelle s'appelle autrement

$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = array(
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
);

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    // DEBUG SI BESOIN :
    // echo "Connexion OK sur base : " . htmlspecialchars($db);
    // exit;
} catch (PDOException $e) {
    die('Connexion échouée : ' . $e->getMessage());
}