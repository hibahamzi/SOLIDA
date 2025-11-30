<?php
require_once __DIR__ . '/../Config.php'; 

// Chemin vers Reclamation.php (remonter d'un niveau, puis entrer dans 'models')
require_once __DIR__ . '/../models/Reclamation.php';

class ReclamationController {
    // Récupérer tous les utilisateurs
    public function getReclamation() {
        $conn = config::getConnexion(); // Connexion à la base de données

        $sql = "SELECT * FROM reclamations";

        try {
            $query = $conn->prepare($sql); // Préparation de la requête
            $query->execute(); // Exécution de la requête
            return $query->fetchAll(); // Retourne tous les résultats
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage()); // Gestion des erreurs
        }
    }

    // Ajouter un utilisateur methode1
    // DANS : ReclamationController.php

public function addReclamation($reclamation) {
    $conn = config::getConnexion();
    // 🛑 CORRECTION : Utiliser client_nom, client_email, etc.
    $sql = "INSERT INTO reclamations(client_nom, client_prenom, client_telephone, client_email, gouvernorat, delegation, ville, position_gps, description_detaillee, priorite, statut, date_creation) 
            VALUES (:nom, :prenom, :telephone, :email, :gouvernorat, :delegation, :ville, :position_gps, :description_detaillee, :priorite, :statut, :date)";

    try {
        $query = $conn->prepare($sql);
        $query->execute([
            ':nom' => $reclamation['nom'], // Ces clés PHP restent les mêmes
            ':prenom' => $reclamation['prenom'],
            ':telephone' => $reclamation['telephone'],
            ':email' => $reclamation['email'],
            ':gouvernorat' => $reclamation['gouvernorat'],
            ':delegation' => $reclamation['delegation'],
            ':ville' => $reclamation['ville'],
            ':position_gps' => $reclamation['position_gps'],
            ':description_detaillee' => $reclamation['description_detaillee'],
            ':priorite' => $reclamation['priorite'],
            ':statut' => $reclamation['statut'],
            ':date' => $reclamation['date']
        ]); 
    } catch (Exception $e) {
        die('Erreur: ' . $e->getMessage()); 
    }
}

   
public function deleteReclamation($id){
        $conn = config::getConnexion();
        $sql="DELETE FROM reponadmin WHERE id = :id";
        try{
            $query=$conn->prepare($sql);
            $query->execute([':id'=>$id]);
        }
        catch (Exception $e) {
            die('Erreur: ' . $e->getMessage()); // Gestion des erreurs
        }
   }
   
public function suppreclamation($id){
        $conn = config::getConnexion();
        $sql="DELETE FROM reclamations WHERE id = :id";
        try{
            $query=$conn->prepare($sql);
            $query->execute([':id'=>$id]);
        }
        catch (Exception $e) {
            die('Erreur: ' . $e->getMessage()); // Gestion des erreurs
        }
   } 

