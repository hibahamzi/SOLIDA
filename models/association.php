<?php
class Association {
    private $id_association;
    private $nom_association;
    private $description;
    private $adresse;
    private $pays;
    private $type_don_supporte;

    public function __construct(
        $id_association,
        $nom_association,
        $description = null,
        $adresse = null,
        $pays = null,
        $type_don_supporte = null
    ) {
        $this->id_association = $id_association;
        $this->nom_association = $nom_association;
        $this->description = $description;
        $this->adresse = $adresse;
        $this->pays = $pays;
        $this->type_don_supporte = $type_don_supporte;
    }

    // GETTERS
    public function getIdAssociation() { return $this->id_association; }
    public function getNomAssociation() { return $this->nom_association; }
    public function getDescription() { return $this->description; }
    public function getAdresse() { return $this->adresse; }
    public function getPays() { return $this->pays; }
    public function getTypeDonSupporte() { return $this->type_don_supporte; }

    // SETTERS
    public function setIdAssociation($id_association): self {
        $this->id_association = $id_association;
        return $this;
    }
    public function setNomAssociation($nom_association): self {
        $this->nom_association = $nom_association;
        return $this;
    }
    public function setDescription($description): self {
        $this->description = $description;
        return $this;
    }
    public function setAdresse($adresse): self {
        $this->adresse = $adresse;
        return $this;
    }
    public function setPays($pays): self {
        $this->pays = $pays;
        return $this;
    }
    public function setTypeDonSupporte($type_don_supporte): self {
        $this->type_don_supporte = $type_don_supporte;
        return $this;
    }
}
?>