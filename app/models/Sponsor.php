<?php
// app/models/Sponsor.php

class Sponsor
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function all()
    {
        $stmt = $this->pdo->query("SELECT * FROM sponsors ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM sponsors WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO sponsors 
            (nomEntreprise, emailContact, telephone, adresse, typeSponsoring, montantEngage, domaineActivite, logoUrl, contratUrl, dateDebutPartenaire, dateFinPartenaire, statut)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['nomEntreprise'],
            $data['emailContact'],
            $data['telephone'],
            $data['adresse'],
            $data['typeSponsoring'],
            $data['montantEngage'],
            $data['domaineActivite'],
            $data['logoUrl'],
            $data['contratUrl'],
            $data['dateDebutPartenaire'],
            $data['dateFinPartenaire'],
            $data['statut'],
        ]);
    }

    public function update($id, $data)
    {
        $sql = "UPDATE sponsors SET
            nomEntreprise = ?, 
            emailContact = ?, 
            telephone = ?, 
            adresse = ?, 
            typeSponsoring = ?, 
            montantEngage = ?, 
            domaineActivite = ?, 
            logoUrl = ?, 
            contratUrl = ?, 
            dateDebutPartenaire = ?, 
            dateFinPartenaire = ?, 
            statut = ?
            WHERE id = ?";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            $data['nomEntreprise'],
            $data['emailContact'],
            $data['telephone'],
            $data['adresse'],
            $data['typeSponsoring'],
            $data['montantEngage'],
            $data['domaineActivite'],
            $data['logoUrl'],
            $data['contratUrl'],
            $data['dateDebutPartenaire'],
            $data['dateFinPartenaire'],
            $data['statut'],
            $id
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->pdo->prepare("DELETE FROM sponsors WHERE id = ?");
        return $stmt->execute([$id]);
    }
}