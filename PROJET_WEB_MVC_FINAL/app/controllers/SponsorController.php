<?php

require_once __DIR__ . '/../models/Sponsor.php';

class SponsorController
{
    private PDO $pdo;

    // On reçoit $pdo depuis index1.php
    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    // =======================
    // PAGE FRONT (PUBLIC) - SPONSORS + OFFRES ACCEPTÉES
    // =======================
    public function front()
    {
        // 1) Récupérer tous les sponsors
        $sql = "SELECT * FROM sponsors";
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
                $row['statut'] ?? null
            );
        }

        // 2) Récupérer les OFFRES ACCEPTÉES avec nom du sponsor
        $sqlDeals = "
            SELECT d.*, s.nomEntreprise
            FROM deals d
            JOIN sponsors s ON d.idSponsor = s.id
            WHERE d.statut = 'accepte'
            ORDER BY d.dateDebut DESC
        ";
        $stmtDeals = $this->pdo->query($sqlDeals);
        $offers = $stmtDeals->fetchAll(PDO::FETCH_ASSOC);

        // 3) Envoyer à la vue
        require __DIR__ . '/../views/sponsor/front.php';
    }

    // =======================
    // LISTE DES SPONSORS (BACKOFFICE)
    // =======================
    public function index()
    {
        $sql = "SELECT * FROM sponsors";
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
                $row['statut'] ?? null
            );
        }

        require __DIR__ . '/../views/sponsor/index.php';
    }

    // =======================
    // CRÉATION D'UN SPONSOR
    // =======================
    public function create()
    {
        $fieldErrors = [];
        $old = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Champs texte
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

            // Validation de base
            if ($old['nomEntreprise'] === '') {
                $fieldErrors['nomEntreprise'] = "Le nom de l'entreprise est obligatoire.";
            }

            if ($old['emailContact'] === '' || !filter_var($old['emailContact'], FILTER_VALIDATE_EMAIL)) {
                $fieldErrors['emailContact'] = "Email invalide.";
            }

            if ($old['telephone'] !== '' && !preg_match('/^\d{8}$/', $old['telephone'])) {
                $fieldErrors['telephone'] = "Le téléphone doit contenir exactement 8 chiffres.";
            }

            // Gestion du logo (optionnel)
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

            // Si pas d'erreurs → insertion
            if (empty($fieldErrors)) {
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
                    $old['statut']
                );

                $sql = "INSERT INTO sponsors
                        (nomEntreprise, emailContact, telephone, adresse, typeSponsoring,
                         montantEngage, domaineActivite, logoUrl, contratUrl,
                         dateDebutPartenaire, dateFinPartenaire, statut)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

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
                ]);

                header('Location: index1.php?controller=sponsor&action=front');
                exit;
            }
        }

        require __DIR__ . '/../views/sponsor/create.php';
    }

    // =======================
    // MODIFICATION
    // =======================
    public function edit()
    {
        if (!isset($_GET['id'])) {
            header('Location: index1.php?controller=sponsor&action=index');
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
            $row['statut']
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
                $sql = "UPDATE sponsors SET
                            nomEntreprise = ?,
                            emailContact = ?,
                            telephone = ?,
                            adresse = ?,
                            typeSponsoring = ?,
                            montantEngage = ?,
                            domaineActivite = ?,
                            logoUrl = ?,
                            contratUrl = ?,
                            dateDebutPartenaire = ?,
                            dateFinPartenaire = ?,
                            statut = ?
                        WHERE id = ?";

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
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
                    $id,
                ]);

                header('Location: index1.php?controller=sponsor&action=index');
                exit;
            }
        }

        require __DIR__ . '/../views/sponsor/edit.php';
    }

    // =======================
    // SUPPRESSION
    // =======================
    public function delete()
    {
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $stmt = $this->pdo->prepare("DELETE FROM sponsors WHERE id = ?");
            $stmt->execute([$id]);
        }

        header('Location: index1.php?controller=sponsor&action=index');
        exit;
    }
}