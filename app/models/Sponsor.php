<?php

class Sponsor
{
    // ATTRIBUTS
    private $id;
    private $nomEntreprise;
    private $emailContact;
    private $telephone;
    private $adresse;
    private $typeSponsoring;
    private $montantEngage;
    private $domaineActivite;
    private $logoUrl;
    private $contratUrl;
    private $dateDebutPartenaire;
    private $dateFinPartenaire;
    private $statut;

    // CONSTRUCTEUR
    public function __construct(
        $id = null,
        $nomEntreprise = null,
        $emailContact = null,
        $telephone = null,
        $adresse = null,
        $typeSponsoring = null,
        $montantEngage = null,
        $domaineActivite = null,
        $logoUrl = null,
        $contratUrl = null,
        $dateDebutPartenaire = null,
        $dateFinPartenaire = null,
        $statut = null
    ) {
        $this->id                  = $id;
        $this->nomEntreprise       = $nomEntreprise;
        $this->emailContact        = $emailContact;
        $this->telephone           = $telephone;
        $this->adresse             = $adresse;
        $this->typeSponsoring      = $typeSponsoring;
        $this->montantEngage       = $montantEngage;
        $this->domaineActivite     = $domaineActivite;
        $this->logoUrl             = $logoUrl;
        $this->contratUrl          = $contratUrl;
        $this->dateDebutPartenaire = $dateDebutPartenaire;
        $this->dateFinPartenaire   = $dateFinPartenaire;
        $this->statut              = $statut;
    }

    // GETTERS
    public function getId()                  { return $this->id; }
    public function getNomEntreprise()       { return $this->nomEntreprise; }
    public function getEmailContact()        { return $this->emailContact; }
    public function getTelephone()           { return $this->telephone; }
    public function getAdresse()             { return $this->adresse; }
    public function getTypeSponsoring()      { return $this->typeSponsoring; }
    public function getMontantEngage()       { return $this->montantEngage; }
    public function getDomaineActivite()     { return $this->domaineActivite; }
    public function getLogoUrl()             { return $this->logoUrl; }
    public function getContratUrl()          { return $this->contratUrl; }
    public function getDateDebutPartenaire() { return $this->dateDebutPartenaire; }
    public function getDateFinPartenaire()   { return $this->dateFinPartenaire; }
    public function getStatut()              { return $this->statut; }

    // SETTERS
    public function setId($id)                                   { $this->id = $id; }
    public function setNomEntreprise($nomEntreprise)             { $this->nomEntreprise = $nomEntreprise; }
    public function setEmailContact($emailContact)               { $this->emailContact = $emailContact; }
    public function setTelephone($telephone)                     { $this->telephone = $telephone; }
    public function setAdresse($adresse)                         { $this->adresse = $adresse; }
    public function setTypeSponsoring($typeSponsoring)           { $this->typeSponsoring = $typeSponsoring; }
    public function setMontantEngage($montantEngage)             { $this->montantEngage = $montantEngage; }
    public function setDomaineActivite($domaineActivite)         { $this->domaineActivite = $domaineActivite; }
    public function setLogoUrl($logoUrl)                         { $this->logoUrl = $logoUrl; }
    public function setContratUrl($contratUrl)                   { $this->contratUrl = $contratUrl; }
    public function setDateDebutPartenaire($dateDebutPartenaire) { $this->dateDebutPartenaire = $dateDebutPartenaire; }
    public function setDateFinPartenaire($dateFinPartenaire)     { $this->dateFinPartenaire = $dateFinPartenaire; }
    public function setStatut($statut)                           { $this->statut = $statut; }
}