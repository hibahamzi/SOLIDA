<?php
// Check if this file is being included by dashboard.php
$isIncluded = (isset($donC) && isset($associationC) && isset($listeDons) && isset($listeAssociations));

// Only initialize if accessed directly (not included)
if (!$isIncluded) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once __DIR__ . '/../../config/config.php';
    require_once __DIR__ . '/../../controllers/donc.php'; 
    require_once __DIR__ . '/../../controllers/associationc.php';
    $donC = new DonC();
    $associationC = new AssociationC();
    $listeDons = $donC->listerTousDonsAvecDetails();
    $listeAssociations = $associationC->listerAssociations();
    $current_view = $_GET['view'] ?? 'dons';
    $message = $_GET['msg'] ?? '';
    $associationToEdit = null;
    if (isset($_GET['edit_asso_id'])) {
        require_once __DIR__ . '/../../models/association.php';
        $associationToEdit = $associationC->recupererAssociation((int)$_GET['edit_asso_id']);
    }
} else {
    // Being included, use variables from dashboard.php
    $current_view = $_GET['view'] ?? 'dons';
    $message = $_GET['msg'] ?? '';
    if (!isset($associationToEdit)) {
        $associationToEdit = null;
        if (isset($_GET['edit_asso_id'])) {
            require_once __DIR__ . '/../../models/association.php';
            $associationToEdit = $associationC->recupererAssociation((int)$_GET['edit_asso_id']);
        }
    }
} 
// Variables are now set above based on whether file is included or accessed directly

// ------------------------------------------------------------------
// LOGIQUE DE GESTION DES DONS (READ & UPDATE de STATUT SEULEMENT)
// ------------------------------------------------------------------
if ($current_view === 'dons' && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['don_action'])) {
    $donId = (int)$_POST['don_id'];
    $action = $_POST['don_action'];
    
    if ($action === 'valider' || $action === 'archiver') {
        $nouveauStatut = ($action === 'valider') ? 'Validé' : 'Archivé';
        
        // CORRECTION : Appel à modifierStatutDon() qui est bien défini
        $result = $donC->modifierStatutDon($donId, $nouveauStatut); 
        
        if ($result) {
            $message = "Le don #$donId a été marqué comme **" . $nouveauStatut . "** avec succès.";
        } else {
            $message = "Erreur lors de la modification du statut du don #$donId.";
        }
        
        header('Location: dashboard.php?section=dons&msg=' . urlencode($message));
        exit();
    }
}

// ------------------------------------------------------------------
// LOGIQUE DE GESTION DES ASSOCIATIONS (CRUD COMPLET)
// NOTE : Vous devez vous assurer que le MODÈLE Association.php est disponible
// ------------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['asso_action'])) {
    $asso_action = $_POST['asso_action'];
    $message = '';

    // Logique de modification/ajout (CREATE/UPDATE)
    if ($asso_action === 'save') {
        $id = isset($_POST['asso_id']) ? (int)$_POST['asso_id'] : null;
        
        // *******************************************************************
        // L'objet Association doit être créé ici pour utiliser les méthodes du contrôleur
        // (J'assume que le modèle association.php est chargé correctement)
       
        
        $association = new Association(
            $id, // L'ID sera ignoré par ajouterAssociation mais utilisé par modifierAssociation
            $_POST['name'],
            $_POST['description'],
            $_POST['address'] ?? 'Non spécifié', // J'assume un champ 'address' manquant dans le formulaire
            $_POST['country'],
            $_POST['type']
        );
        // *******************************************************************
        
        if ($id) { 
            if ($associationC->modifierAssociation($association)) {
                $message = "Association modifiée avec succès."; 
            } else {
                $message = "Erreur lors de la modification de l'association.";
            }
        } else { 
            if ($associationC->ajouterAssociation($association)) {
                $message = "Nouvelle association ajoutée avec succès."; 
            } else {
                 $message = "Erreur lors de l'ajout de l'association.";
            }
        }
        
        header('Location: dashboard.php?section=associations&msg=' . urlencode($message));
        exit();
    }
    
    // Logique de suppression (DELETE)
    else if ($asso_action === 'delete' && isset($_POST['asso_id'])) {
        $id = (int)$_POST['asso_id'];
        
        $result = $associationC->supprimerAssociation($id); 
        
        if ($result) {
            $message = "L'association #$id a été supprimée avec succès.";
        } else {
            $message = "Erreur lors de la suppression de l'association #$id.";
        }
        
        header('Location: dashboard.php?section=associations&msg=' . urlencode($message));
        exit();
    }
}

// ------------------------------------------------------------------
// RÉCUPÉRATION DES DONNÉES POUR L'AFFICHAGE (READ)
// ------------------------------------------------------------------
// Data is already fetched above in the if (!$isIncluded) block or passed from dashboard.php
// Only fetch associationToEdit if not already set and edit_asso_id is in GET
if (!isset($associationToEdit) && isset($_GET['edit_asso_id'])) {
    if (!class_exists('Association')) {
        require_once __DIR__ . '/../../models/association.php';
    }
    $associationToEdit = $associationC->recupererAssociation((int)$_GET['edit_asso_id']);
}

