<?php
session_start();
// Assurez-vous que le chemin est correct depuis views/php/
require_once '../../controllers/donc.php'; 
require_once '../../models/don.php';
require_once '../../models/association.php'; // Pour les détails de l'association si besoin

$donC = new DonC();
$idUtilisateur = 1; // ID utilisateur fixe pour les tests
$message = '';
$error = '';

// ====================================================================
// 1. GESTION DES ACTIONS (Suppression / Modification)
// ====================================================================

// --- SUPPRESSION ---
if (isset($_POST['action']) && $_POST['action'] === 'supprimer' && isset($_POST['id_don'])) {
    $id_don = (int)$_POST['id_don'];
    if ($donC->supprimerDon($id_don)) {
        $message = "✅ Le don #$id_don a été supprimé avec succès.";
    } else {
        $error = "❌ Erreur lors de la suppression du don #$id_don.";
    }
}

// --- MODIFICATION ---
$donToEdit = null;
if (isset($_GET['action']) && $_GET['action'] === 'modifier' && isset($_GET['id_don'])) {
    $id_don = (int)$_GET['id_don'];
    $donToEdit = $donC->recupererDon($id_don);

    if (!$donToEdit || $donToEdit->getIdUtilisateur() != $idUtilisateur) {
        $error = "Don non trouvé ou vous n'êtes pas autorisé à le modifier.";
        $donToEdit = null;
    }
}

// --- TRAITEMENT DU FORMULAIRE DE MODIFICATION (après POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'sauvegarder_modif') {
    $id_don = (int)$_POST['id_don'];
    $currentDon = $donC->recupererDon($id_don);

    if ($currentDon && $currentDon->getIdUtilisateur() == $idUtilisateur) {
        $valide = true;
        $type = strtolower(trim($_POST['type_don']));
        $montant = $currentDon->getMontantDonne(); 
        $groupeSanguin = $currentDon->getGroupeSanguin();
        $detailsNourriture = $currentDon->getDetailsNourriture();

        // ⚠️ Simplification : L'association et le type ne sont pas modifiables ici pour simplifier
        $newStatut = 'Modifié'; 

        if ($type == 'argent') {
            $montant = (float)($_POST['amount'] ?? 0.0);
            if ($montant <= 0) {
                $error = "Le montant du don doit être supérieur à zéro.";
                $valide = false;
            }
        } elseif ($type == 'sang') {
            $groupeSanguin = $_POST['bloodType'] ?? null;
            $aMaladie = isset($_POST['hasDisease']) ? 1 : 0;
            $detailsMaladie = isset($_POST['diseaseDetails']) ? trim($_POST['diseaseDetails']) : null;
            if (empty($groupeSanguin)) {
                $error = "Veuillez sélectionner votre groupe sanguin.";
                $valide = false;
            }
        } elseif ($type == 'nourriture') {
            $foodTypes = $_POST['foodTypes'] ?? [];
            if (empty($foodTypes)) {
                $error = "Veuillez sélectionner au moins un type de nourriture.";
                $valide = false;
            } else {
                $detailsNourriture = implode(', ', $foodTypes);
            }
        }

        if ($valide) {
            // Créer un nouvel objet Don avec les valeurs mises à jour
            $donModifie = new Don(
                $id_don,
                $currentDon->getIdUtilisateur(),
                $currentDon->getIdAssociation(),
                $currentDon->getTypeDon(),
                $newStatut, 
                ($type == 'argent') ? $montant : null,
                $currentDon->getMethodePaiement(), // Non modifiable
                $currentDon->getNumeroCarteToken(), // Non modifiable
                ($type == 'sang') ? $groupeSanguin : null,
                ($type == 'sang') ? $aMaladie : 0,
                ($type == 'sang') ? $detailsMaladie : null,
                ($type == 'nourriture') ? $detailsNourriture : null
            );

            if ($donC->modifierDon($donModifie)) {
                $message = "✅ Le don #$id_don a été mis à jour avec succès. (Statut: $newStatut)";
                // Redirection pour éviter la resoumission du formulaire
                header("Location: historique.php?message=" . urlencode($message));
                exit();
            } else {
                $error = "❌ Échec de la modification du don #$id_don dans la base de données.";
            }
        }
    } else {
        $error = "Don invalide ou accès refusé.";
    }
}

// ====================================================================
// 2. LISTE DES DONS
// ====================================================================

// Récupération des dons de l'utilisateur
$listeDons = $donC->listerDonsParUtilisateur($idUtilisateur);

