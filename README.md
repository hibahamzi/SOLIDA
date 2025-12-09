Projet SOLIDA - Gestion des Participations aux Événements
Description
Application web de gestion des participations aux événements développée en PHP avec architecture MVC.

Architecture
Modèle MVC : Séparation claire des responsabilités
POO : Programmation orientée objet
PDO : Connexion à la base de données via PDO uniquement
Structure du Projet
projet/
├── config/
│   └── config.php          # Configuration de la base de données
├── controllers/
│   ├── evenementControllers.php
│   ├── participationController.php
│   ├── ReclamationController.php
│   └── userControllers.php
├── models/
│   ├── EvenementModel.php
│   ├── ParticipationModel.php
│   ├── Reclamation.php
│   └── UserModel.php
└── views/
    ├── front_office/       # Interface utilisateur
    └── back_office/        # Interface administrateur
Entités
1. User (Première entité)
Gestion des utilisateurs
CRUD complet
2. Participation (Deuxième entité avec jointure)
Jointure entre User et Evenement
CRUD complet FrontOffice et BackOffice
Contrôles de saisie JavaScript (sans HTML5)
Fonctionnalités
FrontOffice
✅ Créer une participation
✅ Voir ses participations
✅ Modifier une participation
✅ Supprimer une participation
✅ Validation JavaScript complète
BackOffice
✅ Créer une participation
✅ Voir toutes les participations
✅ Modifier une participation
✅ Supprimer une participation
✅ Statistiques des participations
✅ Recherche avancée
Fonctionnalités Métier
✅ Statistiques (total, par type, avec déjeuner, etc.)
✅ Recherche avancée avec filtres
✅ Jointures SQL (User ↔ Participation ↔ Evenement)
✅ Validation métier (motivation min 10 mots, nombre personnes selon type, etc.)
Contrôles de Saisie
Tous les formulaires utilisent uniquement la validation JavaScript (pas d'attributs HTML5 comme required, min, max, pattern).

Règles de validation :
Motivation : Minimum 10 mots
Source d'information : 3 à 100 caractères
Nombre de personnes : 2 à 50 pour les participations en groupe
Champs obligatoires : Validation côté client et serveur
Base de Données
Tables principales :
users : Utilisateurs
evenements : Événements
participations : Participations (jointure User ↔ Evenement)
Relations :
participations.id_user → users.id
participations.id_evenement → evenements.id_evenement
Installation
Configurer la base de données dans config/config.php
Importer le schéma de base de données
Accéder à l'application via le serveur web
Technologies
PHP 7.4+
MySQL/MariaDB
PDO
JavaScript (ES6+)
Bootstrap 5
Font Awesome
Auteur
Projet développé dans le cadre d'un projet académique.

=======

SOLIDA-project-2A11# SOLIDA-project-2A11
