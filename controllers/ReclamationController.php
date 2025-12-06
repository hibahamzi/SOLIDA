<?php
// ReclamationController.php
require_once __DIR__ . '/../Config.php'; 
require_once __DIR__ . '/../models/Reclamation.php';

class ReclamationController {
    
    // Récupérer toutes les réclamations avec jointure sur les réponses
    public function getReclamation() {
        $conn = config::getConnexion(); 

        // R.id est l'ID de la réclamation. rep.id_reponse est la clé étrangère qui le lie.
        $sql = "SELECT r.*, 
                rep.id as id_reponse_unique,
                rep.reponse as reponse_text,
                rep.date_creation as reponse_date,
                rep.statut as reponse_statut
            FROM reclamations r
            LEFT JOIN reponadmin rep ON r.id = rep.id_reponse 
            ORDER BY r.date DESC";

        try {
            $query = $conn->prepare($sql); 
            $query->execute(); 
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log('Erreur dans getReclamation: ' . $e->getMessage());
            // Fallback sans jointure
            try {
                $sql_fallback = "SELECT * FROM reclamations ORDER BY date DESC";
                $query = $conn->prepare($sql_fallback);
                $query->execute();
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e2) {
                die('Erreur: ' . $e2->getMessage());
            }
        }
    }

    // Ajouter une réclamation
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
    
    // CORRIGÉ : Supprimer la réponse de l'admin associée à l'ID de la réclamation
    public function deleteReclamation($id){
        $conn = config::getConnexion();
        // Utilise la clé étrangère id_reponse
        $sql="DELETE FROM reponadmin WHERE id_reponse = :id";
        try{
            $query=$conn->prepare($sql);
            $query->execute([':id'=>$id]);
        }
        catch (Exception $e) {
            // Pas critique si la réponse n'existe pas
            error_log('Avertissement: Impossible de supprimer la réponse (probablement déjà supprimée ou non existante): ' . $e->getMessage());
        }
    }
    
    // Supprimer la réclamation principale
    public function suppreclamation($id){
        // Si vous utilisez ON DELETE CASCADE sur la clé étrangère dans la DB, vous pouvez retirer 
        // l'appel à deleteReclamation($id) si vous l'aviez mis avant cette fonction.
        
        // Optionnel : Appel explicite de la suppression de la réponse
        $this->deleteReclamation($id); 

        $conn = config::getConnexion();
        $sql="DELETE FROM reclamations WHERE id = :id";
        try{
            $query=$conn->prepare($sql);
            $query->execute([':id'=>$id]);
        }
        catch (Exception $e) {
            die('Erreur: ' . $e->getMessage()); 
        }
    } 

    // Récupérer une réclamation par ID
    public function getReclamationById($id){
        $conn = config::getConnexion();
        $sql = "SELECT * FROM reclamations WHERE id = :id";
        try {
            $query = $conn->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo '<div style="color: red; font-weight: bold;">Erreur SQL lors de la récupération: ' . $e->getMessage() . '</div>';
            return false;
        }
    }

    // Mettre à jour une réclamation
    public function updateReclamation($data) {
        $conn = config::getConnexion(); 
        
        try {
            if (empty($data['id'])) {
                throw new Exception("ID de réclamation manquant");
            }

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

            $stmt = $conn->prepare($sql);
            
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
                ':date' => $data['date_modification'] 
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            error_log("Erreur lors de la mise à jour de la réclamation: " . $e->getMessage());
            return false;
        }
    }

    // CORRIGÉ : Ajoute la réponse de l'administrateur
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
            
            $email = $reclamation['email'] ?? '';
            $description = $reclamation['description_detaillee'] ?? '';
            
            // 3. Vérifier si une réponse existe déjà pour cette réclamation
            // CORRECTION: Recherche par la clé étrangère id_reponse (qui contient l'ID de la réclamation)
            $sql_check = "SELECT id FROM reponadmin WHERE id_reponse = :id"; 
            $query_check = $conn->prepare($sql_check);
            $query_check->execute([':id' => $id]);
            $existing = $query_check->fetch();
            
            if ($existing) {
                // Mettre à jour la réponse existante
                // CORRECTION: Utiliser id_reponse dans le WHERE
                $sql_insert = "UPDATE reponadmin SET 
                                 email = :email,
                                 description = :description,
                                 reponse = :reponse,
                                 statut = :statut,
                                 date_creation = :date_creation
                               WHERE id_reponse = :id"; 
            } else {
                // Insérer une nouvelle réponse
                // CORRECTION: Utiliser id_reponse comme colonne pour lier à reclamations.id
                $sql_insert = "INSERT INTO reponadmin (id_reponse, email, description, reponse, statut, date_creation) 
                               VALUES (:id, :email, :description, :reponse, :statut, :date_creation)";
            }
            
