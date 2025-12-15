<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/don.php';

class DonC {
    
    
    /**
     * Récupère un don par son ID.
     */
    public function recupererDon(int $id_don): ?Don {
        global $pdo;
        $sql = "SELECT * FROM don WHERE id_don = :id_don";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_don', $id_don, PDO::PARAM_INT);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                return new Don(
                    $row['id_don'],
                    $row['id_utilisateur'],
                    $row['id_association'],
                    $row['type_don'],
                    $row['statut'],
                    $row['montant_donne'],
                    $row['methode_paiement'],
                    $row['numero_carte_token'],
                    $row['groupe_sanguin'],
                    $row['a_maladie'],
                    $row['details_maladie'],
                    $row['details_nourriture'],
                    $row['date_don']
                );
            }
            return null; // Don non trouvé
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération du don ID {$id_don}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Modifie un don existant (seuls les champs de don sont modifiables pour l'exemple).
     */
    public function modifierDon(Don $don): bool {
        global $pdo;
        $sql = "UPDATE don SET 
                type_don = :type, statut = :statut, montant_donne = :montant,
                groupe_sanguin = :groupe_s, a_maladie = :maladie, 
                details_maladie = :details_m, details_nourriture = :details_n
                WHERE id_don = :id_don";

        try {
            $stmt = $pdo->prepare($sql);
            
            $stmt->bindValue(':type', $don->getTypeDon(), PDO::PARAM_STR);
            $stmt->bindValue(':statut', $don->getStatut(), PDO::PARAM_STR);
            $stmt->bindValue(':montant', $don->getMontantDonne(), PDO::PARAM_STR);
            $stmt->bindValue(':groupe_s', $don->getGroupeSanguin(), PDO::PARAM_STR);
            $stmt->bindValue(':maladie', $don->getAMaladie(), PDO::PARAM_INT);
            $stmt->bindValue(':details_m', $don->getDetailsMaladie(), PDO::PARAM_STR);
            $stmt->bindValue(':details_n', $don->getDetailsNourriture(), PDO::PARAM_STR);
            $stmt->bindValue(':id_don', $don->getIdDon(), PDO::PARAM_INT);
            
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Erreur lors de la modification du don ID {$don->getIdDon()}: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajoute un nouveau don à la base de données.
     */
    public function ajouterDon(Don $don): bool {
        global $pdo;
        
        $sql = "INSERT INTO don (id_utilisateur, id_association, type_don, statut, 
                montant_donne, methode_paiement, numero_carte_token, 
                groupe_sanguin, a_maladie, details_maladie, details_nourriture) 
                VALUES (:id_user, :id_asso, :type, :statut, :montant, :methode_p, 
                :token, :groupe_s, :maladie, :details_m, :details_n)";
    
        try {
            $stmt = $pdo->prepare($sql);
            
            // Liaison des valeurs
            $stmt->bindValue(':id_user', $don->getIdUtilisateur(), PDO::PARAM_INT);
            $stmt->bindValue(':id_asso', $don->getIdAssociation(), PDO::PARAM_INT);
            $stmt->bindValue(':type', $don->getTypeDon(), PDO::PARAM_STR);
            $stmt->bindValue(':statut', $don->getStatut(), PDO::PARAM_STR);
            $stmt->bindValue(':montant', $don->getMontantDonne(), PDO::PARAM_STR);
            $stmt->bindValue(':methode_p', $don->getMethodePaiement(), PDO::PARAM_STR);
            $stmt->bindValue(':token', $don->getNumeroCarteToken(), PDO::PARAM_STR);
            $stmt->bindValue(':groupe_s', $don->getGroupeSanguin(), PDO::PARAM_STR);
            $stmt->bindValue(':maladie', $don->getAMaladie(), PDO::PARAM_INT);
            $stmt->bindValue(':details_m', $don->getDetailsMaladie(), PDO::PARAM_STR);
            $stmt->bindValue(':details_n', $don->getDetailsNourriture(), PDO::PARAM_STR);
            
            error_log("Executing INSERT query for donation");
            error_log("Values: id_user=" . $don->getIdUtilisateur() . ", id_asso=" . $don->getIdAssociation() . ", type=" . $don->getTypeDon());
            
            $result = $stmt->execute();
            
            if ($result) {
                $insertId = $pdo->lastInsertId();
                error_log("Don successfully inserted with ID: " . $insertId);
                return true;
            } else {
                $errorInfo = $stmt->errorInfo();
                error_log("SQL Error: " . print_r($errorInfo, true));
                return false;
            }
    
        } catch (Exception $e) {
            error_log("🚨 ERREUR SQL: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Liste les dons par utilisateur avec jointure.
     */
    public function listerDonsParUtilisateur(int $id_utilisateur): array {
        global $pdo;
        $sql = "SELECT d.*, a.nom_association, a.pays
                FROM don d
                LEFT JOIN Association a ON d.id_association = a.id_association
                WHERE d.id_utilisateur = :id_user 
                ORDER BY d.date_don DESC";
        
        $listeDons = [];

        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id_user', $id_utilisateur, PDO::PARAM_INT);
            $stmt->execute();
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resultats as $row) {
                $don = new Don(
                    $row['id_don'],
                    $row['id_utilisateur'],
                    $row['id_association'],
                    $row['type_don'],
                    $row['statut'],
                    $row['montant_donne'],
                    $row['methode_paiement'],
                    $row['numero_carte_token'],
                    $row['groupe_sanguin'],
                    $row['a_maladie'],
                    $row['details_maladie'],
                    $row['details_nourriture'],
                    $row['date_don']
                );
                
                $listeDons[] = [
                    'don' => $don,
                    'association_nom' => $row['nom_association'],
                    'pays' => $row['pays']
                ];
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des dons: " . $e->getMessage());
        }
        
        return $listeDons;
    }

    /**
     * Liste tous les dons avec jointures complètes.
     */
    public function listerTousDonsAvecDetails(): array {
        global $pdo;
        $sql = "SELECT d.*, a.nom_association, a.pays, u.fullname as nom_utilisateur
                FROM don d
                LEFT JOIN association a ON d.id_association = a.id_association
                LEFT JOIN users u ON d.id_utilisateur = u.id
                ORDER BY d.date_don DESC";
        
        $listeDons = [];

        try {
            $stmt = $pdo->query($sql);
            $resultats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($resultats as $row) {
                $don = new Don(
                    $row['id_don'],
                    $row['id_utilisateur'],
                    $row['id_association'],
                    $row['type_don'],
                    $row['statut'],
                    $row['montant_donne'],
                    $row['methode_paiement'],
                    $row['numero_carte_token'],
                    $row['groupe_sanguin'],
                    $row['a_maladie'],
                    $row['details_maladie'],
                    $row['details_nourriture'],
                    $row['date_don']
                );
                
                $listeDons[] = [
                    'don' => $don,
                    'association_nom' => $row['nom_association'],
                    'pays' => $row['pays'],
                    'utilisateur_nom' => $row['nom_utilisateur']
                ];
            }
        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des dons: " . $e->getMessage());
        }
        
        return $listeDons;
    }

    /**
     * Met à jour le statut d'un don.
     */
    public function modifierStatutDon(int $id, string $nouveau_statut): bool {
        global $pdo;
        $sql = "UPDATE don SET statut = :statut WHERE id_don = :id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':statut', $nouveau_statut, PDO::PARAM_STR);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();

        } catch (Exception $e) {
            error_log("Erreur lors de la modification du statut: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Supprime un don par son ID.
     */
    public function supprimerDon(int $id): bool {
        global $pdo;
        $sql = "DELETE FROM don WHERE id_don = :id";
        
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
            
        } catch (Exception $e) {
            error_log("Erreur lors de la suppression du don ID {$id}: " . $e->getMessage());
            return false;
        }
    }

    // METHODE MANQUANTE - AJOUTÉE ICI
    /**
     * Récupère le total global des dons par type (sang, argent, nourriture).
     * Compte les dons pour le sang/nourriture et fait la somme du montant pour l'argent.
     */
    public function getGlobalDonationTotals(): array {
        global $pdo;
        // Utilisation de SUM(CASE...) pour agréger tous les types en une seule requête
        $sql = "SELECT
            SUM(CASE WHEN type_don = 'sang' THEN 1 ELSE 0 END) AS total_sang,
            SUM(CASE WHEN type_don = 'nourriture' THEN 1 ELSE 0 END) AS total_nourriture,
            SUM(CASE WHEN type_don = 'argent' THEN montant_donne ELSE 0 END) AS total_argent
        FROM don";
        
        try {
            $stmt = $pdo->query($sql);
            $results = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // S'assurer que les valeurs sont des nombres
            return [
                'total_sang' => (int)($results['total_sang'] ?? 0),
                'total_nourriture' => (int)($results['total_nourriture'] ?? 0),
                'total_argent' => (float)($results['total_argent'] ?? 0.0),
            ];

        } catch (Exception $e) {
            error_log("Erreur lors de la récupération des statistiques globales: " . $e->getMessage());
            return ['total_sang' => 0, 'total_nourriture' => 0, 'total_argent' => 0.0];
        }
    }
}
?>