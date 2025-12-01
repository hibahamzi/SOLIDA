<?php
require_once __DIR__ . '/../Config.php'; 

// Chemin vers Reclamation.php (remonter d'un niveau, puis entrer dans 'models')
require_once __DIR__ . '/../models/Reclamation.php';

class ReclamationController {
    
    // Récupérer toutes les réclamations avec jointure sur les réponses (exigence du projet)
    public function getReclamation() {
        $conn = config::getConnexion(); // Connexion à la base de données

        // Utilisation de LEFT JOIN pour récupérer les réclamations avec leurs réponses (exigence du projet)
       $sql = "SELECT r.*, 
               rep.id as id_reponse_unique,
               rep.reponse as reponse_text,
               rep.date_creation as reponse_date,
               rep.statut as reponse_statut
        FROM reclamations r
        LEFT JOIN reponadmin rep ON r.id = rep.id_reponse
        ORDER BY r.date DESC";


        try {
            $query = $conn->prepare($sql); // Préparation de la requête
            $query->execute(); // Exécution de la requête
            return $query->fetchAll(PDO::FETCH_ASSOC); // Retourne tous les résultats
        } catch (Exception $e) {
            error_log('Erreur dans getReclamation: ' . $e->getMessage());
            // En cas d'erreur, essayer sans jointure (fallback)
            try {
                $sql_fallback = "SELECT * FROM reclamations ORDER BY date DESC";
                $query = $conn->prepare($sql_fallback);
                $query->execute();
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e2) {
                die('Erreur: ' . $e2->getMessage()); // Gestion des erreurs
            }
        }
    }

