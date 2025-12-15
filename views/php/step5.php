<?php
session_start();

// Helper function to generate correct URLs based on context
function getDonsUrl($path = '', $params = []) {
    // Calculate absolute URL for redirects
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the base path (remove /views/php if present)
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $scriptPath = str_replace('/views/php', '', $scriptPath);
    $scriptPath = str_replace('\\views\\php', '', $scriptPath);
    
    // Build base URL
    $base = $protocol . '://' . $host . $scriptPath . '/views/front_office/index.php?section=dons';
    
    if ($path === 'step-type' || $path === '') {
        return $base;
    } elseif ($path === 'step4') {
        return $base . '&step=4';
    } elseif ($path === 'step5') {
        return $base . '&step=5';
    } elseif ($path === 'association2') {
        $id = $params['id'] ?? '';
        return $base . '&association_id=' . $id;
    }
    return '#';
}

function getHomeUrl() {
    // Calculate absolute URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    
    // Get the base path (remove /views/php if present)
    $scriptPath = dirname($_SERVER['SCRIPT_NAME']);
    $scriptPath = str_replace('/views/php', '', $scriptPath);
    $scriptPath = str_replace('\\views\\php', '', $scriptPath);
    
    // Build home URL
    return $protocol . '://' . $host . $scriptPath . '/views/front_office/index.php';
}

// Start output buffering
if (!ob_get_level()) {
    ob_start();
}

// Récupérer les détails de confirmation de la session
$details = $_SESSION['confirmation_details'] ?? null;

// Si les détails sont manquants (accès direct ou session expirée), rediriger
if (!$details) {
    error_log("step5.php: No confirmation details found, redirecting to step-type");
    // Clear output buffer
    while (ob_get_level()) {
        ob_end_clean();
    }
    $redirectUrl = getDonsUrl('step-type');
    header("Location: " . $redirectUrl, true, 302);
    exit();
}

// Nettoyer la session de confirmation pour éviter l'affichage de l'ancienne donnée
// But keep it for now in case of page refresh
// unset($_SESSION['confirmation_details']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmation de don - SOLIDA</title>

    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/templatemo.css">
    <link rel="stylesheet" href="../css/solida.css"> 
    <link rel="stylesheet" href="../css/event.css"> 
    
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="../css/fontawesome.min.css"> 
</head>
<body>
    
    <?php include '../front_office/includes/navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="text-center confirmation-box">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
                    </div>
                    <h2 class="mb-3">Merci pour votre don !</h2>
                    <p class="lead mb-4">Votre générosité fait la différence. Votre don a été enregistré avec le statut <strong>En attente</strong>.</p>
                    
                    <div class="card shadow-sm mt-4">
                        <div class="card-body text-start">
                            <h5 class="card-title mb-3"><i class="fas fa-info-circle text-primary"></i> Détails de votre don</h5>
                            <div class="mb-2">
                                <strong><i class="fas fa-heart text-danger"></i> Type :</strong> 
                                <span class="ms-2"><?= htmlspecialchars($details['type']); ?></span>
                            </div>
                            <div class="mb-2">
                                <strong><i class="fas fa-handshake text-primary"></i> Association :</strong> 
                                <span class="ms-2"><?= htmlspecialchars($details['association']); ?></span>
                            </div>
                            <div class="mb-2">
                                <strong><i class="fas fa-globe text-success"></i> Pays :</strong> 
                                <span class="ms-2"><?= htmlspecialchars($details['pays']); ?></span>
                            </div>
                            <div class="mb-2">
                                <strong><i class="fas fa-calendar text-info"></i> Date d'enregistrement :</strong> 
                                <span class="ms-2"><?= htmlspecialchars($details['date']); ?></span>
                            </div>
                            <?php if (!empty($details['valeur'])): ?>
                                <div class="mb-2">
                                    <strong><i class="fas fa-list text-warning"></i> Détails :</strong> 
                                    <span class="ms-2"><?= htmlspecialchars($details['valeur']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <a href="<?= getDonsUrl('step-type'); ?>" class="btn btn-success btn-lg me-2">
                            <i class="fas fa-heart"></i> Faire un autre don
                        </a>
                        <a href="<?= getHomeUrl(); ?>" class="btn btn-outline-primary btn-lg">
                            <i class="fas fa-home"></i> Retour à l'accueil
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include '../front_office/includes/footer.php'; ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>