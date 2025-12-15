<?php
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models/ParticipationModel.php';

class participationController {

    /* ================================
       GET ALL PARTICIPATIONS BY USER
    ================================= */
    public function getParticipationsByUser($userId) {
        global $pdo;
        $sql = "SELECT p.*, e.titre_evenement, e.date_evenement, e.organisateur, e.frais_participation 
                FROM participations p 
                INNER JOIN evenements e ON p.id_evenement = e.id_evenement 
                WHERE p.id_user = :user_id 
                ORDER BY p.date_inscription DESC";
        try {
            $query = $pdo->prepare($sql);
            $query->bindValue(":user_id", $userId, PDO::PARAM_INT);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    /* ================================
       GET ALL PARTICIPATIONS (FOR ADMIN)
    ================================= */
    public function getAllParticipations() {
        global $pdo;
        $sql = "SELECT p.*, e.titre_evenement, e.date_evenement, e.organisateur, e.frais_participation,
                u.fullname, u.email
                FROM participations p 
                INNER JOIN evenements e ON p.id_evenement = e.id_evenement 
                INNER JOIN users u ON p.id_user = u.id
                ORDER BY p.date_inscription DESC";
        try {
            $query = $pdo->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    /* ================================
       GET ONE PARTICIPATION BY ID
    ================================= */
    public function getParticipationById($id) {
        global $pdo;
        $sql = "SELECT p.*, e.titre_evenement, e.date_evenement, e.organisateur, e.frais_participation 
                FROM participations p 
                INNER JOIN evenements e ON p.id_evenement = e.id_evenement 
                WHERE p.id_participation = :id";
        try {
            $query = $pdo->prepare($sql);
            $query->bindValue(":id", $id, PDO::PARAM_INT);
            $query->execute();
            return $query->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Erreur lors de la récupération : " . $e->getMessage());
        }
    }

    /* ================================
       CHECK IF USER ALREADY PARTICIPATED
    ================================= */
    public function userAlreadyParticipated($userId, $eventId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM participations WHERE id_user = ? AND id_evenement = ?");
            $stmt->execute([$userId, $eventId]);
            return $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("Erreur lors de la vérification: " . $e->getMessage());
            return false;
        }
    }

    /* ================================
       CREATE PARTICIPATION
    ================================= */
    public function createParticipation($participationData) {
        global $pdo;
        try {
            // Check if user already participated
            if ($this->userAlreadyParticipated($participationData['id_user'], $participationData['id_evenement'])) {
                return [
                    'success' => false,
                    'message' => 'Vous avez déjà participé à cet événement.'
                ];
            }
            
            $stmt = $pdo->prepare("
                INSERT INTO participations (id_evenement, id_user, motivation, source_information, type_participation, nombre_personnes, desir_dejeuner) 
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $participationData['id_evenement'],
                $participationData['id_user'],
                $participationData['motivation'],
                $participationData['source_information'],
                $participationData['type_participation'],
                $participationData['nombre_personnes'] ?? 1,
                $participationData['desir_dejeuner'] ?? 'non'
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Participation enregistrée avec succès!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de l\'enregistrement de la participation.'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error creating participation: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de base de données: ' . $e->getMessage()
            ];
        }
    }

    /* ================================
       UPDATE PARTICIPATION
    ================================= */
    public function updateParticipation($participationId, $data) {
        global $pdo;
        try {
            // Check if admin (no user_id check) or regular user
            $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
            $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            $isBackOfficeUpdate = $isAjaxRequest; // Simplified: if AJAX, it's from back office
            
            if ($isAdmin) {
                // Admin can update any participation with all fields (including type_participation, nombre_personnes, desir_dejeuner)
                $stmt = $pdo->prepare("
                    UPDATE participations 
                    SET motivation = ?, source_information = ?, type_participation = ?, nombre_personnes = ?, desir_dejeuner = ?
                    WHERE id_participation = ?
                ");
                
                $result = $stmt->execute([
                    $data['motivation'],
                    $data['source_information'],
                    $data['type_participation'],
                    $data['nombre_personnes'] ?? 1,
                    $data['desir_dejeuner'] ?? 'non',
                    $participationId
                ]);
                
                // Log for debugging
                if (!$result) {
                    error_log("Update failed for participation: " . $participationId);
                    error_log("PDO Error: " . print_r($stmt->errorInfo(), true));
                }
                
                return $result;
            } else {
                // Regular user can only update their own
                $stmt = $pdo->prepare("
                    UPDATE participations 
                    SET motivation = ?, source_information = ?, type_participation = ?, nombre_personnes = ?, desir_dejeuner = ?
                    WHERE id_participation = ? AND id_user = ?
                ");
                
                return $stmt->execute([
                    $data['motivation'],
                    $data['source_information'],
                    $data['type_participation'],
                    $data['nombre_personnes'] ?? 1,
                    $data['desir_dejeuner'] ?? 'non',
                    $participationId,
                    $data['id_user']
                ]);
            }
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de la participation: " . $e->getMessage());
            return false;
        }
    }

    /* ================================
       DELETE PARTICIPATION
    ================================= */
    public function deleteParticipation($participationId, $userId = null) {
        global $pdo;
        
        // Check if admin (no user_id check) or regular user
        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
        
        if ($isAdmin) {
            // Admin can delete any participation
            $sql = "DELETE FROM participations WHERE id_participation = :id";
            try {
                $query = $pdo->prepare($sql);
                $query->execute(["id" => $participationId]);
                // Check if any row was actually deleted
                return $query->rowCount() > 0;
            } catch (Exception $e) {
                error_log("Erreur lors de la suppression: " . $e->getMessage());
                return false;
            }
        } else {
            // Regular user can only delete their own
            $sql = "DELETE FROM participations WHERE id_participation = :id AND id_user = :user_id";
            try {
                $query = $pdo->prepare($sql);
                $query->execute([
                    "id" => $participationId,
                    "user_id" => $userId
                ]);
                // Check if any row was actually deleted
                return $query->rowCount() > 0;
            } catch (Exception $e) {
                error_log("Erreur lors de la suppression: " . $e->getMessage());
                return false;
            }
        }
    }

    /* ================================
       VALIDATION FUNCTIONS
    ================================= */
    private function validateMotivation($motivation) {
        $motivation = trim($motivation);
        
        if (empty($motivation)) {
            return "La motivation est obligatoire.";
        }
        
        $words = str_word_count($motivation);
        
        if ($words < 10) {
            return "La motivation doit contenir au moins 10 mots.";
        }
        
        return true;
    }

    private function validateSourceInformation($source) {
        $source = trim($source);
        
        if (empty($source)) {
            return "La source d'information est obligatoire.";
        }
        
        if (strlen($source) < 3) {
            return "La source d'information doit contenir au moins 3 caractères.";
        }
        
        if (strlen($source) > 100) {
            return "La source d'information ne peut pas dépasser 100 caractères.";
        }
        
        return true;
    }

    private function validateNombrePersonnes($nombre, $typeParticipation) {
        if ($typeParticipation === 'groupe') {
            if (empty($nombre) || !is_numeric($nombre)) {
                return "Le nombre de personnes est obligatoire pour une participation en groupe.";
            }
            
            $nombre = intval($nombre);
            
            if ($nombre < 2) {
                return "Pour une participation en groupe, le nombre de personnes doit être d'au moins 2.";
            }
            
            if ($nombre > 50) {
                return "Le nombre de personnes ne peut pas dépasser 50.";
            }
        }
        
        return true;
    }

    /* ================================
       PARTICIPATION HANDLERS
    ================================= */
    public function handleParticipation() {
        // Check if this is an AJAX request (likely from admin back office)
        $isAjaxRequest = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        if (!isset($_SESSION['user_id'])) {
            // For AJAX requests, return JSON error instead of redirect
            if ($isAjaxRequest) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'message' => 'Non autorisé. Veuillez vous connecter.'
                ], JSON_UNESCAPED_UNICODE);
                exit();
            }
            header('Location: ../views/front_office/sign-in.php');
            exit();
        }
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        $userId = $_SESSION['user_id'];
        
        // Check if admin
        $isAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
        
        // CREATE PARTICIPATION
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id_evenement = intval($_POST['id_evenement'] ?? 0);
            $motivation = trim($_POST['motivation'] ?? '');
            $source_information = trim($_POST['source_information'] ?? '');
            $type_participation = $_POST['type_participation'] ?? 'seul';
            $nombre_personnes = !empty($_POST['nombre_personnes']) ? intval($_POST['nombre_personnes']) : 1;
            $desir_dejeuner = $_POST['desir_dejeuner'] ?? 'non';
            
            $errors = [];
            
            $motivationResult = $this->validateMotivation($motivation);
            if ($motivationResult !== true) $errors['motivation'] = $motivationResult;
            
            $sourceResult = $this->validateSourceInformation($source_information);
            if ($sourceResult !== true) $errors['source_information'] = $sourceResult;
            
            $nombreResult = $this->validateNombrePersonnes($nombre_personnes, $type_participation);
            if ($nombreResult !== true) $errors['nombre_personnes'] = $nombreResult;
            
            if (!empty($errors)) {
                $_SESSION['participation_errors'] = $errors;
                $_SESSION['participation_message'] = "Veuillez corriger les erreurs dans le formulaire.";
                header('Location: ../views/front_office/evenement.php?modal=participer&event_id=' . $id_evenement);
                exit();
            }
            
            $data = [
                'id_evenement' => $id_evenement,
                'id_user' => $userId,
                'motivation' => $motivation,
                'source_information' => $source_information,
                'type_participation' => $type_participation,
                'nombre_personnes' => $nombre_personnes,
                'desir_dejeuner' => $desir_dejeuner
            ];
            
            $result = $this->createParticipation($data);
            
            if ($result['success']) {
                $_SESSION['participation_success'] = $result['message'];
                header('Location: ../views/front_office/participations-history.php');
                exit();
            } else {
                $_SESSION['participation_message'] = $result['message'];
                header('Location: ../views/front_office/evenement.php?modal=participer&event_id=' . $id_evenement);
                exit();
            }
        }
        
        // UPDATE PARTICIPATION
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $participationId = intval($_POST['id_participation'] ?? 0);
            $motivation = trim($_POST['motivation'] ?? '');
            $source_information = trim($_POST['source_information'] ?? '');
            $type_participation = $_POST['type_participation'] ?? 'seul';
            $nombre_personnes = !empty($_POST['nombre_personnes']) ? intval($_POST['nombre_personnes']) : 1;
            $desir_dejeuner = $_POST['desir_dejeuner'] ?? 'non';
            
            $errors = [];
            
            $motivationResult = $this->validateMotivation($motivation);
            if ($motivationResult !== true) $errors['motivation'] = $motivationResult;
            
            $sourceResult = $this->validateSourceInformation($source_information);
            if ($sourceResult !== true) $errors['source_information'] = $sourceResult;
            
            // Check if this is an AJAX request (like evenementControllers.php)
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            // Validate nombre_personnes based on type_participation
            $nombreResult = $this->validateNombrePersonnes($nombre_personnes, $type_participation);
            if ($nombreResult !== true) $errors['nombre_personnes'] = $nombreResult;
            
            if (!empty($errors)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Veuillez corriger les erreurs dans le formulaire.', 'errors' => $errors]);
                    exit();
                }
                $_SESSION['participation_errors'] = $errors;
                $_SESSION['participation_message'] = "Veuillez corriger les erreurs dans le formulaire.";
                header('Location: ../views/front_office/participations-history.php?modal=edit&id=' . $participationId);
                exit();
            }
            
            $data = [
                'motivation' => $motivation,
                'source_information' => $source_information,
                'type_participation' => $type_participation,
                'nombre_personnes' => $nombre_personnes,
                'desir_dejeuner' => $desir_dejeuner
            ];
            
            // Add user_id only for non-admin users (regular users can only update their own)
            $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
            if (!$isAdmin) {
                $data['id_user'] = $userId;
            }
            
            if ($this->updateParticipation($participationId, $data)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Participation mise à jour avec succès!']);
                    exit();
                }
                $_SESSION['participation_success'] = "Participation mise à jour avec succès!";
                header('Location: ../views/front_office/participations-history.php');
                exit();
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour de la participation.']);
                    exit();
                }
                $_SESSION['participation_message'] = "Erreur lors de la mise à jour de la participation.";
                header('Location: ../views/front_office/participations-history.php?modal=edit&id=' . $participationId);
                exit();
            }
        }
        
        // DELETE PARTICIPATION
        if ($action === 'delete') {
            $participationId = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            
            // Check if this is an AJAX request (like evenementControllers.php)
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            $isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
            
            if ($this->deleteParticipation($participationId, $isAdmin ? null : $userId)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Participation supprimée avec succès!']);
                    exit();
                }
                $_SESSION['participation_success'] = "Participation supprimée avec succès!";
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => "Erreur lors de la suppression de la participation."]);
                    exit();
                }
                $_SESSION['participation_message'] = "Erreur lors de la suppression de la participation.";
            }
            
            // Redirect based on user role (only for non-AJAX requests)
            if (!$isAjax) {
                if ($isAdmin) {
                    header('Location: ../views/back_office/evenementback.php');
                } else {
                    header('Location: ../views/front_office/participations-history.php');
                }
                exit();
            }
        }
        
        header('Location: ../views/front_office/evenement.php');
        exit();
    }
}

// ================================
// ROUTING LOGIC
// ================================

$controller = new participationController();

// Handle Participation actions (create, update, delete)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create' || $action === 'update' || $action === 'delete') {
    $controller->handleParticipation();
}

?>

