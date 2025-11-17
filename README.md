        # Forum MVC PHP - Sample Project

This package contains a minimal MVC structure for a forum + comments system in plain PHP.

## Setup
1. Create a MySQL database named `projet` (or change config.php accordingly).
2. Create the tables (simple example below):

```
CREATE TABLE user (
  id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
  nom_prenom VARCHAR(100),
  email VARCHAR(100),
  num VARCHAR(20),
  role VARCHAR(20),
  mdp VARCHAR(255)
);

CREATE TABLE forum (
  id_forum INT AUTO_INCREMENT PRIMARY KEY,
  id_user INT,
  categorie VARCHAR(100),
  discussion_g TEXT,
  discussion_p TEXT,
  FOREIGN KEY (id_user) REFERENCES user(id_utilisateur)
);

CREATE TABLE commentaire (
  id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
  contenu VARCHAR(255),
  date_commentaire DATETIME,
  id_auteur INT,
  FOREIGN KEY (id_auteur) REFERENCES user(id_utilisateur)
);
```

3. Put the project files in your web server root and open `index.php`.

This is a minimal example for learning purposes. Adjust validations, security, and structure for production use.
