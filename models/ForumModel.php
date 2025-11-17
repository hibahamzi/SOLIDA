<?php

class ForumModel {

    private $db;

    public function __construct() {
        $this->db = new PDO("mysql:host=localhost;dbname=projet;charset=utf8", "root", "");
    }

    public function getAllForums() {
        return $this->db
            ->query("SELECT * FROM forum ORDER BY id_forum DESC")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function createForum($id_user, $categorie, $discussion_g, $discussion_p) {

        // NULLIF = si vide → NULL (important pour FK)
        $sql = "INSERT INTO forum (id_user, categorie, discussion_g, discussion_p)
                VALUES (NULLIF(?, ''), ?, ?, ?)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            $id_user,
            $categorie,
            $discussion_g,
            $discussion_p
        ]);
    }

    public function find($id) {
        $sql = $this->db->prepare("SELECT * FROM forum WHERE id_forum=?");
        $sql->execute([$id]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function updateForum($id, $categorie, $discussion_g, $discussion_p) {
        $sql = $this->db->prepare(
            "UPDATE forum SET categorie=?, discussion_g=?, discussion_p=? WHERE id_forum=?"
        );
        return $sql->execute([$categorie, $discussion_g, $discussion_p, $id]);
    }

    public function deleteForum($id) {
        $sql = $this->db->prepare("DELETE FROM forum WHERE id_forum=?");
        return $sql->execute([$id]);
    }
}
