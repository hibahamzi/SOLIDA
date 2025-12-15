<?php
// Check if this file is being included by the controller or accessed directly
if (!isset($messages)) {
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
    $controller->assistantIA();
    // After calling assistantIA(), the controller includes this file again, so we exit here
    exit;
}

// If we reach here, the file was included by the controller
// $messages, $errorAi, $recentDeals are already set by DealController->assistantIA()
$messages = $messages ?? [];
$errorAi = $errorAi ?? null;
$recentDeals = $recentDeals ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA Admin - Assistant IA</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    
    <!-- Admin CSS -->
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .chat-wrapper {
            max-width: 100%;
        }
        .chat-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 15px 15px 5px 15px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            display:flex;
            flex-direction:column;
            height: 600px;
        }
        .chat-header {
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-bottom:10px;
            padding: 0 5px;
        }
        .chat-header h2 {
            margin:0;
            display:flex;
            align-items:center;
            gap:10px;
            font-size: 20px;
        }
        .chat-header h2 i {
            color:#28a745;
        }
        .chat-header a.btn-back {
            font-size: 13px;
            padding: 5px 10px;
            border-radius: 8px;
            background:#e9ecef;
            color:#333;
            text-decoration:none;
        }
        .chat-header a.btn-back:hover {
            background:#ced4da;
        }

        .chat-history {
            flex: 1;
            overflow-y:auto;
            padding: 10px;
            border-radius: 8px;
            background:#f5f5f5;
            margin-bottom:10px;
        }
        .message-row {
            margin-bottom:8px;
            display:flex;
        }
        .message-row.user {
            justify-content:flex-end;
        }
        .message-row.assistant {
            justify-content:flex-start;
        }
        .msg-bubble {
            max-width: 70%;
            border-radius: 12px;
            padding:8px 10px;
            font-size:14px;
            position:relative;
            white-space:pre-wrap;
        }
        .msg-bubble.user {
            background:#28a745;
            color:#fff;
            border-bottom-right-radius:2px;
        }
        .msg-bubble.assistant {
            background:#ffffff;
            border:1px solid #e0e0e0;
            border-bottom-left-radius:2px;
        }
        .msg-meta {
            font-size:11px;
            color:#aaa;
            margin-top:2px;
        }

        .chat-input-area {
            border-top: 1px solid #ddd;
            padding-top:8px;
        }
        .chat-input-area textarea {
            width:100%;
            min-height:70px;
            max-height:120px;
            padding:8px;
            border-radius:6px;
            border:1px solid #ccc;
            resize: vertical;
            font-size:14px;
        }
        .chat-actions {
            margin-top:6px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:10px;
        }
        .btn-send {
            background:#28a745;
            color:#fff;
            border:none;
            padding:6px 14px;
            border-radius:8px;
            font-size:14px;
            cursor:pointer;
        }
        .btn-send i { margin-right:4px; }
        .btn-send:hover {
            background:#218838;
        }
        .btn-reset {
            background:#e63946;
            color:#fff;
            border:none;
            padding:4px 10px;
            border-radius:8px;
            font-size:12px;
            cursor:pointer;
        }
        .btn-reset:hover {
            background:#c82333;
        }

        .ai-error {
            margin-top:5px;
            padding:5px 8px;
            border-radius:6px;
            background:#f8d7da;
            color:#721c24;
            font-size:13px;
        }

        .recent-deals {
            margin-top:10px;
            font-size:12px;
            color:#555;
        }
        .recent-deals strong { font-size:13px; }
        .recent-deals ul {
            padding-left:18px;
        }
    </style>
</head>
<body>

    <?php include '../includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <div class="main-content">
        
        <?php 
        $pageTitle = 'Assistant IA';
        include '../includes/topbar.php'; 
        ?>
        
        <div class="content-area">
            <div class="table-container">
                <div class="table-header">
                    <h2>Assistant IA - Chat sur les Offres & Sponsors</h2>
                    <a href="index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>
                
                <div class="chat-wrapper" style="padding: 20px;">
        <div class="chat-card">

            <div class="chat-header">
                <h2>
                    <i class="fas fa-robot"></i> Chat IA
                </h2>
            </div>

            <div class="chat-history" id="chatHistory">
                <?php if (empty($messages)): ?>
                    <div class="message-row assistant">
                        <div class="msg-bubble assistant">
                            Bonjour 👋<br>
                            Je suis l'assistant IA de SOLIDA. Pose-moi une question sur tes offres ou tes sponsors
                            (meilleures réductions, offres les plus vues, sponsors les plus chers, etc.).
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $msg): ?>
                        <div class="message-row <?= $msg['role'] === 'user' ? 'user' : 'assistant' ?>">
                            <div class="msg-bubble <?= $msg['role'] === 'user' ? 'user' : 'assistant' ?>">
                                <?= nl2br(htmlspecialchars($msg['content'], ENT_QUOTES, 'UTF-8')) ?>
                                <div class="msg-meta">
                                    <?= htmlspecialchars($msg['time'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                    <?= $msg['role'] === 'user' ? ' • toi' : ' • assistant' ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <form method="post"
                  action="index.php"
                  class="chat-input-area">
                <input type="hidden" name="action" value="assistantIA">
                <textarea id="question" name="question"
                          placeholder="Écris ta question ici..."></textarea>

                <div class="chat-actions">
                    <div>
                        <button type="submit" name="send_question" value="1" class="btn-send">
                            <i class="fas fa-paper-plane"></i> Envoyer
                        </button>
                        <?php if (!empty($messages)): ?>
                            <button type="submit" name="reset_chat" value="1" class="btn-reset">
                                Réinitialiser le chat
                            </button>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($errorAi)): ?>
                        <div class="ai-error">
                            <i class="fas fa-exclamation-triangle"></i>
                            <?= htmlspecialchars($errorAi, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

            <div class="recent-deals">
                <strong>Offres analysées (10 dernières) :</strong>
                <?php if (empty($recentDeals)): ?>
                    <p>Aucune offre n'est encore enregistrée.</p>
                <?php else: ?>
                    <ul>
                        <?php foreach ($recentDeals as $d): ?>
                            <li>
                                <strong><?= htmlspecialchars($d['intitule'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                (Sponsor : <?= htmlspecialchars($d['nomEntreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?>,
                                Prix : <?= htmlspecialchars($d['prixInitial'] ?? $d['prixinitial'] ?? 0, ENT_QUOTES, 'UTF-8') ?> DT,
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
    </div>

<script>
    // Scroll en bas du chat au chargement
    const chatHistory = document.getElementById('chatHistory');
    if (chatHistory) {
        chatHistory.scrollTop = chatHistory.scrollHeight;
    }
</script>

</body>
</html>