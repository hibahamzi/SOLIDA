<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../views/front_office/assets/vendor/phpmailer/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../views/front_office/assets/vendor/phpmailer/phpmailer/src/SMTP.php';
require_once __DIR__ . '/../views/front_office/assets/vendor/phpmailer/phpmailer/src/Exception.php';


class DealController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * LISTE DES DEALS (BACK OFFICE)
     */
    public function index(): void
    {
        $selectedSponsorId = isset($_GET['sponsor_id']) && $_GET['sponsor_id'] !== ''
            ? (int)$_GET['sponsor_id']
            : null;

        $sort = $_GET['sort'] ?? '';

        $sqlSponsors = "SELECT id, nomEntreprise FROM sponsors ORDER BY nomEntreprise ASC";
        $stmtSponsors = $this->db->query($sqlSponsors);
        $sponsors = $stmtSponsors->fetchAll(PDO::FETCH_ASSOC);

        $sql = "SELECT d.*, s.nomEntreprise
                FROM deals d
                JOIN sponsors s ON d.idSponsor = s.id";
        $params = [];

        if ($selectedSponsorId) {
            $sql .= " WHERE d.idSponsor = :idSponsor";
            $params[':idSponsor'] = $selectedSponsorId;
        }

        switch ($sort) {
            case 'deal_date_debut_recent':
                $sql .= " ORDER BY d.dateDebut DESC";
                break;
            case 'deal_periode_longue':
                $sql .= " ORDER BY d.periodeValidite DESC";
                break;
            case 'deal_montant_desc':
                $sql .= " ORDER BY d.prixInitial DESC";
                break;
            case 'deal_accept_date':
                $sql .= " ORDER BY d.dateAcceptation DESC";
                break;
            case 'deal_dispo':
                $sql .= " ORDER BY 
                            (CASE WHEN d.expire = 'NON' THEN 0 ELSE 1 END),
                            d.dateDebut DESC";
                break;
            case 'deal_expire':
                $sql .= " ORDER BY 
                            (CASE WHEN d.expire = 'OUI' THEN 0 ELSE 1 END),
                            d.dateDebut DESC";
                break;
            default:
                $sql .= " ORDER BY d.idDeal DESC";
                break;
        }

        if ($params) {
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
        } else {
            $stmt = $this->db->query($sql);
        }

        $deals = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $sortVar = $sort;
        
        include __DIR__ . '/../views/back_office/deal/index.php';
    }

    /**
     * FORMULAIRE CREATION (BACK OFFICE)
     */
    public function create(): void
    {
        $sqlSponsors = "SELECT id, nomEntreprise FROM sponsors ORDER BY nomEntreprise ASC";
        $stmtSponsors = $this->db->query($sqlSponsors);
        $sponsors = $stmtSponsors->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/back_office/deal/create.php';
    }

    /**
     * ENREGISTRER UN NOUVEAU DEAL (FRONT + BACK)
     */
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDealIndex();
            exit;
        }

        $id_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
        if (!$id_user) {
            // Si pas connecté, retour back office (ou à adapter selon ton auth)
            $this->redirectToDealIndex();
            exit;
        }
        
        $intitule        = $_POST['intitule']        ?? '';
        $descriptionD    = $_POST['descriptionD']    ?? '';
        $prixInitial     = $_POST['prixInitial']     ?? 0;
        $reduction       = $_POST['reduction']       ?? 0;
        $dateDebut       = $_POST['dateDebut']       ?? null;
        $periodeValidite = $_POST['periodeValidite'] ?? 0;
        $idSponsor       = isset($_POST['idSponsor']) ? (int)$_POST['idSponsor'] : null;
        $expire          = $_POST['expire']          ?? 'NON';
        $note            = $_POST['note']            ?? null;

        $statut     = 'en_attente';
        $hasCoupon  = $_POST['has_coupon']           ?? 'NON';
        $couponCode = null;

        if ($hasCoupon === 'OUI') {
            $couponCode = (string)random_int(100000, 999999);
        } else {
            $hasCoupon = 'NON';
        }

        $sql = "INSERT INTO deals (
                    intitule,
                    descriptionD,
                    prixInitial,
                    reduction,
                    dateDebut,
                    periodeValidite,
                    note,
                    expire,
                    idSponsor,
                    id_user,
                    statut,
                    click_count,
                    has_coupon,
                    coupon_code,
                    dateAcceptation
                ) VALUES (
                    :intitule,
                    :descriptionD,
                    :prixInitial,
                    :reduction,
                    :dateDebut,
                    :periodeValidite,
                    :note,
                    :expire,
                    :idSponsor,
                    :id_user,
                    :statut,
                    0,
                    :has_coupon,
                    :coupon_code,
                    NULL
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':intitule'        => $intitule,
            ':descriptionD'    => $descriptionD,
            ':prixInitial'     => $prixInitial,
            ':reduction'       => $reduction,
            ':dateDebut'       => $dateDebut,
            ':periodeValidite' => $periodeValidite,
            ':note'            => $note,
            ':expire'          => $expire,
            ':idSponsor'       => $idSponsor,
            ':id_user'         => $id_user,
            ':statut'          => $statut,
            ':has_coupon'      => $hasCoupon,
            ':coupon_code'     => $couponCode,
        ]);

        // Détecter front / back office
        $currentPath   = $_SERVER['PHP_SELF'];
        $isFrontOffice = strpos($currentPath, '/front_office/') !== false
                         || basename($currentPath) === 'index.php';

        if ($isFrontOffice) {
            // Front office : retour sur la page sponsors avec message succès
            header('Location: index.php?section=sponsors&success=deal_created');
        } else {
            // Back office : retour à la liste des deals
            $this->redirectToDealIndex();
        }
        exit;
    }

    /**
     * EDIT (BACK OFFICE)
     */
    public function edit(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int)$_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            $this->redirectToDealIndex();
            exit;
        }

        $sql = "SELECT * FROM deals WHERE idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);
        $deal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deal) {
            $this->redirectToDealIndex();
            exit;
        }

        $sqlSponsors = "SELECT id, nomEntreprise FROM sponsors ORDER BY nomEntreprise ASC";
        $stmtSponsors = $this->db->query($sqlSponsors);
        $sponsors = $stmtSponsors->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/back_office/deal/edit.php';
    }

    /**
     * UPDATE (BACK OFFICE)
     */
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectToDealIndex();
            exit;
        }

        $idDeal          = isset($_POST['idDeal']) ? (int)$_POST['idDeal'] : 0;
        $intitule        = $_POST['intitule']        ?? '';
        $descriptionD    = $_POST['descriptionD']    ?? '';
        $prixInitial     = $_POST['prixInitial']     ?? 0;
        $reduction       = $_POST['reduction']       ?? 0;
        $dateDebut       = $_POST['dateDebut']       ?? null;
        $periodeValidite = $_POST['periodeValidite'] ?? 0;
        $idSponsor       = isset($_POST['idSponsor']) ? (int)$_POST['idSponsor'] : null;
        $statut          = $_POST['statut']          ?? 'en_attente';
        $expire          = $_POST['expire']          ?? 'NON';
        $hasCoupon       = $_POST['has_coupon']      ?? 'NON';

        $sql = "SELECT coupon_code, note FROM deals WHERE idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);
        $oldData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldData) {
            $this->redirectToDealIndex();
            exit;
        }

        $couponCode = $oldData['coupon_code'];
        $note       = $oldData['note'];

        if ($hasCoupon === 'OUI' && empty($couponCode)) {
            $couponCode = (string)random_int(100000, 999999);
        }
        if ($hasCoupon === 'NON') {
            $couponCode = null;
        }

        $id_user = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

        $sqlUpdate = "UPDATE deals
                      SET intitule        = :intitule,
                          descriptionD    = :descriptionD,
                          prixInitial     = :prixInitial,
                          reduction       = :reduction,
                          dateDebut       = :dateDebut,
                          periodeValidite = :periodeValidite,
                          note            = :note,
                          expire          = :expire,
                          idSponsor       = :idSponsor,
                          statut          = :statut,
                          has_coupon      = :has_coupon,
                          coupon_code     = :coupon_code";

        $params = [
            ':intitule'        => $intitule,
            ':descriptionD'    => $descriptionD,
            ':prixInitial'     => $prixInitial,
            ':reduction'       => $reduction,
            ':dateDebut'       => $dateDebut,
            ':periodeValidite' => $periodeValidite,
            ':note'            => $note,
            ':expire'          => $expire,
            ':idSponsor'       => $idSponsor,
            ':statut'          => $statut,
            ':has_coupon'      => $hasCoupon,
            ':coupon_code'     => $couponCode,
            ':idDeal'          => $idDeal,
        ];

        if ($id_user) {
            $sqlUpdate .= ", id_user = :id_user";
            $params[':id_user'] = $id_user;
        }

        $sqlUpdate .= " WHERE idDeal = :idDeal";

        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->execute($params);

        $this->redirectToDealIndex();
        exit;
    }

    /**
     * DELETE (BACK OFFICE)
     */
    public function delete(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int)$_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            $this->redirectToDealIndex();
            exit;
        }

        $sql = "DELETE FROM deals WHERE idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);

        $this->redirectToDealIndex();
        exit;
    }

    /**
     * DELETE ALL (BACK OFFICE)
     */
    public function deleteAll(): void
    {
        $sql = "DELETE FROM deals";
        $this->db->exec($sql);

        $this->redirectToDealIndex();
        exit;
    }

    /**
     * LISTE DES DEALS EN ATTENTE (BACK OFFICE)
     */
    public function pending(): void
    {
        $sql = "SELECT d.*, s.nomEntreprise
                FROM deals d
                JOIN sponsors s ON d.idSponsor = s.id
                WHERE d.statut = 'en_attente'
                ORDER BY d.idDeal DESC";
        $stmt = $this->db->query($sql);
        $deals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/back_office/deal/pending.php';
    }

    /**
     * ACCEPTER UN DEAL (BACK OFFICE) + ENVOI MAIL
     */
    public function accept(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int) $_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            $this->redirectToDealIndex();
            exit;
        }

        $sql = "SELECT d.*, s.emailContact AS emailSponsor, s.nomEntreprise
                FROM deals d
                JOIN sponsors s ON d.idSponsor = s.id
                WHERE d.idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);
        $deal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deal) {
            $this->redirectToDealIndex();
            exit;
        }

        $sqlUpdate = "UPDATE deals 
                      SET statut = 'accepte',
                          dateAcceptation = NOW()
                      WHERE idDeal = :idDeal";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->execute([':idDeal' => $idDeal]);

        $emailSponsor = $deal['emailSponsor'] ?? null;

        if (!empty($emailSponsor)) {
            $nomSponsor  = $deal['nomEntreprise'] ?? 'Votre entreprise';
            $intitule    = $deal['intitule']      ?? 'votre offre';
            $subject     = "Votre offre a été acceptée sur SOLIDA";

            $urlFront = "sponsor.php?action=front";

            $messageHtml = '
                <html>
                <head>
                    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
                    <title>Offre acceptée</title>
                </head>
                <body>
                    <p>Bonjour <strong>' . htmlspecialchars($nomSponsor, ENT_QUOTES, "UTF-8") . '</strong>,</p>
                    <p>Votre offre <strong>"' . htmlspecialchars($intitule, ENT_QUOTES, "UTF-8") . '"</strong> a été acceptée par l\'équipe <strong>SOLIDA</strong>.</p>
                    <p>Elle est maintenant visible parmi les offres disponibles pour les étudiants.</p>
                    <p style="margin:20px 0;">
                        <a href="' . $urlFront . '"
                           style="background-color:#28a745;color:#ffffff;padding:10px 20px;
                                  text-decoration:none;border-radius:5px;display:inline-block;">
                            Voir l\'offre sur SOLIDA
                        </a>
                    </p>
                    <p>À bientôt,<br>L\'équipe SOLIDA</p>
                </body>
                </html>
            ';

            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host       = 'smtp.gmail.com';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'solida.2a11@gmail.com';
                $mail->Password   = 'TON_MDP_APP';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = 587;

                $mail->CharSet  = 'UTF-8';
                $mail->Encoding = 'base64';

                $mail->setFrom('solida.2a11@gmail.com', 'SOLIDA');
                $mail->addAddress($emailSponsor, $nomSponsor);

                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $messageHtml;

                $mail->send();
            } catch (Exception $e) {
                error_log('Erreur envoi mail: ' . $mail->ErrorInfo);
            }
        }

        $this->redirectToDealIndex();
        exit;
    }

    /**
     * REFUSER UN DEAL (BACK OFFICE)
     */
    public function reject(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int)$_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            $this->redirectToDealIndex();
            exit;
        }

        $sql = "UPDATE deals SET statut = 'refuse' WHERE idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);

        $this->redirectToDealIndex();
        exit;
    }

    /**
     * PAGE DETAIL D'UN DEAL (FRONT)
     */
    public function show(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int)$_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            header('Location: sponsor.php?action=front');
            exit;
        }

        $sql = "SELECT d.*, s.nomEntreprise
                FROM deals d
                JOIN sponsors s ON d.idSponsor = s.id
                WHERE d.idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);
        $deal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deal) {
            header('Location: sponsor.php?action=front');
            exit;
        }

        include __DIR__ . '/../views/back_office/deal/show.php';
    }

    /**
     * INCREMENTER LE NOMBRE DE CLICS ET REDIRIGER VERS SHOW
     */
    public function click(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int)$_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            header('Location: sponsor.php?action=front');
            exit;
        }

        $sql = "UPDATE deals SET click_count = click_count + 1 WHERE idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);

        $currentPath = $_SERVER['PHP_SELF'];
        if (strpos($currentPath, '/deal/') !== false) {
            $redirectPath = dirname($currentPath) . '/show.php?idDeal=' . $idDeal;
        } else {
            if (preg_match('#(/integration2a/f1/projet_chaima/views/back_office)#', $currentPath, $matches)) {
                $basePath = $matches[1];
            } else {
                $basePath = dirname(dirname($currentPath));
            }
            $redirectPath = $basePath . '/deal/show.php?idDeal=' . $idDeal;
        }
        header('Location: ' . $redirectPath);
        exit;
    }

    /**
     * ASSISTANT IA (BACK OFFICE)
     */
    public function assistantIA(): void
    {
        if (!isset($_SESSION['deal_ai_chat'])) {
            $_SESSION['deal_ai_chat'] = [];
        }

        $messages    = $_SESSION['deal_ai_chat'];
        $question    = $_POST['question'] ?? '';
        $errorAi     = null;

        $sql = "SELECT d.idDeal, d.intitule, d.descriptionD, d.reduction, d.prixInitial, 
                       d.click_count, s.nomEntreprise
                FROM deals d
                JOIN sponsors s ON d.idSponsor = s.id
                ORDER BY d.idDeal DESC
                LIMIT 10";
        $stmt = $this->db->query($sql);
        $recentDeals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (isset($_POST['reset_chat'])) {
            $_SESSION['deal_ai_chat'] = [];
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

                $answer = $this->callAI($question, $recentDeals);

                $messages[] = [
                    'role'    => 'assistant',
                    'content' => $answer,
                    'time'    => date('H:i')
                ];

                $_SESSION['deal_ai_chat'] = $messages;
            }
        }

        include __DIR__ . '/../views/back_office/deal/assistant_ia.php';
    }

    /**
     * APPEL OPENAI
     */
    private function callAI(string $question, array $recentDeals): string
    {
        $dealsLines = [];
        foreach ($recentDeals as $d) {
            $dealsLines[] = sprintf(
                "- Titre: %s | Sponsor: %s | Prix: %s DT | Réduction: %s%% | Vues: %d",
                $d['intitule']      ?? 'Sans titre',
                $d['nomEntreprise'] ?? 'Sponsor inconnu',
                $d['prixInitial']   ?? '0',
                $d['reduction']     ?? '0',
                (int)($d['click_count'] ?? 0)
            );
        }
        $dealsContext = implode("\n", $dealsLines);

        $systemPrompt = <<<TXT
Tu es un assistant pour une plateforme de deals étudiants appelée SOLIDA.
Tu disposes d'une liste d'offres (deals) et de sponsors dans le contexte ci-dessous.
Ton rôle :
- Répondre en FRANÇAIS.
- Expliquer clairement et simplement.
- Si la question parle de prix, réductions, popularité, sponsors, etc.,
  base-toi sur les données du contexte (prix, réduction, vues, sponsor).
- Si la question demande des idées (nouvelles offres, stratégies), propose des suggestions adaptées aux étudiants.
TXT;

        $userPrompt = <<<TXT
Contexte (derniers deals) :
$dealsContext

Question de l'utilisateur :
$question
TXT;

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
        if ($response === false) {
            $error = curl_error($ch);
            curl_close($ch);
            return "Erreur lors de l'appel à l'API OpenAI : " . $error;
        }
        curl_close($ch);

        $data = json_decode($response, true);

        if (!empty($data['choices'][0]['message']['content'])) {
            return $data['choices'][0]['message']['content'];
        }

        return "Impossible d'obtenir une réponse valide de l'IA OpenAI.";
    }

    /**
     * REDIRECTION VERS INDEX DES DEALS (BACK OFFICE)
     */
    private function redirectToDealIndex(): void
    {
        $currentPath = $_SERVER['PHP_SELF'];
        
        if (strpos($currentPath, '/deal/') !== false) {
            $redirectPath = dirname($currentPath) . '/index.php';
        } else {
            if (preg_match('#(/integration2a/f1/projet_chaima/views/back_office)#', $currentPath, $matches)) {
                $basePath = $matches[1];
            } else {
                $basePath = dirname(dirname($currentPath));
            }
            $redirectPath = $basePath . '/deal/index.php';
        }
        
        header('Location: ' . $redirectPath);
    }
}

// ================================
// ROUTING LOGIC (si ce fichier est appelé directement)
// ================================
if (basename($_SERVER['PHP_SELF']) === basename(__FILE__)) {
    if (!isset($pdo)) {
        require_once __DIR__ . '/../config/config.php';
    }

    $controller = new DealController($pdo);
    $action = $_POST['action'] ?? $_GET['action'] ?? 'index';

    if (method_exists($controller, $action)) {
        $controller->$action();
    } else {
        $controller->index();
    }
    exit;
}