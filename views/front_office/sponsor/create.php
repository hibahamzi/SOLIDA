<?php
// This file can be included in index.php (front office) or accessed directly (back office)
// Variables $old and $fieldErrors should be set by the controller
$old = $old ?? [];
$fieldErrors = $fieldErrors ?? [];

// Detect if we're in back office context
// Check if this is being included from front office index.php
$isFrontOfficeInclude = isset($GLOBALS['isFrontOfficeInclude']) || 
                        (isset($_GET['section']) && $_GET['section'] === 'sponsor');

// Start session if not started (will be started by config.php if needed)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If accessed directly (not from index.php) and user is admin, it's back office
$currentPath = $_SERVER['PHP_SELF'];
$isDirectAccess = strpos($currentPath, '/sponsor/create.php') !== false;
$isBackOffice = !$isFrontOfficeInclude && 
                $isDirectAccess &&
                isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';

// If back office, we need to set up the page structure
if ($isBackOffice && !isset($backOfficeSetup)) {
    $backOfficeSetup = true;
    
    // Check if accessed directly
    if (!isset($pdo)) {
        require_once '../../../config/config.php';
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: ../../front_office/sign-in.php');
            exit();
        }
        require_once '../../../controllers/SponsorController.php';
        $controller = new SponsorController($pdo);
        
        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'store') {
            $controller->create();
            exit;
        }
        
        // For GET request, get form data from controller
        // The controller will set $GLOBALS['old'] and $GLOBALS['fieldErrors']
        $controller->create();
        // If controller included the view, we exit here
        // Otherwise, get the variables
        if (isset($GLOBALS['old'])) {
            $old = $GLOBALS['old'];
            $fieldErrors = $GLOBALS['fieldErrors'] ?? [];
        } else {
            $old = [];
            $fieldErrors = [];
        }
    }
}
?>
<?php if ($isBackOffice): ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - Créer un Sponsor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="../../back_office/assets/css/admin.css">
    
    <style>
        .required { color: red; }
        .error-message {
            color: #e74c3c;
            font-size: 0.85rem;
            margin-top: 4px;
        }
        .is-invalid { border-color: #e74c3c !important; }
        .form-card {
            background: #fff;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <?php include '../../back_office/includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <?php 
        $pageTitle = 'Créer un Sponsor';
        include '../../back_office/includes/topbar.php'; 
        ?>
        
        <div class="content-area">
            <div class="table-container">
                <div class="table-header">
                    <h2>Créer un nouveau Sponsor</h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                
                <div class="form-card">
<?php else: ?>
<style>
.sponsor-create-section {
    padding: 40px 0;
    background: #f8f9fa;
    min-height: 60vh;
}
.sponsor-form-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow: hidden;
}
.sponsor-form-header {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    color: white;
    padding: 25px 30px;
}
.sponsor-form-header h2 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 600;
}
.sponsor-form-body {
    padding: 30px;
}
.form-label {
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
}
.form-control, .form-select {
    border: 1px solid #ddd;
    border-radius: 6px;
    padding: 10px 15px;
    transition: border-color 0.3s, box-shadow 0.3s;
}
.form-control:focus, .form-select:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}
.is-invalid {
    border-color: #dc3545;
}
.text-danger.small {
    font-size: 0.875rem;
    margin-top: 5px;
}
</style>

