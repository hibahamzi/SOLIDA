<?php
require_once __DIR__ . '/../config.php';
class UserModel {
    private $db;
    public function __construct() { $this->db = Database::connect(); }
    public function getAllUsers() {
        $sql = "SELECT * FROM user ORDER BY id_utilisateur";
        return $this->db->query($sql)->fetchAll();
    }
    public function getUserById($id) {
        $sql = "SELECT * FROM user WHERE id_utilisateur = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
}
?>