<?php

require_once __DIR__ . '/../models/Deal.php';

class DealController
{
    private \PDO $db;

    public function __construct()
    {
        $dsn  = 'mysql:host=localhost;dbname=sponsor;charset=utf8';
        $user = 'root';
        $pass = '';

        $this->db = new PDO($dsn, $user, $pass);
        $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // ------- BACKOFFICE : LISTE --------
    public function index(): void
    {
        $stmt = $this->db->query("
            SELECT d.*, s.nomEntreprise
            FROM deals d
            JOIN sponsors s ON d.idSponsor = s.id
            ORDER BY d.idDeal DESC
        ");
        $deals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/deal/index.php';
    }

    // ------- CREATION DEAL (FORM) -------
    public function create(): void
    {
        $stmt = $this->db->query("SELECT id, nomEntreprise FROM sponsors ORDER BY nomEntreprise ASC");
        $sponsors = $stmt->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/deal/create.php';
    }

    // ------- CREATION DEAL (TRAITEMENT) -------
    public function store(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $intitule        = $_POST['intitule'] ?? '';
            $descriptionD    = $_POST['descriptionD'] ?? '';
            $prixInitial     = (float) ($_POST['prixInitial'] ?? 0);
            $reduction       = (float) ($_POST['reduction'] ?? 0);
            $dateDebut       = $_POST['dateDebut'] ?? date('Y-m-d');
            $periodeValidite = (int) ($_POST['periodeValidite'] ?? 1);
            $note            = $_POST['note'] === '' ? null : (int) $_POST['note'];
            $expire          = $_POST['expire'] ?? 'NON';
            $idSponsor       = (int) ($_POST['idSponsor'] ?? 0);

            $sql = "INSERT INTO deals
                    (intitule, descriptionD, prixInitial, reduction, dateDebut,
                     periodeValidite, note, expire, idSponsor, statut)
                    VALUES
                    (:intitule, :descriptionD, :prixInitial, :reduction, :dateDebut,
                     :periodeValidite, :note, :expire, :idSponsor, 'en_attente')";

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
            ]);

            $idDeal = (int) $this->db->lastInsertId();
        }

        header('Location: index1.php?controller=deal&action=pending');
        exit;
    }

    // ------- PAGE "DEMANDE EN ATTENTE" -------
    public function pending(): void
    {
        require __DIR__ . '/../views/deal/pending.php';
    }

    // ------- EDITION DEAL (FORM) -------
    public function edit(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int) $_GET['idDeal'] : 0;

        $stmt = $this->db->prepare("SELECT * FROM deals WHERE idDeal = :id");
        $stmt->execute([':id' => $idDeal]);
        $deal = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$deal) {
            die('Deal introuvable');
        }

        $stmt2 = $this->db->query("SELECT id, nomEntreprise FROM sponsors ORDER BY nomEntreprise ASC");
        $sponsors = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        require __DIR__ . '/../views/deal/edit.php';
    }

    // ------- EDITION DEAL (TRAITEMENT) -------
    public function update(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $idDeal          = (int) $_POST['idDeal'];
            $intitule        = $_POST['intitule'] ?? '';
            $descriptionD    = $_POST['descriptionD'] ?? '';
            $prixInitial     = (float) ($_POST['prixInitial'] ?? 0);
            $reduction       = (float) ($_POST['reduction'] ?? 0);
            $dateDebut       = $_POST['dateDebut'] ?? date('Y-m-d');
            $periodeValidite = (int) ($_POST['periodeValidite'] ?? 1);
            $note            = $_POST['note'] === '' ? null : (int) $_POST['note'];
            $expire          = $_POST['expire'] ?? 'NON';
            $idSponsor       = (int) ($_POST['idSponsor'] ?? 0);
            $statut          = $_POST['statut'] ?? 'en_attente';

            $sql = "UPDATE deals
                    SET intitule = :intitule,
                        descriptionD = :descriptionD,
                        prixInitial = :prixInitial,
                        reduction = :reduction,
                        dateDebut = :dateDebut,
                        periodeValidite = :periodeValidite,
                        note = :note,
                        expire = :expire,
                        idSponsor = :idSponsor,
                        statut = :statut
                    WHERE idDeal = :idDeal";

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
                ':statut'          => $statut,
                ':idDeal'          => $idDeal,
            ]);
        }

        header('Location: index1.php?controller=deal&action=index');
        exit;
    }

    // ------- SUPPRESSION DEAL -------
    public function delete(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int) $_GET['idDeal'] : 0;

        $stmt = $this->db->prepare("DELETE FROM deals WHERE idDeal = :id");
        $stmt->execute([':id' => $idDeal]);

        header('Location: index1.php?controller=deal&action=index');
        exit;
    }

    // ------- ADMIN : ACCEPTER LE DEAL -------
    public function accept(): void
    {
        $idDeal = isset($_GET['idDeal']) ? (int) $_GET['idDeal'] : 0;

        if ($idDeal > 0) {
            $stmt = $this->db->prepare("
                UPDATE deals
                SET statut = 'accepte'
                WHERE idDeal = :id
            ");
            $stmt->execute([':id' => $idDeal]);
        }

        header('Location: index1.php?controller=deal&action=index');
        exit;
    }
}