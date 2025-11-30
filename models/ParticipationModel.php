<?php

class Participation {

    private int $id_participation;

    private int $id_evenement;

    private int $id_user;

    private $motivation;

    private $source_information;

    private $type_participation;

    private int $nombre_personnes;

    private $desir_dejeuner;

    private $date_inscription;

    public function __construct($id_evenement, $id_user, $motivation, $source_information, $type_participation, $nombre_personnes = 1, $desir_dejeuner = 'non', $date_inscription = null) {
        $this->id_evenement = $id_evenement;
        $this->id_user = $id_user;
        $this->motivation = $motivation;
        $this->source_information = $source_information;
        $this->type_participation = $type_participation;
        $this->nombre_personnes = $nombre_personnes;
        $this->desir_dejeuner = $desir_dejeuner;
        $this->date_inscription = $date_inscription;
    }

    /**
     * Get the value of id_participation
     */
    public function getIdParticipation() {
        return $this->id_participation;
    }

    /**
     * Set the value of id_participation
     */
    public function setIdParticipation($id_participation): self {
        $this->id_participation = $id_participation;
        return $this;
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
     * Get the value of id_user
     */
    public function getIdUser() {
        return $this->id_user;
    }

    /**
     * Set the value of id_user
     */
    public function setIdUser($id_user): self {
        $this->id_user = $id_user;
        return $this;
    }

    /**
     * Get the value of motivation
     */
    public function getMotivation() {
        return $this->motivation;
    }

    /**
     * Set the value of motivation
     */
    public function setMotivation($motivation): self {
        $this->motivation = $motivation;
        return $this;
    }

    /**
     * Get the value of source_information
     */
    public function getSourceInformation() {
        return $this->source_information;
    }

    /**
     * Set the value of source_information
     */
    public function setSourceInformation($source_information): self {
        $this->source_information = $source_information;
        return $this;
    }

    /**
     * Get the value of type_participation
     */
    public function getTypeParticipation() {
        return $this->type_participation;
    }

    /**
     * Set the value of type_participation
     */
    public function setTypeParticipation($type_participation): self {
        $this->type_participation = $type_participation;
        return $this;
    }

    /**
     * Get the value of nombre_personnes
     */
    public function getNombrePersonnes() {
        return $this->nombre_personnes;
    }

    /**
     * Set the value of nombre_personnes
     */
    public function setNombrePersonnes($nombre_personnes): self {
        $this->nombre_personnes = $nombre_personnes;
        return $this;
    }

    /**
     * Get the value of desir_dejeuner
     */
    public function getDesirDejeuner() {
        return $this->desir_dejeuner;
    }

    /**
     * Set the value of desir_dejeuner
     */
    public function setDesirDejeuner($desir_dejeuner): self {
        $this->desir_dejeuner = $desir_dejeuner;
        return $this;
    }

    /**
     * Get the value of date_inscription
     */
    public function getDateInscription() {
        return $this->date_inscription;
    }

    /**
     * Set the value of date_inscription
     */
    public function setDateInscription($date_inscription): self {
        $this->date_inscription = $date_inscription;
        return $this;
    }
}

?>