<!-- Sponsor Creation Section -->
<section class="sponsor-create-section">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-9">
                <a href="index.php?section=sponsors" class="btn btn-secondary mb-4">
                    <i class="fas fa-arrow-left"></i> Retour aux sponsors
                </a>
                <div class="sponsor-form-card">
                    <div class="sponsor-form-header">
                        <h2><i class="fas fa-handshake"></i> Devenir sponsor de SOLIDA</h2>
                    </div>
                    <?php if ($isBackOffice): ?>
                    <p class="mb-4">
                        Remplissez ce formulaire pour créer un nouveau sponsor.
                        Les champs marqués d'une <span class="text-danger">*</span> sont obligatoires.
                    </p>
                    <?php else: ?>
                    <div class="sponsor-form-body">
                        <p class="mb-4 text-muted">
                            Remplissez ce formulaire pour proposer un partenariat avec notre association étudiante.
                            Les champs marqués d'une <span class="text-danger fw-bold">*</span> sont obligatoires.
                        </p>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($fieldErrors['general'])): ?>
                        <div class="alert alert-danger">
                            <?= htmlspecialchars($fieldErrors['general'], ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form id="sponsorCreateForm" method="post"
                          action="<?= $isBackOffice ? 'create.php' : 'index.php?section=sponsor&action=create' ?>"
                          enctype="multipart/form-data">
                        <input type="hidden" name="action" value="store">

                        <!-- NOM ENTREPRISE -->
                        <div class="mb-3">
                            <label class="form-label">Nom de l'entreprise <span class="text-danger">*</span></label>
                            <input type="text" name="nomEntreprise" id="nomEntreprise"
                                   class="form-control <?= isset($fieldErrors['nomEntreprise']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['nomEntreprise'] ?? '') ?>">
                            <?php if (isset($fieldErrors['nomEntreprise'])): ?>
                                <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['nomEntreprise']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- EMAIL + TELEPHONE -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email de contact <span class="text-danger">*</span></label>
                                <input type="email" name="emailContact" id="emailContact"
                                       class="form-control <?= isset($fieldErrors['emailContact']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($old['emailContact'] ?? '') ?>">
                                <?php if (isset($fieldErrors['emailContact'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['emailContact']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Téléphone (8 chiffres)</label>
                                <input type="text" name="telephone" id="telephone"
                                       class="form-control <?= isset($fieldErrors['telephone']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($old['telephone'] ?? '') ?>"
                                       maxlength="8" pattern="[0-9]{8}">
                                <?php if (isset($fieldErrors['telephone'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['telephone']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- ADRESSE -->
                        <div class="mb-3">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" id="adresse"
                                   class="form-control <?= isset($fieldErrors['adresse']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['adresse'] ?? '') ?>">
                            <?php if (isset($fieldErrors['adresse'])): ?>
                                <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['adresse']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- TYPE SPONSORING + MONTANT -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Type de sponsoring <span class="text-danger">*</span></label>
                                <select name="typeSponsoring" id="typeSponsoring"
                                        class="form-control <?= isset($fieldErrors['typeSponsoring']) ? 'is-invalid' : '' ?>">
                                    <option value="">-- Choisir --</option>
                                    <option value="Or" <?= (isset($old['typeSponsoring']) && $old['typeSponsoring'] === 'Or') ? 'selected' : '' ?>>Or</option>
                                    <option value="Argent" <?= (isset($old['typeSponsoring']) && $old['typeSponsoring'] === 'Argent') ? 'selected' : '' ?>>Argent</option>
                                    <option value="Bronze" <?= (isset($old['typeSponsoring']) && $old['typeSponsoring'] === 'Bronze') ? 'selected' : '' ?>>Bronze</option>
                                    <option value="Partenaire" <?= (isset($old['typeSponsoring']) && $old['typeSponsoring'] === 'Partenaire') ? 'selected' : '' ?>>Partenaire</option>
                                </select>
                                <?php if (isset($fieldErrors['typeSponsoring'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['typeSponsoring']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Montant engagé (DT)</label>
                                <input type="number" step="0.01" name="montantEngage" id="montantEngage"
                                       class="form-control <?= isset($fieldErrors['montantEngage']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($old['montantEngage'] ?? '') ?>">
                                <?php if (isset($fieldErrors['montantEngage'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['montantEngage']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- DOMAINE ACTIVITE -->
                        <div class="mb-3">
                            <label class="form-label">Domaine d'activité</label>
                            <input type="text" name="domaineActivite" id="domaineActivite"
                                   class="form-control <?= isset($fieldErrors['domaineActivite']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['domaineActivite'] ?? '') ?>">
                            <?php if (isset($fieldErrors['domaineActivite'])): ?>
                                <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['domaineActivite']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- LOGO -->
                        <div class="mb-3">
                            <label class="form-label">Logo de l'entreprise</label>
                            <input type="file" name="logo" id="logo" accept="image/*"
                                   class="form-control <?= isset($fieldErrors['logo']) ? 'is-invalid' : '' ?>">
                            <?php if (isset($fieldErrors['logo'])): ?>
                                <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['logo']) ?></div>
                            <?php endif; ?>
                            <small class="form-text text-muted">Formats acceptés: JPG, PNG, GIF (max 5MB)</small>
                            <div id="logo-preview" style="margin-top: 10px;"></div>
                        </div>

                        <!-- CONTRAT URL -->
                        <div class="mb-3">
                            <label class="form-label">URL du contrat (optionnel)</label>
                            <input type="url" name="contratUrl" id="contratUrl"
                                   class="form-control <?= isset($fieldErrors['contratUrl']) ? 'is-invalid' : '' ?>"
                                   value="<?= htmlspecialchars($old['contratUrl'] ?? '') ?>"
                                   placeholder="https://...">
                            <?php if (isset($fieldErrors['contratUrl'])): ?>
                                <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['contratUrl']) ?></div>
                            <?php endif; ?>
                        </div>

                        <!-- DATES PARTENARIAT -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de début de partenariat <span class="text-danger">*</span></label>
                                <input type="date" name="dateDebutPartenaire" id="dateDebutPartenaire"
                                       class="form-control <?= isset($fieldErrors['dateDebutPartenaire']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($old['dateDebutPartenaire'] ?? '') ?>">
                                <?php if (isset($fieldErrors['dateDebutPartenaire'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['dateDebutPartenaire']) ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date de fin de partenariat <span class="text-danger">*</span></label>
                                <input type="date" name="dateFinPartenaire" id="dateFinPartenaire"
                                       class="form-control <?= isset($fieldErrors['dateFinPartenaire']) ? 'is-invalid' : '' ?>"
                                       value="<?= htmlspecialchars($old['dateFinPartenaire'] ?? '') ?>">
                                <?php if (isset($fieldErrors['dateFinPartenaire'])): ?>
                                    <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['dateFinPartenaire']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- STATUT -->
                        <div class="mb-3">
                            <label class="form-label">Statut <span class="text-danger">*</span></label>
                            <?php if ($isBackOffice): ?>
                            <select name="statut" id="statut" class="form-control <?= isset($fieldErrors['statut']) ? 'is-invalid' : '' ?>">
                                <option value="">-- Choisir --</option>
                                <option value="Actif" <?= (isset($old['statut']) && $old['statut'] === 'Actif') ? 'selected' : '' ?>>Actif</option>
                                <option value="Inactif" <?= (isset($old['statut']) && ($old['statut'] === 'Inactif' || empty($old['statut']))) ? 'selected' : '' ?>>Inactif</option>
                                <option value="En attente" <?= (isset($old['statut']) && $old['statut'] === 'En attente') ? 'selected' : '' ?>>En attente</option>
                            </select>
                            <?php if (isset($fieldErrors['statut'])): ?>
                                <div class="text-danger small mt-1"><?= htmlspecialchars($fieldErrors['statut']) ?></div>
                            <?php endif; ?>
                            <?php else: ?>
                            <input type="text" class="form-control" value="En attente d'approbation" disabled>
                            <input type="hidden" name="statut" value="inactif">
                            <div class="form-text text-muted">Votre demande de sponsoring sera examinée par un administrateur.</div>
                            <?php endif; ?>
                        </div>

                        <?php if (!$isBackOffice): ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Votre demande de sponsoring sera soumise pour validation. Vous serez notifié une fois qu'elle sera approuvée par l'administration.
                        </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <?php if ($isBackOffice): ?>
                            <a href="index.php" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Créer le sponsor
                            </button>
                            <?php else: ?>
                            <a href="index.php?section=sponsors" class="btn btn-secondary btn-lg">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="fas fa-paper-plane"></i> Soumettre la demande
                            </button>
                            <?php endif; ?>
                        </div>
                    </form>
                    <?php if ($isBackOffice): ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
                    <?php else: ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
                    <?php endif; ?>

<script>
// Logo preview
const logoInput = document.getElementById('logo');
if (logoInput) {
    logoInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('logo-preview');
        if (file) {
            // Check file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('Le fichier est trop volumineux. Taille maximale: 5MB');
                this.value = '';
                preview.innerHTML = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = '<div class="mt-2"><img src="' + e.target.result + '" class="img-thumbnail rounded" style="max-width: 200px; max-height: 200px; object-fit: contain; border: 2px solid #28a745;"></div>';
            };
            reader.readAsDataURL(file);
        } else {
            preview.innerHTML = '';
        }
    });
}

// Form validation
const sponsorForm = document.getElementById('sponsorCreateForm');
if (sponsorForm) {
    sponsorForm.addEventListener('submit', function(e) {
        let hasError = false;

        // Reset previous errors
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.text-danger.small').forEach(el => el.remove());

    // Nom entreprise
    const nomEntreprise = document.getElementById('nomEntreprise');
    if (nomEntreprise.value.trim() === '') {
        hasError = true;
        nomEntreprise.classList.add('is-invalid');
    }

    // Email
    const emailContact = document.getElementById('emailContact');
    if (emailContact.value.trim() === '' || !emailContact.value.includes('@')) {
        hasError = true;
        emailContact.classList.add('is-invalid');
    }

    // Telephone (if provided, must be 8 digits)
    const telephone = document.getElementById('telephone');
    if (telephone.value.trim() !== '' && !/^\d{8}$/.test(telephone.value.trim())) {
        hasError = true;
        telephone.classList.add('is-invalid');
    }

    // Type sponsoring
    const typeSponsoring = document.getElementById('typeSponsoring');
    if (typeSponsoring.value === '') {
        hasError = true;
        typeSponsoring.classList.add('is-invalid');
    }

    // Dates
    const dateDebut = document.getElementById('dateDebutPartenaire');
    const dateFin = document.getElementById('dateFinPartenaire');
    if (dateDebut.value === '') {
        hasError = true;
        dateDebut.classList.add('is-invalid');
    }
    if (dateFin.value === '') {
        hasError = true;
        dateFin.classList.add('is-invalid');
    }
    if (dateDebut.value && dateFin.value && new Date(dateFin.value) < new Date(dateDebut.value)) {
        hasError = true;
        dateFin.classList.add('is-invalid');
    }

        if (hasError) {
            e.preventDefault();
            alert('Veuillez corriger les erreurs dans le formulaire.');
        }
    });
}
</script>
<?php endif; ?>