// If accessed directly, render full page with sidebar/topbar
if (!$isIncluded) {
    ?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Backoffice Spécialisé - Solida</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../back_office/assets/css/admin.css">
</head>
<body>
    <?php include '../back_office/includes/sidebar.php'; ?>
    <div class="main-content">
        <?php 
        $pageTitle = 'Gestion des Dons';
        include '../back_office/includes/topbar.php'; 
        ?>
        <div class="content-area">
<?php } ?>
    <div class="container py-5">
        <h1>Backoffice des Dons Solida</h1>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= $message; ?></div>
        <?php endif; ?>

        <!-- Navigation tabs removed as requested -->
        <!-- Show both sections always -->
        
        <!-- Dons Section -->
        <h2 class="mb-3">Dons Enregistrés <i class="fas fa-lock text-danger"></i></h2>
            <p class="alert alert-warning">
              
            </p>

            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type de Don</th>
                            <th>Association Ciblée</th>
                            <th>Valeur/Détails</th>
                            <th>Statut</th>
                            <th>Actions (Statut)</th>
                        </tr>
                    </thead>
                        <?php if (!empty($listeDons)): ?>
                            <?php foreach ($listeDons as $data): // $data est un tableau: ['don' => DonObject, 'association_nom' => '...']
                                $donObject = $data['don'];
                                $typeDon = $donObject->getTypeDon();
                                $statutDon = $donObject->getStatut();
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($donObject->getIdDon()); ?></td>
                                    <td><?= htmlspecialchars($typeDon); ?></td>
                                    <td><?= htmlspecialchars($data['association_nom']); ?></td> 
                                    <td>
                                        <?php 
                                        // Affiche le détail pertinent en utilisant les getters de l'objet Don
                                        if ($typeDon === 'argent') {
                                            echo (htmlspecialchars($donObject->getMontantDonne()) ?? 'N/A') . ' TND';
                                        } elseif ($typeDon === 'sang') {
                                            echo 'Groupe: ' . (htmlspecialchars($donObject->getGroupeSanguin()) ?? 'N/A');
                                        } else { // nourriture
                                            echo htmlspecialchars($donObject->getDetailsNourriture()) ?? 'Détails non spécifiés';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <span class="badge 
                                            <?= ($statutDon == 'Validé') ? 'bg-success' : 
                                                (($statutDon == 'Archivé') ? 'bg-secondary' : 'bg-warning'); ?>">
                                            <?= htmlspecialchars($statutDon); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" style="display: inline-block;">
                                            <input type="hidden" name="don_id" value="<?= $donObject->getIdDon(); ?>">
                                            <?php if ($statutDon == 'En attente'): ?>
                                                <button type="submit" name="don_action" value="valider" class="btn btn-sm btn-success" title="Valider le don">
                                                    Valider <i class="fas fa-check"></i>
                                                </button>
                                            <?php endif; ?>
                                            <button type="submit" name="don_action" value="archiver" class="btn btn-sm btn-secondary" title="Archiver le don">
                                                Archiver <i class="fas fa-archive"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">Aucun don enregistré.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        <!-- Associations Section -->
        <hr class="my-5">
        <h2 class="mb-3">Gestion des Associations (Partenaires)</h2>
            <p class="alert alert-info">
               
            </p>

            <div class="card p-4 mb-5">
                <h3><?= $associationToEdit ? 'Modifier Association' : 'Ajouter une Association'; ?></h3>
                <form method="POST" action="dashboard.php?section=associations">
                    <input type="hidden" name="asso_action" value="save">
                    <?php if ($associationToEdit): ?>
                        <input type="hidden" name="asso_id" value="<?= $associationToEdit->getIdAssociation(); ?>">
                    <?php endif; ?>
                    
                    <div class="mb-3">
                        <label class="form-label">Nom de l'Association</label>
                        <input type="text" name="name" class="form-control" required 
                            value="<?= $associationToEdit ? htmlspecialchars($associationToEdit->getNomAssociation()) : ''; ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3" required><?= $associationToEdit ? htmlspecialchars($associationToEdit->getDescription()) : ''; ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Pays</label>
                            <input type="text" name="country" class="form-control" required 
                                value="<?= $associationToEdit ? htmlspecialchars($associationToEdit->getPays()) : ''; ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Type de Don Supporté</label>
                            <input type="text" name="type" class="form-control" required 
                                value="<?= $associationToEdit ? htmlspecialchars($associationToEdit->getTypeDonSupporte()) : ''; ?>" 
                                placeholder="ex: sang, argent, nourriture">
                        </div>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Adresse</label>
                        <input type="text" name="address" class="form-control" 
                            value="<?= $associationToEdit ? htmlspecialchars($associationToEdit->getAdresse()) : ''; ?>" 
                            placeholder="Adresse Complète (requis par le contrôleur)">
                    </div>
                    
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
                    <?php if ($associationToEdit): ?>
                        <a href="dashboard.php?section=associations" class="btn btn-outline-secondary">Annuler l'édition</a>
                    <?php endif; ?>
                </form>
            </div>

            <h3 class="mt-4">Liste des Partenaires</h3>
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nom</th>
                        <th>Type de Don</th>
                        <th>Pays</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($listeAssociations)): ?>
                        <?php foreach ($listeAssociations as $asso): ?>
                            <tr>
                                <td><?= htmlspecialchars($asso->getIdAssociation()); ?></td>
                                <td><?= htmlspecialchars($asso->getNomAssociation()); ?></td>
                                <td><?= htmlspecialchars($asso->getTypeDonSupporte()); ?></td>
                                <td><?= htmlspecialchars($asso->getPays()); ?></td>
                                <td>
                                    <a href="dashboard.php?section=associations&edit_asso_id=<?= $asso->getIdAssociation(); ?>" class="btn btn-sm btn-info" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" style="display: inline-block;" onsubmit="return confirm('Confirmer la suppression de <?= htmlspecialchars($asso->getNomAssociation()); ?>?');">
                                        <input type="hidden" name="asso_action" value="delete">
                                        <input type="hidden" name="asso_id" value="<?= $asso->getIdAssociation(); ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">Aucune association partenaire enregistrée.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

    </div>
<?php if (!$isIncluded): ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php endif; ?>