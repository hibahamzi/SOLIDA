<?php
// app/controllers/SponsorController.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Sponsor.php';

class SponsorController
{
    private $sponsorModel;

    public function __construct()
    {
        $db  = new Database();
        $pdo = $db->getConnection();
        $this->sponsorModel = new Sponsor($pdo);
    }

    public function index()
    {
        $sponsors = $this->sponsorModel->all();
        require __DIR__ . '/../views/sponsor/index.php';
    }

    public function create()
    {
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['nomEntreprise']))   $errors[] = "Nom entreprise obligatoire";
            if (empty($_POST['emailContact']))    $errors[] = "Email obligatoire";
            if (empty($_POST['typeSponsoring']))  $errors[] = "Type de sponsoring obligatoire";
            if (empty($_POST['statut']))          $errors[] = "Statut obligatoire";

            if (empty($errors)) {
                $data = [
                    'nomEntreprise'       => $_POST['nomEntreprise'],
                    'emailContact'        => $_POST['emailContact'],
                    'telephone'           => $_POST['telephone'] ?? null,
                    'adresse'             => $_POST['adresse'] ?? null,
                    'typeSponsoring'      => $_POST['typeSponsoring'],
                    'montantEngage'       => $_POST['montantEngage'] ?? null,
                    'domaineActivite'     => $_POST['domaineActivite'] ?? null,
                    'logoUrl'             => $_POST['logoUrl'] ?? null,
                    'contratUrl'          => $_POST['contratUrl'] ?? null,
                    'dateDebutPartenaire' => $_POST['dateDebutPartenaire'] ?: null,
                    'dateFinPartenaire'   => $_POST['dateFinPartenaire'] ?: null,
                    'statut'              => $_POST['statut'],
                ];

                $this->sponsorModel->create($data);
                header('Location: index1.php?controller=sponsor&action=index');
                exit;
            }
        }

        require __DIR__ . '/../views/sponsor/create.php';
    }

    public function edit()
    {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: index1.php?controller=sponsor&action=index');
            exit;
        }

        $sponsor = $this->sponsorModel->find((int)$id);
        if (!$sponsor) {
            die('Sponsor introuvable');
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_POST['nomEntreprise']))   $errors[] = "Nom entreprise obligatoire";
            if (empty($_POST['emailContact']))    $errors[] = "Email obligatoire";
            if (empty($_POST['typeSponsoring']))  $errors[] = "Type de sponsoring obligatoire";
            if (empty($_POST['statut']))          $errors[] = "Statut obligatoire";

            if (empty($errors)) {
                $data = [
                    'nomEntreprise'       => $_POST['nomEntreprise'],
                    'emailContact'        => $_POST['emailContact'],
                    'telephone'           => $_POST['telephone'] ?? null,
                    'adresse'             => $_POST['adresse'] ?? null,
                    'typeSponsoring'      => $_POST['typeSponsoring'],
                    'montantEngage'       => $_POST['montantEngage'] ?? null,
                    'domaineActivite'     => $_POST['domaineActivite'] ?? null,
                    'logoUrl'             => $_POST['logoUrl'] ?? null,
                    'contratUrl'          => $_POST['contratUrl'] ?? null,
                    'dateDebutPartenaire' => $_POST['dateDebutPartenaire'] ?: null,
                    'dateFinPartenaire'   => $_POST['dateFinPartenaire'] ?: null,
                    'statut'              => $_POST['statut'],
                ];

                $this->sponsorModel->update((int)$id, $data);
                header('Location: index1.php?controller=sponsor&action=index');
                exit;
            }
        }

        // $sponsor dispo dans la vue
        require __DIR__ . '/../views/sponsor/edit.php';
    }

    public function delete()
    {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->sponsorModel->delete((int)$id);
        }
        header('Location: index1.php?controller=sponsor&action=index');
        exit;
    }
}