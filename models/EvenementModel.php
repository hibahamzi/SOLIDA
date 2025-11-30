<?php

class Evenement {

    private int $id_evenement;

    private $titre_evenement;

    private $date_evenement;

    private $description;

    private $organisateur;

    private $frais_participation;

    private $created_at;

    public function __construct($titre_evenement, $date_evenement, $description, $organisateur, $frais_participation = 0, $created_at = null) {
        $this->titre_evenement = $titre_evenement;
        $this->date_evenement = $date_evenement;
        $this->description = $description;
        $this->organisateur = $organisateur;
        $this->frais_participation = $frais_participation;
        $this->created_at = $created_at;
    }

    /**
     * Get the value of id_evenement
     */
    public function getIdEvenement() {
        return $this->id_evenement;
    }

    /**
     * Set the value of id_evenement
     */
    public function setIdEvenement($id_evenement): self {
        $this->id_evenement = $id_evenement;
        return $this;
    }

    /**
     * Get the value of titre_evenement
     */
    public function getTitreEvenement() {
        return $this->titre_evenement;
    }

    /**
     * Set the value of titre_evenement
     */
    public function setTitreEvenement($titre_evenement): self {
        $this->titre_evenement = $titre_evenement;
        return $this;
    }

    /**
     * Get the value of date_evenement
     */
    public function getDateEvenement() {
        return $this->date_evenement;
    }

    /**
     * Set the value of date_evenement
     */
    public function setDateEvenement($date_evenement): self {
        $this->date_evenement = $date_evenement;
        return $this;
    }

    /**
     * Get the value of description
     */
    public function getDescription() {
        return $this->description;
    }

    /**
     * Set the value of description
     */
    public function setDescription($description): self {
        $this->description = $description;
        return $this;
    }

    /**
     * Get the value of organisateur
     */
    public function getOrganisateur() {
        return $this->organisateur;
    }

    /**
     * Set the value of organisateur
     */
    public function setOrganisateur($organisateur): self {
        $this->organisateur = $organisateur;
        return $this;
    }

    /**
     * Get the value of frais_participation
     */
    public function getFraisParticipation() {
        return $this->frais_participation;
    }

    /**
     * Set the value of frais_participation
     */
    public function setFraisParticipation($frais_participation): self {
        $this->frais_participation = $frais_participation;
        return $this;
    }

    /**
     * Get the value of created_at
     */
    public function getCreatedAt() {
        return $this->created_at;
    }

    /**
     * Set the value of created_at
     */
    public function setCreatedAt($created_at): self {
        $this->created_at = $created_at;
        return $this;
    }
}

?>

