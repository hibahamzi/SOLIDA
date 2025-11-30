<?php
session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/EvenementModel.php';

class evenementController {

    /* ================================
       GET ALL EVENTS
    ================================= */
    public function getAllEvenements() {
        global $pdo;
        $sql = "SELECT * FROM evenements ORDER BY date_evenement DESC, created_at DESC";
        try {
            $query = $pdo->prepare($sql);
            $query->execute();
            return $query->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            die("Erreur : " . $e->getMessage());
        }
    }

    /* ================================
       GET ONE EVENT BY ID
    ================================= */
    public function getEvenementById($id) {
        global $pdo;
        $sql = "SELECT * FROM evenements WHERE id_evenement = :id";
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
       CREATE EVENT
    ================================= */
    public function createEvenement($eventData) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                INSERT INTO evenements (titre_evenement, date_evenement, description, organisateur, frais_participation) 
                VALUES (?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $eventData['titre_evenement'],
                $eventData['date_evenement'],
                $eventData['description'],
                $eventData['organisateur'],
                $eventData['frais_participation'] ?? 1
            ]);
            
            if ($result) {
                return [
                    'success' => true,
                    'message' => 'Événement créé avec succès!'
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Erreur lors de la création de l\'événement.'
                ];
            }
            
        } catch (PDOException $e) {
            error_log("Error creating event: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Erreur de base de données: ' . $e->getMessage()
            ];
        }
    }

    /* ================================
       UPDATE EVENT
    ================================= */
    public function updateEvenement($eventId, $data) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                UPDATE evenements 
                SET titre_evenement = ?, date_evenement = ?, description = ?, organisateur = ?, frais_participation = ?
                WHERE id_evenement = ?
            ");
            
            return $stmt->execute([
                $data['titre_evenement'],
                $data['date_evenement'],
                $data['description'],
                $data['organisateur'],
                $data['frais_participation'] ?? 1,
                $eventId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur lors de la mise à jour de l'événement: " . $e->getMessage());
            return false;
        }
    }

    /* ================================
       DELETE EVENT
    ================================= */
    public function deleteEvenement($eventId) {
        global $pdo;
        $sql = "DELETE FROM evenements WHERE id_evenement = :id";
        try {
            $query = $pdo->prepare($sql);
            $query->execute(["id" => $eventId]);
            return true;
        } catch (Exception $e) {
            error_log("Erreur lors de la suppression: " . $e->getMessage());
            return false;
        }
    }

    /* ================================
       VALIDATION FUNCTIONS
    ================================= */
    private function validateTitre($titre) {
        $titre = trim($titre);
        
        if (empty($titre)) {
            return "Le titre de l'événement est obligatoire.";
        }
        
        // Only letters and spaces allowed (French characters included)
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $titre)) {
            return "Le titre ne peut contenir que des lettres (pas de chiffres ni de symboles).";
        }
        
        if (strlen($titre) < 5) {
            return "Le titre doit contenir au moins 5 caractères.";
        }
        
        if (strlen($titre) > 150) {
            return "Le titre ne peut pas dépasser 150 caractères.";
        }
        
        return true;
    }

    private function validateDate($date) {
        if (empty($date)) {
            return "La date de l'événement est obligatoire.";
        }
        
        $eventDate = new DateTime($date);
        $today = new DateTime();
        $today->setTime(0, 0, 0);
        
        if ($eventDate < $today) {
            return "La date de l'événement ne peut pas être dans le passé.";
        }
        
        return true;
    }

    private function validateDescription($description) {
        $description = trim($description);
        
        if (empty($description)) {
            return "La description est obligatoire.";
        }
        
        $words = str_word_count($description);
        
        if ($words < 10) {
            return "La description doit contenir au moins 10 mots.";
        }
        
        return true;
    }

    private function validateOrganisateur($organisateur) {
        $organisateur = trim($organisateur);
        
        if (empty($organisateur)) {
            return "L'organisateur est obligatoire.";
        }
        
        // Only letters and spaces allowed (French characters included)
        if (!preg_match('/^[a-zA-ZÀ-ÿ\s]+$/', $organisateur)) {
            return "L'organisateur ne peut contenir que des lettres (pas de chiffres ni de symboles).";
        }
        
        if (strlen($organisateur) < 3) {
            return "Le nom de l'organisateur doit contenir au moins 3 caractères.";
        }
        
        if (strlen($organisateur) > 100) {
            return "Le nom de l'organisateur ne peut pas dépasser 100 caractères.";
        }
        
        return true;
    }

    private function validateFrais($frais) {
        if (empty($frais) || $frais === '') {
            return "Les frais de participation sont obligatoires (minimum 1 DT).";
        }
        
        if (!is_numeric($frais)) {
            return "Les frais de participation doivent être un nombre.";
        }
        
        $frais = floatval($frais);
        
        if ($frais < 1) {
            return "Les frais de participation doivent être d'au moins 1 DT.";
        }
        
        return true;
    }

    /* ================================
       EVENTS BACK OFFICE HANDLERS
    ================================= */
    public function handleEvenementsBack() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ../views/front_office/sign-in.php');
            exit();
        }
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        // CREATE EVENT
        if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $titre = trim($_POST['titre_evenement'] ?? '');
            $date = trim($_POST['date_evenement'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $organisateur = trim($_POST['organisateur'] ?? '');
            $frais = !empty($_POST['frais_participation']) ? floatval($_POST['frais_participation']) : 1;
            
            $errors = [];
            
            $titreResult = $this->validateTitre($titre);
            if ($titreResult !== true) $errors['titre_evenement'] = $titreResult;
            
            $dateResult = $this->validateDate($date);
            if ($dateResult !== true) $errors['date_evenement'] = $dateResult;
            
            $descriptionResult = $this->validateDescription($description);
            if ($descriptionResult !== true) $errors['description'] = $descriptionResult;
            
            $organisateurResult = $this->validateOrganisateur($organisateur);
            if ($organisateurResult !== true) $errors['organisateur'] = $organisateurResult;
            
            $fraisResult = $this->validateFrais($frais);
            if ($fraisResult !== true) $errors['frais_participation'] = $fraisResult;
            
            if (!empty($errors)) {
                $_SESSION['evenements_errors'] = $errors;
                $_SESSION['evenements_message'] = "Veuillez corriger les erreurs dans le formulaire.";
                header('Location: ../views/back_office/evenementback.php?modal=add');
                exit();
            }
            
            $data = [
                'titre_evenement' => $titre,
                'date_evenement' => $date,
                'description' => $description,
                'organisateur' => $organisateur,
                'frais_participation' => $frais
            ];
            
            $result = $this->createEvenement($data);
            
            if ($result['success']) {
                $_SESSION['evenements_success'] = $result['message'];
                header('Location: ../views/back_office/evenementback.php');
                exit();
            } else {
                $_SESSION['evenements_message'] = $result['message'];
                header('Location: ../views/back_office/evenementback.php?modal=add');
                exit();
            }
        }
        
        // UPDATE EVENT
        if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $eventId = intval($_POST['id_evenement'] ?? 0);
            $titre = trim($_POST['titre_evenement'] ?? '');
            $date = trim($_POST['date_evenement'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $organisateur = trim($_POST['organisateur'] ?? '');
            $frais = !empty($_POST['frais_participation']) ? floatval($_POST['frais_participation']) : 1;
            
            $errors = [];
            
            $titreResult = $this->validateTitre($titre);
            if ($titreResult !== true) $errors['titre_evenement'] = $titreResult;
            
            $dateResult = $this->validateDate($date);
            if ($dateResult !== true) $errors['date_evenement'] = $dateResult;
            
            $descriptionResult = $this->validateDescription($description);
            if ($descriptionResult !== true) $errors['description'] = $descriptionResult;
            
            $organisateurResult = $this->validateOrganisateur($organisateur);
            if ($organisateurResult !== true) $errors['organisateur'] = $organisateurResult;
            
            $fraisResult = $this->validateFrais($frais);
            if ($fraisResult !== true) $errors['frais_participation'] = $fraisResult;
            
            // Check if this is an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            if (!empty($errors)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Veuillez corriger les erreurs dans le formulaire.', 'errors' => $errors]);
                    exit();
                }
                $_SESSION['evenements_errors'] = $errors;
                $_SESSION['evenements_message'] = "Veuillez corriger les erreurs dans le formulaire.";
                header('Location: ../views/back_office/evenementback.php?modal=edit&id=' . $eventId);
                exit();
            }
            
            $data = [
                'titre_evenement' => $titre,
                'date_evenement' => $date,
                'description' => $description,
                'organisateur' => $organisateur,
                'frais_participation' => $frais
            ];
            
            if ($this->updateEvenement($eventId, $data)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Événement mis à jour avec succès!']);
                    exit();
                }
                $_SESSION['evenements_success'] = "Événement mis à jour avec succès!";
                header('Location: ../views/back_office/evenementback.php');
                exit();
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => "Erreur lors de la mise à jour de l'événement."]);
                    exit();
                }
                $_SESSION['evenements_message'] = "Erreur lors de la mise à jour de l'événement.";
                header('Location: ../views/back_office/evenementback.php?modal=edit&id=' . $eventId);
                exit();
            }
        }
        
        // DELETE EVENT
        if ($action === 'delete') {
            $eventId = intval($_GET['id'] ?? $_POST['id'] ?? 0);
            
            // Check if this is an AJAX request
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
            
            if ($this->deleteEvenement($eventId)) {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Événement supprimé avec succès!']);
                    exit();
                }
                $_SESSION['evenements_success'] = "Événement supprimé avec succès!";
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => "Erreur lors de la suppression de l'événement."]);
                    exit();
                }
                $_SESSION['evenements_message'] = "Erreur lors de la suppression de l'événement.";
            }
            
            if (!$isAjax) {
                header('Location: ../views/back_office/evenementback.php');
                exit();
            }
        }
        
        header('Location: ../views/back_office/evenementback.php');
        exit();
    }
}

// ================================
// ROUTING LOGIC
// ================================

$controller = new evenementController();

// Handle Events Back Office actions (create, update, delete)
$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'create' || $action === 'update' || $action === 'delete') {
    $controller->handleEvenementsBack();
}

?>

