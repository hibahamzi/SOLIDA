<?php
// Check if this file is being included by the controller or accessed directly
if (!isset($messages)) {
    // File is being accessed directly, so we need to set everything up
    require_once '../../../config/config.php';
    
    // Start session only if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    require_once '../../../controllers/SponsorController.php';
    $controller = new SponsorController($pdo);
    $controller->assistantIA();
    // After calling assistantIA(), the controller includes this file again, so we exit here
    exit;
    // DEBUG TEMP
// var_dump('ASSISTANT_IA user_id = ', $_SESSION['user_id'] ?? null);
}

// If we reach here, the file was included by the controller
// $messages, $errorAi, $recentSponsors are already set by SponsorController->assistantIA()
$messages = $messages ?? [];
$errorAi = $errorAi ?? null;
$recentSponsors = $recentSponsors ?? [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>SOLIDA - Assistant IA Sponsors</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    
    <!-- Include navbar -->
    <?php include __DIR__ . '/../includes/navbar.php'; ?>    <style>
        body {
            background: #f8f9fa;
            padding-top: 100px;
        }
        .chat-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }
        .chat-card {
            background: #ffffff;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        .chat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        .chat-header h2 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            color: #28a745;
        }
        .chat-history {
            flex: 1;
            overflow-y: auto;
            padding: 15px;
            border-radius: 8px;
            background: #f5f5f5;
            margin-bottom: 15px;
        }
        .message-row {
            margin-bottom: 12px;
            display: flex;
        }
        .message-row.user {
            justify-content: flex-end;
        }
        .message-row.assistant {
            justify-content: flex-start;
        }
        .msg-bubble {
            max-width: 70%;
            border-radius: 12px;
            padding: 12px 15px;
            font-size: 14px;
            position: relative;
            white-space: pre-wrap;
        }
        .msg-bubble.user {
            background: #28a745;
            color: #fff;
            border-bottom-right-radius: 2px;
        }
        .msg-bubble.assistant {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-bottom-left-radius: 2px;
        }
        .msg-meta {
            font-size: 11px;
            color: #aaa;
            margin-top: 5px;
        }
        .chat-input-area {
            border-top: 1px solid #ddd;
            padding-top: 15px;
        }
        .chat-input-area textarea {
            width: 100%;
            min-height: 80px;
            max-height: 150px;
            padding: 10px;
            border-radius: 8px;
            border: 1px solid #ccc;
            resize: vertical;
            font-size: 14px;
        }
        .chat-actions {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
        }
        .btn-send {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 14px;
            cursor: pointer;
        }
        .btn-send:hover {
            background: #218838;
        }
        .btn-reset {
            background: #dc3545;
            color: #fff;
            border: none;
            padding: 6px 15px;
            border-radius: 8px;
            font-size: 12px;
            cursor: pointer;
        }
        .btn-reset:hover {
            background: #c82333;
        }
        .ai-error {
            margin-top: 10px;
            padding: 10px;
            border-radius: 6px;
            background: #f8d7da;
            color: #721c24;
            font-size: 13px;
        }
        .recent-sponsors {
            margin-top: 15px;
            font-size: 12px;
            color: #555;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .recent-sponsors strong {
            font-size: 13px;
            display: block;
            margin-bottom: 5px;
        }
        .recent-sponsors ul {
            padding-left: 20px;
            margin: 0;
        }
    </style>
</head>
<body>

    <section class="container py-5">
        <div class="row">
            <div class="col-12">
                <a href="index.php?section=sponsors" class="btn btn-secondary mb-3">
                    <i class="fas fa-arrow-left"></i> Retour aux sponsors
                </a>
                
                <div class="chat-wrapper">
                    <div class="chat-card">
                        <div class="chat-header">
                            <h2>
                                <i class="fas fa-robot"></i> Assistant IA - Sponsors
                            </h2>
                        </div>

                        <div class="chat-history" id="chatHistory">
                            <?php if (empty($messages)): ?>
                                <div class="message-row assistant">
                                    <div class="msg-bubble assistant">
                                        Bonjour 👋<br>
                                        Je suis l'assistant IA de SOLIDA. Pose-moi une question sur les sponsors
                                        (types de sponsoring, montants engagés, domaines d'activité, etc.).
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php foreach ($messages as $msg): ?>
                                    <div class="message-row <?= $msg['role'] === 'user' ? 'user' : 'assistant' ?>">
                                        <div class="msg-bubble <?= $msg['role'] === 'user' ? 'user' : 'assistant' ?>">
                                            <?= nl2br(htmlspecialchars($msg['content'], ENT_QUOTES, 'UTF-8')) ?>
                                            <div class="msg-meta">
                                                <?= htmlspecialchars($msg['time'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                                <?= $msg['role'] === 'user' ? ' • vous' : ' • assistant' ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <form method="post"
                              action="index.php?section=sponsor&action=assistantIA"
                              class="chat-input-area">
                            <textarea id="question" name="question"
                                      placeholder="Écris ta question ici..."></textarea>

                            <div class="chat-actions">
                                <div>
                                    <button type="submit" name="send_question" value="1" class="btn btn-success btn-send">
                                        <i class="fas fa-paper-plane"></i> Envoyer
                                    </button>
                                    <?php if (!empty($messages)): ?>
                                        <button type="submit" name="reset_chat" value="1" class="btn btn-danger btn-reset">
                                            Réinitialiser
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

                        <div class="recent-sponsors">
                            <strong>Sponsors analysés (10 derniers) :</strong>
                            <?php if (empty($recentSponsors)): ?>
                                <p>Aucun sponsor n'est encore enregistré.</p>
                            <?php else: ?>
                                <ul>
                                    <?php foreach ($recentSponsors as $s): ?>
                                        <li>
                                            <strong><?= htmlspecialchars($s['nomEntreprise'] ?? '', ENT_QUOTES, 'UTF-8') ?></strong>
                                            (Type : <?= htmlspecialchars($s['typeSponsoring'] ?? '', ENT_QUOTES, 'UTF-8') ?>,
                                            Montant : <?= htmlspecialchars($s['montantEngage'] ?? 0, ENT_QUOTES, 'UTF-8') ?>,
                                            Domaine : <?= htmlspecialchars($s['domaineActivite'] ?? 'Non spécifié', ENT_QUOTES, 'UTF-8') ?>,
                                            Offres : <?= (int)($s['totalDeals'] ?? 0) ?>)
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <?php include '../includes/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Scroll en bas du chat au chargement
        const chatHistory = document.getElementById('chatHistory');
        if (chatHistory) {
            chatHistory.scrollTop = chatHistory.scrollHeight;
        }
    </script>

</body>
</html>

