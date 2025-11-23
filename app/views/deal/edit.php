<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Backoffice - Modifier une offre</title>

    <!-- CSS BOOTSTRAP + TEMPLATE BACKOFFICE -->
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/deal/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/deal/assets/css/templatemo.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/deal/assets/css/custom.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/deal/assets/css/fontawesome.min.css">

    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            height: 100vh;
            background-color: #343a40;
            color: #fff;
        }

        .sidebar a {
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #495057;
            color: #fff;
        }

        .content-wrapper {
            padding: 20px;
        }

        .page-title {
            margin-bottom: 20px;
        }

        .deal-form-wrapper {
            max-width: 900px;
            margin: 0 auto 40px auto;
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .required {
            color: red;
        }

        .error-message {
            color: #dc3545;
            font-size: 0.9rem;
            margin-top: 4px;
        }

        .is-invalid {
            border-color: #dc3545;
        }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">

        <!-- SIDEBAR BACKOFFICE -->
        <nav class="col-md-2 d-none d-md-block sidebar">
            <div class="py-4 px-3">
                <h4 class="text-white">SOLIDA Admin</h4>
                <p class="mb-0 text-muted">Backoffice</p>
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php" class="nav-link">
                        <i class="fa fa-home me-2"></i> Dashboard général
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=index"
                       class="nav-link">
                        <i class="fa fa-handshake-o me-2"></i> Gestion des sponsors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index"
                       class="nav-link active">
                        <i class="fa fa-tags me-2"></i> Gestion des deals
                    </a>
                </li>
            </ul>
        </nav>

        <!-- CONTENU PRINCIPAL -->
        <main class="col-md-10 ms-sm-auto col-lg-10 content-wrapper">

            <!-- Bandeau top -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3 border-bottom pb-2">
                <h1 class="h3 page-title">Modifier une offre</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a class="btn btn-outline-secondary"
                       href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index">
                        ⟵ Retour à la liste
                    </a>
                </div>
            </div>

            <div class="deal-form-wrapper">
                <form id="dealEditForm" method="post"
                      action="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=update">

                    <!-- clé primaire -->
                    <input type="hidden" name="idDeal" value="<?= htmlspecialchars($deal['idDeal']) ?>">

                    <!-- INTITULÉ -->
                    <div class="mb-3">
                        <label class="form-label">Titre de l'offre <span class="required">*</span></label>
                        <input type="text" name="intitule" id="intitule"
                               class="form-control"
                               value="<?= htmlspecialchars($deal['intitule']) ?>">
                        <div class="error-message" id="error-intitule"></div>
                    </div>

                    <!-- SPONSOR + PRIX INITIAL -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Sponsor concerné <span class="required">*</span></label>
                            <select name="idSponsor" id="idSponsor" class="form-select">
                                <option value="">-- Choisir un sponsor --</option>
                                <?php foreach ($sponsors as $s): ?>
                                    <option value="<?= (int)$s['id'] ?>"
                                        <?= ($s['id'] == $deal['idSponsor']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($s['nomEntreprise']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="error-message" id="error-idSponsor"></div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Prix initial (€) <span class="required">*</span></label>
                            <input type="text" name="prixInitial" id="prixInitial"
                                   class="form-control"
                                   value="<?= htmlspecialchars($deal['prixInitial']) ?>">
                            <div class="error-message" id="error-prixInitial"></div>
                        </div>
                    </div>

                    <!-- REDUCTION -->
                    <div class="mb-3">
                        <label class="form-label">Réduction (%) <span class="required">*</span></label>
                        <input type="text" name="reduction" id="reduction"
                               class="form-control"
                               value="<?= htmlspecialchars($deal['reduction']) ?>">
                        <div class="error-message" id="error-reduction"></div>
                    </div>

                    <!-- DESCRIPTION -->
                    <div class="mb-3">
                        <label class="form-label">Description de l'offre <span class="required">*</span></label>
                        <textarea name="descriptionD" id="descriptionD" rows="4"
                                  class="form-control"><?= htmlspecialchars($deal['descriptionD']) ?></textarea>
                        <div class="error-message" id="error-descriptionD"></div>
                    </div>

                    <!-- DATES + PÉRIODE -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Date de début <span class="required">*</span></label>
                            <input type="date" name="dateDebut" id="dateDebut"
                                   class="form-control"
                                   value="<?= htmlspecialchars($deal['dateDebut']) ?>">
                            <div class="error-message" id="error-dateDebut"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Période de validité (jours) <span class="required">*</span></label>
                            <input type="text" name="periodeValidite" id="periodeValidite"
                                   class="form-control"
                                   value="<?= htmlspecialchars($deal['periodeValidite']) ?>">
                            <div class="error-message" id="error-periodeValidite"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Expire ? <span class="required">*</span></label>
                            <select name="expire" id="expire" class="form-select">
                                <option value="NON" <?= ($deal['expire'] === 'NON') ? 'selected' : '' ?>>NON</option>
                                <option value="OUI" <?= ($deal['expire'] === 'OUI') ? 'selected' : '' ?>>OUI</option>
                            </select>
                            <div class="error-message" id="error-expire"></div>
                        </div>
                    </div>

                    <!-- NOTE (optionnelle) -->
                    <div class="mb-3">
                        <label class="form-label">Note (1 à 5, optionnel)</label>
                        <input type="text" name="note" id="note"
                               class="form-control"
                               value="<?= htmlspecialchars($deal['note']) ?>">
                        <div class="error-message" id="error-note"></div>
                    </div>

                    <!-- STATUT -->
                    <div class="mb-3">
                        <label class="form-label">Statut <span class="required">*</span></label>
                        <select name="statut" id="statut" class="form-select">
                            <option value="en_attente" <?= ($deal['statut'] === 'en_attente') ? 'selected' : '' ?>>En attente</option>
                            <option value="accepte"    <?= ($deal['statut'] === 'accepte')    ? 'selected' : '' ?>>Accepté</option>
                            <option value="refuse"     <?= ($deal['statut'] === 'refuse')     ? 'selected' : '' ?>>Refusé</option>
                        </select>
                        <div class="error-message" id="error-statut"></div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index"
                           class="btn btn-secondary">
                            ⟵ Annuler / Retour
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>
</div>

<!-- JS -->
<script src="/PROJET_WEB_MVC_FINAL/app/views/deal/assets/js/jquery-1.11.0.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/deal/assets/js/bootstrap.bundle.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/deal/assets/js/templatemo.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/deal/assets/js/custom.js"></script>

<script>
// Validation côté client - édition deal (adaptée à tes champs)
document.getElementById('dealEditForm').addEventListener('submit', function (e) {
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

    fields.forEach((field, index) => {
        field.classList.remove('is-invalid');
        document.getElementById(errorIds[index]).innerText = '';
    });

    // Intitulé obligatoire
    if (intitule.value.trim() === '') {
        hasError = true;
        intitule.classList.add('is-invalid');
        document.getElementById('error-intitule').innerText =
            "L'intitulé de l'offre est obligatoire.";
    }

    // Sponsor obligatoire
    if (idSponsor.value.trim() === '') {
        hasError = true;
        idSponsor.classList.add('is-invalid');
        document.getElementById('error-idSponsor').innerText =
            "Veuillez choisir un sponsor.";
    }

    // Prix initial obligatoire, nombre > 0
    const prixVal = prixInitial.value.trim();
    if (prixVal === '' || isNaN(prixVal) || Number(prixVal) <= 0) {
        hasError = true;
        prixInitial.classList.add('is-invalid');
        document.getElementById('error-prixInitial').innerText =
            "Le prix initial doit être un nombre positif.";
    }

    // Réduction obligatoire, nombre >= 0
    const redVal = reduction.value.trim();
    if (redVal === '' || isNaN(redVal) || Number(redVal) < 0) {
        hasError = true;
        reduction.classList.add('is-invalid');
        document.getElementById('error-reduction').innerText =
            "La réduction doit être un nombre supérieur ou égal à 0.";
    }

    // Description obligatoire
    if (descriptionD.value.trim() === '') {
        hasError = true;
        descriptionD.classList.add('is-invalid');
        document.getElementById('error-descriptionD').innerText =
            "La description est obligatoire.";
    }

    // Date début obligatoire
    const debVal = dateDebut.value.trim();
    if (debVal === '') {
        hasError = true;
        dateDebut.classList.add('is-invalid');
        document.getElementById('error-dateDebut').innerText =
            "La date de début est obligatoire.";
    }

    // Période obligatoire, nombre > 0
    const perVal = periodeValidite.value.trim();
    if (perVal === '' || isNaN(perVal) || Number(perVal) <= 0) {
        hasError = true;
        periodeValidite.classList.add('is-invalid');
        document.getElementById('error-periodeValidite').innerText =
            "La période de validité doit être un nombre de jours positif.";
    }

    // Note optionnelle, mais si renseignée, entre 1 et 5
    const noteVal = note.value.trim();
    if (noteVal !== '') {
        if (isNaN(noteVal) || Number(noteVal) < 1 || Number(noteVal) > 5) {
            hasError = true;
            note.classList.add('is-invalid');
            document.getElementById('error-note').innerText =
                "La note doit être un nombre entre 1 et 5.";
        }
    }

    // Statut obligatoire
    if (statut.value.trim() === '') {
        hasError = true;
        statut.classList.add('is-invalid');
        document.getElementById('error-statut').innerText =
            "Le statut est obligatoire.";
    }

    if (hasError) {
        e.preventDefault();
    }
});
</script>

</body>
</html>