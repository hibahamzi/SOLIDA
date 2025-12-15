<?php
session_start();
// Inclusion des contrôleurs
require_once '../../controllers/associationc.php'; 
require_once '../../controllers/donc.php'; 
require_once '../../models/association.php';

// Assurez-vous que le chemin vers Connection est inclus si nécessaire pour AssociationC

$associationC = new AssociationC();
$donC = new DonC(); // Ligne 8

$liste_associations_details = $associationC->listerAssociationsAvecTotalDons();

// Récupération des statistiques globales
$global_stats = $donC->getGlobalDonationTotals(); // Ligne 17

// Helper function to generate correct URLs based on context
function getDonsUrl($path = '', $params = []) {
    // Check if accessed through routing (index.php?section=dons)
    $isRouted = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'index.php?section=dons') !== false) 
                || (isset($_GET['routed']) && $_GET['routed'] == '1');
    
    if ($isRouted) {
        // Use routing format
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
    } else {
        // Use direct file access
        if ($path === 'step-type' || $path === '') {
            return 'step-type.php';
        } elseif ($path === 'step4') {
            return 'step4.php';
        } elseif ($path === 'step5') {
            return 'step5.php';
        } elseif ($path === 'association2') {
            $id = $params['id'] ?? '';
            return 'association2.php?id=' . $id;
        }
    }
    return '#';
}

// Helper for home/index link
function getHomeUrl() {
    $isRouted = (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'index.php') !== false);
    if ($isRouted) {
        return '../front_office/index.php';
    }
    return 'index (1).php';
}

// Mappage pour les icônes et les couleurs
$type_icons = [
    'sang' => ['icon' => 'fas fa-tint text-danger', 'unit' => 'dons'],
    'argent' => ['icon' => 'fas fa-euro-sign text-success', 'unit' => '€'],
    'nourriture' => ['icon' => 'fas fa-utensils text-warning', 'unit' => 'dons']
];

// NOTE: Ajout d'une fonction simple pour la réutilisation des classes d'icônes dans le filtrage
function getIconClassByType(string $type, array $icons): string {
    return $icons[$type]['icon'] ?? 'fas fa-handshake text-muted';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Solida - Accueil</title>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">

    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/templatemo.css">
    <link rel="stylesheet" href="../css/event.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="../css/fontawesome.min.css">
    

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
      /*
|--------------------------------------------------------------------------
| solida.css - Styles généraux et professionnels
|--------------------------------------------------------------------------
*/

/* --- Variables Globales --- */

  

/* --- Généralités et Typographie --- */
body {
    font-family: var(--font-family-base);
    color: #343a40;
    background-color: #ffffff;
}

h1, h2, h3, h4, h5 {
    font-weight: 600;
    color: #212529;
}

/* --- Navigation et Boutons --- */
.navbar-brand {
    font-weight: 700;
    color: #ffffff !important;
    font-size: 1.5rem;
}

.btn-donate {
    background-color: var(--primary-green);
    color: #ffffff;
    border: none;
    padding: 12px 30px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 50px;
    transition: background-color 0.3s, transform 0.3s;
}

.btn-donate:hover {
    background-color: #218838; /* Vert un peu plus foncé */
    color: #ffffff;
    transform: translateY(-1px);
}

/* --- Section Hero (Introduction) --- */
.hero-section {
    background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('https://cdn.helloasso.com/img/photos/93261.jpg') center center no-repeat;
    background-size: cover;
    color: white;
    padding: 100px 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
    /* Utilisez votre propre image pour le fond! */
}

/* --- Section Statistiques (Impacts) --- */
.stats-card {
    background-color: var(--card-bg);
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(230, 213, 213, 0.98);
    transition: transform 0.3s, box-shadow 0.3s;
    height: 100%; /* S'assurer que les cartes ont la même hauteur dans la rangée */
}

.stats-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
}

.stats-number {
    font-size: 2.2rem; /* Augmenter la taille pour l'impact */
    font-weight: 700;
    margin-bottom: 5px;
}

/* --- Section Vidéo (Nouveauté) --- */
.video-container {
    position: relative;
    padding-bottom: 56.25%; /* Ratio 16:9 */
    height: 0;
    overflow: hidden;
    border-radius: 8px;
}

