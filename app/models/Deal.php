<?php

class Deal
{
    private ?int $idDeal;
    private string $intitule;
    private string $descriptionD;
    private float $prixInitial;
    private float $reduction;
    private string $dateDebut;      // format 'Y-m-d'
    private int $periodeValidite;   // en jours
    private ?int $note;             // 0..5 ou null
    private string $expire;         // 'OUI' / 'NON'
    private int $idSponsor;         // FK vers sponsors.id

    public function __construct(
        ?int $idDeal,
        string $intitule,
        string $descriptionD,
        float $prixInitial,
        float $reduction,
        string $dateDebut,
        int $periodeValidite,
        ?int $note,
        string $expire,
        int $idSponsor
    ) {
        $this->idDeal        = $idDeal;
        $this->intitule      = $intitule;
        $this->descriptionD  = $descriptionD;
        $this->prixInitial   = $prixInitial;
        $this->reduction     = $reduction;
        $this->dateDebut     = $dateDebut;
        $this->periodeValidite = $periodeValidite;
        $this->note          = $note;
        $this->expire        = $expire;
        $this->idSponsor     = $idSponsor;
    }

    // Getters
    public function getIdDeal(): ?int { return $this->idDeal; }
    public function getIntitule(): string { return $this->intitule; }
    public function getDescriptionD(): string { return $this->descriptionD; }
    public function getPrixInitial(): float { return $this->prixInitial; }
    public function getReduction(): float { return $this->reduction; }
    public function getDateDebut(): string { return $this->dateDebut; }
    public function getPeriodeValidite(): int { return $this->periodeValidite; }
    public function getNote(): ?int { return $this->note; }
    public function getExpire(): string { return $this->expire; }
    public function getIdSponsor(): int { return $this->idSponsor; }

    // (Setters si besoin plus tard)
}