<?php
// Check if this file is being included by the controller or accessed directly
if (!isset($sponsors)) {
    // File is being accessed directly, so we need to set everything up
    require_once '../../../config/config.php';
    
    // Start session only if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Vérifier si l'utilisateur est connecté et est admin
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
        header('Location: ../front_office/sign-in.php');
        exit();
    }
    
    require_once '../../../controllers/DealController.php';
    $controller = new DealController($pdo);
    $controller->create();
    // After calling create(), the controller includes this file again, so we exit here
    exit;
}

// If we reach here, the file was included by the controller
// $sponsors is already set by DealController->create()
$sponsors = $sponsors ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - Créer un Deal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    
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

    <?php include '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <?php 
        $pageTitle = 'Créer un Deal';
        include '../includes/topbar.php'; 
        ?>
        
        <div class="content-area">
            <div class="table-container">
                <div class="table-header">
                    <h2>Créer un nouveau Deal</h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                
                <div class="form-card">
                    <form id="dealCreateForm" method="post" action="index.php">
                        <input type="hidden" name="action" value="store">
                        
                        <!-- INTITULE -->
                        <div class="mb-3">
                            <label class="form-label">Intitulé de l'offre <span class="required">*</span></label>
                            <input type="text" name="intitule" id="intitule" class="form-control">
                            <div class="error-message" id="error-intitule"></div>
                        </div>

                        <!-- SPONSOR + PRIX INITIAL -->
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Sponsor concerné <span class="required">*</span></label>
                                <select name="idSponsor" id="idSponsor" class="form-control">
                                    <option value="">-- Choisir un sponsor --</option>
                                    <?php foreach ($sponsors as $s): ?>
                                        <option value="<?= (int)$s['id'] ?>">
                                            <?= htmlspecialchars($s['nomEntreprise']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="error-message" id="error-idSponsor"></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Prix initial (DT) <span class="required">*</span></label>
                                <input type="text" name="prixInitial" id="prixInitial" class="form-control">
                                <div class="error-message" id="error-prixInitial"></div>
                            </div>
                        </div>

                        <!-- REDUCTION -->
                        <div class="mb-3">
                            <label class="form-label">Réduction (%) <span class="required">*</span></label>
                            <input type="text" name="reduction" id="reduction" class="form-control">
                            <div class="error-message" id="error-reduction"></div>
                        </div>

                        <!-- DESCRIPTION -->
                        <div class="mb-3">
                            <label class="form-label">Description de l'offre <span class="required">*</span></label>
                            <textarea name="descriptionD" id="descriptionD" rows="4" class="form-control"></textarea>
                            <div class="error-message" id="error-descriptionD"></div>
                        </div>

                        <!-- DATES + PÉRIODE -->
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Date de début <span class="required">*</span></label>
                                <input type="date" name="dateDebut" id="dateDebut" class="form-control">
                                <div class="error-message" id="error-dateDebut"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Période de validité (jours) <span class="required">*</span></label>
                                <input type="text" name="periodeValidite" id="periodeValidite" class="form-control">
                                <div class="error-message" id="error-periodeValidite"></div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Expire ? <span class="required">*</span></label>
                                <select name="expire" id="expire" class="form-control">
                                    <option value="NON" selected>NON</option>
                                    <option value="OUI">OUI</option>
                                </select>
                                <div class="error-message" id="error-expire"></div>
                            </div>
                        </div>

                        <!-- NOTE (optionnelle) -->
                        <div class="mb-3">
                            <label class="form-label">Note (1 à 5, optionnel)</label>
                            <input type="text" name="note" id="note" class="form-control">
                            <div class="error-message" id="error-note"></div>
                        </div>

                        <!-- COUPON OUI/NON -->
                        <div class="mb-3">
                            <label class="form-label">Générer un coupon pour ce deal ?</label><br>
                            <label class="me-3">
                                <input type="radio" name="has_coupon" value="NON" checked>
                                Non
                            </label>
                            <label>
                                <input type="radio" name="has_coupon" value="OUI">
                                Oui
                            </label>
                        </div>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="index.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Créer le Deal
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

<script>
// Validation côté client
document.getElementById('dealCreateForm').addEventListener('submit', function (e) {
    let hasError = false;

    const intitule        = document.getElementById('intitule');
    const idSponsor       = document.getElementById('idSponsor');
    const prixInitial     = document.getElementById('prixInitial');
    const reduction       = document.getElementById('reduction');
    const descriptionD    = document.getElementById('descriptionD');
    const dateDebut       = document.getElementById('dateDebut');
    const periodeValidite = document.getElementById('periodeValidite');
    const expire          = document.getElementById('expire');
    const note            = document.getElementById('note');

    const fields = [
        intitule, idSponsor, prixInitial, reduction,
        descriptionD, dateDebut, periodeValidite, expire, note
    ];
    const errorIds = [
        'error-intitule','error-idSponsor','error-prixInitial','error-reduction',
        'error-descriptionD','error-dateDebut','error-periodeValidite','error-expire','error-note'
    ];

    fields.forEach((field, index) => {
        field.classList.remove('is-invalid');
        document.getElementById(errorIds[index]).innerText = '';
    });

    // Intitulé obligatoire
    if (intitule.value.trim() === '') {
        hasError = true;
        intitule.classList.add('is-invalid');
        document.getElementById('error-intitule').innerText = "L'intitulé de l'offre est obligatoire.";
    }

    // Sponsor obligatoire
    if (idSponsor.value.trim() === '') {
        hasError = true;
        idSponsor.classList.add('is-invalid');
        document.getElementById('error-idSponsor').innerText = "Veuillez choisir un sponsor.";
    }

    // Prix initial : obligatoire, nombre positif
    const prixVal = prixInitial.value.trim();
    if (prixVal === '' || isNaN(prixVal) || Number(prixVal) <= 0) {
        hasError = true;
        prixInitial.classList.add('is-invalid');
        document.getElementById('error-prixInitial').innerText = "Le prix initial doit être un nombre positif.";
    }

    // Réduction : obligatoire, nombre >= 0
    const redVal = reduction.value.trim();
    if (redVal === '' || isNaN(redVal) || Number(redVal) < 0) {
        hasError = true;
        reduction.classList.add('is-invalid');
        document.getElementById('error-reduction').innerText = "La réduction doit être un nombre supérieur ou égal à 0.";
    }

    // Description obligatoire
    if (descriptionD.value.trim() === '') {
        hasError = true;
        descriptionD.classList.add('is-invalid');
        document.getElementById('error-descriptionD').innerText = "La description est obligatoire.";
    }

    // Date début obligatoire
    const debVal = dateDebut.value.trim();
    if (debVal === '') {
        hasError = true;
        dateDebut.classList.add('is-invalid');
        document.getElementById('error-dateDebut').innerText = "La date de début est obligatoire.";
    }

    // Période : obligatoire, nombre > 0
    const perVal = periodeValidite.value.trim();
    if (perVal === '' || isNaN(perVal) || Number(perVal) <= 0) {
        hasError = true;
        periodeValidite.classList.add('is-invalid');
        document.getElementById('error-periodeValidite').innerText = "La période de validité doit être un nombre de jours positif.";
    }

    // Note optionnelle, si remplie : 1 à 5
    const noteVal = note.value.trim();
    if (noteVal !== '') {
        if (isNaN(noteVal) || Number(noteVal) < 1 || Number(noteVal) > 5) {
            hasError = true;
            note.classList.add('is-invalid');
            document.getElementById('error-note').innerText = "La note doit être un nombre entre 1 et 5.";
        }
    }

    if (hasError) {
        e.preventDefault();
    }
});
</script>

</body>
</html>
