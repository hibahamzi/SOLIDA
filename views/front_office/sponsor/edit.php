<?php
// Check if this file is being included by the controller or accessed directly
if (!isset($old)) {
    // File is being accessed directly, so we need to set everything up
    require_once '../../../config/config.php';
    
    // Start session only if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérifier si l'utilisateur est connecté et est admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: ../../front_office/sign-in.php');
        exit();
    }
    
    require_once '../../../controllers/SponsorController.php';
    $controller = new SponsorController($pdo);
    
    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
        $controller->edit();
        exit;
    }
    
    // Get sponsor data for editing
    if (isset($_GET['id']) || isset($_GET['action']) && $_GET['action'] === 'edit') {
        $controller->edit();
        exit;
    }
}

// $old doit contenir les infos du sponsor (array associatif)
$old = $old ?? [];
$fieldErrors = $fieldErrors ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - <?= isset($old['id']) ? 'Modifier un sponsor' : 'Créer un sponsor' ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">

    <!-- Template admin -->
    <link rel="stylesheet" href="../../back_office/assets/css/admin.css">

    <style>
        .required { color: red; }
        .error-message {
            color:#e74c3c;
            font-size: 0.85rem;
            margin-top: 4px;
        }
        .is-invalid { border-color: #e74c3c !important; }
        .form-card {
            background:#fff;
            border-radius: 10px;
            padding: 25px;
        }
    </style>
</head>
<body>

    <?php include '../../back_office/includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <?php 
        $pageTitle = isset($old['id']) ? 'Modifier un sponsor' : 'Créer un sponsor';
        include '../../back_office/includes/topbar.php'; 
        ?>

    <div class="content-area">

        <div class="table-container">
            <div class="table-header">
                <h2><?= isset($old['id']) ? 'Modifier un sponsor' : 'Créer un sponsor' ?></h2>
                <a href="index.php"
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            <div class="form-card">
                <form id="sponsorEditForm" method="post"
                      action="index.php">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="id" value="<?= htmlspecialchars($old['id'] ?? '') ?>">

                    <div class="mb-3">
                        <label class="form-label">Nom de l'entreprise <span class="required">*</span></label>
                        <input type="text" name="nomEntreprise" id="nomEntreprise"
                               class="form-control"
                               value="<?= htmlspecialchars($old['nomEntreprise'] ?? '') ?>">
                        <div class="error-message" id="error-nomEntreprise"></div>
                    </div>

                    <div class="grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                        <div>
                            <label class="form-label">Email de contact <span class="required">*</span></label>
                            <input type="email" name="emailContact" id="emailContact"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['emailContact'] ?? '') ?>">
                            <div class="error-message" id="error-emailContact"></div>
                        </div>
                        <div>
                            <label class="form-label">Téléphone (8 chiffres)</label>
                            <input type="text" name="telephone" id="telephone"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['telephone'] ?? '') ?>">
                            <div class="error-message" id="error-telephone"></div>
                        </div>
                    </div>

                    <div class="mb-3" style="margin-top:15px;">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="adresse" id="adresse"
                               class="form-control"
                               value="<?= htmlspecialchars($old['adresse'] ?? '') ?>">
                        <div class="error-message" id="error-adresse"></div>
                    </div>

                    <div class="grid-3" style="display:grid; grid-template-columns:repeat(3,1fr); gap:15px;">
                        <div>
                            <label class="form-label">Type de sponsoring <span class="required">*</span></label>
                            <select name="typeSponsoring" id="typeSponsoring" class="form-control">
                                <option value="">-- Choisir --</option>
                                <option value="Financier" <?= ($old['typeSponsoring'] ?? '') === 'Financier' ? 'selected' : '' ?>>Financier</option>
                                <option value="Matériel" <?= ($old['typeSponsoring'] ?? '') === 'Matériel' ? 'selected' : '' ?>>Matériel</option>
                                <option value="Média"    <?= ($old['typeSponsoring'] ?? '') === 'Média'    ? 'selected' : '' ?>>Média</option>
                            </select>
                            <div class="error-message" id="error-typeSponsoring"></div>
                        </div>
                        <div>
                            <label class="form-label">Montant engagé (DT)</label>
                            <input type="number" step="0.01" name="montantEngage" id="montantEngage"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['montantEngage'] ?? '') ?>">
                            <div class="error-message" id="error-montantEngage"></div>
                        </div>
                        <div>
                            <label class="form-label">Domaine d'activité</label>
                            <input type="text" name="domaineActivite" id="domaineActivite"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['domaineActivite'] ?? '') ?>">
                            <div class="error-message" id="error-domaineActivite"></div>
                        </div>
                    </div>

                    <div class="grid-2" style="display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:15px;">
                        <div>
                            <label class="form-label">Date début partenariat</label>
                            <input type="date" name="dateDebutPartenaire" id="dateDebutPartenaire"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['dateDebutPartenaire'] ?? '') ?>">
                            <div class="error-message" id="error-dateDebutPartenaire"></div>
                        </div>
                        <div>
                            <label class="form-label">Date fin partenariat</label>
                            <input type="date" name="dateFinPartenaire" id="dateFinPartenaire"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['dateFinPartenaire'] ?? '') ?>">
                            <div class="error-message" id="error-dateFinPartenaire"></div>
                        </div>
                    </div>

                    <div class="mb-3" style="margin-top:15px;">
                        <label class="form-label">Statut <span class="required">*</span></label>
                        <select name="statut" id="statut" class="form-control">
                            <option value="">-- Choisir --</option>
                            <option value="actif"   <?= ($old['statut'] ?? '') === 'actif'   ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= ($old['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        </select>
                        <div class="error-message" id="error-statut"></div>
                    </div>

                    <div style="display:flex; justify-content:space-between; margin-top:20px;">
                        <a href="index.php"
                           class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Enregistrer
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

</div>

<script>
const form = document.getElementById('sponsorEditForm');
form.addEventListener('submit', function(e) {
    let hasError = false;

    const nomEntreprise  = document.getElementById('nomEntreprise');
    const emailContact   = document.getElementById('emailContact');
    const telephone      = document.getElementById('telephone');
    const typeSponsoring = document.getElementById('typeSponsoring');
    const montantEngage  = document.getElementById('montantEngage');
    const statut         = document.getElementById('statut');

    const fields = [nomEntreprise, emailContact, telephone, typeSponsoring, montantEngage, statut];
    const errorIds = [
        'error-nomEntreprise','error-emailContact','error-telephone',
        'error-typeSponsoring','error-montantEngage','error-statut'
    ];

    fields.forEach((field, i) => {
        field.classList.remove('is-invalid');
        document.getElementById(errorIds[i]).innerText = '';
    });

    if (nomEntreprise.value.trim() === '') {
        hasError = true;
        nomEntreprise.classList.add('is-invalid');
        document.getElementById('error-nomEntreprise').innerText =
            "Le nom de l'entreprise est obligatoire.";
    }

    const emailVal = emailContact.value.trim();
    if (emailVal === '') {
        hasError = true;
        emailContact.classList.add('is-invalid');
        document.getElementById('error-emailContact').innerText =
            "L'email de contact est obligatoire.";
    } else {
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!regexEmail.test(emailVal)) {
            hasError = true;
            emailContact.classList.add('is-invalid');
            document.getElementById('error-emailContact').innerText =
                "Format d'email invalide.";
        }
    }

    const telVal = telephone.value.trim();
    if (telVal !== '') {
        const regexTel = /^\d{8}$/;
        if (!regexTel.test(telVal)) {
            hasError = true;
            telephone.classList.add('is-invalid');
            document.getElementById('error-telephone').innerText =
                "Le téléphone doit contenir exactement 8 chiffres.";
        }
    }

    if (typeSponsoring.value.trim() === '') {
        hasError = true;
        typeSponsoring.classList.add('is-invalid');
        document.getElementById('error-typeSponsoring').innerText =
            "Le type de sponsoring est obligatoire.";
    }

    const montantVal = montantEngage.value.trim();
    if (montantVal !== '') {
        if (isNaN(montantVal) || Number(montantVal) <= 0) {
            hasError = true;
            montantEngage.classList.add('is-invalid');
            document.getElementById('error-montantEngage').innerText =
                "Le montant engagé doit être un nombre positif.";
        }
    }

    if (statut.value.trim() === '') {
        hasError = true;
        statut.classList.add('is-invalid');
        document.getElementById('error-statut').innerText =
            "Le statut est obligatoire.";
    }

    if (hasError) e.preventDefault();
});
</script>

</body>
</html>