// Récupérer un utilisateur par ID
public function getReclamationById($id){
    $conn = config::getConnexion();
    $sql = "SELECT * FROM reclamations WHERE id = :id";
    try {
        $query = $conn->prepare($sql);
        $query->execute([':id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Pour le débogage, nous allons afficher l'erreur SQL exacte
        echo '<div style="color: red; font-weight: bold;">Erreur SQL lors de la récupération: ' . $e->getMessage() . '</div>';
        return false;
    }
}
/**
 * DANS : ../Controller/ReclamationController.php
 * * Ajoute la réponse de l'administrateur dans la table 'reponadmin'
 * et met à jour le statut dans la table 'reclamations'.
 */
public function addAdminResponse($id, $response_detaillee, $new_statut, $date_reponse) {
    $conn = config::getConnexion(); 
    
    try {
        // Démarrer une transaction
        $conn->beginTransaction();
        
        // 1. Mettre à jour le statut dans la table reclamations
        $sql_update = "UPDATE reclamations SET statut = :statut WHERE id = :id";
        $query_update = $conn->prepare($sql_update);
        $query_update->execute([
            ':id' => $id,
            ':statut' => $new_statut
        ]);
        
        // 2. Récupérer l'email et la description de la réclamation
        $reclamation = $this->getReclamationById($id);
        if (!$reclamation) {
            $conn->rollBack();
            return false;
        }
        
        $email = $reclamation['email'] ?? $reclamation['client_email'] ?? '';
        $description = $reclamation['description_detaillee'] ?? '';
        
        // 3. Vérifier si une réponse existe déjà pour cette réclamation
        $sql_check = "SELECT id FROM reponadmin WHERE id = :id";
        $query_check = $conn->prepare($sql_check);
        $query_check->execute([':id' => $id]);
        $existing = $query_check->fetch();
        
        if ($existing) {
            // Mettre à jour la réponse existante
            $sql_insert = "UPDATE reponadmin SET 
                            email = :email,
                            description = :description,
                            reponse = :reponse,
                            statut = :statut,
                            date_creation = :date_creation
                          WHERE id = :id";
        } else {
            // Insérer une nouvelle réponse
            $sql_insert = "INSERT INTO reponadmin (id, email, description, reponse, statut, date_creation) 
                          VALUES (:id, :email, :description, :reponse, :statut, :date_creation)";
        }
        
        $query_insert = $conn->prepare($sql_insert);
        $success = $query_insert->execute([
            ':id' => $id,
            ':email' => $email,
            ':description' => $description,
            ':reponse' => $response_detaillee,
            ':statut' => $new_statut,
            ':date_creation' => $date_reponse
        ]);
        
        if ($success) {
            $conn->commit();
            return true;
        } else {
            $conn->rollBack();
            return false;
        }
        
    } catch (Exception $e) {
        // En cas d'erreur, annuler la transaction
        $conn->rollBack();
        error_log("Erreur DB dans addAdminResponse: " . $e->getMessage());
        return false;
    }
}

// Dans la classe ReclamationController

public function modifierReponseReclamation($id_reponse, $nouveau_contenu) {
    // 1. Validation des données (à compléter selon vos besoins)
    if (empty($nouveau_contenu)) {
        return ['success' => false, 'message' => 'Le contenu de la réponse ne peut pas être vide.'];
    }

    // 2. Appel au modèle pour la mise à jour
    $result = $this->reclamationModel->modifierReponse($id_reponse, $nouveau_contenu);

    if ($result) {
        return ['success' => true, 'message' => 'Réponse modifiée avec succès.'];
    } else {
        return ['success' => false, 'message' => 'Erreur lors de la modification de la réponse.'];
    }
}

/**
 * Récupère la réponse d'une réclamation depuis la table reponadmin
 */
public function getResponseByReclamationId($id) {
    $conn = config::getConnexion();
    $sql = "SELECT * FROM reponadmin WHERE id = :id";
    try {
        $query = $conn->prepare($sql);
        $query->execute([':id' => $id]);
        return $query->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log("Erreur DB dans getResponseByReclamationId: " . $e->getMessage());
        return false;
    }
}

/**
 * Récupère toutes les réponses depuis la table reponadmin
 * Retourne un tableau associatif avec l'id de réclamation comme clé
 */
public function getAllResponses() {
    $conn = config::getConnexion();
    $sql = "SELECT * FROM reponadmin";
    try {
        $query = $conn->prepare($sql);
        $query->execute();
        $responses = $query->fetchAll(PDO::FETCH_ASSOC);
        
        // Créer un tableau associatif avec l'id comme clé
        $responses_array = [];
        foreach ($responses as $response) {
            $responses_array[$response['id']] = $response;
        }
        return $responses_array;
    } catch (Exception $e) {
        error_log("Erreur DB dans getAllResponses: " . $e->getMessage());
        return [];
    }
}
// Mettre à jour un utilisateur
public function updateReclamation($id, $nom, $prenom, $telephone, $email, $gouvernorat, $delegation, $ville, $position_gps, $description_detaillee, $priorite, $statut, $date){
    $conn = config::getConnexion();
    $sql = "UPDATE reclamations SET nom = :nom,prenom =:prenom, telephone = :telephone,email=:email, gouvernorat=:gouvernorat,delegation=:delegation,ville=:ville,position_gps=:position_gps,description_detaillee=:description_detaillee,priorite=:priorite,statut=:statut,date=:date WHERE id = :id";
    try {
        $query = $conn->prepare($sql);
        $success = $query->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':telephone' => $telephone,
            ':email' => $email,
            ':gouvernorat' => $gouvernorat,
            ':delegation' => $delegation,
            ':ville' => $ville,
            ':position_gps' => $position_gps,
            ':description_detaillee' => $description_detaillee,
            ':priorite' => $priorite,
            ':statut' => $statut,
            ':date' => $date,
            ':id' => $id
        ]);
        return $success;
    } catch (Exception $e) {
        // Pour le débogage, nous allons afficher l'erreur SQL exacte
        echo '<div style="color: red; font-weight: bold;">Erreur SQL lors de la mise à jour: ' . $e->getMessage() . '</div>';
        return false;
    }
}

}