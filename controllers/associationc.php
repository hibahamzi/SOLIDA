<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/association.php';

class AssociationC {
  
    
     
    // =========================================================================

    private const GLOBAL_ID = 1; // ID de l'enregistrement utilisé pour stocker les avis globaux

    /**
     * Récupère la liste des avis globaux décodés (array) pour Solida.
     */
    public function getAvisSolida(): array {
        global $pdo;
        $sql = "SELECT avis_plateforme_global FROM Association WHERE id_association = :id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', self::GLOBAL_ID, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && $row['avis_plateforme_global']) {
                $avis = json_decode($row['avis_plateforme_global'], true);
                if (!empty($avis)) {
                    // Enrichir avec le nom de l'utilisateur (comme pour les avis par association)
                    $avis = $this->enrichirAvisAvecNoms($avis);
                }
                return is_array($avis) ? $avis : [];
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des avis Solida: " . $e->getMessage());
        }
        return [];
    }
    
    /**
     * Ajoute un avis au champ `avis_plateforme_global` (via JSON).
     */
    public function ajouterAvisSolida(int $id_utilisateur, int $note, string $texte): bool {
        global $pdo;
        $avis_actuels = $this->getAvisSolida(); // Récupère les avis (décodes)

        // Crée le nouvel avis avec un ID unique
        $nouvel_avis = [
            'id_avis' => uniqid(), 
            'id_utilisateur' => $id_utilisateur,
            'note' => $note,
            'texte_avis' => $texte,
            'date_creation' => date('Y-m-d H:i:s'),
            'date_modification' => null
        ];

        // Ajout du nouvel avis à la liste
        $avis_actuels[] = $nouvel_avis;

        // Met à jour la base de données avec le JSON encodé
        $avis_json = json_encode($avis_actuels);
        
        $sql = "UPDATE association SET avis_plateforme_global = :avis WHERE id_association = :id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':avis', $avis_json, PDO::PARAM_STR);
            $stmt->bindValue(':id', self::GLOBAL_ID, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Erreur lors de l'ajout d'avis Solida (JSON): " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Modifie un avis existant par son ID (dans le JSON).
     */
    public function modifierAvisSolida(int $id_utilisateur, string $id_avis, int $note, string $texte): bool {
        global $pdo;
        $avis_actuels = $this->getAvisSolida();
        $modifie = false;

        foreach ($avis_actuels as $key => $avi) {
            // Vérifie l'ID de l'avis ET l'ID de l'utilisateur (pour la sécurité)
            if ($avi['id_avis'] === $id_avis && $avi['id_utilisateur'] === $id_utilisateur) {
                $avis_actuels[$key]['note'] = $note;
                $avis_actuels[$key]['texte_avis'] = $texte;
                $avis_actuels[$key]['date_modification'] = date('Y-m-d H:i:s');
                $modifie = true;
                break;
            }
        }

        if ($modifie) {
            $avis_json = json_encode($avis_actuels);
            $sql = "UPDATE association SET avis_plateforme_global = :avis WHERE id_association = :id";
            
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':avis', $avis_json, PDO::PARAM_STR);
                $stmt->bindValue(':id', self::GLOBAL_ID, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (Exception $e) {
                error_log("Erreur lors de la modification d'avis Solida (JSON): " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
    
    /**
     * Supprime un avis existant par son ID (dans le JSON).
     */
    public function supprimerAvisSolida(int $id_utilisateur, string $id_avis): bool {
        global $pdo;
        $avis_actuels = $this->getAvisSolida();
        $supprime = false;

        foreach ($avis_actuels as $key => $avi) {
            // Vérifie l'ID de l'avis ET l'ID de l'utilisateur (pour la sécurité)
            if ($avi['id_avis'] === $id_avis && $avi['id_utilisateur'] === $id_utilisateur) {
                unset($avis_actuels[$key]);
                $supprime = true;
                break;
            }
        }
        
        // Réindexe l'array après suppression
        $avis_actuels = array_values($avis_actuels);

        if ($supprime) {
            $avis_json = json_encode($avis_actuels);
            $sql = "UPDATE association SET avis_plateforme_global = :avis WHERE id_association = :id";
            
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':avis', $avis_json, PDO::PARAM_STR);
                $stmt->bindValue(':id', self::GLOBAL_ID, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (Exception $e) {
                error_log("Erreur lors de la suppression d'avis Solida (JSON): " . $e->getMessage());
                return false;
            }
        }
        return false;
    }
    
    /**
     * Fonction utilitaire pour récupérer les noms des utilisateurs associés aux avis.
     */
    private function enrichirAvisAvecNoms(array $avis): array {
        global $pdo;
        $ids_utilisateurs = array_unique(array_column($avis, 'id_utilisateur'));
        
        if (empty($ids_utilisateurs)) {
            return $avis;
        }

        $placeholders = implode(',', array_fill(0, count($ids_utilisateurs), '?'));
        $sql_users = "SELECT id_utilisateur, nom FROM utilisateur WHERE id_utilisateur IN ($placeholders)";
        $stmt_users = $pdo->prepare($sql_users);
        $stmt_users->execute($ids_utilisateurs);
        
        $noms_utilisateurs = $stmt_users->fetchAll(PDO::FETCH_KEY_PAIR); // [id => nom]

        foreach ($avis as $key => $avi) {
            $id_u = $avi['id_utilisateur'];
            $avis[$key]['nom_utilisateur'] = $noms_utilisateurs[$id_u] ?? 'Utilisateur Inconnu';
        }

        return $avis;
    }


    // Fichier : associationc.php

// ... (code existant de AssociationC) ...

    /**
     * Récupère la liste des commentaires décodés (array) pour une association.
     */
    public function getCommentairesAssociation(int $id_association): array {
        global $pdo;
        $sql = "SELECT avis FROM association WHERE id_association = :id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id_association, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row && $row['avis']) {
                $commentaires = json_decode($row['avis'], true);
                // Si la jointure est nécessaire (pour afficher le nom de l'utilisateur)
                if (!empty($commentaires)) {
                    $commentaires = $this->enrichirCommentairesAvecNoms($commentaires);
                }
                return is_array($commentaires) ? $commentaires : [];
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des avis: " . $e->getMessage());
        }
        return [];
    }

    /**
     * Ajoute un commentaire au champ `avis` de l'association (via JSON).
     */
    public function ajouterCommentaireAssociation(int $id_association, int $id_utilisateur, int $note, string $texte): bool {
        global $pdo;
        
        // 1. Récupérer les commentaires actuels
        $commentaires = $this->getCommentairesAssociation($id_association);

        // 2. Créer le nouveau commentaire avec un ID unique et la date
        $nouveau_commentaire = [
            // Génération d'un ID unique simple (nécessaire pour la modification/suppression)
            'id_commentaire' => uniqid(), 
            'id_utilisateur' => $id_utilisateur,
            'note' => $note,
            'texte' => $texte,
            'date_creation' => date('Y-m-d H:i:s'),
            'date_modification' => null
        ];

        // 3. Ajouter le nouveau commentaire à la liste
        $commentaires[] = $nouveau_commentaire;

        // 4. Mettre à jour la base de données avec le JSON encodé
        $avis_json = json_encode($commentaires);
        
        $sql = "UPDATE association SET avis = :avis WHERE id_association = :id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':avis', $avis_json, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id_association, PDO::PARAM_INT);

            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Erreur lors de l'ajout d'avis (JSON): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Modifie un commentaire existant par son ID (dans le JSON).
     */
    public function modifierCommentaireAssociation(int $id_association, int $id_utilisateur, string $id_commentaire, int $note, string $texte): bool {
        global $pdo;
        $commentaires = $this->getCommentairesAssociation($id_association);
        $modifie = false;

        foreach ($commentaires as $key => $cmt) {
            // Vérifie l'ID du commentaire ET l'ID de l'utilisateur (pour la sécurité)
            if ($cmt['id_commentaire'] === $id_commentaire && $cmt['id_utilisateur'] === $id_utilisateur) {
                $commentaires[$key]['note'] = $note;
                $commentaires[$key]['texte'] = $texte;
                $commentaires[$key]['date_modification'] = date('Y-m-d H:i:s');
                $modifie = true;
                break;
            }
        }

        if ($modifie) {
            $avis_json = json_encode($commentaires);
            $sql = "UPDATE association SET avis = :avis WHERE id_association = :id";
            
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':avis', $avis_json, PDO::PARAM_STR);
                $stmt->bindValue(':id', $id_association, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (Exception $e) {
                error_log("Erreur lors de la modification d'avis (JSON): " . $e->getMessage());
                return false;
            }
        }
        return false; // Non trouvé ou non autorisé
    }

    /**
     * Supprime un commentaire existant par son ID (dans le JSON).
     */
    public function supprimerCommentaireAssociation(int $id_association, int $id_utilisateur, string $id_commentaire): bool {
        global $pdo;
        $commentaires = $this->getCommentairesAssociation($id_association);
        $supprime = false;

        foreach ($commentaires as $key => $cmt) {
            // Vérifie l'ID du commentaire ET l'ID de l'utilisateur (pour la sécurité)
            if ($cmt['id_commentaire'] === $id_commentaire && $cmt['id_utilisateur'] === $id_utilisateur) {
                unset($commentaires[$key]);
                $supprime = true;
                break;
            }
        }
        
        // Réindexe l'array après suppression
        $commentaires = array_values($commentaires);

        if ($supprime) {
            $avis_json = json_encode($commentaires);
            $sql = "UPDATE association SET avis = :avis WHERE id_association = :id";
            
            try {
                $stmt = $pdo->prepare($sql);
                $stmt->bindValue(':avis', $avis_json, PDO::PARAM_STR);
                $stmt->bindValue(':id', $id_association, PDO::PARAM_INT);
                return $stmt->execute();
            } catch (Exception $e) {
                error_log("Erreur lors de la suppression d'avis (JSON): " . $e->getMessage());
                return false;
            }
        }
        return false; // Non trouvé ou non autorisé
    }
    
    /**
     * Fonction utilitaire pour récupérer les noms des utilisateurs.
     * Non optimal, mais nécessaire si l'on ne veut pas créer de jointure SQL.
     */
    private function enrichirCommentairesAvecNoms(array $commentaires): array {
        global $pdo;
        $ids_utilisateurs = array_unique(array_column($commentaires, 'id_utilisateur'));
        
        if (empty($ids_utilisateurs)) {
            return $commentaires;
        }

        $placeholders = implode(',', array_fill(0, count($ids_utilisateurs), '?'));
        $sql_users = "SELECT id_utilisateur, nom FROM utilisateur WHERE id_utilisateur IN ($placeholders)";
        $stmt_users = $pdo->prepare($sql_users);
        $stmt_users->execute($ids_utilisateurs);
        
        $noms_utilisateurs = $stmt_users->fetchAll(PDO::FETCH_KEY_PAIR); // [id => nom]

        foreach ($commentaires as $key => $cmt) {
            $id_u = $cmt['id_utilisateur'];
            $commentaires[$key]['nom_utilisateur'] = $noms_utilisateurs[$id_u] ?? 'Utilisateur Inconnu';
        }

        return $commentaires;
    }

// ... (reste du code de AssociationC)
    // ... (code existant de AssociationC)

    /**
     * Liste toutes les associations avec le total des dons agrégés.
     * Le total est COUNT(*) pour sang/nourriture et SUM(montant_donne) pour argent.
     */
    public function listerAssociationsAvecTotalDons(): array {
        global $pdo;
        $listeAssociations = [];
        
        // 1. Récupérer toutes les associations
        $associations = $this->listerAssociations(); 
        
        if (empty($associations)) {
            return [];
        }

        // 2. Préparer la requête d'agrégation des dons
        $sqlDons = "SELECT 
                        id_association, 
                        type_don,
                        COUNT(id_don) as total_dons_count,
                        SUM(CASE WHEN type_don = 'argent' THEN montant_donne ELSE 0 END) as total_dons_argent
                    FROM don
                    GROUP BY id_association, type_don";
        
        try {
            $stmtDons = $pdo->query($sqlDons);
            $resultatsDons = $stmtDons->fetchAll(PDO::FETCH_ASSOC);

            // Mapper les résultats par ID d'association pour un accès rapide
            $donsAgreges = [];
            foreach ($resultatsDons as $row) {
                $id = $row['id_association'];
                $type = $row['type_don'];
                
                if (!isset($donsAgreges[$id])) {
                    $donsAgreges[$id] = ['total_argent' => 0.0, 'total_sang' => 0, 'total_nourriture' => 0];
                }
                
                if ($type === 'argent') {
                    $donsAgreges[$id]['total_argent'] = (float)$row['total_dons_argent'];
                } elseif ($type === 'sang') {
                    $donsAgreges[$id]['total_sang'] = (int)$row['total_dons_count'];
                } elseif ($type === 'nourriture') {
                    $donsAgreges[$id]['total_nourriture'] = (int)$row['total_dons_count'];
                }
            }

            // 3. Fusionner les associations avec les totaux de dons
            foreach ($associations as $association) {
                $id = $association->getIdAssociation();
                $totaux = $donsAgreges[$id] ?? ['total_argent' => 0.0, 'total_sang' => 0, 'total_nourriture' => 0];

                $listeAssociations[] = [
                    'association' => $association, // L'objet Association
                    'totaux_dons' => $totaux
                ];
            }
            
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des associations avec totaux de dons: " . $e->getMessage());
        }
        
        return $listeAssociations;
    }

// ... (reste du code de AssociationC)
    
    /**
     * Ajoute une nouvelle association à la base de données.
     */
    public function ajouterAssociation(Association $association): bool {
        global $pdo;
        $sql = "INSERT INTO association (nom_association, description, adresse, pays, type_don_supporte) 
                VALUES (:nom, :desc, :adr, :pays, :type_don)";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nom', $association->getNomAssociation(), PDO::PARAM_STR);
            $stmt->bindValue(':desc', $association->getDescription(), PDO::PARAM_STR);
            $stmt->bindValue(':adr', $association->getAdresse(), PDO::PARAM_STR);
            $stmt->bindValue(':pays', $association->getPays(), PDO::PARAM_STR);
            $stmt->bindValue(':type_don', $association->getTypeDonSupporte(), PDO::PARAM_STR);

            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Erreur lors de l'ajout de l'association: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère une association par son ID.
     */
    public function recupererAssociation(int $id): ?Association {
        global $pdo;
        $sql = "SELECT * FROM association WHERE id_association = :id";

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return new Association(
                    $row['id_association'],
                    $row['nom_association'],
                    $row['description'],
                    $row['adresse'],
                    $row['pays'],
                    $row['type_don_supporte']
                );
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération de l'association ID {$id}: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Liste toutes les associations.
     */
    public function listerAssociations(): array {
        global $pdo;
        $sql = "SELECT * FROM association ORDER BY nom_association ASC";
        $listeAssociations = [];

        try {
            $stmt = $pdo->query($sql);
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resultats as $row) {
                $listeAssociations[] = new Association(
                    $row['id_association'],
                    $row['nom_association'],
                    $row['description'],
                    $row['adresse'],
                    $row['pays'],
                    $row['type_don_supporte']
                );
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des associations: " . $e->getMessage());
        }
        return $listeAssociations;
    }

    /**
     * Récupère les associations filtrées par type de don et pays.
     * Utilise une jointure implicite via les champs de la table Association.
     */
    public function listerAssociationsParTypeEtPays(string $typeDon, string $pays): array {
        global $pdo;
        $listeAssociations = [];
        
        $sql = "SELECT * FROM association 
                WHERE type_don_supporte = :typeDon 
                AND pays = :pays
                ORDER BY nom_association ASC";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':typeDon', $typeDon, PDO::PARAM_STR);
            $stmt->bindValue(':pays', $pays, PDO::PARAM_STR);
            $stmt->execute();
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resultats as $row) {
                $listeAssociations[] = new Association(
                    $row['id_association'],
                    $row['nom_association'],
                    $row['description'],
                    $row['adresse'],
                    $row['pays'],
                    $row['type_don_supporte']
                );
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des associations filtrées: " . $e->getMessage());
            return [];
        }
        return $listeAssociations;
    }

    /**
     * Modifie les informations d'une association existante.
     */
    public function modifierAssociation(Association $association): bool {
        global $pdo;
        $sql = "UPDATE association SET 
                nom_association = :nom, 
                description = :desc, 
                adresse = :adr,
                pays = :pays,
                type_don_supporte = :type_don
                WHERE id_association = :id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':nom', $association->getNomAssociation(), PDO::PARAM_STR);
            $stmt->bindValue(':desc', $association->getDescription(), PDO::PARAM_STR);
            $stmt->bindValue(':adr', $association->getAdresse(), PDO::PARAM_STR);
            $stmt->bindValue(':pays', $association->getPays(), PDO::PARAM_STR);
            $stmt->bindValue(':type_don', $association->getTypeDonSupporte(), PDO::PARAM_STR);
            $stmt->bindValue(':id', $association->getIdAssociation(), PDO::PARAM_INT);

            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Erreur lors de la modification de l'association: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime une association par son ID.
     */
    public function supprimerAssociation(int $id): bool {
        global $pdo;
        $sql = "DELETE FROM association WHERE id_association = :id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Erreur lors de la suppression de l'association ID {$id}: " . $e->getMessage());
            return false;
        }
    }
}
?>