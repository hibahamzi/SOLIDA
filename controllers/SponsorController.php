<?php

require_once __DIR__ . '/../models/Sponsor.php';

class SponsorController
{
    private PDO $pdo;

    /**
     * Constructor
     * @param PDO $pdo Database connection
     */
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Display public front page with sponsors and accepted deals
     * Supports sorting
     */
    public function front()
    {
        $sort = $_GET['sort'] ?? '';
        $sqlSponsors = "SELECT * FROM sponsors WHERE statut = 'Actif'"; // Only show active sponsors
        
        switch ($sort) {
            case 'nom_asc':
                $sqlSponsors .= " ORDER BY nomEntreprise ASC";
                break;

            case 'montant_desc':
                $sqlSponsors .= " ORDER BY montantEngage DESC";
                break;

            default:
                $sqlSponsors .= " ORDER BY id DESC";
                break;
        }

        $stmt = $this->pdo->query($sqlSponsors);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sponsors = [];
        foreach ($rows as $row) {
            $sponsors[] = new Sponsor(
                $row['id'] ?? null,
                $row['nomEntreprise'] ?? null,
                $row['emailContact'] ?? null,
                $row['telephone'] ?? null,
                $row['adresse'] ?? null,
                $row['typeSponsoring'] ?? null,
                $row['montantEngage'] ?? null,
                $row['domaineActivite'] ?? null,
                $row['logoUrl'] ?? null,
                $row['contratUrl'] ?? null,
                $row['dateDebutPartenaire'] ?? null,
                $row['dateFinPartenaire'] ?? null,
                $row['statut'] ?? null,
                $row['id_user'] ?? null
            );
        }

        $sqlDeals = "
            SELECT d.*, s.nomEntreprise
            FROM deals d
            JOIN sponsors s ON d.idSponsor = s.id
            WHERE d.statut = 'accepte' AND s.statut = 'Actif'
        ";

        switch ($sort) {
            case 'deal_date_debut_recent':
                $sqlDeals .= " ORDER BY d.dateDebut DESC";
                break;

            case 'deal_periode_longue':
                $sqlDeals .= " ORDER BY d.periodeValidite DESC";
                break;

            case 'deal_montant_desc':
                $sqlDeals .= " ORDER BY d.prixInitial DESC";
                break;

            case 'deal_accept_date':
                $sqlDeals .= " ORDER BY d.dateAcceptation DESC";
                break;

            case 'deal_dispo':
                $sqlDeals .= " ORDER BY 
                               (CASE WHEN d.expire = 'NON' THEN 0 ELSE 1 END),
                               d.dateDebut DESC";
                break;

            case 'deal_expire':
                $sqlDeals .= " ORDER BY 
                               (CASE WHEN d.expire = 'OUI' THEN 0 ELSE 1 END),
                               d.dateDebut DESC";
                break;

            default:
                $sqlDeals .= " ORDER BY d.dateDebut DESC";
                break;
        }

        $stmtDeals = $this->pdo->query($sqlDeals);
        $offers = $stmtDeals->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/sponsor/front.php';
    }

    /**
     * Display list of sponsors in backoffice
     * Supports sorting and shows pending sponsors for admin approval
     */
    public function index()
    {
        $sort = $_GET['sort'] ?? '';
        $sql = "SELECT * FROM sponsors";
        
        switch ($sort) {
            case 'nom_asc':
                $sql .= " ORDER BY nomEntreprise ASC";
                break;

            case 'montant_desc':
                $sql .= " ORDER BY montantEngage DESC";
                break;

            default:
                // Show pending sponsors first, then by ID
                $sql .= " ORDER BY (CASE WHEN statut = 'Inactif' THEN 0 ELSE 1 END), id DESC";
                break;
        }

        $stmt = $this->pdo->query($sql);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $sponsors = [];
        foreach ($rows as $row) {
            $sponsors[] = new Sponsor(
                $row['id'] ?? null,
                $row['nomEntreprise'] ?? null,
                $row['emailContact'] ?? null,
                $row['telephone'] ?? null,
                $row['adresse'] ?? null,
                $row['typeSponsoring'] ?? null,
                $row['montantEngage'] ?? null,
                $row['domaineActivite'] ?? null,
                $row['logoUrl'] ?? null,
                $row['contratUrl'] ?? null,
                $row['dateDebutPartenaire'] ?? null,
                $row['dateFinPartenaire'] ?? null,
                $row['statut'] ?? null,
                $row['id_user'] ?? null
            );
        }

        // Pass sort variable to view
        $sortVar = $sort;
        
        // Use the front office sponsor index for both front and back office
        require __DIR__ . '/../views/front_office/sponsor/index.php';
    }

