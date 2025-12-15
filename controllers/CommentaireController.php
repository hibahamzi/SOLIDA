<?php
require_once __DIR__ . '/../models/CommentaireModel.php';

class CommentaireController
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        $this->redirectToIndex();
    }

    public function adminIndex(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        try {
            $sql = "SELECT c.*, u.fullname as auteur_nom, f.discussion_g as forum_titre
                    FROM commentaire c
                    LEFT JOIN users u ON c.id_auteur = u.id
                    LEFT JOIN forum f ON c.id_forum = f.id_forum
                    ORDER BY c.date_commentaire DESC";

            $stmt = $this->pdo->query($sql);
            $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $error = null;
        } catch (PDOException $e) {
            $comments = [];
            $error = 'Erreur de connexion à la base de données: ' . $e->getMessage();
        }

        require __DIR__ . '/../views/commentaire/indexCommentaire.php';
    }

    /**
     * Formulaire pour ajouter un commentaire (ou une réponse) côté front
     */
    public function create(): void
    {
        $id_forum = isset($_GET['id_forum']) ? (int) $_GET['id_forum'] : 0;
        $parent_id = isset($_GET['parent_id']) ? (int) $_GET['parent_id'] : 0;

        if ($id_forum <= 0) {
            $this->redirectToIndex();
            return;
        }

        require __DIR__ . '/../views/commentaire/create.php';
    }

    /**
     * Enregistre un commentaire / réponse
     */
    public function store(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';
        $id_auteur = $_SESSION['user_id'] ?? null;
        $id_forum = isset($_POST['id_forum']) ? (int) $_POST['id_forum'] : 0;
        $parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? (int) $_POST['parent_id'] : null;

        if ($contenu === '' || $id_auteur === null || $id_forum <= 0) {
            $_SESSION['error'] = "Données invalides pour le commentaire.";
            $this->redirectToForum($id_forum);
            return;
        }

        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO commentaire (contenu, date_commentaire, id_auteur, id_forum, parent_id, signale) 
                VALUES (:contenu, NOW(), :id_auteur, :id_forum, :parent_id, 0)
            ");
            $stmt->bindValue(':contenu', $contenu, PDO::PARAM_STR);
            $stmt->bindValue(':id_auteur', $id_auteur, PDO::PARAM_INT);
            $stmt->bindValue(':id_forum', $id_forum, PDO::PARAM_INT);
            $stmt->bindValue(':parent_id', $parent_id, PDO::PARAM_NULL);

            $stmt->execute();

            $_SESSION['success'] = "Commentaire ajouté avec succès.";
            $this->redirectToForum($id_forum);

        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de l'ajout du commentaire.";
            $this->redirectToForum($id_forum);
        }
    }

    public function edit(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = "Commentaire invalide.";
            $this->redirectToAdminIndex();
            return;
        }

        try {
            $stmt = $this->pdo->prepare("SELECT * FROM commentaire WHERE id_commentaire = :id");
            $stmt->execute([':id' => $id]);
            $comment = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$comment) {
                $_SESSION['error'] = "Commentaire introuvable.";
                $this->redirectToAdminIndex();
                return;
            }

            // Set $old for the view (both as local variable and in GLOBALS for safety)
            $old = [
                'id_commentaire' => $comment['id_commentaire'],
                'contenu' => $comment['contenu'],
                'date_commentaire' => $comment['date_commentaire'],
                'id_auteur' => $comment['id_auteur'],
                'id_forum' => $comment['id_forum'],
                'parent_id' => $comment['parent_id'],
                'signale' => $comment['signale'],
            ];
            $fieldErrors = [];
            
            // Also set in GLOBALS in case view is accessed differently
            $GLOBALS['old'] = $old;
            $GLOBALS['fieldErrors'] = $fieldErrors;

            require __DIR__ . '/../views/commentaire/edit.php';

        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur base de données : " . $e->getMessage();
            $this->redirectToAdminIndex();
        }
    }

    public function update(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = isset($_POST['id_commentaire']) ? (int) $_POST['id_commentaire'] : 0;
        $contenu = isset($_POST['contenu']) ? trim($_POST['contenu']) : '';

        if ($id <= 0 || $contenu === '') {
            $_SESSION['error'] = "Données invalides pour la mise à jour du commentaire.";
            $this->redirectToAdminIndex();
            return;
        }

        try {
            $stmt = $this->pdo->prepare("UPDATE commentaire SET contenu = :contenu WHERE id_commentaire = :id");
            $stmt->bindValue(':contenu', $contenu, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $_SESSION['success'] = "Commentaire mis à jour avec succès.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la mise à jour du commentaire.";
        }

        $this->redirectToAdminIndex();
    }

    public function delete(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = "Commentaire invalide.";
            $this->redirectToAdminIndex();
            return;
        }

        try {
            $stmt = $this->pdo->prepare("DELETE FROM commentaire WHERE id_commentaire = :id");
            $stmt->execute([':id' => $id]);

            $_SESSION['success'] = "Commentaire supprimé avec succès.";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la suppression du commentaire.";
        }

        $this->redirectToAdminIndex();
    }

    public function toggleSignal(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($id <= 0) {
            $_SESSION['error'] = "Commentaire invalide.";
            $this->redirectToAdminIndex();
            return;
        }

        try {
            // Get current signal status
            $stmt = $this->pdo->prepare("SELECT signale FROM commentaire WHERE id_commentaire = :id");
            $stmt->execute([':id' => $id]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($current) {
                $newSignal = $current['signale'] == 1 ? 0 : 1;
                $stmt = $this->pdo->prepare("UPDATE commentaire SET signale = :signale WHERE id_commentaire = :id");
                $stmt->execute([':signale' => $newSignal, ':id' => $id]);

                $_SESSION['success'] = "Statut du signalement modifié avec succès.";
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur lors de la modification du signalement.";
        }

        $this->redirectToAdminIndex();
    }

    /**
     * Redirect to front office forum index
     */
    private function redirectToIndex(): void
    {
        $currentPath = $_SERVER['PHP_SELF'];
        if (strpos($currentPath, '/front_office/') !== false || (strpos($currentPath, 'index.php') !== false && isset($_GET['section']))) {
            header('Location: index.php?section=forum');
        } else {
            header('Location: ../front_office/index.php?section=forum');
        }
        exit;
    }

    /**
     * Redirect to forum show page
     */
    private function redirectToForum(?int $id_forum = null): void
    {
        if ($id_forum) {
            $currentPath = $_SERVER['PHP_SELF'];
            if (strpos($currentPath, '/front_office/') !== false || (strpos($currentPath, 'index.php') !== false && isset($_GET['section']))) {
                header('Location: index.php?section=forum&action=show&id=' . (int)$id_forum);
            } else {
                header('Location: ../front_office/index.php?section=forum&action=show&id=' . (int)$id_forum);
            }
        } else {
            $this->redirectToIndex();
        }
        exit;
    }

    /**
     * Redirect to admin commentaire index
     */
    private function redirectToAdminIndex(): void
    {
        // Calculate the correct path to dashboard.php
        $currentPath = $_SERVER['PHP_SELF'];
        $basePath = '';
        
        // Check if we're in a subdirectory
        if (preg_match('#/back_office/[^/]+/#', $currentPath)) {
            // We're in a subdirectory (e.g., /back_office/deal/index.php), need to go up 1 level
            $basePath = '../';
        } elseif (strpos($currentPath, '/back_office/') !== false) {
            // We're in back_office root (e.g., /back_office/dashboard.php), no need to go up
            $basePath = '';
        } else {
            // We're in views/commentaire/, need to go to back_office
            $basePath = '../back_office/';
        }
        
        header('Location: ' . $basePath . 'dashboard.php?section=commentaire');
        exit;
    }
}
