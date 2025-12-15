<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Demande de deal en attente - SOLIDA</title>

    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/css/bootstrap.min.css">
</head>
<body class="bg-light">

<div class="container py-5">
    <div class="card mx-auto" style="max-width: 600px;">
        <div class="card-body text-center">
            <h3 class="card-title mb-4">Votre demande d'offre a été envoyée ✅</h3>
            <p class="card-text">
                Merci ! Votre deal a bien été enregistré dans notre système.<br>
                Il est actuellement <strong>en attente de validation par un administrateur</strong>.
            </p>
            <p class="card-text mt-3">
                Une fois votre offre <strong>acceptée</strong>, elle sera visible dans la page sponsors,
                dans la section <strong>“Nos offres”</strong>.
            </p>
            <a href="../sponsor.php?action=sponsor&action=front"
               class="btn btn-success mt-4">
                ⟵ Retour à la page sponsors
            </a>
        </div>
    </div>
</div>

<script src="/PROJET_WEB_MVC_FINAL/app/views/sponsor/assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>