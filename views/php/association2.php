// Fichier : association2.php (Début)

<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../controllers/associationc.php'; 
require_once __DIR__ . '/../../models/association.php';

// Helper function to generate correct URLs based on context
function getDonsUrl($path = '', $params = []) {
    // Always use routing format for consistency
    $base = '../front_office/index.php?section=dons';
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
    $isRouted = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'index.php') !== false);
    if ($isRouted) {
        return '../front_office/index.php';
    }
    return 'index.php';
}

$associationC = new AssociationC();
$id_association = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$association = null;

if ($id_association) {
    // Récupérer les détails de l'association depuis le contrôleur
    $association = $associationC->recupererAssociation($id_association);
}

if (!$association) {
    // Redirection si l'ID est manquant ou l'association non trouvée
    header("Location: " . getDonsUrl('step-type')); 
    exit();
}

// Handle "Faire un don" button click - set session variables and redirect to step4
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['don_type']) && isset($_POST['association_id'])) {
    $_SESSION['don_type'] = strtolower(trim($_POST['don_type']));
    $_SESSION['association_id'] = (int)$_POST['association_id'];
    $_SESSION['association_name'] = $_POST['association_name'] ?? $association->getNomAssociation();
    $_SESSION['country'] = $_POST['country'] ?? $association->getPays();
    
    header("Location: " . getDonsUrl('step4'));
    exit();
}

// ------------------------------------------------------------------
// NOUVEAU CODE POUR LA GESTION DES AVIS (JSON)
// ------------------------------------------------------------------
$currentUserId = $_SESSION['user_id'] ?? 1; // ID de l'utilisateur connecté (Exemple: 1)
$commentaires = $associationC->getCommentairesAssociation($id_association);
$commentError = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    
    $action = $_POST['action'];
    $id_commentaire = $_POST['id_commentaire'] ?? null;
    $texte = trim($_POST['texte_commentaire'] ?? '');
    $note = (int)($_POST['note'] ?? 0);
    
    if ($action == 'add' && !empty($texte) && $note >= 1 && $note <= 5) {
        if (!$associationC->ajouterCommentaireAssociation($id_association, $currentUserId, $note, $texte)) {
            $commentError = "Erreur lors de l'ajout du commentaire.";
        }
    } 
    elseif ($action == 'edit' && $id_commentaire && !empty($texte) && $note >= 1 && $note <= 5) {
        if (!$associationC->modifierCommentaireAssociation($id_association, $currentUserId, $id_commentaire, $note, $texte)) {
            $commentError = "Erreur ou permission refusée pour la modification.";
        }
    } 
    elseif ($action == 'delete' && $id_commentaire) {
        if (!$associationC->supprimerCommentaireAssociation($id_association, $currentUserId, $id_commentaire)) {
            $commentError = "Erreur ou permission refusée pour la suppression.";
        }
    }
    
    // Rediriger pour recharger la page et voir le nouveau commentaire (PRG pattern)
    if (empty($commentError)) {
        header("Location: " . getDonsUrl('association2', ['id' => $id_association])); 
        exit();
    }
    
    // Si une erreur s'est produite, on recharge les commentaires pour affichage
    $commentaires = $associationC->getCommentairesAssociation($id_association);
}
// ------------------------------------------------------------------
// FIN NOUVEAU CODE
// ------------------------------------------------------------------
$type_don_label = [
    'sang' => 'Don de Sang',
    'argent' => 'Don d\'Argent',
    'nourriture' => 'Don de Nourriture'
][$association->getTypeDonSupporte()] ?? 'Don Général';

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
 <title>Historique de mes dons - Solida</title>
    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">

    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/templatemo.css">
    <link rel="stylesheet" href="../css/event.css">
       <link rel="stylesheet" href="../css/slick.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="../css/fontawesome.min.css">
    
    <link rel="stylesheet" href="../assets/solida2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>
    <?php include '../front_office/includes/navbar.php'; ?>

    <style>
        .details-card {
            background: #f7fcf9; /* Vert très clair */
            border-left: 5px solid #28a745; /* Vert Solida */
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .details-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }
        .icon-type {
            font-size: 2.5rem;
            color: #28a745;
        }
        .detail-row {
            padding: 8px 0;
            border-bottom: 1px dashed #e0e0e0;
        }
    </style>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>


    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <a href="<?= getDonsUrl('step4'); ?>" class="btn btn-outline-secondary mb-4"><i class="fas fa-arrow-left"></i> Retour</a>

                <div class="card details-card p-4">
                    <div class="text-center mb-4">
                        <i class="icon-type fas fa-handshake"></i>
                        <h1 class="mt-2 text-primary"><?= htmlspecialchars($association->getNomAssociation()); ?></h1>
                        <p class="lead text-muted"><?= htmlspecialchars($type_don_label); ?> | <?= htmlspecialchars($association->getPays()); ?></p>
                    </div>
                    <div class="detail-row d-flex align-items-center">
    <i class="fas fa-map-marker-alt me-3 mt-1 text-danger"></i>
    <p class="mb-0 me-3">
        <strong>Adresse :</strong> <?= htmlspecialchars($association->getAdresse()); ?>
    </p>

    <?php
    // Encodage de l'adresse pour l'URL Google Maps
    $adresse_encodee = urlencode($association->getAdresse() . ', ' . $association->getPays());
    $google_maps_url = "https://www.google.com/maps/search/?api=1&query={$adresse_encodee}";
    ?>

    <a 
        href="<?= $google_maps_url; ?>" 
        target="_blank" 
        class="btn btn-sm btn-outline-secondary" 
        title="Voir l'emplacement sur Google Maps"
    >
        <i class="fas fa-map me-1"></i> Voir sur la carte
    </a>