    /**
     * Activate a sponsor (change status from Inactif to Actif)
     * Admin only action
     */
    public function activate()
    {
        if (!isset($_GET['id'])) {
            header('Location: sponsor.php?action=index');
            exit;
        }

        $id = (int)$_GET['id'];

        // Update sponsor status to Actif
        $stmt = $this->pdo->prepare("UPDATE sponsors SET statut = 'Actif' WHERE id = ?");
        $stmt->execute([$id]);

        // Redirect back to index
        $currentPath = $_SERVER['PHP_SELF'];
        if (strpos($currentPath, '/front_office/sponsor/') !== false || strpos($currentPath, '/sponsor/') !== false) {
            header('Location: index.php?success=sponsor_activated');
        } else {
            header('Location: sponsor.php?action=index&success=sponsor_activated');
        }
        exit;
    }

    /**
     * Create a new sponsor
     * Handles form submission and file upload for logo
     */
    public function create()
    {
        // Make variables available to index.php via $GLOBALS
        $GLOBALS['fieldErrors'] = [];
        $GLOBALS['old'] = [];
        $fieldErrors = &$GLOBALS['fieldErrors'];
        $old = &$GLOBALS['old'];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['nomEntreprise']       = trim($_POST['nomEntreprise'] ?? '');
            $old['emailContact']        = trim($_POST['emailContact'] ?? '');
            $old['telephone']           = trim($_POST['telephone'] ?? '');
            $old['adresse']             = trim($_POST['adresse'] ?? '');
            $old['typeSponsoring']      = trim($_POST['typeSponsoring'] ?? '');
            $old['montantEngage']       = trim($_POST['montantEngage'] ?? '');
            $old['domaineActivite']     = trim($_POST['domaineActivite'] ?? '');
            $old['contratUrl']          = trim($_POST['contratUrl'] ?? '');
            $old['dateDebutPartenaire'] = trim($_POST['dateDebutPartenaire'] ?? '');
            $old['dateFinPartenaire']   = trim($_POST['dateFinPartenaire'] ?? '');
            $old['statut']              = trim($_POST['statut'] ?? '');

            if ($old['nomEntreprise'] === '') {
                $fieldErrors['nomEntreprise'] = "Le nom de l'entreprise est obligatoire.";
            }

            if ($old['emailContact'] === '' || !filter_var($old['emailContact'], FILTER_VALIDATE_EMAIL)) {
                $fieldErrors['emailContact'] = "Email invalide.";
            }

            if ($old['telephone'] !== '' && !preg_match('/^\d{8}$/', $old['telephone'])) {
                $fieldErrors['telephone'] = "Le téléphone doit contenir exactement 8 chiffres.";
            }

            $logoPath = null;
            if (!empty($_FILES['logo']['name'])) {
                $file = $_FILES['logo'];

                if ($file['size'] > 1024 * 1024) {
                    $fieldErrors['logo'] = "Le logo est trop grand (taille maximale 1 Mo).";
                } else {
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    if (!in_array($file['type'], $allowedTypes, true)) {
                        $fieldErrors['logo'] = "Le logo doit être une image (JPG, PNG ou GIF).";
                    } else {
                        $uploadDir = __DIR__ . '/../../public/uploads/logos';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0777, true);
                        }

                        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                        $newName = 'logo_' . time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
                        $dest = $uploadDir . '/' . $newName;

                        if (!move_uploaded_file($file['tmp_name'], $dest)) {
                            $fieldErrors['logo'] = "Erreur lors de l'upload du logo.";
                        } else {
                            $logoPath = '/PROJET_WEB_MVC_FINAL/public/uploads/logos/' . $newName;
                        }
                    }
                }
            }

            if (empty($fieldErrors)) {
                $id_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
                if (!$id_user) {
                    $fieldErrors['general'] = "Vous devez être connecté pour créer un sponsor.";
                } else {
                    // Front office users: force statut to "Inactif" (pending approval)
                    // Only admins can set statut to "Actif"
                    $statut = (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') 
                        ? ($old['statut'] ?: 'Inactif') 
                        : 'Inactif';
                    
                    $sponsor = new Sponsor(
                        null,
                        $old['nomEntreprise'],
                        $old['emailContact'],
                        $old['telephone'] ?: null,
                        $old['adresse'] ?: null,
                        $old['typeSponsoring'],
                        $old['montantEngage'] ?: null,
                        $old['domaineActivite'] ?: null,
                        $logoPath,
                        $old['contratUrl'] ?: null,
                        $old['dateDebutPartenaire'] ?: null,
                        $old['dateFinPartenaire'] ?: null,
                        $statut,
                        $id_user
                    );

                    $sql = "INSERT INTO sponsors
                            (nomEntreprise, emailContact, telephone, adresse, typeSponsoring,
                             montantEngage, domaineActivite, logoUrl, contratUrl,
                             dateDebutPartenaire, dateFinPartenaire, statut, id_user)
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)";

                    $stmt = $this->pdo->prepare($sql);
                    $stmt->execute([
                        $sponsor->getNomEntreprise(),
                        $sponsor->getEmailContact(),
                        $sponsor->getTelephone(),
                        $sponsor->getAdresse(),
                        $sponsor->getTypeSponsoring(),
                        $sponsor->getMontantEngage(),
                        $sponsor->getDomaineActivite(),
                        $sponsor->getLogoUrl(),
                        $sponsor->getContratUrl(),
                        $sponsor->getDateDebutPartenaire(),
                        $sponsor->getDateFinPartenaire(),
                        $sponsor->getStatut(),
                        $sponsor->getIdUser(),
                    ]);

                    // Redirect based on context
                    $currentPath = $_SERVER['PHP_SELF'];
                    if (strpos($currentPath, '/front_office/sponsor/create.php') !== false || 
                        strpos($currentPath, '/sponsor/create.php') !== false) {
                        // Back office context - redirect to sponsor index
                        header('Location: index.php?success=sponsor_created');
                    } elseif (strpos($currentPath, '/front_office/') !== false) {
                        header('Location: index.php?section=sponsors&success=sponsor_created');
                    } else {
                        header('Location: sponsor.php?action=front&success=sponsor_created');
                    }
                    exit;
                }
            }
        }

        // Check if accessed from front office index.php (via section parameter) or directly
        $isFrontOfficeRequest = isset($_GET['section']) && $_GET['section'] === 'sponsor';
        $currentPath = $_SERVER['PHP_SELF'];
        $isDirectAccess = strpos($currentPath, '/sponsor/create.php') !== false;
        
        if ($isFrontOfficeRequest) {
            // Front office request via index.php - don't include view directly, let index.php handle it with navbar/footer
            // Variables $old and $fieldErrors are set above and available via $GLOBALS
            return; // Let index.php continue and display the form
        } elseif ($isDirectAccess) {
            // Direct access from back office (e.g., from sponsor/index.php) - include the view directly
            // The view will detect it's back office and show sidebar/topbar
            require __DIR__ . '/../views/front_office/sponsor/create.php';
            exit; // Exit after including the view
        } else {
            // Fallback - include the view
            require __DIR__ . '/../views/front_office/sponsor/create.php';
            exit;
        }
    }

    /**
     * Edit an existing sponsor
     * Optionally updates user ID if provided in session
     */
    public function edit()
    {
        // Check if ID is provided in GET or POST
        if (!isset($_GET['id']) && !isset($_POST['id'])) {
            // Redirect to sponsor index
            $currentPath = $_SERVER['PHP_SELF'];
            if (strpos($currentPath, '/front_office/sponsor/') !== false || strpos($currentPath, '/sponsor/') !== false) {
                header('Location: index.php');
            } else {
                header('Location: sponsor.php?action=index');
            }
            exit;
        }
        
        // Get ID from GET or POST
        $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['id'] ?? 0);

        // Fetch sponsor from database
        $stmt = $this->pdo->prepare("SELECT * FROM sponsors WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            die('Sponsor introuvable');
        }

        $sponsor = new Sponsor(
            $row['id'],
            $row['nomEntreprise'],
            $row['emailContact'],
            $row['telephone'],
            $row['adresse'],
            $row['typeSponsoring'],
            $row['montantEngage'],
            $row['domaineActivite'],
            $row['logoUrl'],
            $row['contratUrl'],
            $row['dateDebutPartenaire'],
            $row['dateFinPartenaire'],
            $row['statut'],
            $row['id_user'] ?? null
        );

        $fieldErrors = [];
        $old = [
            'id'                  => $sponsor->getId(),
            'nomEntreprise'       => $sponsor->getNomEntreprise(),
            'emailContact'        => $sponsor->getEmailContact(),
            'telephone'           => $sponsor->getTelephone(),
            'adresse'             => $sponsor->getAdresse(),
            'typeSponsoring'      => $sponsor->getTypeSponsoring(),
            'montantEngage'       => $sponsor->getMontantEngage(),
            'domaineActivite'     => $sponsor->getDomaineActivite(),
            'logoUrl'             => $sponsor->getLogoUrl(),
            'contratUrl'          => $sponsor->getContratUrl(),
            'dateDebutPartenaire' => $sponsor->getDateDebutPartenaire(),
            'dateFinPartenaire'   => $sponsor->getDateFinPartenaire(),
            'statut'              => $sponsor->getStatut(),
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $old['nomEntreprise']       = trim($_POST['nomEntreprise'] ?? '');
            $old['emailContact']        = trim($_POST['emailContact'] ?? '');
            $old['telephone']           = trim($_POST['telephone'] ?? '');
            $old['adresse']             = trim($_POST['adresse'] ?? '');
            $old['typeSponsoring']      = trim($_POST['typeSponsoring'] ?? '');
            $old['montantEngage']       = trim($_POST['montantEngage'] ?? '');
            $old['domaineActivite']     = trim($_POST['domaineActivite'] ?? '');
            $old['logoUrl']             = trim($_POST['logoUrl'] ?? $old['logoUrl']);
            $old['contratUrl']          = trim($_POST['contratUrl'] ?? '');
            $old['dateDebutPartenaire'] = trim($_POST['dateDebutPartenaire'] ?? '');
            $old['dateFinPartenaire']   = trim($_POST['dateFinPartenaire'] ?? '');
            $old['statut']              = trim($_POST['statut'] ?? '');

            if ($old['nomEntreprise'] === '') {
                $fieldErrors['nomEntreprise'] = "Le nom de l'entreprise est obligatoire.";
            }

            if ($old['emailContact'] === '' || !filter_var($old['emailContact'], FILTER_VALIDATE_EMAIL)) {
                $fieldErrors['emailContact'] = "Email invalide.";
            }

            if ($old['telephone'] !== '' && !preg_match('/^\d{8}$/', $old['telephone'])) {
                $fieldErrors['telephone'] = "Le téléphone doit contenir exactement 8 chiffres.";
            }

            if (empty($fieldErrors)) {
                $id_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

                $sql = "UPDATE sponsors SET
                            nomEntreprise = ?,
                            emailContact  = ?,
                            telephone     = ?,
                            adresse       = ?,
                            typeSponsoring = ?,
                            montantEngage = ?,
                            domaineActivite = ?,
                            logoUrl       = ?,
                            contratUrl    = ?,
                            dateDebutPartenaire = ?,
                            dateFinPartenaire   = ?,
                            statut        = ?";

                $params = [
                    $old['nomEntreprise'],
                    $old['emailContact'],
                    $old['telephone'] ?: null,
                    $old['adresse'] ?: null,
                    $old['typeSponsoring'],
                    $old['montantEngage'] ?: null,
                    $old['domaineActivite'] ?: null,
                    $old['logoUrl'] ?: null,
                    $old['contratUrl'] ?: null,
                    $old['dateDebutPartenaire'] ?: null,
                    $old['dateFinPartenaire'] ?: null,
                    $old['statut'],
                ];

                if ($id_user) {
                    $sql .= ", id_user = ?";
                    $params[] = $id_user;
                }

                $sql .= " WHERE id = ?";
                $params[] = $id;

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
  
                // Redirect to sponsor index
                $currentPath = $_SERVER['PHP_SELF'];
                if (strpos($currentPath, '/front_office/sponsor/') !== false || strpos($currentPath, '/sponsor/') !== false) {
                    header('Location: index.php?success=sponsor_updated');
                } else {
                    header('Location: sponsor.php?action=index&success=sponsor_updated');
                }
                exit;
            }
        }

        // Always use the front_office/sponsor/edit.php view, it will detect context
        require __DIR__ . '/../views/front_office/sponsor/edit.php';
    }

    /**
     * Display a single sponsor details in backoffice
     */
    public function show()
    {
        if (!isset($_GET['id'])) {
            // Redirect to sponsor index
            $currentPath = $_SERVER['PHP_SELF'];
            if (strpos($currentPath, '/front_office/sponsor/') !== false || strpos($currentPath, '/sponsor/') !== false) {
                header('Location: index.php');
            } else {
                header('Location: sponsor.php?action=index');
            }
            exit;
        }

        $id = (int)$_GET['id'];

        $stmt = $this->pdo->prepare("SELECT * FROM sponsors WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            die('Sponsor introuvable');
        }

        $sponsor = new Sponsor(
            $row['id'],
            $row['nomEntreprise'],
            $row['emailContact'],
            $row['telephone'],
            $row['adresse'],
            $row['typeSponsoring'],
            $row['montantEngage'],
            $row['domaineActivite'],
            $row['logoUrl'],
            $row['contratUrl'],
            $row['dateDebutPartenaire'],
            $row['dateFinPartenaire'],
            $row['statut'],
            $row['id_user'] ?? null
        );

        // Check if accessed from front office index.php (via section parameter) or directly
        $isFrontOfficeRequest = isset($_GET['section']) && $_GET['section'] === 'sponsor';
        $currentPath = $_SERVER['PHP_SELF'];
        $isDirectAccess = strpos($currentPath, '/sponsor/show.php') !== false;
        
        if ($isFrontOfficeRequest) {
            // Front office request - don't include view directly, let index.php handle it with navbar/footer
            // Set sponsor in $GLOBALS so index.php can access it
            $GLOBALS['sponsor'] = $sponsor;
            return; // Let index.php continue and display the sponsor
        } else {
            // Direct access from back office - include the view directly
            require __DIR__ . '/../views/front_office/sponsor/show.php';
        }
    }

    /**
     * Delete a sponsor
     */
    public function delete()
    {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $stmt = $this->pdo->prepare("DELETE FROM sponsors WHERE id = ?");
            $stmt->execute([$id]);
        }

        // Redirect to sponsor index
        $currentPath = $_SERVER['PHP_SELF'];
        if (strpos($currentPath, '/front_office/sponsor/') !== false || strpos($currentPath, '/sponsor/') !== false) {
            header('Location: index.php?success=sponsor_deleted');
        } else {
            header('Location: sponsor.php?action=index&success=sponsor_deleted');
        }
        exit;
    }

    /**
     * AI Assistant for sponsors using OpenAI
     * Provides chat interface for sponsor-related questions
     */
    public function assistantIA(): void
    {
        if (!isset($_SESSION['sponsor_ai_chat'])) {
            $_SESSION['sponsor_ai_chat'] = [];
        }

        $messages    = $_SESSION['sponsor_ai_chat'];
        $question    = $_POST['question'] ?? '';
        $errorAi     = null;

        $sql = "SELECT s.id, s.nomEntreprise, s.typeSponsoring, s.montantEngage, s.domaineActivite,
                       COUNT(d.idDeal) as totalDeals
                FROM sponsors s
                LEFT JOIN deals d ON s.id = d.idSponsor
                GROUP BY s.id
                ORDER BY s.id DESC
                LIMIT 10";
        $stmt = $this->pdo->query($sql);
        $recentSponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (isset($_POST['reset_chat'])) {
            $_SESSION['sponsor_ai_chat'] = [];
            $messages = [];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_question'])) {
            $question = trim($question);

            if ($question === '') {
                $errorAi = "Merci de saisir une question avant d'interroger l'assistant IA.";
            } else {
                $messages[] = [
                    'role'    => 'user',
                    'content' => $question,
                    'time'    => date('H:i')
                ];

                $answer = $this->callAI($question, $recentSponsors);

                $messages[] = [
                    'role'    => 'assistant',
                    'content' => $answer,
                    'time'    => date('H:i')
                ];

                $_SESSION['sponsor_ai_chat'] = $messages;
            }
        }

        // Include the front office or back office view based on context
        $currentPath = $_SERVER['PHP_SELF'];
        if (strpos($currentPath, '/front_office/') !== false || strpos($currentPath, 'index.php') !== false && isset($_GET['section']) && $_GET['section'] === 'sponsor') {
            include __DIR__ . '/../views/front_office/sponsor/assistant_ia.php';
        } else {
            // Back office view (if it exists)
            if (file_exists(__DIR__ . '/../views/back_office/sponsor/assistant_ia.php')) {
                include __DIR__ . '/../views/back_office/sponsor/assistant_ia.php';
            } else {
                include __DIR__ . '/../views/front_office/sponsor/assistant_ia.php';
            }
        }
    }

    private function callAI(string $question, array $recentSponsors): string
    {
        $sponsorsLines = [];
        foreach ($recentSponsors as $s) {
            $sponsorsLines[] = sprintf(
                "- Entreprise: %s | Type: %s | Montant: %s | Domaine: %s | Offres: %d",
                $s['nomEntreprise'] ?? 'Sans nom',
                $s['typeSponsoring'] ?? 'Non spécifié',
                $s['montantEngage'] ?? '0',
                $s['domaineActivite'] ?? 'Non spécifié',
                (int)($s['totalDeals'] ?? 0)
            );
        }
        $sponsorsContext = implode("\n", $sponsorsLines);

        $systemPrompt = <<<TXT
Tu es un assistant pour une plateforme de sponsoring étudiante appelée SOLIDA.
Tu disposes d'une liste de sponsors dans le contexte ci-dessous.
Ton rôle :
- Répondre en FRANÇAIS.
- Expliquer clairement et simplement.
- Si la question parle de sponsors, montants, types de sponsoring, domaines d'activité, etc.,
  base-toi sur les données du contexte (nom, type, montant, domaine, nombre d'offres).
- Si la question demande des idées (nouveaux sponsors, stratégies), propose des suggestions adaptées aux étudiants.
TXT;

        $userPrompt = <<<TXT
Contexte (derniers sponsors) :
$sponsorsContext

Question de l'utilisateur :
$question
TXT;

        // Use the same OpenAI constants from DealController
        if (!defined('OPENAI_API_KEY')) {
            define('OPENAI_API_KEY', 'TA_CLE_ICI');
        }
        if (!defined('OPENAI_API_URL')) {
            define('OPENAI_API_URL', 'https://api.openai.com/v1/chat/completions');
        }
        if (!defined('OPENAI_MODEL_ID')) {
            define('OPENAI_MODEL_ID', 'gpt-4.1-mini');
        }

        $payload = [
            "model"    => OPENAI_MODEL_ID,
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user",   "content" => $userPrompt],
            ],
            "temperature" => 0.5,
        ];

        $ch = curl_init(OPENAI_API_URL);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . OPENAI_API_KEY,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return "Désolé, je n'ai pas pu obtenir de réponse de l'assistant IA. Veuillez réessayer plus tard.";
        }

        $data = json_decode($response, true);
        if (!isset($data['choices'][0]['message']['content'])) {
            return "Erreur lors de la récupération de la réponse de l'IA.";
        }

        return trim($data['choices'][0]['message']['content']);
    }
}

// ================================
// ROUTING LOGIC (only runs when controller is accessed directly)
// ================================

// Only execute routing if this file is accessed directly (not included)
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    if (!isset($pdo)) {
        require_once __DIR__ . '/../config/config.php';
    }

    $controller = new SponsorController($pdo);
    $action = $_POST['action'] ?? $_GET['action'] ?? 'front';

    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        $controller->front();
    }
    exit;
}