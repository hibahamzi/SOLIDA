<?php
class ForumModel {
    // Attributs seulement
    private $id_forum;
    private $id_user;
    private $categorie;
    private $discussion_g;
    private $discussion_p;
    private $date_creation;

    // Constructeur seulement - date_creation optionnel
    public function __construct($id_forum = null, $id_user = null, $categorie = '', $discussion_g = '', $discussion_p = '', $date_creation = null) {
        $this->id_forum = $id_forum;
        $this->id_user = $id_user;
        $this->categorie = $categorie;
        $this->discussion_g = $discussion_g;
        $this->discussion_p = $discussion_p;
        $this->date_creation = $date_creation; // Peut être null
    }

    // Getters seulement
    public function getIdForum() { return $this->id_forum; }
    public function getIdUser() { return $this->id_user; }
    public function getCategorie() { return $this->categorie; }
    public function getDiscussionG() { return $this->discussion_g; }
    public function getDiscussionP() { return $this->discussion_p; }
    public function getDateCreation() { return $this->date_creation; }

    // Setters seulement
    public function setIdForum($id_forum) { $this->id_forum = $id_forum; }
    public function setIdUser($id_user) { $this->id_user = $id_user; }
    public function setCategorie($categorie) { $this->categorie = $categorie; }
    public function setDiscussionG($discussion_g) { $this->discussion_g = $discussion_g; }
    public function setDiscussionP($discussion_p) { $this->discussion_p = $discussion_p; }
    public function setDateCreation($date_creation) { $this->date_creation = $date_creation; }

    // Méthode utilitaire pour conversion en tableau
    public function toArray() {
        return [
            'id_forum' => $this->id_forum,
            'id_user' => $this->id_user,
            'categorie' => $this->categorie,
            'discussion_g' => $this->discussion_g,
            'discussion_p' => $this->discussion_p,
            'date_creation' => $this->date_creation
        ];
    }

    // Méthode pour afficher la date formatée (si elle existe)
    public function getDateCreationFormatted() {
        if ($this->date_creation) {
            return date('d/m/Y H:i', strtotime($this->date_creation));
        }
        return 'Non spécifiée';
    }
}
?>