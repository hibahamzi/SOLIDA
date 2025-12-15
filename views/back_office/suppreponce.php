<?php
require_once __DIR__ . '/../../config/config.php';
session_start();

// Vérifier si l'utilisateur est connecté et est admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../front_office/sign-in.php');
    exit();
}

require_once __DIR__ . '/../../controllers/ReclamationController.php';

// Vérifier si l'ID est présent dans l'URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

// Vérifier que l'ID est un nombre valide
$id_reclamation = filter_var($_GET['id'], FILTER_VALIDATE_INT);

if ($id_reclamation === false || $id_reclamation <= 0) {
    header('Location: dashboard.php?error=invalid_id');
    exit;
}

try {
    // Créer une instance du contrôleur
    $reclamationController = new ReclamationController($pdo);
    
    // Utiliser votre méthode existante suppreclamation() avec l'ID de la réclamation
    $result = $reclamationController->suppreclamation($id_reclamation);
    
    if ($result) {
        // Redirection après succès
        header('Location: dashboard.php?success=response_deleted');
        exit;
    } else {
        // Redirection après échec
        header('Location: dashboard.php?error=delete_failed');
        exit;
    }
    
} catch (Exception $e) {
    // Gestion des erreurs
    error_log("❌ Erreur suppression: " . $e->getMessage());
    header('Location: dashboard.php?error=server_error&message=' . urlencode($e->getMessage()));
    exit;
}
?>