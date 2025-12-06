<?php
// (Vide exprès — pour que tu ajoutes du PHP plus tard si tu veux)
// Aucun changement sur ton code HTML/CSS/JS.
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Réclamation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7f6;
            padding: 20px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            display: flex;
            align-items: center;
            color: #961096;
            margin-bottom: 30px;
            border-bottom: 2px solid #6903769c;
            padding-bottom: 10px;
        }
        .section-header {
            color: #f23fb9;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
        }
        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
        }
        .form-group {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        label {
            margin-bottom: 5px;
            font-size: 0.9em;
            color: #40b6f1;
        }
        input[type="text"], input[type="email"], select, textarea {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            width: 100%;
            box-sizing: border-box;
        }
        textarea {
            resize: vertical;
            min-height: 100px;
        }
        .required::after {
            content: " *";
            color: red;
        }
        .tel-group { display: flex; }
        .tel-prefix {
            padding: 10px;
            border: 1px solid #ccc;
            border-right: none;
            background-color: #eee;
            border-radius: 4px 0 0 4px;
        }
        .tel-input {
            flex-grow: 1;
            border-radius: 0 4px 4px 0 !important;
        }
        .btn-localize {
            background-color: #d443c8;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 10px;
        }
        .priority-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .priority-group .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            cursor: pointer;
            flex: 1;
            text-align: center;
        }
        .btn-faible { background-color: #408f50; }
        .btn-normale { background-color: #f8ce4f; color: #333;}
        .btn-urgente { background-color: #b65962;}
        .btn-submit {
            background-color: #65cff6;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            margin-top: 30px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .btn-cancel {
            background-color: #f44336;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            margin-top: 10px;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .navigation { margin-bottom: 20px; }
        .nav-btn {
            background-color: #961096;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
    </style>
</head>

<body>
    <div class="container">

        <div class="navigation">
            <a href="Addreclam.php" class="nav-btn">← Retour à la liste</a>
        </div>

        <h2>&#9998;&nbsp;Modifier une Réclamation</h2>

        <div class="section-header">&#9432; Informations Personnelles</div>

        <form id="reclamation-form">

            <input type="hidden" id="reclamation-id" name="id">

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Nom</label>
                    <input type="text" id="nom" name="nom" required>
                </div>

                <div class="form-group">
                    <label class="required">Prénom</label>
                    <input type="text" id="prenom" name="prenom" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Téléphone</label>
                    <div class="tel-group">
                        <span class="tel-prefix">+216</span>
                        <input type="text" id="telephone" name="telephone" class="tel-input" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="required">Email</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>

            <div class="section-header">&#9906; Localisation</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Gouvernorat</label>
                    <select id="gouvernorat" name="gouvernorat" required>
                        <option value="">-- Choisir --</option>
                        <option value="Tunis">Tunis</option>
                        <option value="Ariana">Ariana</option>
                        <option value="Sfax">Sfax</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="required">Délégation</label>
                    <select id="delegation" name="delegation" required>
                        <option value="">-- Choisir d'abord le gouvernorat --</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Ville</label>
                    <input type="text" id="ville" name="ville">
                </div>

                <div class="form-group">
                    <label class="required">Position GPS</label>
                    <div style="display:flex;">
                        <input type="text" id="position-gps" name="positionGPS" required>
                        <button type="button" class="btn-localize" id="btn-localiser">&#9906; Localiser</button>
                    </div>
                </div>
            </div>

            <div class="section-header">&#9776; Détails de la Réclamation</div>

            <div class="form-row">
                <div class="form-group">
                    <label class="required">Priorité</label>
                    <div class="priority-group">
                        <button type="button" class="btn btn-faible" data-value="Faible">Faible</button>
                        <button type="button" class="btn btn-normale" data-value="Normale">Normale</button>
                        <button type="button" class="btn btn-urgente" data-value="Urgente">Urgente</button>
                    </div>
                    <input type="hidden" id="priorite" name="priorite" value="Normale">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group" style="width:100%;">
                    <label class="required">Description détaillée</label>
                    <textarea id="description" name="description" required></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Statut</label>
                    <select id="statut" name="statut">
                        <option value="Nouveau">Nouveau</option>
                        <option value="En cours">En cours</option>
                        <option value="Résolu">Résolu</option>
                        <option value="Fermé">Fermé</option>
                    </select>
                </div>
            </div>

            <button class="btn-submit" type="submit">&#9998;&nbsp;Modifier la réclamation</button>
            <button type="button" class="btn-cancel" onclick="location.href='liste-reclam.php'">Annuler</button>

        </form>
    </div>

<script>
/* Ton JavaScript original – inchangé */
const delegationsData = {
    "Tunis": ["Tunis Ville", "Bab Souika", "Carthage"],
    "Ariana": ["Ariana Ville", "Soukra"],
    "Sfax": ["Sfax Ville", "Sfax Sud"]
};

// ... TOUT TON CODE JS ORIGINAL ...
</script>

</body>
</html>