</div>

                    <p class="text-justify mb-4"><?= nl2br(htmlspecialchars($association->getDescription())); ?></p>
                    
                    <hr>


                    <h4 class="mt-4 mb-3 text-secondary">Coordonnées et Mission</h4>
                    <div class="mt-5 pt-3 border-top">
    <div class="card my-4">
        <div class="card-header bg-success text-white">Laissez un Avis</div>
        <div class="card-body">
            <?php if ($currentUserId): // S'assurer qu'un utilisateur est connecté ?>
                <form method="POST" action="association2.php?id=<?= htmlspecialchars($id_association); ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="mb-3">
                        <label class="form-label">Votre Note (1 à 5)</label>
                        <div>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <input type="radio" name="note" value="<?= $i; ?>" id="note<?= $i; ?>" required>
                                <label for="note<?= $i; ?>"><?= $i; ?> <i class="fas fa-star text-warning"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="texte_commentaire" class="form-label">Votre Commentaire</label>
                        <textarea class="form-control" name="texte_commentaire" id="texte_commentaire" rows="3" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-success">Envoyer l'Avis</button>
                </form>
            <?php else: ?>
                <p class="alert alert-warning">Vous devez être connecté pour laisser un avis.</p>
            <?php endif; ?>

            <?php if (!empty($commentError)): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($commentError); ?></div>
            <?php endif; ?>
        </div>
    </div>


    <h3 class="mt-5 mb-3 text-primary">Avis des Utilisateurs (<?= count($commentaires); ?>)</h3>

    <?php if (empty($commentaires)): ?>
        <p class="alert alert-info">Soyez le premier à laisser un avis pour cette association !</p>
    <?php endif; ?>

    <?php foreach ($commentaires as $commentaire): ?>
        <div class="card mb-3 shadow-sm" id="comment-<?= htmlspecialchars($commentaire['id_commentaire']); ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= ($i <= $commentaire['note'] ? 'text-warning' : 'text-secondary'); ?>"></i>
                        <?php endfor; ?>
                        <span class="small text-muted ms-2">par <strong><?= htmlspecialchars($commentaire['nom_utilisateur'] ?? 'Utilisateur'); ?></strong></span>
                    </div>
                    <div class="text-muted small">
                        Publié le <?= date('d/m/Y', strtotime($commentaire['date_creation'])); ?>
                    </div>
                </div>
                
                <p class="card-text mt-2"><?= nl2br(htmlspecialchars($commentaire['texte'])); ?></p>
                
                <?php if ($commentaire['id_utilisateur'] == $currentUserId): ?>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-outline-primary" 
                                onclick="toggleEditForm('<?= htmlspecialchars($commentaire['id_commentaire']); ?>')">
                            <i class="fas fa-edit"></i> Modifier
                        </button>
                        
                        <form method="POST" action="association2.php?id=<?= htmlspecialchars($id_association); ?>" class="d-inline-block">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id_commentaire" value="<?= htmlspecialchars($commentaire['id_commentaire']); ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer votre commentaire ?')">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                        </form>
                    </div>
                    
                    <div id="edit-form-<?= htmlspecialchars($commentaire['id_commentaire']); ?>" style="display:none;" class="mt-3 p-3 border rounded bg-light">
                        <h6>Modifier votre commentaire</h6>
                        <form method="POST" action="association2.php?id=<?= htmlspecialchars($id_association); ?>">
                            <input type="hidden" name="action" value="edit">
                            <input type="hidden" name="id_commentaire" value="<?= htmlspecialchars($commentaire['id_commentaire']); ?>">
                            <div class="mb-3">
                                <label class="form-label small">Nouvelle Note</label>
                                <div>
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <input type="radio" name="note" value="<?= $i; ?>" id="edit-note-<?= htmlspecialchars($commentaire['id_commentaire'] . '-' . $i); ?>" 
                                            <?= ($i == $commentaire['note'] ? 'checked' : ''); ?> required>
                                        <label for="edit-note-<?= htmlspecialchars($commentaire['id_commentaire'] . '-' . $i); ?>"><?= $i; ?> <i class="fas fa-star text-warning"></i></label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <div class="mb-3">
                                <textarea class="form-control" name="texte_commentaire" rows="2" required><?= htmlspecialchars($commentaire['texte']); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">Enregistrer les modifications</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