.video-container iframe {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* --- Section Associations --- */
#associations-list {
    padding-top: 20px;
}

.association-item .card-hover {
    border: 1px solid #dee2e6;
}

.association-item .card-hover:hover {
    border-color: var(--primary-green);
}

.filter-btn {
    transition: all 0.2s;
}

.filter-btn.active {
    background-color: var(--primary-green) !important;
    color: white !important;
    border-color: var(--primary-green) !important;
}

/* --- Chatbot SolidaBot --- */
#chatbot-section {
    padding: 50px 0;
    background-color: var(--section-bg);
}

.card-header {
    border-radius: 8px 8px 0 0 !important;
}

.msg-bot {
    border-radius: 15px 15px 15px 0 !important;
    font-size: 0.95rem;
}

.msg-user {
    border-radius: 15px 15px 0 15px !important;
    font-size: 0.95rem;
}

.chat-suggest {
    margin-right: 5px;
    margin-bottom: 5px;
    border: 1px solid rgba(255, 255, 255, 0.5) !important;
    font-size: 0.8rem;
}

/* Utilitaires */
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}
        .filter-btn.active {
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .stats-number {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .card-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        /* --- NOUVEAUX STYLES POUR LES COMMENTAIRES ET RATING --- */

.rating-stars {
    color: #ffc107; /* Jaune standard pour les étoiles */
    font-size: 1.2rem;
}

.star-filled {
    /* Style pour les étoiles pleines/demi-pleines */
    cursor: pointer;
}

/* Style de l'image de la communauté dans la colonne de droite */
#chatbot-section img.img-fluid {
    max-height: 200px;
    object-fit: cover;
    border: 3px solid var(--primary-green);
    border-radius: 8px;
}
    </style>
</head>

<body>
    <?php include '../front_office/includes/navbar.php'; ?>


<div id="step-home" class="container-fluid d-block">
  <div class="hero-section text-center">
    <div class="container">
      <h1 class="display-4 fw-bold">Chaque don compte. Ensemble, construisons un monde plus solidaire.</h1>
      <p class="lead mt-3">Donnez du sang, de l’argent ou de la nourriture – votre geste sauve des vies.</p>
      <button class="btn btn-donate mt-4" onclick="window.location.href='#associations-list'">Commencer un don</button>
    </div>
  </div>

  <div class="container py-5">
    <div class="row text-center mb-5">
      <div class="col">
        <h2 class="mb-4">Nos objectifs, vos impacts</h2>
        <p class="text-muted">
          Grâce à vous, nous avançons chaque jour vers un monde plus juste. Voici l’impact de notre communauté Solida.
        </p>
      </div>
    </div>
  <div class="row mb-5">
    <div class="col-md-4 mb-4">
        <div class="stats-card p-4 text-center shadow-sm">
            <i class="fas fa-tint fa-3x text-danger mb-3"></i>
            <div class="stats-number text-danger">
                <?= number_format($global_stats['total_sang'] ?? 0, 0, ',', ' '); ?>
            </div>
            <p class="mb-0 text-muted">Dons de Sang collectés</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="stats-card p-4 text-center shadow-sm">
            <i class="fas fa-euro-sign fa-3x text-success mb-3"></i>
            <div class="stats-number text-success">
                €<?= number_format($global_stats['total_argent'] ?? 0.0, 2, ',', ' '); ?>
            </div>
            <p class="mb-0 text-muted">Collectés pour les démunis</p>
        </div>
    </div>
    <div class="col-md-4 mb-4">
        <div class="stats-card p-4 text-center shadow-sm">
            <i class="fas fa-utensils fa-3x text-warning mb-3"></i>
            <div class="stats-number text-warning">
                <?= number_format($global_stats['total_nourriture'] ?? 0, 0, ',', ' '); ?>
            </div>
            <p class="mb-0 text-muted">Dons de Nourriture enregistrés</p>
        </div>
    </div>
</div>

<div class="row justify-content-center my-5">
    <div class="col-lg-8">
        <h2 class="text-center mb-4">Découvrez notre impact en vidéo</h2>
        <div class="video-container shadow-lg">
            <iframe 
                src="https://www.youtube.com/embed/votre_code_video" 
                frameborder="0" 
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                allowfullscreen
                title="Vidéo de présentation Solida"
            ></iframe>
        </div>
    </div>
</div>
      
      <div class="container py-5">
        <h2 class="text-center mb-4">Associations partenaires</h2>
        
        <div class="d-flex justify-content-center mb-4 flex-wrap">
            <button class="btn btn-outline-secondary mx-2 mb-2 filter-btn active" data-filter="all" id="filter-all">
                <i class="fas fa-list-ul"></i> Tout Afficher
            </button>
            <button class="btn btn-outline-danger mx-2 mb-2 filter-btn" data-filter="sang">
                <i class="<?= getIconClassByType('sang', $type_icons); ?>"></i> Sang
            </button>
            <button class="btn btn-outline-success mx-2 mb-2 filter-btn" data-filter="argent">
                <i class="<?= getIconClassByType('argent', $type_icons); ?>"></i> Argent
            </button>
            <button class="btn btn-outline-warning mx-2 mb-2 filter-btn" data-filter="nourriture">
                <i class="<?= getIconClassByType('nourriture', $type_icons); ?>"></i> Nourriture
            </button>
        </div>

        <div class="row" id="associations-list">

            <?php if (!empty($liste_associations_details)): ?>
                <?php foreach ($liste_associations_details as $item): 
                    $association = $item['association'];
                    $totaux = $item['totaux_dons'];
                    $type = $association->getTypeDonSupporte();
                    $data = $type_icons[$type] ?? ['icon' => 'fas fa-handshake text-muted', 'unit' => 'dons'];
                    
                    // Calcul du total pertinent pour l'affichage
                    $total_value = 0;
                    $total_display = '0';
                    
                    if ($type === 'argent') {
                        $total_value = $totaux['total_argent'];
                        $total_display = number_format($total_value, 2, ',', ' ');
                    } else if ($type === 'sang') {
                        $total_value = $totaux['total_sang'];
                        $total_display = number_format($total_value, 0, ',', ' ');
                    } else if ($type === 'nourriture') {
                        $total_value = $totaux['total_nourriture'];
                        $total_display = number_format($total_value, 0, ',', ' ');
                    }
                ?>
                <div class="col-md-4 mb-4 association-item" data-don-type="<?= $type; ?>"> 
                    <a href="<?= getDonsUrl('association2', ['id' => $association->getIdAssociation()]); ?>" class="card card-hover p-3 text-decoration-none text-dark h-100">
                        <div class="d-flex align-items-center mb-3">
                            <i class="<?= $data['icon']; ?> fa-3x me-3"></i>
                            <div>
                                <h5 class="card-title mb-0"><?= htmlspecialchars($association->getNomAssociation()); ?></h5>
                                <small class="text-muted"><?= ucfirst($association->getPays()); ?> | <?= ucfirst($type); ?></small>
                            </div>
                        </div>
                        
                        <p class="card-text text-muted mb-3 line-clamp-2">
                            <?= htmlspecialchars($association->getDescription()); ?>
                        </p>

                        <div class="mt-auto pt-3 border-top">
                            <p class="mb-0 text-uppercase fw-bold">
                                <i class="<?= $data['icon']; ?> me-1"></i>
                                Total collecté :
                            </p>
                            <div class="stats-number text-dark">
                                <?= $total_display; ?> <?= $data['unit']; ?>
                            </div>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center alert alert-info">
                    Aucune association partenaire n'a été trouvée dans la base de données.
                </div>
            <?php endif; ?>

        </div>
      </div>
    <div class="container py-5" id="chatbot-section">
    <h2 class="text-center mb-4">Besoin d'aide ? Discutez avec SolidaBot ou laissez-nous votre avis !</h2>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <h3 class="h4 text-center mb-3 text-muted"><i class="fas fa-robot me-2"></i> Assistance Rapide</h3>
            <div class="card shadow-lg h-100">
                <div class="card-header text-white" style="background-color: var(--primary-green);">
                    <i class="fas fa-robot me-2"></i> SolidaBot
                </div>
                <div class="card-body" style="height: 400px; overflow-y: auto;" id="chat-window">
                    <div class="d-flex justify-content-start mb-3">
                        <div class="msg-bot p-2 rounded text-white" style="background-color: #0d6efd; max-width: 80%;">
                            Bonjour ! Je suis SolidaBot, votre assistant de don. Posez-moi une question ou cliquez sur un sujet :
                            <div class="mt-2">
                                <button class="btn btn-sm btn-outline-light chat-suggest" data-q="Quels sont les types de dons acceptés ?">Types de dons</button>
                                <button class="btn btn-sm btn-outline-light chat-suggest" data-q="Comment fonctionne le don d'argent ?">Don d'argent</button>
                                <button class="btn btn-sm btn-outline-light chat-suggest" data-q="Comment savoir où va mon don ?">Traçabilité</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-light">
                    <div class="input-group">
                        <input type="text" id="user-input" class="form-control" placeholder="Posez votre question...">
                        <button class="btn btn-success" type="button" id="send-btn">
                            <i class="fas fa-paper-plane"></i> Envoyer
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <h3 class="h4 text-center mb-3 text-muted"><i class="fas fa-star me-2"></i> Avis et Communauté</h3>
            <div class="card shadow-lg h-100 p-3">

                <div class="text-center mb-4">
                    <img 
                        src="https://cdn.helloasso.com/img/uploads/266568433_10159512182453334_9142332478018171636_n-031bf8fb0fb54863be92466b5a78df6d.jpeg" 
                        alt="Photo de la communauté Solida" 
                        class="img-fluid rounded shadow-sm" 
                        style="max-height: 200px; object-fit: cover; width: 100%;"
                    >
                    <small class="text-muted d-block mt-2">L'impact de nos actions solidaires.</small>
                </div>

                <h4 class="h5 border-top pt-3">Laissez votre évaluation</h4>
                <div class="rating-stars mb-3" data-rating="4.5">
                    <i class="fas fa-star star-filled"></i>
                    <i class="fas fa-star star-filled"></i>
                    <i class="fas fa-star star-filled"></i>
                    <i class="fas fa-star star-filled"></i>
                    <i class="fas fa-star-half-alt star-filled"></i>
                    <span class="ms-2 text-muted">(4.5 / 5 basés sur X avis)</span>
                </div>
                
                <form>
                    <div class="mb-3">
                        <label for="comment-text" class="form-label">Votre commentaire :</label>
                        <textarea class="form-control" id="comment-text" rows="3" placeholder="Partagez votre expérience..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="rating-input" class="form-label">Note (1-5) :</label>
                        <input type="number" class="form-control w-25" id="rating-input" min="1" max="5" value="5">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Soumettre l'avis</button>
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
                        1, 2 rue André Ampère - 2083 - Pôle Technologique - El Ghazala
                    </li>
                    <li>
                        <i class="fa fa-phone fa-fw"></i>
                        <a class="text-decoration-none" href="tel:010-020-0340">010-020-0340</a>
                    </li>
                    <li>
                        <i class="fa fa-envelope fa-fw"></i>
                        <a class="text-decoration-none" href="mailto:info@company.com">info@company.com</a>
                    </li>
                </ul>
            </div>

            <div class="col-md-4 pt-5">
                <h2 class="h2 text-light border-bottom pb-3 border-light">Further Info</h2>
                <ul class="list-unstyled text-light footer-link-list">
                    <li><a class="text-decoration-none" href="<?= getHomeUrl(); ?>">Home</a></li>
                    <li><a class="text-decoration-none" href="#">Événement</a></li>
                    <li><a class="text-decoration-none" href="<?= getDonsUrl('step-type'); ?>">Dons</a></li>
                    <li><a class="text-decoration-none" href="<?= getHomeUrl(); ?>?section=reclamations">Reclamation</a></li>
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
<script src="assets/js/jquery-1.11.0.min.js"></script>
    <script src="assets/js/jquery-migrate-1.2.1.min.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/templatemo.js"></script>
    <script src="assets/js/custom.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>


<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. LOGIQUE DE FILTRAGE DES ASSOCIATIONS ---
        const filterButtons = document.querySelectorAll('.filter-btn');
        const associationItems = document.querySelectorAll('.association-item');

        const filterAssociations = (filterType) => {
            filterButtons.forEach(btn => {
                if (btn.getAttribute('data-filter') === filterType) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            associationItems.forEach(item => {
                const itemType = item.getAttribute('data-don-type');
                if (filterType === 'all' || itemType === filterType) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        };

        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                const filterType = this.getAttribute('data-filter');
                filterAssociations(filterType);
            });
        });
        
        const filterAllBtn = document.getElementById('filter-all');
        if (filterAllBtn) {
             filterAssociations('all');
        }


        // --- 2. LOGIQUE CHATBOT (CORRIGÉE) ---
        
        const chatWindow = document.getElementById('chat-window');
        const userInput = document.getElementById('user-input');
        const sendBtn = document.getElementById('send-btn');
        
        // VÉRIFICATION CRITIQUE : Si un des éléments manque, on arrête l'exécution du chatbot.
        if (!chatWindow || !userInput || !sendBtn) {
            console.error("ERREUR CHATBOT : Un élément HTML (chat-window, user-input ou send-btn) est manquant. Vérifiez vos ID.");
            return;
        }

        // Base de connaissances du Chatbot (Q/R)
        const knowledgeBase = {
            "types de dons": "Solida accepte trois types de dons : le sang, l'argent et la nourriture. Choisissez l'icône correspondante en haut de page pour commencer.",
            "don d'argent": "Le don d'argent est traité via une plateforme de paiement sécurisée (tokenisation simulée dans notre projet). Nous ne stockons aucune information de carte. Les fonds sont directement reversés à l'association de votre choix.",
            "traçabilité": "Après validation, vous recevrez un e-mail de confirmation avec un numéro de référence unique. Vous pourrez ensuite suivre le statut de votre don dans votre Historique (fonctionnalité à venir).",
            "association": "Nous travaillons avec des associations partenaires. Utilisez le filtre au-dessus pour voir celles qui acceptent votre type de don, ou cliquez sur 'Voir Détails' pour leurs coordonnées.",
            "aide": "Je suis SolidaBot. Je peux répondre aux questions sur les dons de sang, d'argent, de nourriture et la traçabilité. Essayez 'types de dons' ou 'aide'.",
            "default": "Désolé, je n'ai pas compris votre question. Pouvez-vous reformuler ou essayer un terme comme 'types de dons' ou 'aide' ?",
        };

        // Fonction pour ajouter un message à la fenêtre de chat
        function appendMessage(sender, text) {
            const isBot = sender === 'bot';
            const messageDiv = document.createElement('div');
            messageDiv.classList.add('d-flex', 'mb-3', isBot ? 'justify-content-start' : 'justify-content-end');
            
            const msgContent = document.createElement('div');
            msgContent.classList.add('p-2', 'rounded');
            msgContent.style.maxWidth = '80%';
            
            if (isBot) {
                msgContent.classList.add('msg-bot', 'text-white');
                msgContent.style.backgroundColor = '#0d6efd';
            } else {
                msgContent.classList.add('msg-user', 'bg-light');
                msgContent.style.backgroundColor = '#f0f0f0';
            }
            
            text = text.replace(/SolidaBot/g, '<strong>SolidaBot</strong>');
            
            msgContent.innerHTML = text;
            messageDiv.appendChild(msgContent);
            chatWindow.appendChild(messageDiv);
            
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }

        // Fonction pour obtenir la réponse du chatbot
        function getBotResponse(message) {
            const msg = message.toLowerCase();
            for (const key in knowledgeBase) {
                if (msg.includes(key)) {
                    return knowledgeBase[key];
                }
            }
            return knowledgeBase['default'];
        }

        // Gestion de l'envoi de message
        function handleSendMessage() {
            const userText = userInput.value.trim();
            if (userText === "") return;

            // 1. Afficher le message de l'utilisateur
            appendMessage('user', userText);
            
            // 2. Vider le champ d'entrée
            userInput.value = '';

            // 3. Récupérer et afficher la réponse du bot après un court délai
            setTimeout(() => {
                const botResponse = getBotResponse(userText);
                appendMessage('bot', botResponse);
            }, 500); 
        }

        // Événement sur le bouton d'envoi
        sendBtn.addEventListener('click', handleSendMessage);

        // Événement sur la touche Entrée
        userInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                handleSendMessage();
            }
        });

        // Événement sur les suggestions du bot
        chatWindow.addEventListener('click', function(e) {
            if (e.target.classList.contains('chat-suggest')) {
                const suggestedQuestion = e.target.getAttribute('data-q');
                userInput.value = suggestedQuestion;
                handleSendMessage();
            }
        });
    });
</script>
</body>
</html>