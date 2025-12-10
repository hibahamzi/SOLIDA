<?php
// Variables fournies par DealController::assistantIA()
// $question, $aiAnswer, $errorAi, $recentDeals
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - Assistant IA (Deals)</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">

    <link rel="stylesheet" href="/PROJET_WEB_MVC_FINAL/public/backoffice/assets/css/admin.css">

    <style>
        .ai-container {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
        }
        .ai-header {
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-bottom:15px;
        }
        .ai-header h2 {
            margin:0;
            display:flex;
            align-items:center;
            gap:10px;
        }
        .ai-header h2 i {
            color:#59ab6e;
        }
        .ai-question {
            margin-bottom: 15px;
        }
        .ai-question textarea {
            width:100%;
            min-height:120px;
            padding:10px;
            border-radius:6px;
            border:1px solid #ddd;
            resize: vertical;
            font-family: inherit;
        }
        .ai-answer {
            margin-top:20px;
            padding:15px;
            border-radius:8px;
            background:#f5f7fb;
            border:1px solid #e0e3f0;
            white-space:pre-wrap;
        }
        .ai-error {
            margin-top:10px;
            color:#c0392b;
            font-size:14px;
        }
        .recent-deals {
            margin-top:25px;
            font-size:14px;
        }
        .recent-deals ul {
            padding-left:20px;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-header">
        <h2>SOLIDA</h2>
        <p>Panneau d'Administration</p>
    </div>

    <div style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <div style="width: 45px; height: 45px; border-radius: 50%; background: linear-gradient(135deg, #59ab6e, #69bb7e); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 18px;">
                AD
            </div>
            <div style="flex: 1; min-width: 0;">
                <div style="color: white; font-weight: 600; font-size: 14px;">Administrateur</div>
                <div style="color: rgba(255,255,255,0.7); font-size: 12px;">admin@solida.com</div>
            </div>
        </div>
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
        <h1>Assistant IA - Offres & Deals</h1>
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
        <div class="ai-container">
            <div class="ai-header">
                <h2>
                    <i class="fas fa-robot"></i> Assistant IA
                </h2>
                <a href="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=index"
                   class="btn btn-small">
                    <i class="fas fa-arrow-left"></i> Retour aux deals
                </a>
            </div>

            <p style="margin-bottom:10px;">
                Posez une question sur vos offres (popularité, idées d'amélioration, types d'offres à créer, etc.).
                L'IA analysera vos <strong>10 derniers deals</strong> (titre, sponsor, prix, réduction, vues).
            </p>

            <form method="post"
                  action="/PROJET_WEB_MVC_FINAL/public/index1.php?controller=deal&action=assistantIA">
                <div class="ai-question">
                    <label for="question"><strong>Question pour l'IA :</strong></label>
                    <textarea id="question" name="question"
                              placeholder="Exemples : 
- Quelles sont les offres les plus intéressantes pour les nouveaux étudiants ?
- Comment améliorer la performance de mes deals restauration ?
- Propose-moi 3 idées de nouvelles offres adaptées aux étudiants."
                    ><?= htmlspecialchars($question ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Poser la question à l'IA
                </button>
            </form>

            <?php if (!empty($errorAi)): ?>
                <div class="ai-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?= htmlspecialchars($errorAi, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($aiAnswer)): ?>
                <div class="ai-answer">
                    <strong>Réponse de l'IA :</strong><br><br>
                    <?= nl2br(htmlspecialchars($aiAnswer, ENT_QUOTES, 'UTF-8')) ?>
                </div>
            <?php endif; ?>

            <div class="recent-deals">
                <strong>Contexte utilisé (10 derniers deals) :</strong>
                <?php if (empty($recentDeals)): ?>
                    <p>Aucun deal en base pour le moment.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($recentDeals as $d): ?>
                            <li>
                                <strong><?= htmlspecialchars($d['intitule'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                (Sponsor : <?= htmlspecialchars($d['nomEntreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>,
                                Prix : <?= htmlspecialchars($d['prixinitial'] ?? 0, ENT_QUOTES, 'UTF-8') ?> DT,
                                Réduction : <?= htmlspecialchars($d['reduction'] ?? 0, ENT_QUOTES, 'UTF-8') ?> %,
                                Vues : <?= (int)($d['click_count'] ?? 0) ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

</body>
</html>