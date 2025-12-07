<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../../PHPMailer/src/phpmailer.php';
require __DIR__ . '/../../PHPMailer/src/SMTP.php';
require __DIR__ . '/../../PHPMailer/src/Exception.php';

class DealController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function index(): void
    {
        $sql = "SELECT d.*, s.nomEntreprise 
                FROM deals d
                JOIN sponsors s ON d.IdSponsor = s.id
                ORDER BY d.idDeal DESC";
        $stmt = $this->db->query($sql);
        $deals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/deal/index.php';
    }

    public function create(): void
    {
        $sqlSponsors = "SELECT id, nomEntreprise FROM sponsors ORDER BY nomEntreprise ASC";
        $stmtSponsors = $this->db->query($sqlSponsors);
        $sponsors = $stmtSponsors->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/deal/create.php';
    }

    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index1.php?controller=deal&action=pending');
            exit;
        }

        $intitule        = $_POST['intitule']        ?? '';
        $descriptionD    = $_POST['description']     ?? '';
        $prixinitial     = $_POST['prixinitial']     ?? 0;
        $reduction       = $_POST['reduction']       ?? 0;
        $dateDebut       = $_POST['dateDebut']       ?? null;
        $periodeValidite = $_POST['periodeValidite'] ?? 0;
        $idSponsor       = isset($_POST['idSponsor']) ? (int)$_POST['idSponsor'] : null;
        $statut          = 'en_attente';
        $expire          = 'NON';
        $note            = null;
        $hasCoupon       = $_POST['has_coupon']      ?? 'NON';
        $couponCode      = null;

        // Coupon
        if ($hasCoupon === 'OUI') {
            $couponCode = (string)random_int(100000, 999999);
        } else {
            $hasCoupon  = 'NON';
        }

        $sql = "INSERT INTO deals (
                    intitule,
                    descriptionD,
                    prixinitial,
                    reduction,
                    dateDebut,
                    periodeValidite,
                    note,
                    expire,
                    IdSponsor,
                    statut,
                    click_count,
                    has_coupon,
                    coupon_code
                ) VALUES (
                    :intitule,
                    :descriptionD,
                    :prixinitial,
                    :reduction,
                    :dateDebut,
                    :periodeValidite,
                    :note,
                    :expire,
                    :IdSponsor,
                    :statut,
                    0,
                    :has_coupon,
                    :coupon_code
                )";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':intitule'        => $intitule,
            ':descriptionD'    => $descriptionD,
            ':prixinitial'     => $prixinitial,
            ':reduction'       => $reduction,
            ':dateDebut'       => $dateDebut,
            ':periodeValidite' => $periodeValidite,
            ':note'            => $note,
            ':expire'          => $expire,
            ':IdSponsor'       => $idSponsor,
            ':statut'          => $statut,
            ':has_coupon'      => $hasCoupon,
            ':coupon_code'     => $couponCode,
        ]);

        // après création -> liste des deals en attente
        header('Location: index1.php?controller=deal&action=pending');
        exit;
    }

    public function edit(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int)$_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            header('Location: index1.php?controller=deal&action=index');
            exit;
        }

        $sql = "SELECT * FROM deals WHERE idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);
        $deal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deal) {
            header('Location: index1.php?controller=deal&action=index');
            exit;
        }

        $sqlSponsors = "SELECT id, nomEntreprise FROM sponsors ORDER BY nomEntreprise ASC";
        $stmtSponsors = $this->db->query($sqlSponsors);
        $sponsors = $stmtSponsors->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/deal/edit.php';
    }

    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: index1.php?controller=deal&action=index');
            exit;
        }

        $idDeal          = isset($_POST['idDeal']) ? (int)$_POST['idDeal'] : 0;
        $intitule        = $_POST['intitule']        ?? '';
        $descriptionD    = $_POST['description']     ?? '';
        $prixinitial     = $_POST['prixinitial']     ?? 0;
        $reduction       = $_POST['reduction']       ?? 0;
        $dateDebut       = $_POST['dateDebut']       ?? null;
        $periodeValidite = $_POST['periodeValidite'] ?? 0;
        $idSponsor       = isset($_POST['idSponsor']) ? (int)$_POST['idSponsor'] : null;
        $statut          = $_POST['statut']          ?? 'en_attente';
        $expire          = $_POST['expire']          ?? 'NON';
        $hasCoupon       = $_POST['has_coupon']      ?? 'NON';

        // Anciennes valeurs coupon + note
        $sql = "SELECT coupon_code, note FROM deals WHERE idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);
        $oldData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$oldData) {
            header('Location: index1.php?controller=deal&action=index');
            exit;
        }

        $couponCode = $oldData['coupon_code'];
        $note       = $oldData['note'];

        // Gestion du coupon à la mise à jour
        if ($hasCoupon === 'OUI' && empty($couponCode)) {
            $couponCode = (string)random_int(100000, 999999);
        }

        if ($hasCoupon === 'NON') {
            $couponCode = null;
        }

        $sqlUpdate = "UPDATE deals
                      SET intitule        = :intitule,
                          descriptionD    = :descriptionD,
                          prixinitial     = :prixinitial,
                          reduction       = :reduction,
                          dateDebut       = :dateDebut,
                          periodeValidite = :periodeValidite,
                          note            = :note,
                          expire          = :expire,
                          IdSponsor       = :IdSponsor,
                          statut          = :statut,
                          has_coupon      = :has_coupon,
                          coupon_code     = :coupon_code
                      WHERE idDeal        = :idDeal";

        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':intitule'        => $intitule,
            ':descriptionD'    => $descriptionD,
            ':prixinitial'     => $prixinitial,
            ':reduction'       => $reduction,
            ':dateDebut'       => $dateDebut,
            ':periodeValidite' => $periodeValidite,
            ':note'            => $note,
            ':expire'          => $expire,
            ':IdSponsor'       => $idSponsor,
            ':statut'          => $statut,
            ':has_coupon'      => $hasCoupon,
            ':coupon_code'     => $couponCode,
            ':idDeal'          => $idDeal,
        ]);

        // ici tu restes sur l'index backoffice
        header('Location: index1.php?controller=deal&action=index');
        exit;
    }

    public function delete(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int)$_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            header('Location: index1.php?controller=deal&action=index');
            exit;
        }

        $sql = "DELETE FROM deals WHERE idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);

        header('Location: index1.php?controller=deal&action=index');
        exit;
    }

    public function pending(): void
    {
        $sql = "SELECT d.*, s.nomEntreprise
                FROM deals d
                JOIN sponsors s ON d.IdSponsor = s.id
                WHERE d.statut = 'en_attente'
                ORDER BY d.idDeal DESC";
        $stmt = $this->db->query($sql);
        $deals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        include __DIR__ . '/../views/deal/pending.php';
    }

    public function accept(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int) $_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            header('Location: index1.php?controller=deal&action=pending');
            exit;
        }

        $sql = "SELECT d.*, s.emailContact AS emailSponsor, s.nomEntreprise
                FROM deals d
                JOIN sponsors s ON d.IdSponsor = s.id
                WHERE d.idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);
        $deal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deal) {
            header('Location: index1.php?controller=deal&action=pending');
            exit;
        }

        $sqlUpdate = "UPDATE deals SET statut = 'accepte' WHERE idDeal = :idDeal";
        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->execute([':idDeal' => $idDeal]);

        $emailSponsor = $deal['emailSponsor'] ?? null;

        if (!empty($emailSponsor)) {
            $nomSponsor  = $deal['nomEntreprise'] ?? 'Votre entreprise';
            $intitule    = $deal['intitule']      ?? 'votre offre';
            $subject     = "Votre offre a été acceptée sur SOLIDA";

            $urlFront = "http://localhost:8080/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=front";

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
                $mail->Password   = 'rgvj wehx qbvi jzql';
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
                // error_log('Erreur envoi mail: ' . $mail->ErrorInfo);
            }
        }

        // après acceptation -> rester sur la liste des en attente
        header('Location: index1.php?controller=deal&action=pending');
        exit;
    }

    public function reject(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int)$_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            header('Location: index1.php?controller=deal&action=pending');
            exit;
        }

        $sql = "UPDATE deals SET statut = 'refuse' WHERE idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);

        header('Location: index1.php?controller=deal&action=pending');
        exit;
    }

    public function show(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int)$_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            header('Location: index1.php?controller=sponsor&action=front');
            exit;
        }

        // IMPORTANT : plus de s.logo ici, car la colonne n'existe pas
        $sql = "SELECT d.*, s.nomEntreprise
                FROM deals d
                JOIN sponsors s ON d.IdSponsor = s.id
                WHERE d.idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);
        $deal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deal) {
            header('Location: index1.php?controller=sponsor&action=front');
            exit;
        }

        include __DIR__ . '/../views/deal/show.php';
    }

    public function click(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int)$_GET['idDeal'] : 0;

        if ($idDeal <= 0) {
            header('Location: index1.php?controller=sponsor&action=front');
            exit;
        }

        $sql = "UPDATE deals SET click_count = click_count + 1 WHERE idDeal = :idDeal";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':idDeal' => $idDeal]);

        header('Location: index1.php?controller=deal&action=show&idDeal=' . $idDeal);
        exit;
    }
}