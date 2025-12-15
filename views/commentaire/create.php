<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupération sécurisée de l'id_forum (le sujet sur lequel on commente)
$id_forum = isset($_GET['id_forum']) ? (int) $_GET['id_forum'] : 0;
if ($id_forum <= 0) {
    // Try to get from POST if it's a form submission
    $id_forum = isset($_POST['id_forum']) ? (int) $_POST['id_forum'] : 0;
    if ($id_forum <= 0) {
        header('Location: index.php?section=forum');
        exit();
    }
}

// Récupération éventuelle du parent_id (si on répond à un commentaire)
$parent_id = isset($_GET['parent_id']) ? (int) $_GET['parent_id'] : 0;
if ($parent_id <= 0 && isset($_POST['parent_id']) && $_POST['parent_id'] !== '') {
    $parent_id = (int) $_POST['parent_id'];
}

// Id de l'auteur (from session)
$id_auteur = isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
if (!$id_auteur) {
    header('Location: sign-in.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <title>Nouveau Commentaire - SOLIDA Forum</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="../front_office/assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../front_office/assets/css/templatemo.css">
    <link rel="stylesheet" href="../front_office/assets/css/custom.css">

    <!-- Fonts & icons -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="../front_office/assets/css/fontawesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        .forum-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.06);
            margin: 20px 0 60px 0;
        }

        .forum-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 15px;
            background-color: #f8f9fa;
            border-radius: 6px;
        }
        .action-button {
            background-color: #28a745;
            color: white;
            text-decoration: none;
            font-weight: bold;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }
        .action-button:hover {
            background-color: #218838;
            color: white;
            text-decoration: none;
        }

        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #212934;
        }
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        .form-control:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 0 2px rgba(40, 167, 69, 0.15);
        }
        textarea.form-control {
            resize: vertical;
            min-height: 120px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-primary {
            background: #28a745;
            color: white;
        }
        .btn-primary:hover {
            background: #218838;
            color: #fff;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }

        .floating-help-chat {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
            background-color: #ffc107;
            color: #343a40;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
        }
        .floating-help-chat:hover {
            background-color: #e0a800;
            transform: scale(1.05);
        }
    </style>
</head>

<body>
<?php include '../front_office/includes/navbar.php'; ?>

    <!-- Titre page -->
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-12">
                <h1 class="h1 text-success">
                    <i class="fas fa-reply me-3"></i>
                    <?php echo $parent_id > 0 ? 'Répondre à un Commentaire' : 'Ajouter un Commentaire'; ?>
                </h1>
                <p class="lead">
                    <?php echo $parent_id > 0
                        ? 'Vous répondez à un commentaire existant.'
                        : 'Partagez votre avis et répondez à la discussion.'; ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Contenu / Formulaire commentaire -->
    <div class="container forum-container">
        <div class="floating-help-chat"
             onclick="alert('Ouverture du Chat d\\'Aide ou de l\\'Assistance IA...')">
            <i class="fas fa-question-circle"></i>
        </div>

        <div class="forum-controls">
            <a href="index.php?section=forum" class="action-button">
                <i class="fas fa-arrow-left"></i> Retour au Forum
            </a>
            <span style="font-size: 13px; color:#6c757d;">
                Les champs marqués d'un <strong>*</strong> sont obligatoires.
            </span>
        </div>

        <form id="commentForm"
              action="index.php?section=forum"
              method="POST">
            <input type="hidden" name="action" value="store_comment">
            <!-- champs cachés : forum + parent éventuel (id_auteur comes from session) -->
            <input type="hidden" name="id_forum"
                   value="<?php echo htmlspecialchars($id_forum); ?>">
            <input type="hidden" name="parent_id"
                   value="<?php echo $parent_id > 0 ? (int)$parent_id : ''; ?>">

            <div class="form-group" style="margin-bottom: 30px;">
                <label for="contenu" class="form-label">Votre Commentaire *</label>
                <textarea class="form-control" id="contenu" name="contenu" rows="6"
                          placeholder="Rédigez votre commentaire ici..."
                          required></textarea>
            </div>
            
            <div style="display: flex; gap: 15px; justify-content: flex-end;
                        border-top: 1px solid #e9ecef; padding-top: 25px;">
                <a href="index.php?section=forum" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Annuler
                </a>
                <button type="submit" id="submitBtn" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Publier le Commentaire
                </button>
            </div>
        </form>
    </div>

<?php include '../front_office/includes/footer.php'; ?>
</body>
</html>