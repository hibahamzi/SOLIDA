<?php
require_once __DIR__ . '/../config/config.php';
session_start();
require_once __DIR__ . '/../models/EvenementModel.php';

// Include PHPMailer
require_once __DIR__ . '/../views/front_office/assets/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

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
                INSERT INTO evenements (titre_evenement, date_evenement, description, organisateur, adresse, latitude, longitude, max_participants, frais_participation) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $result = $stmt->execute([
                $eventData['titre_evenement'],
                $eventData['date_evenement'],
                $eventData['description'],
                $eventData['organisateur'],
                $eventData['adresse'],
                $eventData['latitude'] ?? null,
                $eventData['longitude'] ?? null,
                $eventData['max_participants'] ?? 50,
                $eventData['frais_participation'] ?? 1
            ]);
            
            if ($result) {
                // Get the newly created event ID
                $newEventId = $pdo->lastInsertId();
                
                // Send email notifications to users
                $this->sendNewEventEmailToUsers($eventData, $newEventId);
                
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
                SET titre_evenement = ?, date_evenement = ?, description = ?, organisateur = ?, adresse = ?, latitude = ?, longitude = ?, max_participants = ?, frais_participation = ?
                WHERE id_evenement = ?
            ");
            
            return $stmt->execute([
                $data['titre_evenement'],
                $data['date_evenement'],
                $data['description'],
                $data['organisateur'],
                $data['adresse'],
                $data['latitude'] ?? null,
                $data['longitude'] ?? null,
                $data['max_participants'] ?? 50,
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

    private function validateAdresse($adresse) {
        // Address is optional - if empty, that's fine
        if (empty($adresse) || trim($adresse) === '') {
            return true;
        }
        
        if (strlen($adresse) < 5) {
            return "L'adresse doit contenir au moins 5 caractères.";
        }
        
        if (strlen($adresse) > 255) {
            return "L'adresse ne peut pas dépasser 255 caractères.";
        }
        
        return true;
    }

    private function validateMaxParticipants($max_participants) {
        if (empty($max_participants) || $max_participants === '') {
            return "Le nombre maximum de participants est obligatoire.";
        }
        
        if (!is_numeric($max_participants)) {
            return "Le nombre maximum de participants doit être un nombre entier.";
        }
        
        $max_participants = intval($max_participants);
        
        if ($max_participants < 1) {
            return "Le nombre maximum de participants doit être d'au moins 1.";
        }
        
        if ($max_participants > 1000) {
            return "Le nombre maximum de participants ne peut pas dépasser 1000.";
        }
        
        return true;
    }

    /* ================================
       PARTICIPANT COUNT UTILITIES
    ================================= */
    public function getCurrentParticipantCount($eventId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT SUM(nombre_personnes) as total_participants 
                FROM participations 
                WHERE id_evenement = ?
            ");
            $stmt->execute([$eventId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['total_participants'] ?? 0;
        } catch (PDOException $e) {
            error_log("Error counting participants: " . $e->getMessage());
            return 0;
        }
    }

    public function isEventFull($eventId) {
        global $pdo;
        try {
            $stmt = $pdo->prepare("
                SELECT max_participants,
                       (SELECT SUM(nombre_personnes) FROM participations WHERE id_evenement = ?) as current_participants
                FROM evenements 
                WHERE id_evenement = ?
            ");
            $stmt->execute([$eventId, $eventId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) return true; // Event not found, consider as full
            
            $maxParticipants = $result['max_participants'];
            $currentParticipants = $result['current_participants'] ?? 0;
            
            return $currentParticipants >= $maxParticipants;
        } catch (PDOException $e) {
            error_log("Error checking event availability: " . $e->getMessage());
            return true; // On error, consider as full for safety
        }
    }

    public function canParticipate($eventId, $userId, $requestedParticipants) {
        global $pdo;
        try {
            // Check if user already participates
            $stmt = $pdo->prepare("SELECT id_participation FROM participations WHERE id_evenement = ? AND id_user = ?");
            $stmt->execute([$eventId, $userId]);
            if ($stmt->fetch()) {
                return ['can_participate' => false, 'reason' => 'already_participating'];
            }
            
            // Check availability
            $stmt = $pdo->prepare("
                SELECT max_participants,
                       (SELECT COALESCE(SUM(nombre_personnes), 0) FROM participations WHERE id_evenement = ?) as current_participants
                FROM evenements 
                WHERE id_evenement = ?
            ");
            $stmt->execute([$eventId, $eventId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$result) {
                return ['can_participate' => false, 'reason' => 'event_not_found'];
            }
            
            $maxParticipants = $result['max_participants'];
            $currentParticipants = $result['current_participants'] ?? 0;
            $availableSpots = $maxParticipants - $currentParticipants;
            
            if ($requestedParticipants > $availableSpots) {
                return [
                    'can_participate' => false, 
                    'reason' => 'insufficient_spots',
                    'available_spots' => $availableSpots,
                    'requested' => $requestedParticipants
                ];
            }
            
            return ['can_participate' => true, 'available_spots' => $availableSpots];
            
        } catch (PDOException $e) {
            error_log("Error checking participation eligibility: " . $e->getMessage());
            return ['can_participate' => false, 'reason' => 'error'];
        }
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
            $adresse = trim($_POST['adresse'] ?? '');
            $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
            $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
            $max_participants = !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : 50;
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
            
            $adresseResult = $this->validateAdresse($adresse);
            if ($adresseResult !== true) $errors['adresse'] = $adresseResult;
            
            $maxParticipantsResult = $this->validateMaxParticipants($max_participants);
            if ($maxParticipantsResult !== true) $errors['max_participants'] = $maxParticipantsResult;
            
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
                'adresse' => $adresse,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'max_participants' => $max_participants,
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
            $adresse = trim($_POST['adresse'] ?? '');
            $latitude = !empty($_POST['latitude']) ? floatval($_POST['latitude']) : null;
            $longitude = !empty($_POST['longitude']) ? floatval($_POST['longitude']) : null;
            $max_participants = !empty($_POST['max_participants']) ? intval($_POST['max_participants']) : 50;
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
            
            $adresseResult = $this->validateAdresse($adresse);
            if ($adresseResult !== true) $errors['adresse'] = $adresseResult;
            
            $maxParticipantsResult = $this->validateMaxParticipants($max_participants);
            if ($maxParticipantsResult !== true) $errors['max_participants'] = $maxParticipantsResult;
            
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
                'adresse' => $adresse,
                'latitude' => $latitude,
                'longitude' => $longitude,
                'max_participants' => $max_participants,
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

    /* ================================
       GET EVENT DETAILS WITH PARTICIPANTS
    ================================= */
    public function getEventDetails($eventId) {
        global $pdo;
        try {
            // Get event details
            $stmt = $pdo->prepare("SELECT * FROM evenements WHERE id_evenement = ?");
            $stmt->execute([$eventId]);
            $event = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$event) {
                return ['success' => false, 'message' => 'Événement non trouvé'];
            }
            
            // Get participants
            $stmt = $pdo->prepare("
                SELECT p.*, u.fullname, u.email 
                FROM participations p 
                JOIN users u ON p.id_user = u.id 
                WHERE p.id_evenement = ? 
                ORDER BY p.date_inscription ASC
            ");
            $stmt->execute([$eventId]);
            $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate statistics
            $totalParticipants = array_sum(array_column($participants, 'nombre_personnes'));
            $availableSpots = $event['max_participants'] - $totalParticipants;
            
            // Generate HTML
            $html = $this->generateEventDetailsHTML($event, $participants, $totalParticipants, $availableSpots);
            
            return ['success' => true, 'html' => $html];
            
        } catch (PDOException $e) {
            error_log("Error getting event details: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de base de données'];
        }
    }
    
    private function generateEventDetailsHTML($event, $participants, $totalParticipants, $availableSpots) {
        ob_start();
        ?>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
            <!-- Informations Générales -->
            <div class="table-container">
                <div class="table-header" style="background: #59ab6e; color: white; padding: 15px;">
                    <h3 style="margin: 0; font-size: 16px;">
                        <i class="fas fa-info-circle" style="margin-right: 8px;"></i>
                        Informations Générales
                    </h3>
                </div>
                <div style="padding: 20px;">
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #212934;">Titre:</strong>
                        <span style="color: #bcbcbc; margin-left: 10px;"><?php echo htmlspecialchars($event['titre_evenement']); ?></span>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #212934;">Date:</strong>
                        <span style="color: #bcbcbc; margin-left: 10px;">
                            <i class="far fa-calendar" style="margin-right: 5px;"></i>
                            <?php echo date('d/m/Y', strtotime($event['date_evenement'])); ?>
                        </span>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #212934;">Organisateur:</strong>
                        <span style="color: #bcbcbc; margin-left: 10px;">
                            <i class="fas fa-user" style="margin-right: 5px; color: #59ab6e;"></i>
                            <?php echo htmlspecialchars($event['organisateur']); ?>
                        </span>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #212934;">Frais:</strong>
                        <span style="color: #bcbcbc; margin-left: 10px;">
                            <i class="fas fa-money-bill" style="margin-right: 5px; color: #59ab6e;"></i>
                            <?php echo $event['frais_participation'] > 0 ? number_format($event['frais_participation'], 2) . ' DT' : 'Gratuit'; ?>
                        </span>
                    </div>
                    <?php if (!empty($event['adresse'])): ?>
                    <div style="margin-bottom: 15px;">
                        <strong style="color: #212934;">Lieu:</strong>
                        <span style="color: #bcbcbc; margin-left: 10px;">
                            <i class="fas fa-map-marker-alt" style="margin-right: 5px; color: #59ab6e;"></i>
                            <?php echo htmlspecialchars($event['adresse']); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                    <div style="margin-top: 20px;">
                        <strong style="color: #212934;">Description:</strong>
                        <div style="background-color: #e9eef5; padding: 15px; border-radius: 8px; margin-top: 8px; line-height: 1.6; color: #212934;">
                            <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistiques de Participation -->
            <div class="table-container">
                <div class="table-header" style="background: #59ab6e; color: white; padding: 15px;">
                    <h3 style="margin: 0; font-size: 16px;">
                        <i class="fas fa-users" style="margin-right: 8px;"></i>
                        Statistiques de Participation
                    </h3>
                </div>
                <div style="padding: 20px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 15px; text-align: center; margin-bottom: 25px;">
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #59ab6e;"><?php echo $totalParticipants; ?></div>
                            <small style="color: #bcbcbc; font-size: 12px;">Participants Actuels</small>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: #212934;"><?php echo $event['max_participants']; ?></div>
                            <small style="color: #bcbcbc; font-size: 12px;">Capacité Max</small>
                        </div>
                        <div>
                            <div style="font-size: 32px; font-weight: bold; color: <?php echo $availableSpots > 0 ? '#ede861' : '#e74c3c'; ?>;">
                                <?php echo $availableSpots; ?>
                            </div>
                            <small style="color: #bcbcbc; font-size: 12px;">Places Restantes</small>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div style="background-color: #e9eef5; border-radius: 10px; overflow: hidden; height: 20px; position: relative;">
                        <div style="background: linear-gradient(90deg, <?php echo $availableSpots <= 0 ? '#e74c3c' : ($availableSpots < 10 ? '#ede861' : '#59ab6e'); ?> 0%, <?php echo $availableSpots <= 0 ? '#c0392b' : ($availableSpots < 10 ? '#f1c40f' : '#4a9659'); ?> 100%); 
                                    height: 100%; 
                                    width: <?php echo ($totalParticipants / $event['max_participants']) * 100; ?>%; 
                                    transition: all 0.3s ease;
                                    display: flex; align-items: center; justify-content: center;
                                    font-size: 12px; font-weight: bold; color: white;">
                            <?php echo round(($totalParticipants / $event['max_participants']) * 100, 1); ?>%
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Liste des Participants -->
        <div class="table-container">
            <div class="table-header">
                <h2 style="margin: 0;">
                    <i class="fas fa-list" style="margin-right: 10px; color: #59ab6e;"></i>
                    Liste des Participants (<?php echo count($participants); ?>)
                </h2>
            </div>
            
            <div class="table-responsive">
                <?php if (count($participants) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Type</th>
                            <th>Nombre</th>
                            <th>Déjeuner</th>
                            <th>Date d'inscription</th>
                            <th>Motivation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($participants as $participant): ?>
                        <tr>
                            <td>
                                <i class="fas fa-user-circle" style="margin-right: 8px; color: #59ab6e;"></i>
                                <?php echo htmlspecialchars($participant['fullname']); ?>
                            </td>
                            <td style="color: #bcbcbc;"><?php echo htmlspecialchars($participant['email']); ?></td>
                            <td>
                                <?php if ($participant['type_participation'] === 'groupe'): ?>
                                    <span class="badge admin" style="background: #59ab6e; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px;">Groupe</span>
                                <?php else: ?>
                                    <span class="badge user" style="background: #212934; color: white; padding: 4px 8px; border-radius: 12px; font-size: 11px;">Seul</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <strong style="color: #59ab6e; font-size: 16px;"><?php echo $participant['nombre_personnes']; ?></strong>
                            </td>
                            <td style="text-align: center;">
                                <?php if ($participant['desir_dejeuner'] === 'oui'): ?>
                                    <i class="fas fa-check-circle" style="color: #59ab6e; font-size: 16px;"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle" style="color: #bcbcbc; font-size: 16px;"></i>
                                <?php endif; ?>
                            </td>
                            <td style="color: #bcbcbc;">
                                <i class="far fa-calendar" style="margin-right: 5px;"></i>
                                <?php echo date('d/m/Y H:i', strtotime($participant['date_inscription'])); ?>
                            </td>
                            <td>
                                <span style="color: #bcbcbc; font-size: 13px;" title="<?php echo htmlspecialchars($participant['motivation']); ?>">
                                    <?php echo substr(htmlspecialchars($participant['motivation']), 0, 50) . (strlen($participant['motivation']) > 50 ? '...' : ''); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <div style="text-align: center; padding: 60px 20px; color: #bcbcbc;">
                    <i class="fas fa-users" style="font-size: 48px; display: block; margin-bottom: 15px; color: #cfd6e1;"></i>
                    <h4 style="color: #212934; margin-bottom: 10px;">Aucun participant pour le moment</h4>
                    <p style="font-size: 14px;">Les participants apparaîtront ici une fois qu'ils s'inscriront à l'événement.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /* ================================
       SEND EMAIL NOTIFICATIONS FOR NEW EVENT
    ================================= */
    private function sendNewEventEmailToUsers($eventData, $eventId) {
        global $pdo;
        
        try {
            // Get all non-admin users
            $stmt = $pdo->prepare("SELECT fullname, email FROM users WHERE role != 'admin'");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($users)) {
                return; // No users to notify
            }
            
            // Format event date
            $eventDate = new DateTime($eventData['date_evenement']);
            $formattedDate = $eventDate->format('d/m/Y');
            
            // Prepare email content
            $subject = "🎉 Nouvel événement disponible: " . $eventData['titre_evenement'];
            
            $emailBody = $this->generateEventEmailTemplate(
                $eventData['titre_evenement'],
                $formattedDate,
                $eventData['organisateur'],
                $eventData['description'],
                $eventData['adresse'] ?? 'Lieu à confirmer',
                $eventData['frais_participation'] ?? 0,
                $eventId
            );
            
            // Send email to each user
            foreach ($users as $user) {
                $this->sendEmailToUser($user['email'], $user['fullname'], $subject, $emailBody);
            }
            
        } catch (Exception $e) {
            error_log("Error sending new event emails: " . $e->getMessage());
            // Don't throw exception - email failure shouldn't stop event creation
        }
    }
    
    /* ================================
       SEND EMAIL TO INDIVIDUAL USER
    ================================= */
    private function sendEmailToUser($userEmail, $userName, $subject, $htmlBody) {
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'boubakriines11@gmail.com';
            $mail->Password = 'epkp tebq xnpf ibah';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            $mail->CharSet = 'UTF-8';
            
            // Recipients
            $mail->setFrom('boubakriines11@gmail.com', 'SOLIDA - web-chaima');
            $mail->addAddress($userEmail, $userName);
            $mail->addReplyTo('boubakriines11@gmail.com', 'SOLIDA Support');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            
            // Alternative plain text version
            $mail->AltBody = strip_tags(str_replace('<br>', "\n", $htmlBody));
            
            $mail->send();
            
        } catch (Exception $e) {
            error_log("Email sending failed to {$userEmail}: " . $e->getMessage());
        }
    }
    
    /* ================================
       GENERATE EMAIL TEMPLATE
    ================================= */
    private function generateEventEmailTemplate($title, $date, $organizer, $description, $address, $fees, $eventId) {
        $feesText = $fees > 0 ? number_format($fees, 2) . ' DT' : 'Gratuit';
        $eventUrl = "http://localhost/projet_chaima/views/front_office/evenement.php#event-" . $eventId;
        
        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; background-color: #f8f9fa;'>
            <div style='background: linear-gradient(135deg, #59ab6e, #69bb7e); color: white; padding: 30px; text-align: center;'>
                <h1 style='margin: 0; font-size: 28px;'>🎉 SOLIDA</h1>
                <p style='margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;'>web-chaima</p>
            </div>
            
            <div style='padding: 30px; background-color: white;'>
                <h2 style='color: #212934; margin-bottom: 20px; font-size: 24px;'>
                    📅 Nouvel événement disponible !
                </h2>
                
                <div style='background-color: #e9eef5; padding: 20px; border-radius: 8px; margin-bottom: 25px;'>
                    <h3 style='color: #59ab6e; margin: 0 0 15px 0; font-size: 20px;'>{$title}</h3>
                    
                    <div style='margin-bottom: 10px;'>
                        <strong style='color: #212934;'>📅 Date:</strong> 
                        <span style='color: #666;'>{$date}</span>
                    </div>
                    
                    <div style='margin-bottom: 10px;'>
                        <strong style='color: #212934;'>👨‍💼 Organisateur:</strong> 
                        <span style='color: #666;'>{$organizer}</span>
                    </div>
                    
                    <div style='margin-bottom: 10px;'>
                        <strong style='color: #212934;'>📍 Lieu:</strong> 
                        <span style='color: #666;'>{$address}</span>
                    </div>
                    
                    <div style='margin-bottom: 10px;'>
                        <strong style='color: #212934;'>💰 Frais:</strong> 
                        <span style='color: #666;'>{$feesText}</span>
                    </div>
                </div>
                
                <div style='margin-bottom: 25px;'>
                    <h4 style='color: #212934; margin-bottom: 10px;'>📝 Description:</h4>
                    <p style='color: #666; line-height: 1.6; margin: 0;'>{$description}</p>
                </div>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$eventUrl}' style='
                        background: linear-gradient(135deg, #59ab6e, #69bb7e);
                        color: white;
                        padding: 15px 30px;
                        text-decoration: none;
                        border-radius: 8px;
                        font-size: 16px;
                        font-weight: bold;
                        display: inline-block;
                        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
                    '>
                        🎫 Voir l'événement et s'inscrire
                    </a>
                </div>
            </div>
            
            <div style='background-color: #212934; color: white; padding: 20px; text-align: center;'>
                <p style='margin: 0; font-size: 14px; opacity: 0.8;'>
                    Vous recevez cet email car vous êtes inscrit sur SOLIDA<br>
                    <small>web-chaima - Plateforme de gestion d'événements</small>
                </p>
            </div>
        </div>";
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
} elseif ($action === 'get_event_details') {
    $eventId = intval($_GET['id'] ?? 0);
    header('Content-Type: application/json');
    echo json_encode($controller->getEventDetails($eventId));
    exit();
}

?>

