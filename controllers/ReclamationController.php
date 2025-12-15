<?php
// ReclamationController.php - VERSION COMPLÈTE ET DÉFINITIVEMENT CORRIGÉE
require_once __DIR__ . '/../config/config.php';

class ReclamationController {
    private PDO $pdo;

    /**
     * Constructor
     * @param PDO $pdo Database connection
     */
    public function __construct(PDO $pdo = null)
    {
        if ($pdo === null) {
            // Use global $pdo if not provided
            global $pdo;
            if (!isset($pdo)) {
                require_once __DIR__ . '/../config/config.php';
            }
        }
        $this->pdo = $pdo ?? $GLOBALS['pdo'];
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    // =========================================================================
    // CRUD DE BASE POUR LA TABLE 'reclamations'
    // =========================================================================
    
    /**
     * Récupérer toutes les réclamations
     * Effectue une jointure pour inclure la réponse admin si elle existe.
     */
    public function getReclamation() {
        $conn = $this->pdo; 

        $sql = "SELECT r.*, rep.reponse as admin_reponse, rep.id_reponse as id_reponse_admin, rep.date_creation as date_reponse 
                FROM reclamations r
                LEFT JOIN reponadmin rep ON r.id = rep.id 
                ORDER BY r.date DESC, r.priorite DESC";

        try {
            $query = $conn->prepare($sql); 
            $query->execute(); 
            $results = $query->fetchAll(PDO::FETCH_ASSOC);
            
            error_log("📊 getReclamation: " . count($results) . " réclamations récupérées");
            
            return $results;
        } catch (Exception $e) {
            error_log('❌ Erreur dans getReclamation: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Ajouter une réclamation (Crée la réclamation initiale)
     */
    public function addReclamation($reclamation) {
        $conn = $this->pdo;
        
        // Get user_id from session if available
        $id_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        
        $sql = "INSERT INTO reclamations(
                            nom, prenom, telephone, email, gouvernorat, 
                            delegation, ville, position_gps, description_detaillee, 
                            priorite, statut, date, id_user
                        ) VALUES (
                            :nom, :prenom, :telephone, :email, :gouvernorat, 
                            :delegation, :ville, :position_gps, :description_detaillee, 
                            :priorite, :statut, :date, :id_user
                        )";

        try {
            $query = $conn->prepare($sql);
            $result = $query->execute([
                ':nom' => $reclamation['nom'] ?? '',
                ':prenom' => $reclamation['prenom'] ?? '',
                ':telephone' => $reclamation['telephone'] ?? '',
                ':email' => $reclamation['email'] ?? '',
                ':gouvernorat' => $reclamation['gouvernorat'] ?? '',
                ':delegation' => $reclamation['delegation'] ?? '',
                ':ville' => $reclamation['ville'] ?? '',
                ':position_gps' => $reclamation['position_gps'] ?? '',
                ':description_detaillee' => $reclamation['description_detaillee'] ?? '',
                ':priorite' => $reclamation['priorite'] ?? 'Moyenne',
                ':statut' => $reclamation['statut'] ?? 'Nouveau',
                ':date' => $reclamation['date'] ?? date('Y-m-d H:i:s'),
                ':id_user' => $id_user
            ]);
            
            if ($result) {
                $id = $conn->lastInsertId();
                error_log("✅ Réclamation ajoutée! ID: $id");
                return $id;
            }
            error_log("❌ Échec addReclamation");
            return false;
            
        } catch (Exception $e) {
            error_log('❌ Erreur dans addReclamation: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprimer une réclamation
     */
    public function suppreclamation($id){
        $conn = $this->pdo;
        
        try {
            // Supprime d'abord la réponse liée pour garantir l'intégrité référentielle
            $this->deleteResponseByReclamationId($id);

            $sql = "DELETE FROM reclamations WHERE id = :id";
            $query = $conn->prepare($sql);
            $success = $query->execute([':id' => $id]);
            
            if ($success) {
                error_log("✅ Réclamation $id supprimée");
                return true;
            } else {
                return false;
            }
        } catch (Exception $e) {
            error_log('❌ Erreur dans suppreclamation: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer une réclamation par ID
     */
    public function getReclamationById($id){
        $conn = $this->pdo;
        
        $sql = "SELECT * FROM reclamations WHERE id = :id";
                
        try {
            $query = $conn->prepare($sql);
            $query->execute([':id' => $id]);
            $result = $query->fetch(PDO::FETCH_ASSOC);
            
            return $result ?: false;
        } catch (Exception $e) {
            error_log('❌ Erreur dans getReclamationById: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mettre à jour une réclamation (CORRECT)
     */
    public function updateReclamation($data) {
        $conn = $this->pdo; 
        
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
                ':nom' => $data['nom'] ?? '',
                ':prenom' => $data['prenom'] ?? '',
                ':telephone' => $data['telephone'] ?? '',
                ':email' => $data['email'] ?? '',
                ':gouvernorat' => $data['gouvernorat'] ?? '',
                ':delegation' => $data['delegation'] ?? '',
                ':ville' => $data['ville'] ?? '',
                ':position_gps' => $data['position_gps'] ?? '',
                ':description_detaillee' => $data['description_detaillee'] ?? '',
                ':priorite' => $data['priorite'] ?? 'Moyenne',
                ':statut' => $data['statut'] ?? 'Nouveau',
                ':date' => $data['date'] ?? date('Y-m-d H:i:s')
            ]);
            
            error_log("📝 updateReclamation ID {$data['id']}: " . ($result ? "✅" : "❌"));
            return $result;
            
        } catch (Exception $e) {
            error_log("❌ Erreur dans updateReclamation: " . $e->getMessage());
            return false;
        }
    }
    
    // =========================================================================
    // GESTION DES RÉPONSES ADMIN ('reponadmin')
    // =========================================================================

    /**
     * Ajouter/Mettre à jour une réponse admin (CORRECT)
     * La logique pour INSERT/UPDATE est basée sur l'existence d'une ligne ayant le même 'id' (FK).
     */
    public function addAdminResponse($reclamation_id, $response_detaillee, $new_statut, $date_reponse) {
        $conn = $this->pdo; 
        
        try {
            // 1. Récupérer les informations de la réclamation (pour copier email/description)
            $sql_get = "SELECT email, description_detaillee, nom, prenom FROM reclamations WHERE id = :id";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->execute([':id' => $reclamation_id]);
            $reclamation = $stmt_get->fetch(PDO::FETCH_ASSOC);
            
            if (!$reclamation) {
                throw new Exception("Réclamation non trouvée ID: $reclamation_id");
            }
            
            $email = $reclamation['email'] ?? '';
            $description = $reclamation['description_detaillee'] ?? '';
            
            // 2. Vérifier si une réponse existe déjà pour CETTE RÉCLAMATION (via la clé étrangère 'id')
            $sql_check = "SELECT id_reponse FROM reponadmin WHERE id = :id";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->execute([':id' => $reclamation_id]);
            $existing_response = $stmt_check->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_response && isset($existing_response['id_reponse'])) {
                // Mettre à jour la réponse existante
                $sql_response = "UPDATE reponadmin SET 
                                            email = :email, 
                                            description = :description, 
                                            reponse = :reponse, 
                                            statut = :statut, 
                                            date_creation = :date_creation,
                                            date_update = NOW()
                                        WHERE id = :id"; // WHERE sur l'ID de la réclamation (clé étrangère)
                
                $params = [
                    ':email' => $email,
                    ':description' => $description,
                    ':reponse' => $response_detaillee,
                    ':statut' => $new_statut,
                    ':date_creation' => $date_reponse,
                    ':id' => $reclamation_id 
                ];
                
                $stmt_response = $conn->prepare($sql_response);
                $success_response = $stmt_response->execute($params);
                
                $id_reponse = $existing_response['id_reponse']; 
                
            } else {
                // Insérer une nouvelle réponse
                $sql_response = "INSERT INTO reponadmin (
                                            id, email, description, 
                                            reponse, statut, date_creation
                                        ) VALUES (
                                            :id, :email, :description, 
                                            :reponse, :statut, :date_creation
                                        )";
                
                $params = [
                    ':id' => $reclamation_id, // Clé étrangère vers reclamations
                    ':email' => $email,
                    ':description' => $description,
                    ':reponse' => $response_detaillee,
                    ':statut' => $new_statut,
                    ':date_creation' => $date_reponse
                ];
                
                $stmt_response = $conn->prepare($sql_response);
                $success_response = $stmt_response->execute($params);
                
                $id_reponse = $conn->lastInsertId(); // Récupère l'ID PK généré
            }
            
            if (!$success_response) {
                $error_info = $stmt_response->errorInfo();
                throw new Exception("Erreur SQL lors de l'opération reponadmin: " . ($error_info[2] ?? 'Inconnue'));
            }
            
            // 3. Mettre à jour le statut de la réclamation principale
            $sql_update = "UPDATE reclamations SET statut = :statut WHERE id = :id";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->execute([
                ':statut' => $new_statut,
                ':id' => $reclamation_id
            ]);
            
            error_log("✅ Réponse ajoutée/mise à jour! Réclamation ID: $reclamation_id, Réponse PK ID: $id_reponse");
            return $id_reponse;
            
        } catch (Exception $e) {
            error_log("❌ Erreur dans addAdminResponse: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Modifier une réponse existante (utilise la PK de la table reponadmin) (CORRECT)
     */
    public function modifierReponseReclamation($id_reponse_admin, $nouveau_contenu) {
    if (empty($nouveau_contenu)) {
        return ['success' => false, 'message' => 'Le contenu de la réponse ne peut pas être vide.'];
    }

    $conn = $this->pdo;
    
    try {
        // CORRECTION : Supprimer date_update qui n'existe pas dans la table
        $sql = "UPDATE reponadmin SET 
                    reponse = :reponse
                WHERE id_reponse = :id_reponse_admin"; 
        
        $stmt = $conn->prepare($sql);
        $result = $stmt->execute([
            ':reponse' => $nouveau_contenu,
            ':id_reponse_admin' => $id_reponse_admin
        ]);
        
        if ($result) {
            error_log("✅ Réponse $id_reponse_admin modifiée");
            return [
                'success' => true, 
                'message' => 'Réponse modifiée avec succès.'
            ];
        } else {
            return ['success' => false, 'message' => 'Erreur lors de la modification de la réponse.'];
        }
    } catch (Exception $e) {
        error_log("❌ Erreur dans modifierReponseReclamation: " . $e->getMessage());
        return ['success' => false, 'message' => 'Erreur DB: ' . $e->getMessage()];
    }
}
    /**
     * Supprimer une réponse (utilise la PK de la table reponadmin)
     */
    public function deleteResponse($id_reponse_admin) {
        $conn = $this->pdo;
        
        try {
            $sql_delete = "DELETE FROM reponadmin WHERE id_reponse = :id_reponse_admin"; 
            $stmt_delete = $conn->prepare($sql_delete);
            $success = $stmt_delete->execute([':id_reponse_admin' => $id_reponse_admin]);
            
            if ($success) {
                error_log("🗑️ Réponse $id_reponse_admin supprimée");
                return [
                    'success' => true,
                    'message' => 'Réponse supprimée avec succès'
                ];
            } else {
                return ['success' => false, 'message' => 'Erreur lors de la suppression'];
            }
            
        } catch (Exception $e) {
            error_log("❌ Erreur dans deleteResponse: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()];
        }
    }
    
    /**
     * Supprimer une réponse par ID de Réclamation (utilisé pour la suppression cascade)
     */
    private function deleteResponseByReclamationId($reclamation_id) {
        $conn = $this->pdo;
        
        try {
            // Utilise la clé étrangère 'id'
            $sql_delete = "DELETE FROM reponadmin WHERE id = :id";
            $stmt_delete = $conn->prepare($sql_delete);
            $stmt_delete->execute([':id' => $reclamation_id]);
            
        } catch (Exception $e) {
            error_log("⚠️ Erreur silencieuse dans deleteResponseByReclamationId: " . $e->getMessage());
        }
    }

    /**
     * Récupère la réponse admin d'une réclamation par son ID (FK) (CORRECT)
     */
    public function getResponseByReclamationId($reclamation_id) {
        $conn = $this->pdo;
        // Utilise la clé étrangère 'id'
        $sql = "SELECT * FROM reponadmin WHERE id = :id";
        try {
            $query = $conn->prepare($sql);
            $query->execute([':id' => $reclamation_id]);
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur DB dans getResponseByReclamationId: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère toutes les réponses administratives
     */
    public function getAllAdminResponses() {
        $conn = $this->pdo;
        $sql = "SELECT rep.*, r.nom, r.prenom, r.email as reclamation_email 
                FROM reponadmin rep
                LEFT JOIN reclamations r ON rep.id = r.id
                ORDER BY rep.date_creation DESC";
        try {
            $query = $conn->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Erreur DB dans getAllAdminResponses: " . $e->getMessage());
            return [];
        }
    }
    
    /* =========================================================================
        MÉTHODES SIMPLIFIÉES (ALIAS)
    ========================================================================= */
    
    /**
     * Alias: Ajouter une réponse
     * Utilise la FK (ID de la réclamation)
     */
    public function addResponse($reclamation_id, $reponse_text, $statut = 'En cours') {
        return $this->addAdminResponse(
            $reclamation_id, 
            $reponse_text, 
            $statut, 
            date('Y-m-d H:i:s')
        );
    }
    
    /**
     * Alias: Mettre à jour une réponse existante
     * Utilise la PK (ID de la réponse admin)
     */
    public function updateResponse($id_reponse_admin, $reponse_text) {
        return $this->modifierReponseReclamation($id_reponse_admin, $reponse_text);
    }
    
    /**
     * Ajouter une réponse simple (méthode rapide)
     */
    public function quickAddResponse($reclamation_id, $reponse_text) {
        return $this->addResponse($reclamation_id, $reponse_text, 'En cours');
    }

    // =========================================================================
    // FONCTIONNALITÉS MÉTIER ET STATISTIQUES
    // =========================================================================
    
    /**
     * Calcule le temps de traitement moyen pour les réclamations résolues (CORRECT)
     */
    public function getAverageProcessingTime() {
        $conn = $this->pdo;
        
        try {
            // Jointure sur r.id = rep.id (clé étrangère)
            $sql = "SELECT r.id, r.date as date_creation, 
                            COALESCE(MAX(rep.date_creation), r.date) as date_resolution,
                            r.statut
                    FROM reclamations r
                    LEFT JOIN reponadmin rep ON r.id = rep.id 
                    WHERE r.statut IN ('Traitée', 'Résolue', 'Clôturée', 
                                         'traitee', 'resolue', 'cloturee',
                                         'Traitee', 'Resolue', 'Cloturee')
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
    
    /**
     * Analyse des tendances par période (Fonction fournie, non modifiée)
     */
    public function getTrendsAnalysis($period = 'day') { 
        $conn = $this->pdo;
        
        try {
            $sql = "";
            
            if ($period === 'week') {
                $sql = "SELECT 
                            DATE_FORMAT(date, '%Y-%u') as period,
                            COUNT(*) as count,
                            SUM(CASE WHEN statut IN ('Résolue', 'Clôturée', 'Résolu', 'Clôturé') THEN 1 ELSE 0 END) as resolved
                        FROM reclamations
                        WHERE date >= DATE_SUB(CURRENT_DATE(), INTERVAL 12 WEEK)
                        GROUP BY DATE_FORMAT(date, '%Y-%u')
                        ORDER BY period ASC";
            } else { 
                $sql = "SELECT 
                            DATE(date) as period,
                            COUNT(*) as count,
                            SUM(CASE WHEN statut IN ('Résolue', 'Clôturée', 'Résolu', 'Clôturé') THEN 1 ELSE 0 END) as resolved
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
     * Recherche avancée avec filtres multiples (Fonction fournie, non modifiée)
     */
    public function searchReclamations($filters = []) {
        $conn = $this->pdo;
        
        try {
            $sql = "SELECT * FROM reclamations WHERE 1=1";
            $params = [];
            
            // Logique de construction de la requête
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
            
            if (!empty($filters['statut'])) {
                $sql .= " AND statut = :statut";
                $params[':statut'] = $filters['statut'];
            }
            
            if (!empty($filters['priorite'])) {
                $sql .= " AND priorite = :priorite";
                $params[':priorite'] = $filters['priorite'];
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

    /**
     * Récupère les statistiques complètes des réclamations (Fonction fournie, non modifiée)
     */
    public function getStatistics() {
        $conn = $this->pdo;
        $stats = [];
        
        try {
            // Total des réclamations
            $stmt = $conn->query("SELECT COUNT(*) as total FROM reclamations");
            $stats['total'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
            
            // Réclamations résolues
            $stmt = $conn->query("SELECT COUNT(*) as resolved FROM reclamations 
                                         WHERE statut IN ('Résolue', 'Clôturée', 'Résolu', 'Clôturé')");
            $stats['resolved'] = $stmt->fetch(PDO::FETCH_ASSOC)['resolved'] ?? 0;
            
            // Réclamations en cours
            $stats['unresolved'] = $stats['total'] - $stats['resolved'];
            
            // Taux de résolution
            if ($stats['total'] > 0) {
                $stats['resolution_rate'] = round(($stats['resolved'] / $stats['total']) * 100, 2);
            } else {
                $stats['resolution_rate'] = 0;
            }
            
            // Réclamations urgentes
            $stmt = $conn->query("SELECT COUNT(*) as urgent FROM reclamations 
                                         WHERE (LOWER(priorite) LIKE '%urgent%' OR priorite = 'Urgente')
                                         AND statut NOT IN ('Résolue', 'Clôturée', 'Résolu', 'Clôturé')");
            $stats['urgent'] = $stmt->fetch(PDO::FETCH_ASSOC)['urgent'] ?? 0;
            
            // Réclamations cette semaine
            $stmt = $conn->query("SELECT COUNT(*) as this_week FROM reclamations 
                                         WHERE YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)");
            $stats['this_week'] = $stmt->fetch(PDO::FETCH_ASSOC)['this_week'] ?? 0;
            
            // Total des réponses (séparé)
            $stmt = $conn->query("SELECT COUNT(*) as total_responses FROM reponadmin");
            $stats['total_responses'] = $stmt->fetch(PDO::FETCH_ASSOC)['total_responses'] ?? 0;
            
            error_log("📈 Statistiques: Total réclamations={$stats['total']}, Réponses={$stats['total_responses']}");
            
            return $stats;
        } catch (Exception $e) {
            error_log("❌ Erreur dans getStatistics: " . $e->getMessage());
            return [
                'total' => 0,
                'resolved' => 0,
                'unresolved' => 0,
                'resolution_rate' => 0,
                'urgent' => 0,
                'this_week' => 0,
                'total_responses' => 0
            ];
        }
    }
    
    /**
     * Récupère les réclamations nécessitant une attention urgente (Fonction fournie, non modifiée)
     */
    public function getUrgentReclamations() {
        $conn = $this->pdo;
        
        try {
            $sql = "SELECT * FROM reclamations
                    WHERE (LOWER(priorite) LIKE '%urgent%' OR priorite = 'Urgente')
                    AND statut NOT IN ('Résolue', 'Clôturée', 'Résolu', 'Clôturé')
                    ORDER BY date DESC
                    LIMIT 20";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("❌ ERREUR dans getUrgentReclamations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Mettre à jour le statut d'une réclamation (Fonction fournie, non modifiée)
     */
    public function updateReclamationStatus($reclamation_id, $new_status) {
        $conn = $this->pdo;
        
        try {
            $sql = "UPDATE reclamations SET statut = :statut WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $success = $stmt->execute([
                ':statut' => $new_status,
                ':id' => $reclamation_id
            ]);
            
            error_log("📝 Statut réclamation $reclamation_id mis à jour: $new_status");
            return $success;
        } catch (Exception $e) {
            error_log("❌ Erreur dans updateReclamationStatus: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupérer le nombre de réclamations par mois (Fonction fournie, non modifiée)
     */
    public function getReclamationsByMonth($year = null) {
        $conn = $this->pdo;
        
        if (!$year) {
            $year = date('Y');
        }
        
        try {
            $sql = "SELECT 
                        DATE_FORMAT(date, '%Y-%m') as month,
                        COUNT(*) as count
                    FROM reclamations
                    WHERE YEAR(date) = :year
                    GROUP BY DATE_FORMAT(date, '%Y-%m')
                    ORDER BY month ASC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute([':year' => $year]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("❌ Erreur dans getReclamationsByMonth: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les dernières réclamations (Fonction fournie, non modifiée)
     */
    public function getLatestReclamations($limit = 10) {
        $conn = $this->pdo;
        
        try {
            $sql = "SELECT * FROM reclamations
                    ORDER BY date DESC
                    LIMIT :limit";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("❌ Erreur dans getLatestReclamations: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Récupérer les réclamations par gouvernorat (Fonction fournie, non modifiée)
     */
    public function getReclamationsByGovernorate() {
        $conn = $this->pdo;
        
        try {
            $sql = "SELECT 
                        gouvernorat,
                        COUNT(*) as count,
                        ROUND((COUNT(CASE WHEN statut IN ('Résolue', 'Clôturée', 'Résolu', 'Clôturé') THEN 1 END) / COUNT(*)) * 100, 2) as resolution_rate
                    FROM reclamations
                    WHERE gouvernorat IS NOT NULL AND gouvernorat != ''
                    GROUP BY gouvernorat
                    ORDER BY count DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("❌ Erreur dans getReclamationsByGovernorate: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Tester la connexion et la structure (Fonction fournie, non modifiée)
     */
    public function testConnection() {
        $conn = $this->pdo;
        
        try {
            $tests = [];
            
            // Test 1: Connexion
            $tests['connexion'] = $conn ? true : false;
            
            // Test 2: Table reclamations
            $stmt = $conn->query("SHOW TABLES LIKE 'reclamations'");
            $tests['table_reclamations'] = $stmt->fetch() ? true : false;
            
            // Test 3: Table reponadmin
            $stmt = $conn->query("SHOW TABLES LIKE 'reponadmin'");
            $tests['table_reponadmin'] = $stmt->fetch() ? true : false;
            
            // Test 4: Structure reclamations
            if ($tests['table_reclamations']) {
                $stmt = $conn->query("DESCRIBE reclamations");
                $tests['structure_reclamations'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Test 5: Structure reponadmin
            if ($tests['table_reponadmin']) {
                $stmt = $conn->query("DESCRIBE reponadmin");
                $tests['structure_reponadmin'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            return $tests;
            
        } catch (Exception $e) {
            error_log("❌ Test connection failed: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}