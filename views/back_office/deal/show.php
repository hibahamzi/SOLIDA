<?php
// Check if this file is being included by the controller or accessed directly
if (!isset($deal)) {
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
    $controller->show();
    // After calling show(), the controller includes this file again, so we exit here
    exit;
}

// If we reach here, the file was included by the controller
// $deal is already set by DealController->show()
$deal = $deal ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - Détail du Deal</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">

    <style>
        .deal-detail-wrapper {
            width: 100%;
            margin: 0 auto 20px auto;
        }
        .deal-detail-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .deal-title {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .deal-sponsor {
            font-weight: 500;
            color: #17a2b8;
            margin-bottom: 15px;
        }
        .deal-meta {
            margin-bottom: 10px;
            font-size: 15px;
        }
        .deal-meta strong {
            font-weight: 600;
        }
        .offer-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 15px;
            font-size: 13px;
            color: #fff;
        }
        .badge-available {
            background-color: #28a745;
        }
        .badge-expired {
            background-color: #dc3545;
        }
        .deal-description {
            margin-top: 15px;
            font-size: 15px;
        }
        .deal-views {
            margin-top: 10px;
            font-size: 14px;
            color: #555;
        }
        .back-link {
            display:inline-block;
            margin-top:20px;
            text-decoration:none;
            color:#007bff;
            font-weight:500;
        }
        .back-link i {
            margin-right:5px;
        }

        /* Bloc coupon */
        .coupon-section {
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .coupon-info {
            font-size: 14px;
        }
        .coupon-code-text {
            font-size: 18px;
            font-weight: bold;
            letter-spacing: 3px;
        }
        .coupon-code-hidden {
            display: none;
            margin-top: 10px;
        }

        /* Bouton imprimer */
        .print-btn-container {
            margin-top: 20px;
            text-align: right;
        }

        @media (max-width: 576px) {
            .deal-detail-wrapper {
                width: 95%;
            }
            .clr {
                background-color: green;
            }
        }

        /* Styles pour l'impression (PDF via Ctrl+P / Imprimer) */
        @media print {
            body {
                background: #ffffff !important;
            }
            /* Cacher les menus, footer, boutons inutiles */
            #templatemo_nav_top,
            .clr,
            footer,
            .back-link,
            .print-btn-container,
            .modal,
            .navbar {
                display: none !important;
            }
            .deal-detail-wrapper {
                width: 100% !important;
                margin: 0 !important;
            }
            .deal-detail-card {
                box-shadow: none !important;
                border-radius: 0 !important;
            }
        }
    </style>
</head>

<body>

    <?php include '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <?php 
        $pageTitle = 'Détail du Deal';
        include '../includes/topbar.php'; 
        ?>
        
        <div class="content-area">
            <div class="table-container">
                <div class="table-header">
                    <h2>Détail du Deal</h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                
                <div class="deal-detail-wrapper">
                    <div class="deal-detail-card">
                        <?php
                        // Calcul pour badge "Disponible / Expirée"
                        $dateDebut = $deal['dateDebut'] ?? null;
                        $periode   = (int)($deal['periodeValidite'] ?? 0);
                        $isExpired = false;

                        if ($dateDebut && $periode > 0) {
                            $start = new DateTime($dateDebut);
                            $end   = clone $start;
                            $end->modify('+' . $periode . ' days');
                            $now   = new DateTime('today');

                            if ($now > $end) {
                                $isExpired = true;
                            }
                        }

                        $badgeClass = $isExpired ? 'badge-expired' : 'badge-available';
                        $badgeText  = $isExpired ? 'Expirée' : 'Disponible';

                        // Coupon + QR (API goqr.me)
                        $hasCoupon  = strtoupper(trim($deal['has_coupon'] ?? 'NON'));
                        $couponCode = trim($deal['coupon_code'] ?? '');
                        $qrUrl      = null;

                        // Check if coupon exists - show QR code if coupon_code exists and has_coupon is 'OUI' or similar
                        // Also show if coupon_code exists even if has_coupon might be inconsistent
                        if (!empty($couponCode) && ($hasCoupon === 'OUI' || $hasCoupon === 'YES' || $hasCoupon === '1' || $hasCoupon === 'TRUE')) {
                            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?'
                                   . 'size=200x200'
                                   . '&data=' . urlencode($couponCode);
                        } elseif (!empty($couponCode)) {
                            // If coupon_code exists but has_coupon is not 'OUI', still show QR code
                            $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?'
                                   . 'size=200x200'
                                   . '&data=' . urlencode($couponCode);
                        }
                        ?>

                        <div id="deal-print-block">

                            <!-- Bouton imprimer -->
                            <div class="print-btn-container">
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print();">
                                    <i class="fa fa-print"></i> Imprimer l'offre
                                </button>
                            </div>

                            <!-- NOUVEL LAYOUT : 2 colonnes dans le même bloc -->
                            <div class="row">
                                <!-- Colonne gauche : informations du deal -->
                                <div class="col-md-7">
                                    <div class="deal-title">
                                        <?= htmlspecialchars($deal['intitule'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>

                                    <div class="deal-sponsor">
                                        Sponsor : <?= htmlspecialchars($deal['nomEntreprise'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>

                                    <div class="deal-meta">
                                        Prix initial :
                                        <strong><?= htmlspecialchars($deal['prixInitial'] ?? $deal['prixinitial'] ?? 0, ENT_QUOTES, 'UTF-8') ?> DT</strong><br>
                                        Réduction :
                                        <strong><?= htmlspecialchars($deal['reduction'], ENT_QUOTES, 'UTF-8') ?> %</strong>
                                    </div>

                                    <div class="deal-meta">
                                        Début :
                                        <?= htmlspecialchars($deal['dateDebut'], ENT_QUOTES, 'UTF-8') ?><br>
                                        Période :
                                        <?= htmlspecialchars($deal['periodeValidite'], ENT_QUOTES, 'UTF-8') ?> jours
                                    </div>

                                    <div class="deal-meta">
                                        Statut :
                                        <span class="offer-badge <?= $badgeClass ?>"><?= $badgeText ?></span>
                                    </div>

                                    <div class="deal-views">
                                        <i class="fa fa-eye"></i>
                                        Vues :
                                        <strong><?= (int)($deal['click_count'] ?? 0) ?></strong>
                                    </div>

                                    <?php if (!empty($deal['descriptionD'])): ?>
                                        <div class="deal-description">
                                            <?= nl2br(htmlspecialchars($deal['descriptionD'], ENT_QUOTES, 'UTF-8')) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Colonne droite : QR + texte + bouton -->
                                <div class="col-md-5">
                                    <?php if ($qrUrl): ?>
                                        <div class="coupon-section" style="border-top:none; margin-top:0; padding-top:0;">
                                            <div style="text-align:center;">
                                                <img src="<?= htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8') ?>"
                                                     alt="QR Code coupon"
                                                     width="200"
                                                     height="200"
                                                     style="border:1px solid #ddd; border-radius:10px; background:#fff; padding:10px; display:block; margin:0 auto;">
                                            </div>
                                            <div class="coupon-info">
                                                <p><strong>Coupon disponible pour ce deal</strong></p>
                                                <p>Scannez ce QR code avec votre téléphone pour voir le code du coupon.</p>

                                                <!-- Bouton pour voir le code directement sans scanner -->
                                                <button type="button" class="btn btn-success btn-sm mt-2"
                                                        onclick="document.getElementById('coupon-code').classList.toggle('coupon-code-hidden');">
                                                    <i class="fas fa-eye"></i> Voir le code
                                                </button>

                                                <div id="coupon-code" class="coupon-code-hidden">
                                                    <p class="mt-2"><strong>Code :</strong></p>
                                                    <span class="coupon-code-text" style="display:block; margin-top:10px;">
                                                        <?= htmlspecialchars($couponCode, ENT_QUOTES, 'UTF-8') ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <div class="coupon-section" style="border-top:none; margin-top:0; padding-top:0;">
                                            <div class="coupon-info">
                                                <p><em>Aucun coupon disponible pour ce deal.</em></p>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- fin row -->

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Function to toggle coupon code visibility
        function toggleCouponCode() {
            const codeElement = document.getElementById('coupon-code');
            if (codeElement) {
                codeElement.classList.toggle('coupon-code-hidden');
            }
        }
    </script>
</body>
</html>