            $query_insert = $conn->prepare($sql_insert);
            $success = $query_insert->execute([
                ':id' => $id, // C'est l'ID de la réclamation, passé à id_reponse
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
            $conn->rollBack();
            error_log("Erreur DB dans addAdminResponse: " . $e->getMessage());
            return false;
        }
    }

    // La méthode modifierReponseReclamation n'est pas complète (elle appelle une méthode de modèle non définie)
    // Je la laisse telle quelle, mais elle nécessite la définition de $this->reclamationModel.

    /**
     * Récupère la réponse d'une réclamation depuis la table reponadmin
     * CORRIGÉ : Utilise id_reponse
     */
    public function getResponseByReclamationId($id) {
        $conn = config::getConnexion();
        // Utilise la clé étrangère id_reponse
        $sql = "SELECT * FROM reponadmin WHERE id_reponse = :id";
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
     * CORRIGÉ : Retourne un tableau associatif avec l'id de réclamation (id_reponse) comme clé
     */
    public function getAllResponses() {
        $conn = config::getConnexion();
        $sql = "SELECT * FROM reponadmin";
        try {
            $query = $conn->prepare($sql);
            $query->execute();
            $responses = $query->fetchAll(PDO::FETCH_ASSOC);
            
            // CORRECTION: Utiliser id_reponse comme clé pour lier à l'affichage des réclamations
            $responses_array = [];
            foreach ($responses as $response) {
                $responses_array[$response['id_reponse']] = $response;
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
    
    // Récupère les statistiques complètes des réclamations (Fonction inchangée)
    public function getStatistics() {
        // ... (Code des statistiques inchangé car il n'impacte pas reponadmin, sauf pour le taux de résolution)
        // ... (J'ai laissé la fonction complète mais non modifiée pour concision, car la logique est correcte pour la plupart des stats)
        $conn = config::getConnexion();
        $stats = [];
        
        try {
            // ... (requêtes SQL)
            $stmt = $conn->query("SELECT COUNT(*) as total FROM reclamations");
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            $stmt = $conn->query("SELECT statut, COUNT(*) as count FROM reclamations GROUP BY statut");
            $stats['by_status'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $conn->query("SELECT priorite, COUNT(*) as count FROM reclamations GROUP BY priorite");
            $stats['by_priority'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $conn->query("SELECT gouvernorat, COUNT(*) as count FROM reclamations GROUP BY gouvernorat ORDER BY count DESC LIMIT 10");
            $stats['by_governorate'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $stmt = $conn->query("SELECT COUNT(*) as count FROM reclamations WHERE statut NOT IN ('Résolu', 'Clôturé')");
            $stats['unresolved'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            $stmt = $conn->query("SELECT COUNT(*) as count FROM reclamations WHERE statut IN ('Résolu', 'Clôturé')");
            $stats['resolved'] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            
            if ($stats['total'] > 0) {
                $stats['resolution_rate'] = round(($stats['resolved'] / $stats['total']) * 100, 2);
            } else {
                $stats['resolution_rate'] = 0;
            }
            
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
     * CORRIGÉ : Utilise id_reponse
     */
    public function getAverageProcessingTime() {
        $conn = config::getConnexion();
        
        try {
            $sql = "SELECT r.id, r.date as date_creation, 
                            COALESCE(MAX(rep.date_creation), r.date) as date_resolution,
                            r.statut
                    FROM reclamations r
                    LEFT JOIN reponadmin rep ON r.id = rep.id_reponse -- CORRECTION ICI
                    WHERE r.statut IN ('Traitée', 'Résolue', 'Clôturée', 
                                        'traitee', 'resolue', 'cloturee',
                                        'Traitee', 'Resolue', 'Cloturee',
                                        'Traitée', 'Résolue', 'Clôturée')
                    GROUP BY r.id, r.date, r.statut";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $reclamations = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($reclamations)) {
                return ['average_days' => 0, 'total_processed' => 0];
            }
            
            $totalDays = 0;
            $count = 0;
            
            foreach ($reclamations as $rec) {
                try {
                    $dateCreation = new DateTime($rec['date_creation']);
                    $dateResolution = new DateTime($rec['date_resolution']);
                    $diff = $dateCreation->diff($dateResolution);
                    
                    $days = $diff->days;
                    $totalDays += $days;
                    $count++;
                    
                } catch (Exception $e) {
                    error_log("❌ Erreur calcul dates ID " . ($rec['id'] ?? 'N/A') . ": " . $e->getMessage());
                    continue;
                }
            }
            
            $averageDays = $count > 0 ? round($totalDays / $count, 2) : 0;
            
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
    
    // Analyse des tendances par période (Fonction inchangée)
    public function getTrendsAnalysis($period = 'month') {
        // ... (Code inchangé)
        $conn = config::getConnexion();
        
        try {
            $sql = "";
            
            if ($period === 'week') {
                $sql = "SELECT 
                            DATE_FORMAT(date, '%Y-%u') as period,
                            COUNT(*) as count,
                            SUM(CASE WHEN statut IN ('Résolu', 'Clôturé') THEN 1 ELSE 0 END) as resolved
                        FROM reclamations
                        WHERE date >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 WEEK)
                        GROUP BY DATE_FORMAT(date, '%Y-%u')
                        ORDER BY period ASC";
            } else {
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
    
    // Recherche avancée avec filtres multiples (Fonction inchangée)
    public function searchReclamations($filters = []) {
        // ... (Code inchangé)
        $conn = config::getConnexion();
        
        try {
            $sql = "SELECT * FROM reclamations WHERE 1=1";
            $params = [];
            
            // ... (logique de construction de la requête)
             if (!empty($filters['nom_ou_prenom'])) {
                $sql .= " AND (
                    nom LIKE :nom_ou_prenom OR 
                    prenom LIKE :nom_ou_prenom
                )";
                $params[':nom_ou_prenom'] = "%" . trim($filters['nom_ou_prenom']) . "%";
            }
            
            if (!empty($filters['nom']) && empty($filters['nom_ou_prenom'])) {
                $sql .= " AND nom LIKE :nom";
                $params[':nom'] = "%" . trim($filters['nom']) . "%";
            }
            
            if (!empty($filters['prenom']) && empty($filters['nom_ou_prenom'])) {
                $sql .= " AND prenom LIKE :prenom";
                $params[':prenom'] = "%" . trim($filters['prenom']) . "%";
            }
            
            if (!empty($filters['email'])) {
                $sql .= " AND email LIKE :email";
                $params[':email'] = "%" . trim($filters['email']) . "%";
            }
            
            $sql .= " ORDER BY date DESC";
            
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
    
    // Récupère les réclamations nécessitant une attention urgente (Fonction inchangée)
    public function getUrgentReclamations() {
        // ... (Code inchangé)
        $conn = config::getConnexion();
        
        try {
            $sql = "SELECT * FROM reclamations WHERE LOWER(priorite) LIKE '%urgent%'";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $all_urgent = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $results = [];
            $statuts_resolus = ['résolu', 'clôturé', 'resolu', 'cloture', 'traité', 'traite', 'traitée', 'traitee'];
            
            foreach ($all_urgent as $rec) {
                $statut = strtolower(trim($rec['statut'] ?? ''));
                $priorite = strtolower(trim($rec['priorite'] ?? ''));
                
                if (stripos($priorite, 'urgent') !== false) {
                    if (!in_array($statut, $statuts_resolus) && !empty($statut)) {
                        $results[] = $rec;
                    }
                }
            }
            
            $results = array_slice($results, 0, 20);
            
            return $results;
        } catch (Exception $e) {
            error_log("ERREUR dans getUrgentReclamations: " . $e->getMessage());
            return [];
        }
    }
    
    // Récupère les réclamations en retard (Fonction inchangée)
    public function getOverdueReclamations($days = 7) {
        // ... (Code inchangé)
        $conn = config::getConnexion();
        
        try {
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
    
    // Calcule le taux de résolution par gouvernorat (Fonction inchangée)
    public function getResolutionRateByGovernorate() {
        // ... (Code inchangé)
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

    // Le reste de la classe du contrôleur doit être placé ici.
    public function modifierReponseReclamation($id_reponse, $nouveau_contenu) {
        // Cette fonction dépend d'une propriété $this->reclamationModel non définie dans ce fichier.
        // Si vous utilisez un Modèle, assurez-vous de l'initialiser dans le constructeur.
        // Si c'est un contrôleur "tout-en-un", vous devriez déplacer la logique de mise à jour dans ce contrôleur
        
        if (empty($nouveau_contenu)) {
            return ['success' => false, 'message' => 'Le contenu de la réponse ne peut pas être vide.'];
        }

        // Si $this->reclamationModel n'est pas défini, cette ligne va crasher :
        // $result = $this->reclamationModel->modifierReponse($id_reponse, $nouveau_contenu);

        // Implémentation simplifiée si vous voulez que la modification soit ici:
        $conn = config::getConnexion();
        $sql = "UPDATE reponadmin SET reponse = :reponse, date_creation = NOW() WHERE id = :id_reponse";
        
        try {
            $query = $conn->prepare($sql);
            $result = $query->execute([
                ':reponse' => $nouveau_contenu,
                ':id_reponse' => $id_reponse // ID est la clé primaire de reponadmin
            ]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Réponse modifiée avec succès.'];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la modification de la réponse.'];
            }
        } catch (Exception $e) {
            error_log("Erreur DB dans modifierReponseReclamation: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()];
        }
    }

}