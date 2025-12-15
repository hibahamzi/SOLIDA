<?php
// export_reclamations.php - Export CSV des réclamations
require_once __DIR__ . '/../../controllers/ReclamationController.php';

session_start();

// Vérifier si l'utilisateur est admin
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../front_office/sign-in.php');
    exit();
}

// Créer une instance de ReclamationController
$reclamationController = new ReclamationController();

try {
    // Récupérer toutes les réclamations
    $reclamations = $reclamationController->getReclamation();
    
    if (!is_array($reclamations)) {
        $reclamations = [];
    }
    
    // Ajouter les réponses pour chaque réclamation
    $dataWithResponses = [];
    foreach ($reclamations as $reclamation) {
        $id = $reclamation['id'];
        $response = $reclamationController->getResponseByReclamationId($id);
        
        $reclamation['reponse'] = $response ? ($response['reponse'] ?? '') : '';
        $reclamation['reponse_date'] = $response ? ($response['date_creation'] ?? '') : '';
        $reclamation['reponse_statut'] = $response ? ($response['statut'] ?? '') : '';
        
        $dataWithResponses[] = $reclamation;
    }
    
    // Préparer les en-têtes CSV
    $headers = ['ID', 'Nom', 'Prénom', 'Email', 'Téléphone', 'Gouvernorat', 
                'Priorité', 'Statut', 'Date', 'Description', 'Réponse', 'Date Réponse'];
    
    // Générer le nom de fichier avec date
    $filename = 'reclamations_' . date('Y-m-d_H-i') . '.csv';
    
    // Définir les en-têtes HTTP pour le téléchargement[citation:6]
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Ouvrir le flux de sortie[citation:2]
    $output = fopen('php://output', 'w');
    
    // Écrire l'en-tête BOM UTF-8 pour Excel[citation:4]
    fputs($output, chr(0xEF) . chr(0xBB) . chr(0xBF));
    
    // Écrire les en-têtes[citation:6]
    fputcsv($output, $headers);
    
    // Écrire les données
    foreach ($dataWithResponses as $reclamation) {
        $row = [
            $reclamation['id'] ?? '',
            $reclamation['nom'] ?? '',
            $reclamation['prenom'] ?? '',
            $reclamation['email'] ?? '',
            $reclamation['telephone'] ?? '',
            $reclamation['gouvernorat'] ?? '',
            $reclamation['priorite'] ?? '',
            $reclamation['statut'] ?? '',
            $reclamation['date'] ?? $reclamation['date_creation'] ?? '',
            $reclamation['description'] ?? '',
            $reclamation['reponse'] ?? '',
            $reclamation['reponse_date'] ?? ''
        ];
        
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
    
} catch (Exception $e) {
    error_log("Erreur lors de l'export CSV: " . $e->getMessage());
    header('Location: BackofficeReclamations.php?error=export_failed');
    exit();
}
?>