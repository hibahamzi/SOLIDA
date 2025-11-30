<?php
class reclamations {
    // Propriétés de l'entité User
    private $id;
    private $nom;
    private $prenom;
    private $telephone;
    private $email;
    private $gouvernorat;
    private $delegation;
    private $ville;
    private $postion_gps;
    private $description_detaillee;
    private $priorite;
    private $statut;
    private $date ;


    // Constructeur de la classe User
    public function __construct($nom, $prenom, $telephone, $email, $gouvernorat, $delegation, $ville, $postion_gps, $description_detaillee, $priorite, $statut, $date) {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->telephone = $telephone;
        $this->email = $email;
        $this->gouvernorat = $gouvernorat;
        $this->delegation = $delegation;
        $this->ville = $ville;
        $this->postion_gps = $postion_gps;
        $this->description_detaillee = $description_detaillee;
        $this->priorite = $priorite;
        $this->statut = $statut;
        $this->date = $date;
    }
    // Getters et Setters pour chaque propriété
    public function getId() {
        return $this->id;
        }
    public function getNom() {
        return $this->nom;      
    }
    public function setNom($nom) {
        $this->nom = $nom;
    }
    public function getPrenom() {
        return $this->prenom;
    }
    public function setPrenom($prenom) {
        $this->prenom = $prenom;
    }
    public function getTelephone() {
        return $this->telephone;
    }
    public function setTelephone($telephone) {
        $this->telephone = $telephone;
    }   
    public function getEmail() {
        return $this->email;
    }
    public function setEmail($email) {
        $this->email = $email;  
    }
    public function getGouvernorat() {
        return $this->gouvernorat;
    }
    public function setGouvernorat($gouvernorat) {
        $this->gouvernorat = $gouvernorat;
    }
    public function getDelegation() {
        return $this->delegation;
    }
    public function setDelegation($delegation) {
        $this->delegation = $delegation;
    }
    public function getVille() {
        return $this->ville;
    }
    public function setVille($ville) {  
           $this->ville = $ville;
        }
    public function getPostion_gps() {
        return $this->postion_gps;
    }
    public function setPostion_gps($postion_gps) {
        $this->postion_gps = $postion_gps;
    }
    public function getDescription_detaillee() {
        return $this->description_detaillee;
    }
    public function setDescription_detaillee($description_detaillee) {
        $this->description_detaillee = $description_detaillee;
    }
    public function getPriorite() {
        return $this->priorite;
    }
    public function setPriorite($priorite) {
        $this->priorite = $priorite;
    }
    public function getStatut() {
        return $this->statut;
    }
    public function setStatut($statut) {
        $this->statut = $statut;
    }
    public function getDate() {
        return $this->date;
    }
    public function setDate($date) {
        $this->date = $date;
    }
    
   // Dans la classe ReclamationModel

public function modifierReponse($id_reponse, $nouveau_contenu) {
    $sql = "UPDATE reponses_reclamation SET contenu_reponse = :contenu WHERE id_reponse = :id";
    $stmt = $this->db->prepare($sql); // Assurez-vous que $this->db est votre connexion PDO
    $stmt->bindParam(':contenu', $nouveau_contenu);
    $stmt->bindParam(':id', $id_reponse);
    
    return $stmt->execute();
}

// Fonction pour récupérer la réponse existante (utile pour pré-remplir le formulaire)
public function getReponseById($id_reponse) {
    $sql = "SELECT * FROM reponses_reclamation WHERE id_reponse = :id";
    $stmt = $this->db->prepare($sql);
    $stmt->bindParam(':id', $id_reponse);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
 
}   
?>