    // Ajouter un utilisateur methode1
    public function addReclamation($reclamation) {
        $conn = config::getConnexion();
        $sql = "INSERT INTO reclamations(nom, prenom, telephone, email, gouvernorat, delegation, ville, position_gps, description_detaillee, priorite, statut, date) 
                VALUES (:nom, :prenom, :telephone, :email, :gouvernorat, :delegation, :ville, :position_gps, :description_detaillee, :priorite, :statut, :date)";

        try {
            $query = $conn->prepare($sql);
            $query->execute([
                ':nom' => $reclamation['nom'],
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
     * Mettre à jour une réclamation
     * CORRIGÉ : Utilise config::getConnexion() au lieu de $this->connection
     */
    public function updateReclamation($data) {
        $conn = config::getConnexion(); // CORRECTION : Utiliser la connexion existante
        
        try {
            // Vérifier que l'ID existe
            if (empty($data['id'])) {
                throw new Exception("ID de réclamation manquant");
            }

            // Construction de la requête SQL
            $sql = "UPDATE reclamations SET 
                    nom = :nom, 
                    prenom = :prenom, 
                    telephone = :telephone, 
                    email = :email, 
                    gouvernorat = :gouvernorat, 
                    delegation = :delegation, 
                    ville = :ville, 
                    position_gps = :position_gps, 
                    description_detaillee = :description_detaillee, 
                    priorite = :priorite, 
                    statut = :statut, 
                    date = :date 
                    WHERE id = :id";

            $stmt = $conn->prepare($sql); // CORRECTION : Utiliser $conn au lieu de $this->connection
            
            $result = $stmt->execute([
                ':id' => $data['id'],
                ':nom' => $data['nom'],
                ':prenom' => $data['prenom'],
                ':telephone' => $data['telephone'],
                ':email' => $data['email'],
                ':gouvernorat' => $data['gouvernorat'],
                ':delegation' => $data['delegation'],
                ':ville' => $data['ville'],
                ':position_gps' => $data['position_gps'],
                ':description_detaillee' => $data['description_detaillee'],
                ':priorite' => $data['priorite'],
                ':statut' => $data['statut'],
                ':date' => $data['date_modification'] // Utiliser date_modification pour la date de mise à jour
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour de la réclamation: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ajoute la réponse de l'administrateur dans la table 'reponadmin'
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

    /* ================================
       FONCTIONNALITÉS MÉTIER - STATISTIQUES
    ================================= */
    
    /**
     * Récupère les statistiques complètes des réclamations
     */
    public function getStatistics() {
        $conn = config::getConnexion();
        $stats = [];
        
        try {
            // Total des réclamations
            $stmt = $conn->query("SELECT COUNT(*) as total FROM reclamations");
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Réclamations par statut
            $stmt = $conn->query("SELECT statut, COUNT(*) as count FROM reclamations GROUP BY statut");
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Réclamations par priorité
            $stmt = $conn->query("SELECT priorite, COUNT(*) as count FROM reclamations GROUP BY priorite");
            $stats['by_priority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Réclamations par gouvernorat
            $stmt = $conn->query("SELECT gouvernorat, COUNT(*) as count FROM reclamations GROUP BY gouvernorat ORDER BY count DESC LIMIT 10");
            $stats['by_governorate'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Réclamations non résolues
            $stmt = $conn->query("SELECT COUNT(*) as count FROM reclamations WHERE statut NOT IN ('Résolu', 'Clôturé')");
            $stats['unresolved'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Réclamations résolues
            $stmt = $conn->query("SELECT COUNT(*) as count FROM reclamations WHERE statut IN ('Résolu', 'Clôturé')");
            $stats['resolved'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            // Taux de résolution
            if ($stats['total'] > 0) {
                $stats['resolution_rate'] = round(($stats['resolved'] / $stats['total']) * 100, 2);
            } else {
                $stats['resolution_rate'] = 0;
            }
            
            // Réclamations urgentes (non résolues uniquement)
            $stmt = $conn->query("SELECT COUNT(*) as count FROM reclamations 
                                  WHERE (LOWER(TRIM(priorite)) = 'urgente' OR LOWER(priorite) LIKE '%urgent%')
                                  AND LOWER(TRIM(statut)) NOT IN ('résolu', 'clôturé', 'resolu', 'cloture', 'traité', 'traite', 'traitée', 'traitee')
                                  AND (statut IS NOT NULL AND TRIM(statut) != '')");
            $stats['urgent'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            
            return $stats;
        } catch (Exception $e) {
            error_log("Erreur dans getStatistics: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcule le temps moyen de traitement des réclamations résolues
     */
    public function getAverageProcessingTime() {
    $conn = config::getConnexion();
    
    try {
        // CORRECTION : Utiliser les bons statuts selon votre tableau
        // Dans votre tableau, les statuts sont : 'Urgente', 'Nouveau', etc.
        // Les statuts terminés semblent être : 'Traitée', 'Résolue', 'Clôturée'
        
        $sql = "SELECT r.id, r.date as date_creation, 
                       COALESCE(MAX(rep.date_creation), r.date) as date_resolution,
                       r.statut
                FROM reclamations r
                LEFT JOIN reponadmin rep ON r.id = rep.id_reclamation
                WHERE r.statut IN ('Traitée', 'Résolue', 'Clôturée', 
                                   'traitee', 'resolue', 'cloturee',
                                   'Traitee', 'Resolue', 'Cloturee',
                                   'Traitée', 'Résolue', 'Clôturée')
                GROUP BY r.id, r.date, r.statut";
        
        error_log("📊 SQL exécuté: " . $sql);
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $reclamations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        error_log("📊 Nombre de réclamations résolues trouvées: " . count($reclamations));
        
        if (empty($reclamations)) {
            error_log("📊 Aucune réclamation résolue trouvée");
            return ['average_days' => 0, 'total_processed' => 0];
        }
        
        $totalDays = 0;
        $count = 0;
        
        foreach ($reclamations as $rec) {
            error_log("📊 Réclamation ID: " . $rec['id'] . 
                     ", Statut: " . $rec['statut'] . 
                     ", Date création: " . $rec['date_creation'] . 
                     ", Date résolution: " . $rec['date_resolution']);
            
            try {
                $dateCreation = new DateTime($rec['date_creation']);
                $dateResolution = new DateTime($rec['date_resolution']);
                $diff = $dateCreation->diff($dateResolution);
                
                $days = $diff->days;
                $totalDays += $days;
                $count++;
                
                error_log("📊 Durée traitement: " . $days . " jours");
                
            } catch (Exception $e) {
                error_log("❌ Erreur calcul dates ID " . $rec['id'] . ": " . $e->getMessage());
                continue;
            }
        }
        
        $averageDays = $count > 0 ? round($totalDays / $count, 2) : 0;
        
        error_log("📊 Résultat final - Total: $count réclamations, Moyenne: $averageDays jours");
        
        return [
            'average_days' => $averageDays,
            'total_processed' => $count,
            'total_days' => $totalDays
        ];
        
    } catch (Exception $e) {
        error_log("❌ Erreur dans getAverageProcessingTime: " . $e->getMessage());
        return ['average_days' => 0, 'total_processed' => 0];
    }
}
    /**
     * Analyse des tendances par période
     */
    public function getTrendsAnalysis($period = 'month') {
        $conn = config::getConnexion();
        
        try {
            $sql = "";
            
        
            if ($period === 'week') {
                // Par semaine
                $sql = "SELECT 
                            DATE_FORMAT(date, '%Y-%u') as period,
                            COUNT(*) as count,
                            SUM(CASE WHEN statut IN ('Résolu', 'Clôturé') THEN 1 ELSE 0 END) as resolved
                        FROM reclamations
                        WHERE date >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 WEEK)
                        GROUP BY DATE_FORMAT(date, '%Y-%u')
                        ORDER BY period ASC";
            } else {
                // Par jour
                $sql = "SELECT 
                            DATE(date) as period,
                            COUNT(*) as count,
                            SUM(CASE WHEN statut IN ('Résolu', 'Clôturé') THEN 1 ELSE 0 END) as resolved
                        FROM reclamations
                        WHERE date >= DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
                        GROUP BY DATE(date)
                        ORDER BY period ASC";
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur dans getTrendsAnalysis: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Recherche avancée avec filtres multiples
     */
    public function searchReclamations($filters = []) {
        $conn = config::getConnexion();
        
        try {
            $sql = "SELECT * FROM reclamations WHERE 1=1";
            $params = [];
            
            // Recherche par nom OU prénom (si un seul mot est entré)
            if (!empty($filters['nom_ou_prenom'])) {
                $sql .= " AND (
                    nom LIKE :nom_ou_prenom OR 
                    prenom LIKE :nom_ou_prenom
                )";
                $params[':nom_ou_prenom'] = "%" . trim($filters['nom_ou_prenom']) . "%";
            }
            
            // Recherche par nom (fonctionne combiné avec prénom - recherche précise)
            if (!empty($filters['nom']) && empty($filters['nom_ou_prenom'])) {
                $sql .= " AND nom LIKE :nom";
                $params[':nom'] = "%" . trim($filters['nom']) . "%";
            }
            
            // Recherche par prénom (fonctionne combiné avec nom - recherche précise)
            if (!empty($filters['prenom']) && empty($filters['nom_ou_prenom'])) {
                $sql .= " AND prenom LIKE :prenom";
                $params[':prenom'] = "%" . trim($filters['prenom']) . "%";
            }
            
            // Recherche par email (fonctionne seul ou combiné avec nom/prénom)
            if (!empty($filters['email'])) {
                $sql .= " AND email LIKE :email";
                $params[':email'] = "%" . trim($filters['email']) . "%";
            }
            
            // Tri par date
            $sql .= " ORDER BY date DESC";
            
            // Préparer et exécuter la requête
            $stmt = $conn->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue($key, $value);
            }
            
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (Exception $e) {
            error_log("Erreur dans searchReclamations: " . $e->getMessage());
            error_log("Params: " . print_r($params, true));
            return [];
        }
    }
    
    /**
     * Récupère les réclamations nécessitant une attention urgente
     */
    public function getUrgentReclamations() {
        $conn = config::getConnexion();
        
        try {
            // Requête SIMPLE et DIRECTE - trouver toutes les réclamations avec priorité urgente
            // Ne pas filtrer par statut dans la requête SQL, on le fera après
            $sql = "SELECT * FROM reclamations WHERE LOWER(priorite) LIKE '%urgent%'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $all_urgent = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Log pour debug
            error_log("=== getUrgentReclamations DEBUG ===");
            error_log("Total réclamations avec 'urgent' dans priorité: " . count($all_urgent));
            
            // Filtrer pour exclure les statuts résolus
            $results = [];
            $statuts_resolus = ['résolu', 'clôturé', 'resolu', 'cloture', 'traité', 'traite', 'traitée', 'traitee'];
            
            foreach ($all_urgent as $rec) {
                $statut = strtolower(trim($rec['statut'] ?? ''));
                $priorite = strtolower(trim($rec['priorite'] ?? ''));
                
                // Vérifier que la priorité contient bien "urgent"
                if (stripos($priorite, 'urgent') !== false) {
                    // Si le statut n'est pas résolu ET n'est pas vide
                    if (!in_array($statut, $statuts_resolus) && !empty($statut)) {
                        $results[] = $rec;
                        error_log("Réclamation urgente trouvée: ID=" . ($rec['id'] ?? 'N/A') . ", Priorité=" . ($rec['priorite'] ?? 'N/A') . ", Statut=" . ($rec['statut'] ?? 'N/A'));
                    } else {
                        error_log("Réclamation urgente EXCLUE (statut résolu): ID=" . ($rec['id'] ?? 'N/A') . ", Statut=" . ($rec['statut'] ?? 'N/A'));
                    }
                }
            }
            
            // Limiter à 20 résultats
            $results = array_slice($results, 0, 20);
            
            error_log("Résultat final: " . count($results) . " réclamations urgentes actives");
            error_log("=== FIN DEBUG getUrgentReclamations ===");
            
            return $results;
        } catch (Exception $e) {
            error_log("ERREUR dans getUrgentReclamations: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return [];
        }
    }
    
    /**
     * Récupère les réclamations en retard (non résolues depuis plus de X jours)
     */
    public function getOverdueReclamations($days = 7) {
        $conn = config::getConnexion();
        
        try {
            // Utiliser 'date' au lieu de 'date_creation' et gérer différentes valeurs de statut
            $sql = "SELECT * FROM reclamations 
                    WHERE statut NOT IN ('Résolu', 'Clôturé', 'resolu', 'cloture', 'Resolu', 'Cloture')
                    AND date < DATE_SUB(CURRENT_DATE(), INTERVAL :days DAY)
                    ORDER BY date ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur dans getOverdueReclamations: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Calcule le taux de résolution par gouvernorat
     */
    public function getResolutionRateByGovernorate() {
        $conn = config::getConnexion();
        
        try {
            $sql = "SELECT 
                        gouvernorat,
                        COUNT(*) as total,
                        SUM(CASE WHEN statut IN ('Résolu', 'Clôturé') THEN 1 ELSE 0 END) as resolved,
                        ROUND((SUM(CASE WHEN statut IN ('Résolu', 'Clôturé') THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as resolution_rate
                    FROM reclamations
                    WHERE gouvernorat IS NOT NULL AND gouvernorat != ''
                    GROUP BY gouvernorat
                    ORDER BY resolution_rate DESC, total DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur dans getResolutionRateByGovernorate: " . $e->getMessage());
            return [];
        }
    }

}