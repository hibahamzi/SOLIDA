<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Backoffice - Modifier un sponsor</title>

    <!-- CSS BOOTSTRAP + TEMPLATE BACKOFFICE (même que deals) -->
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/templatemo.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/custom.css">
    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/fontawesome.min.css">

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

        .sponsor-form-wrapper {
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

        .logo-preview {
            max-width: 150px;
            max-height: 150px;
            border-radius: 8px;
            object-fit: contain;
            background: #f5f5f5;
            margin-top: 6px;
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
                       class="nav-link active">
                        <i class="fa fa-handshake-o me-2"></i> Gestion des sponsors
                    </a>
                </li>
                <li class="nav-item">
                    <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index"
                       class="nav-link">
                        <i class="fa fa-tags me-2"></i> Gestion des deals
                    </a>
                </li>
            </ul>
        </nav>

        <!-- CONTENU PRINCIPAL -->
        <main class="col-md-10 ms-sm-auto col-lg-10 content-wrapper">

            <!-- Bandeau top -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center mb-3 border-bottom pb-2">
                <h1 class="h3 page-title">Modifier un sponsor</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <a class="btn btn-outline-secondary"
                       href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=index">
                        ⟵ Retour à la liste
                    </a>
                </div>
            </div>

            <div class="sponsor-form-wrapper">
                <form id="sponsorEditForm" method="post"
                      action="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=edit&id=<?= htmlspecialchars($old['id']) ?>">

                    <!-- ID caché -->
                    <input type="hidden" name="id" value="<?= htmlspecialchars($old['id']) ?>">

                    <!-- NOM ENTREPRISE -->
                    <div class="mb-3">
                        <label class="form-label">Nom de l'entreprise <span class="required">*</span></label>
                        <input type="text" name="nomEntreprise" id="nomEntreprise"
                               class="form-control"
                               value="<?= htmlspecialchars($old['nomEntreprise'] ?? '') ?>">
                        <div class="error-message" id="error-nomEntreprise"></div>
                    </div>

                    <!-- EMAIL + TELEPHONE -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email de contact <span class="required">*</span></label>
                            <input type="email" name="emailContact" id="emailContact"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['emailContact'] ?? '') ?>">
                            <div class="error-message" id="error-emailContact"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Téléphone (8 chiffres)</label>
                            <input type="text" name="telephone" id="telephone"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['telephone'] ?? '') ?>">
                            <div class="error-message" id="error-telephone"></div>
                        </div>
                    </div>

                    <!-- ADRESSE -->
                    <div class="mb-3">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="adresse" id="adresse"
                               class="form-control"
                               value="<?= htmlspecialchars($old['adresse'] ?? '') ?>">
                        <div class="error-message" id="error-adresse"></div>
                    </div>

                    <!-- TYPE SPONSORING + MONTANT + DOMAINE -->
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Type de sponsoring <span class="required">*</span></label>
                            <select name="typeSponsoring" id="typeSponsoring" class="form-select">
                                <option value="">-- Choisir --</option>
                                <option value="Financier" <?= ($old['typeSponsoring'] ?? '') === 'Financier' ? 'selected' : '' ?>>Financier</option>
                                <option value="Matériel" <?= ($old['typeSponsoring'] ?? '') === 'Matériel' ? 'selected' : '' ?>>Matériel</option>
                                <option value="Média"    <?= ($old['typeSponsoring'] ?? '') === 'Média'    ? 'selected' : '' ?>>Média</option>
                            </select>
                            <div class="error-message" id="error-typeSponsoring"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Montant engagé (€)</label>
                            <input type="number" step="0.01" name="montantEngage" id="montantEngage"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['montantEngage'] ?? '') ?>">
                            <div class="error-message" id="error-montantEngage"></div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Domaine d'activité</label>
                            <input type="text" name="domaineActivite" id="domaineActivite"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['domaineActivite'] ?? '') ?>">
                            <div class="error-message" id="error-domaineActivite"></div>
                        </div>
                    </div>

                   
                    <!-- DATES -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date début partenariat</label>
                            <input type="date" name="dateDebutPartenaire" id="dateDebutPartenaire"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['dateDebutPartenaire'] ?? '') ?>">
                            <div class="error-message" id="error-dateDebutPartenaire"></div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date fin partenariat</label>
                            <input type="date" name="dateFinPartenaire" id="dateFinPartenaire"
                                   class="form-control"
                                   value="<?= htmlspecialchars($old['dateFinPartenaire'] ?? '') ?>">
                            <div class="error-message" id="error-dateFinPartenaire"></div>
                        </div>
                    </div>

                    <!-- STATUT -->
                    <div class="mb-3">
                        <label class="form-label">Statut <span class="required">*</span></label>
                        <select name="statut" id="statut" class="form-select">
                            <option value="">-- Choisir --</option>
                            <option value="actif"   <?= ($old['statut'] ?? '') === 'actif'   ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= ($old['statut'] ?? '') === 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        </select>
                        <div class="error-message" id="error-statut"></div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=sponsor&action=index"
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
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/jquery-1.11.0.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/bootstrap.bundle.min.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/templatemo.js"></script>
<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/custom.js"></script>

<script>
// Validation côté client - édition sponsor
document.getElementById('sponsorEditForm').addEventListener('submit', function (e) {
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

    fields.forEach((field, index) => {
        field.classList.remove('is-invalid');
        document.getElementById(errorIds[index]).innerText = '';
    });

    // Nom obligatoire
    if (nomEntreprise.value.trim() === '') {
        hasError = true;
        nomEntreprise.classList.add('is-invalid');
        document.getElementById('error-nomEntreprise').innerText =
            "Le nom de l'entreprise est obligatoire.";
    }

    // Email obligatoire + format
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

    // Téléphone : optionnel, mais si rempli → 8 chiffres
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

    // Type sponsoring obligatoire
    if (typeSponsoring.value.trim() === '') {
        hasError = true;
        typeSponsoring.classList.add('is-invalid');
        document.getElementById('error-typeSponsoring').innerText =
            "Le type de sponsoring est obligatoire.";
    }

    // Montant : optionnel, mais si rempli > 0
    const montantVal = montantEngage.value.trim();
    if (montantVal !== '') {
        if (isNaN(montantVal) || Number(montantVal) <= 0) {
            hasError = true;
            montantEngage.classList.add('is-invalid');
            document.getElementById('error-montantEngage').innerText =
                "Le montant engagé doit être un nombre positif.";
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