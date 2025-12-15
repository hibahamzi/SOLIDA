<?php

class CommentaireModel
{
    private $id_commentaire;
    private $contenu;
    private $date_commentaire;
    private $id_auteur;
    private $id_forum;
    private $parent_id;
    private $signale;

    public function __construct(
        $id_commentaire = null,
        $contenu = '',
        $date_commentaire = null,
        $id_auteur = null,
        $id_forum = null,
        $parent_id = null,
        $signale = 0
    ) {
        $this->id_commentaire   = $id_commentaire;
        $this->contenu          = $contenu;
        $this->date_commentaire = $date_commentaire ?: date('Y-m-d H:i:s');
        $this->id_auteur        = $id_auteur;
        $this->id_forum         = $id_forum;
        $this->parent_id        = $parent_id;
        $this->signale          = $signale;
    }

    // Getters
    public function getIdCommentaire()   { return $this->id_commentaire; }
    public function getContenu()        { return $this->contenu; }
    public function getDateCommentaire(){ return $this->date_commentaire; }
    public function getIdAuteur()       { return $this->id_auteur; }
    public function getIdForum()        { return $this->id_forum; }
    public function getParentId()       { return $this->parent_id; }
    public function getSignale()        { return $this->signale; }

    // Setters
    public function setIdCommentaire($id_commentaire)    { $this->id_commentaire = $id_commentaire; }
    public function setContenu($contenu)                 { $this->contenu = $contenu; }
    public function setDateCommentaire($date_commentaire){ $this->date_commentaire = $date_commentaire; }
    public function setIdAuteur($id_auteur)              { $this->id_auteur = $id_auteur; }
    public function setIdForum($id_forum)                { $this->id_forum = $id_forum; }
    public function setParentId($parent_id)              { $this->parent_id = $parent_id; }
    public function setSignale($signale)                 { $this->signale = $signale; }

    public function toArray()
    {
        return [
            'id_commentaire'   => $this->id_commentaire,
            'contenu'          => $this->contenu,
            'date_commentaire' => $this->date_commentaire,
            'id_auteur'        => $this->id_auteur,
            'id_forum'         => $this->id_forum,
            'parent_id'        => $this->parent_id,
            'signale'          => $this->signale,
        ];
    }
}