// Récupérer les messages passés par redirection
if (isset($_GET['message'])) {
    $message = htmlspecialchars($_GET['message']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
 <title>Historique de mes dons - Solida</title>
    <link rel="apple-touch-icon" href="assets/img/apple-icon.png">
    <link rel="shortcut icon" type="image/x-icon" href="assets/img/favicon.ico">

    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/templatemo.css">
    <link rel="stylesheet" href="../css/event.css">
       <link rel="stylesheet" href="../css/slick.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;200;300;400;500;700;900&display=swap">
    <link rel="stylesheet" href="../css/fontawesome.min.css">
    
    <link rel="stylesheet" href="../assets/solida2.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
</head>
<body>
    <?php include '../front_office/includes/navbar.php'; ?>
    <div class="container py-5">
        <h2 class="mb-4"><i class="fas fa-history text-info"></i> Mon Historique de Dons</h2>

        <?php if ($message): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($donToEdit): 
            // Préparer les valeurs pour le formulaire (comme dans step4.php)
            $type = $donToEdit->getTypeDon();
            $label = match ($type) {
                'sang' => 'Sang', 'argent' => 'Argent', 'nourriture' => 'Nourriture', default => 'Inconnu'
            };
            $details_maladie = htmlspecialchars($donToEdit->getDetailsMaladie() ?? '');
            $has_disease = (bool)$donToEdit->getAMaladie();
            $food_array = explode(', ', $donToEdit->getDetailsNourriture() ?? '');
        ?>
            <div class="card mb-5 border-primary shadow-sm">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-edit"></i> Modifier le Don #<?= $donToEdit->getIdDon(); ?> (Type : <?= $label; ?>)
                </div>
                <div class="card-body">
                    <form method="POST" action="historique.php">
                        <input type="hidden" name="action" value="sauvegarder_modif">
                        <input type="hidden" name="id_don" value="<?= $donToEdit->getIdDon(); ?>">
                        <input type="hidden" name="type_don" value="<?= $type; ?>">

                        <?php if ($type == 'argent'): ?>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Montant du don (€) *</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" min="1" class="form-control" id="amount" name="amount" required 
                                        value="<?= htmlspecialchars($donToEdit->getMontantDonne() ?? '1.00'); ?>">
                                    <span class="input-group-text">€</span>
                                </div>
                            </div>
                        <?php elseif ($type == 'sang'): ?>
                            <div class="mb-3">
                                <label for="bloodType" class="form-label">Groupe Sanguin *</label>
                                <select class="form-select" id="bloodType" name="bloodType" required>
                                    <option value="">-- Sélectionnez votre groupe --</option>
                                    <?php 
                                    $options = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
                                    $selected_group = $donToEdit->getGroupeSanguin();
                                    foreach ($options as $opt): ?>
                                        <option value="<?= $opt; ?>" <?= ($selected_group == $opt) ? 'selected' : ''; ?>>
                                            <?= $opt; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-check mt-3">
                                <input class="form-check-input" type="checkbox" id="hasDisease" name="hasDisease" value="1" 
                                       <?= $has_disease ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="hasDisease">
                                    J'ai été diagnostiqué(e) avec une maladie chronique/grave.
                                </label>
                            </div>
                            <div class="mt-2" id="diseaseDetailsField" style="display: <?= $has_disease ? 'block' : 'none'; ?>;">
                                <label for="diseaseDetails" class="form-label">Détails médicaux (Optionnel)</label>
                                <textarea class="form-control" id="diseaseDetails" name="diseaseDetails" rows="3" placeholder="Précisez si nécessaire..."><?= $details_maladie; ?></textarea>
                            </div>
                        <?php elseif ($type == 'nourriture'): ?>
                            <div class="mb-3">
                                <label class="form-label">Type de nourriture *</label>
                                <div class="border rounded p-3">
                                    <?php 
                                    $food_options = ['Conserves', 'Pates/Riz', 'Eau'];
                                    foreach ($food_options as $opt): ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" id="food_<?= $opt; ?>" name="foodTypes[]" value="<?= $opt; ?>" 
                                                   <?= in_array($opt, $food_array) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="food_<?= $opt; ?>">
                                                <?= $opt; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="historique.php" class="btn btn-outline-secondary">
                                Annuler la modification
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
        
        <h3>Dons Enregistrés (Utilisateur #<?= $idUtilisateur; ?>)</h3>
        
        <?php if (empty($listeDons)): ?>
            <div class="alert alert-info mt-3">
                Vous n'avez effectué aucun don pour l'instant. <a href="step-type.php">Faire un don.</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover mt-3 align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Détails</th>
                            <th>Association</th>
                            <th>Pays</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($listeDons as $item): 
                            $don = $item['don'];
                            $type = $don->getTypeDon();

                            $details = match($type) {
                                'argent' => $don->getMontantDonne() . ' €',
                                'sang' => $don->getGroupeSanguin() . ($don->getAMaladie() ? ' (Maladie)' : ''),
                                'nourriture' => $don->getDetailsNourriture(),
                                default => 'N/A'
                            };

                            $typeLabel = match ($type) {
                                'sang' => '<span class="badge bg-danger"><i class="fas fa-tint"></i> Sang</span>',
                                'argent' => '<span class="badge bg-success"><i class="fas fa-euro-sign"></i> Argent</span>',
                                'nourriture' => '<span class="badge bg-warning text-dark"><i class="fas fa-utensils"></i> Nourriture</span>',
                                default => '<span class="badge bg-secondary">Inconnu</span>'
                            };

                            // Afficher le statut avec une couleur (exemple)
                            $statutClass = match($don->getStatut()) {
                                'Terminé', 'Validé' => 'bg-success',
                                'En attente', 'Modifié' => 'bg-info',
                                'Annulé' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        ?>
                            <tr>
                                <td><?= $don->getIdDon(); ?></td>
                                <td><?= $typeLabel; ?></td>
                                <td><?= htmlspecialchars($details); ?></td>
                                <td><?= htmlspecialchars($item['association_nom']); ?></td>
                                <td><?= htmlspecialchars($item['pays']); ?></td>
                                <td><span class="badge <?= $statutClass; ?>"><?= htmlspecialchars($don->getStatut()); ?></span></td>
                                <td><?= date('d/m/Y', strtotime($don->getDateDon())); ?></td>
                                <td class="text-center">
                                    <a href="historique.php?action=modifier&id_don=<?= $don->getIdDon(); ?>" 
                                       class="btn btn-sm btn-outline-primary me-2" 
                                       title="Modifier ce don">
                                        <i class="fas fa-edit"></i> Modifier
                                    </a>

                                    <form method="POST" action="historique.php" class="d-inline"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement ce don (ID <?= $don->getIdDon(); ?>) ?');">
                                        <input type="hidden" name="action" value="supprimer">
                                        <input type="hidden" name="id_don" value="<?= $don->getIdDon(); ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer ce don">
                                            <i class="fas fa-trash-alt"></i> Supprimer
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-end mt-4">
             <a href="step-type.php" class="btn btn-success btn-lg">
                <i class="fas fa-plus-circle"></i> Faire un nouveau don
            </a>
        </div>
    </div>
    <footer class="bg-dark" id="tempaltemo_footer">
        <div class="container">
            <div class="row">

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-success border-bottom pb-3 border-light logo">SOLIDA</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li>
                            <i class="fas fa-map-marker-alt fa-fw"></i>
                            123 Consectetur at ligula 10660
                        </li>
                        <li>
                            <i class="fa fa-phone fa-fw"></i>
                            <a class="text-decoration-none" href="tel:010-020-0340">010-020-0340</a>
                        </li>
                        <li>
                            <i class="fa fa-envelope fa-fw"></i>
                            <a class="text-decoration-none" href="mailto:info@solida.com">info@solida.com</a>
                        </li>
                    </ul>
                </div>

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-light border-bottom pb-3 border-light">Actions</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li><a class="text-decoration-none" href="#">Donner du Sang</a></li>
                        <li><a class="text-decoration-none" href="#">Donner de l'Argent</a></li>
                        <li><a class="text-decoration-none" href="#">Donner de la Nourriture</a></li>
                        <li><a class="text-decoration-none" href="#">Devenir Volontaire</a></li>
                        <li><a class="text-decoration-none" href="#">Événements</a></li>
                        <li><a class="text-decoration-none" href="#">FAQ</a></li>
                    </ul>
                </div>

                <div class="col-md-4 pt-5">
                    <h2 class="h2 text-light border-bottom pb-3 border-light">Infos Supplémentaires</h2>
                    <ul class="list-unstyled text-light footer-link-list">
                        <li><a class="text-decoration-none" href="#">Accueil</a></li>
                        <li><a class="text-decoration-none" href="#">À Propos de Nous</a></li>
                        <li><a class="text-decoration-none" href="#">Localisations</a></li>
                        <li><a class="text-decoration-none" href="#">Historique des Dons</a></li>
                        <li><a class="text-decoration-none" href="#">Contact</a></li>
                    </ul>
                </div>

            </div>

            <div class="row text-light mb-4">
                <div class="col-12 mb-3">
                    <div class="w-100 my-3 border-top border-light"></div>
                </div>
                <div class="col-auto me-auto">
                    <ul class="list-inline text-left footer-icons">
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="http://facebook.com/"><i class="fab fa-facebook-f fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="https://www.instagram.com/"><i class="fab fa-instagram fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="https://twitter.com/"><i class="fab fa-twitter fa-lg fa-fw"></i></a>
                        </li>
                        <li class="list-inline-item border border-light rounded-circle text-center">
                            <a class="text-light text-decoration-none" target="_blank" href="https://www.linkedin.com/"><i class="fab fa-linkedin fa-lg fa-fw"></i></a>
                        </li>
                    </ul>
                </div>
                <div class="col-auto">
                    <label class="sr-only" for="subscribeEmail">Email address</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control bg-dark border-light" id="subscribeEmail" placeholder="Email address">
                        <div class="input-group-text btn-success text-light">Subscribe</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-100 bg-black py-3">
            <div class="container">
                <div class="row pt-2">
                    <div class="col-12">
                        <p class="text-left text-light">
                            Copyright &copy; 2025 SOLIDA 
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script pour gérer l'affichage conditionnel des détails de maladie (dans le formulaire de modification)
        document.addEventListener('DOMContentLoaded', function() {
            const hasDisease = document.getElementById('hasDisease');
            const detailsField = document.getElementById('diseaseDetailsField');
            
            if (hasDisease && detailsField) {
                // Événement de changement
                hasDisease.addEventListener('change', function() {
                    detailsField.style.display = this.checked ? 'block' : 'none';
                });
            }
        });
    </script>
</body>
</html>