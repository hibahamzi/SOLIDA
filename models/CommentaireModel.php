<?php
require_once __DIR__ . '/../config.php';
class CommentaireModel {
    private $db;
    public function __construct() { $this->db = Database::connect(); }
    public function createCommentaire($contenu, $id_auteur, $id_forum) {
        $sql = "INSERT INTO commentaire (contenu, date_commentaire, id_auteur) VALUES (?, NOW(), ?)";
        $stmt = $this->db->prepare($sql);
        $ok = $stmt->execute([$contenu, $id_auteur]);
        // Note: association to forum can be managed with a linking table if needed.
        return $ok;
    }
    public function getCommentairesByForum($id_forum) {
        // This assumes commentaire has id_auteur and we join user; adjust if you use a forum_comment table.
        $sql = "SELECT c.*, u.nom_prenom FROM commentaire c LEFT JOIN user u ON c.id_auteur = u.id_utilisateur ORDER BY date_commentaire DESC";
        return $this->db->query($sql)->fetchAll();
    }
}
?>