function toggleEditForm(commentId) {
    const form = document.getElementById('edit-form-' + commentId);
    if (form) {
        // Toggle the display property
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
        } else {
            form.style.display = 'none';
        }
    }
}
</script>
                    
                    <div class="detail-row d-flex">
                        <i class="fas fa-map-marker-alt me-3 mt-1 text-danger"></i>
                        <p class="mb-0"><strong>Adresse :</strong> <?= htmlspecialchars($association->getAdresse()); ?></p>
                    </div>

                    <div class="detail-row d-flex">
                        <i class="fas fa-globe me-3 mt-1 text-success"></i>
                        <p class="mb-0"><strong>Pays d'Opération :</strong> <?= htmlspecialchars($association->getPays()); ?></p>
                    </div>

                    <div class="detail-row d-flex">
                        <i class="fas fa-heart me-3 mt-1 text-primary"></i>
                        <p class="mb-0"><strong>Type de Don Soutenu :</strong> <?= htmlspecialchars($type_don_label); ?></p>
                    </div>
                    
                    <div class="mt-4 text-center">
                        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF'] . '?id=' . $id_association); ?>" style="display: inline;">
                            <input type="hidden" name="don_type" value="<?= htmlspecialchars($association->getTypeDonSupporte()); ?>">
                            <input type="hidden" name="association_id" value="<?= htmlspecialchars($id_association); ?>">
                            <input type="hidden" name="association_name" value="<?= htmlspecialchars($association->getNomAssociation()); ?>">
                            <input type="hidden" name="country" value="<?= htmlspecialchars($association->getPays()); ?>">
                            <button type="submit" class="btn btn-success btn-lg">Faire un don à cette association</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <footer class="bg-dark" id="tempaltemo_footer">
        <div class="container">
            <div class="row">

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-success border-bottom pb-3 border-light logo">SOLIDA</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li>
                            <i class="fas fa-map-marker-alt fa-fw"></i>
                            123 Consectetur at ligula 10660
                        </li>
                        <li>
                            <i class="fa fa-phone fa-fw"></i>
                            <a class="text-decoration-none" href="tel:010-020-0340">010-020-0340</a>
                        </li>
                        <li>
                            <i class="fa fa-envelope fa-fw"></i>
                            <a class="text-decoration-none" href="mailto:info@solida.com">info@solida.com</a>
                        </li>
                    </ul>
                </div>

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-light border-bottom pb-3 border-light">Actions</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li><a class="text-decoration-none" href="#">Donner du Sang</a></li>
                        <li><a class="text-decoration-none" href="#">Donner de l'Argent</a></li>
                        <li><a class="text-decoration-none" href="#">Donner de la Nourriture</a></li>
                        <li><a class="text-decoration-none" href="#">Devenir Volontaire</a></li>
                        <li><a class="text-decoration-none" href="#">Événements</a></li>
                        <li><a class="text-decoration-none" href="#">FAQ</a></li>
                    </ul>
                </div>

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-light border-bottom pb-3 border-light">Infos Supplémentaires</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li><a class="text-decoration-none" href="#">Accueil</a></li>
                        <li><a class="text-decoration-none" href="#">À Propos de Nous</a></li>
                        <li><a class="text-decoration-none" href="#">Localisations</a></li>
                        <li><a class="text-decoration-none" href="#">Historique des Dons</a></li>
                        <li><a class="text-decoration-none" href="#">Contact</a></li>
                    </ul>
                </div>

            </div>

            <div class="row text-light mb-4">
                <div class="col-12 mb-3">
                    <div class="w-100 my-3 border-top border-light"></div>
                </div>
                <div class="col-auto me-auto">
                    <ul class="list-inline text-left footer-icons">
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="http://facebook.com/"><i class="fab fa-facebook-f fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="https://www.instagram.com/"><i class="fab fa-instagram fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="https://twitter.com/"><i class="fab fa-twitter fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="https://www.linkedin.com/"><i class="fab fa-linkedin fa-lg fa-fw"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="subscribeEmail">Email address</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control bg-dark border-light" id="subscribeEmail" placeholder="Email address">
                        <div class="input-group-text btn-success text-light">Subscribe</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-100 bg-black py-3">
            <div class="container">
                <div class="row pt-2">
                    <div class="col-12">
                        <p class="text-left text-light">
                            Copyright &copy; 2025 SOLIDA 
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>