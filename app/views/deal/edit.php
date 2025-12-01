<?php
session_start();

// $deal et $sponsors sont fournis par DealController::edit()
$deal     = $deal ?? [];
$sponsors = $sponsors ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - <?= !empty($deal['idDeal']) ? 'Modifier un deal' : 'Créer un deal'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">

    <!-- Template admin (même que sponsor) -->
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/public/backoffice/assets/css/admin.css">

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

<aside class="sidebar">
    <div class="sidebar-header">
        <h2>SOLIDA</h2>
        <p>Panneau d'Administration</p>
    </div>

    <ul class="sidebar-menu">
        <li>
            <a href="/PROJET_WEB_MVC_FINAL/public/backoffice/dashboard.php">
                <i class="fas fa-tachometer-alt"></i>
                <span>Tableau de Bord</span>
            </a>
        </li>
        <li>
            <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=index">
                <i class="fas fa-handshake"></i>
                <span>Sponsors</span>
            </a>
        </li>
        <li>
            <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index" class="active">
                <i class="fas fa-tags"></i>
                <span>Deals</span>
            </a>
        </li>
    </ul>
</aside>

<div class="main-content">

    <div class="top-bar">
        <h1><?= !empty($deal['idDeal']) ? 'Modifier un deal' : 'Créer un deal'; ?></h1>
        <div class="user-info">
            <div class="user-avatar">
                <i class="fas fa-user"></i>
            </div>
            <div class="user-details">
                <span>Administrateur</span>
                <small>admin@solida.com</small>
            </div>
        </div>
    </div>

    <div class="content-area">

        <div class="table-container">
            <div class="table-header">
                <h2><?= !empty($deal['idDeal']) ? 'Modifier un deal' : 'Créer un deal' ?></h2>
                <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index"
                   class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Retour
                </a>
            </div>

            <div class="form-card">
                <form id="dealForm" method="post"
                      action="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=update">

                    <input type="hidden" name="idDeal"
                           value="<?= htmlspecialchars($deal['idDeal'] ?? '') ?>">

                    <!-- Intitulé + Sponsor -->
                    <div style="display:grid; grid-template-columns:1.5fr 1.5fr; gap:20px;">
                        <div>
                            <label class="form-label">Intitulé du deal <span class="required">*</span></label>
                            <input type="text" name="intitule" id="intitule"
                                   class="form-control"
                                   value="<?= htmlspecialchars($deal['intitule'] ?? '') ?>">
                            <div class="error-message" id="error-intitule"></div>
                        </div>
                        <div>
                            <label class="form-label">Sponsor <span class="required">*</span></label>
                            <select name="idSponsor" id="idSponsor" class="form-control">
                                <option value="">-- Choisir un sponsor --</option>
                                <?php foreach ($sponsors as $s): ?>
                                    <option value="<?= (int)$s['id']; ?>"
                                        <?= ((isset($deal['idSponsor']) && $deal['idSponsor'] == $s['id']) ? 'selected' : '') ?>>
                                        <?= htmlspecialchars($s['nomEntreprise']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="error-message" id="error-idSponsor"></div>
                        </div>
                    </div>

                    <!-- Prix + Réduction -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:15px;">
                        <div>
                            <label class="form-label">Prix initial (DT) <span class="required">*</span></label>
                            <input type="text" name="prixInitial" id="prixInitial"
                                   class="form-control"
                                   value="<?= htmlspecialchars($deal['prixInitial'] ?? '') ?>">
                            <div class="error-message" id="error-prixInitial"></div>
                        </div>
                        <div>
                            <label class="form-label">Réduction (%) <span class="required">*</span></label>
                            <input type="text" name="reduction" id="reduction"
                                   class="form-control"
                                   value="<?= htmlspecialchars($deal['reduction'] ?? '') ?>">
                            <div class="error-message" id="error-reduction"></div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-3" style="margin-top:15px;">
                        <label class="form-label">Description <span class="required">*</span></label>
                        <textarea name="descriptionD" id="descriptionD" rows="4"
                                  class="form-control"><?= htmlspecialchars($deal['descriptionD'] ?? '') ?></textarea>
                        <div class="error-message" id="error-descriptionD"></div>
                    </div>

                    <!-- Date / Période / Expire -->
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px;">
                        <div>
                            <label class="form-label">Date début <span class="required">*</span></label>
                            <input type="date" name="dateDebut" id="dateDebut"
                                   class="form-control"
                                   value="<?= htmlspecialchars($deal['dateDebut'] ?? '') ?>">
                            <div class="error-message" id="error-dateDebut"></div>
                        </div>
                        <div>
                            <label class="form-label">Période de validité (jours) <span class="required">*</span></label>
                            <input type="text" name="periodeValidite" id="periodeValidite"
                                   class="form-control"
                                   value="<?= htmlspecialchars($deal['periodeValidite'] ?? '') ?>">
                            <div class="error-message" id="error-periodeValidite"></div>
                        </div>
                        <div>
                            <label class="form-label">Expire ? <span class="required">*</span></label>
                            <?php $expireVal = $deal['expire'] ?? 'NON'; ?>
                            <select name="expire" id="expire" class="form-control">
                                <option value="NON" <?= ($expireVal === 'NON') ? 'selected' : '' ?>>NON</option>
                                <option value="OUI" <?= ($expireVal === 'OUI') ? 'selected' : '' ?>>OUI</option>
                            </select>
                            <div class="error-message" id="error-expire"></div>
                        </div>
                    </div>

                    <!-- Note + Statut -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-top:15px;">
                        <div>
                            <label class="form-label">Note (1 à 5, optionnel)</label>
                            <input type="text" name="note" id="note"
                                   class="form-control"
                                   value="<?= htmlspecialchars($deal['note'] ?? '') ?>">
                            <div class="error-message" id="error-note"></div>
                        </div>
                        <div>
                            <label class="form-label">Statut <span class="required">*</span></label>
                            <?php $statutVal = $deal['statut'] ?? 'en_attente'; ?>
                            <select name="statut" id="statut" class="form-control">
                                <option value="en_attente" <?= ($statutVal === 'en_attente') ? 'selected' : '' ?>>En attente</option>
                                <option value="accepte"    <?= ($statutVal === 'accepte')    ? 'selected' : '' ?>>Accepté</option>
                                <option value="refuse"     <?= ($statutVal === 'refuse')     ? 'selected' : '' ?>>Refusé</option>
                            </select>
                            <div class="error-message" id="error-statut"></div>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div style="display:flex; justify-content:space-between; margin-top:25px;">
                        <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index"
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
document.getElementById('dealForm').addEventListener('submit', function (e) {
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
    const statut          = document.getElementById('statut');

    const fields = [
        intitule, idSponsor, prixInitial, reduction,
        descriptionD, dateDebut, periodeValidite, expire,
        note, statut
    ];
    const errorIds = [
        'error-intitule','error-idSponsor','error-prixInitial','error-reduction',
        'error-descriptionD','error-dateDebut','error-periodeValidite','error-expire',
        'error-note','error-statut'
    ];

    fields.forEach((f, i) => {
        f.classList.remove('is-invalid');
        document.getElementById(errorIds[i]).innerText = '';
    });

    if (intitule.value.trim() === '') {
        hasError = true;
        intitule.classList.add('is-invalid');
        document.getElementById('error-intitule').innerText =
            "L'intitulé du deal est obligatoire.";
    }

    if (idSponsor.value.trim() === '') {
        hasError = true;
        idSponsor.classList.add('is-invalid');
        document.getElementById('error-idSponsor').innerText =
            "Veuillez choisir un sponsor.";
    }

    const prixVal = prixInitial.value.trim();
    if (prixVal === '' || isNaN(prixVal) || Number(prixVal) <= 0) {
        hasError = true;
        prixInitial.classList.add('is-invalid');
        document.getElementById('error-prixInitial').innerText =
            "Le prix initial doit être un nombre positif.";
    }

    const redVal = reduction.value.trim();
    if (redVal === '' || isNaN(redVal) || Number(redVal) < 0) {
        hasError = true;
        reduction.classList.add('is-invalid');
        document.getElementById('error-reduction').innerText =
            "La réduction doit être un nombre supérieur ou égal à 0.";
    }

    if (descriptionD.value.trim() === '') {
        hasError = true;
        descriptionD.classList.add('is-invalid');
        document.getElementById('error-descriptionD').innerText =
            "La description est obligatoire.";
    }

    if (dateDebut.value.trim() === '') {
        hasError = true;
        dateDebut.classList.add('is-invalid');
        document.getElementById('error-dateDebut').innerText =
            "La date de début est obligatoire.";
    }

    const perVal = periodeValidite.value.trim();
    if (perVal === '' || isNaN(perVal) || Number(perVal) <= 0) {
        hasError = true;
        periodeValidite.classList.add('is-invalid');
        document.getElementById('error-periodeValidite').innerText =
            "La période de validité doit être un nombre de jours positif.";
    }

    const noteVal = note.value.trim();
    if (noteVal !== '') {
        if (isNaN(noteVal) || Number(noteVal) < 1 || Number(noteVal) > 5) {
            hasError = true;
            note.classList.add('is-invalid');
            document.getElementById('error-note').innerText =
                "La note doit être comprise entre 1 et 5.";
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