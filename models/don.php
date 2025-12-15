<?php
class Don {
    private $id_don;
    private $id_utilisateur;
    private $id_association;
    private $type_don;
    private $date_don;
    private $statut;
    private $montant_donne;
    private $methode_paiement;
    private $numero_carte_token;
    private $groupe_sanguin;
    private $a_maladie;
    private $details_maladie;
    private $details_nourriture;

    public function __construct(
        $id_don = null,
        $id_utilisateur,
        $id_association,
        $type_don,
        $statut,
        $montant_donne = null,
        $methode_paiement = null,
        $numero_carte_token = null,
        $groupe_sanguin = null,
        $a_maladie = 0,
        $details_maladie = null,
        $details_nourriture = null,
        $date_don = null
    ) {
        $this->id_don = $id_don;
        $this->id_utilisateur = $id_utilisateur;
        $this->id_association = $id_association;
        $this->type_don = $type_don;
        $this->date_don = $date_don;
        $this->statut = $statut;
        $this->montant_donne = $montant_donne;
        $this->methode_paiement = $methode_paiement;
        $this->numero_carte_token = $numero_carte_token;
        $this->groupe_sanguin = $groupe_sanguin;
        $this->a_maladie = $a_maladie;
        $this->details_maladie = $details_maladie;
        $this->details_nourriture = $details_nourriture;
    }

    // GETTERS
    public function getIdDon() { return $this->id_don; }
    public function getIdUtilisateur() { return $this->id_utilisateur; }
    public function getIdAssociation() { return $this->id_association; }
    public function getTypeDon() { return $this->type_don; }
    public function getDateDon() { return $this->date_don; }
    public function getStatut() { return $this->statut; }
    public function getMontantDonne() { return $this->montant_donne; }
    public function getMethodePaiement() { return $this->methode_paiement; }
    public function getNumeroCarteToken() { return $this->numero_carte_token; }
    public function getGroupeSanguin() { return $this->groupe_sanguin; }
    public function getAMaladie() { return $this->a_maladie; }
    public function getDetailsMaladie() { return $this->details_maladie; }
    public function getDetailsNourriture() { return $this->details_nourriture; }

    // SETTERS
    public function setIdDon($id_don): self {
        $this->id_don = $id_don;
        return $this;
    }
    public function setIdUtilisateur($id_utilisateur): self {
        $this->id_utilisateur = $id_utilisateur;
        return $this;
    }
    public function setIdAssociation($id_association): self {
        $this->id_association = $id_association;
        return $this;
    }
    public function setTypeDon($type_don): self {
        $this->type_don = $type_don;
        return $this;
    }
    public function setDateDon($date_don): self {
        $this->date_don = $date_don;
        return $this;
    }
    public function setStatut($statut): self {
        $this->statut = $statut;
        return $this;
    }
    public function setMontantDonne($montant_donne): self {
        $this->montant_donne = $montant_donne;
        return $this;
    }
    public function setMethodePaiement($methode_paiement): self {
        $this->methode_paiement = $methode_paiement;
        return $this;
    }
    public function setNumeroCarteToken($numero_carte_token): self {
        $this->numero_carte_token = $numero_carte_token;
        return $this;
    }
    public function setGroupeSanguin($groupe_sanguin): self {
        $this->groupe_sanguin = $groupe_sanguin;
        return $this;
    }
    public function setAMaladie($a_maladie): self {
        $this->a_maladie = $a_maladie;
        return $this;
    }
    public function setDetailsMaladie($details_maladie): self {
        $this->details_maladie = $details_maladie;
        return $this;
    }
    public function setDetailsNourriture($details_nourriture): self {
        $this->details_nourriture = $details_nourriture;
        return $this;
    }
}
?>