<?php

class Deal
{
    private ?int $idDeal;
    private string $intitule;
    private string $descriptionD;
    private float $prixinitial;   // <- cohérent avec la base et le contrôleur
    private float $reduction;
    private ?string $dateDebut;
    private int $periodeValidite;
    private ?string $note;
    private string $expire;
    private int $IdSponsor;
    private int $id_user;
    private string $statut;
    private int $click_count;
    private string $has_coupon;
    private ?string $coupon_code;
    private ?string $dateAcceptation;

    
    public function __construct(
        ?int $idDeal,
        string $intitule,
        string $descriptionD,
        float $prixinitial,      // <- paramètre en minuscules
        float $reduction,
        ?string $dateDebut,
        int $periodeValidite,
        ?string $note,
        string $expire,
        int $IdSponsor,
        int $id_user,
        string $statut = 'en_attente',
        int $click_count = 0,
        string $has_coupon = 'NON',
        ?string $coupon_code = null,
        ?string $dateAcceptation = null
    ) {
        $this->idDeal          = $idDeal;
        $this->intitule        = $intitule;
        $this->descriptionD    = $descriptionD;
        $this->prixinitial     = $prixinitial;   // <- affectation OK
        $this->reduction       = $reduction;
        $this->dateDebut       = $dateDebut;
        $this->periodeValidite = $periodeValidite;
        $this->note            = $note;
        $this->expire          = $expire;
        $this->IdSponsor       = $IdSponsor;
        $this->id_user         = $id_user;
        $this->statut          = $statut;
        $this->click_count     = $click_count;
        $this->has_coupon      = $has_coupon;
        $this->coupon_code     = $coupon_code;
        $this->dateAcceptation = $dateAcceptation;
    }

    // --- Getters / Setters ---

    public function getIdDeal(): ?int                { return $this->idDeal; }
    public function setIdDeal(?int $id): void        { $this->idDeal = $id; }

    public function getIntitule(): string            { return $this->intitule; }
    public function setIntitule(string $v): void     { $this->intitule = $v; }

    public function getDescriptionD(): string        { return $this->descriptionD; }
    public function setDescriptionD(string $v): void { $this->descriptionD = $v; }

    public function getPrixinitial(): float          { return $this->prixinitial; }
    public function setPrixinitial(float $v): void   { $this->prixinitial = $v; }

    public function getReduction(): float            { return $this->reduction; }
    public function setReduction(float $v): void     { $this->reduction = $v; }

    public function getDateDebut(): ?string          { return $this->dateDebut; }
    public function setDateDebut(?string $v): void   { $this->dateDebut = $v; }

    public function getPeriodeValidite(): int        { return $this->periodeValidite; }
    public function setPeriodeValidite(int $v): void { $this->periodeValidite = $v; }

    public function getNote(): ?string               { return $this->note; }
    public function setNote(?string $v): void        { $this->note = $v; }

    public function getExpire(): string              { return $this->expire; }
    public function setExpire(string $v): void       { $this->expire = $v; }

    public function getIdSponsor(): int              { return $this->IdSponsor; }
    public function setIdSponsor(int $v): void       { $this->IdSponsor = $v; }

    public function getStatut(): string              { return $this->statut; }
    public function setStatut(string $v): void       { $this->statut = $v; }

    public function getClickCount(): int             { return $this->click_count; }
    public function setClickCount(int $v): void      { $this->click_count = $v; }

    public function getHasCoupon(): string           { return $this->has_coupon; }
    public function setHasCoupon(string $v): void    { $this->has_coupon = $v; }

    public function getCouponCode(): ?string         { return $this->coupon_code; }
    public function setCouponCode(?string $v): void  { $this->coupon_code = $v; }

    public function getDateAcceptation(): ?string    { return $this->dateAcceptation; }
    public function setDateAcceptation(?string $v): void { $this->dateAcceptation = $v; }

    public function getIdUser(): int                 { return $this->id_user; }
    public function setIdUser(int $v): void          { $this->id_user = $v; }
}