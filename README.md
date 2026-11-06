# 📝 Projet Blog PHP - BUT2 Informatique

Réalisation d'un blog dynamique développé en PHP natif dans le cadre d'un projet universitaire de **BUT2 Informatique**. Ce projet met en œuvre les concepts fondamentaux du développement web côté serveur, la gestion d'une base de données relationnelle et une architecture modulaire.

## 👥 Équipe de développement (G2S3 - 2026)
* ERRABIA Mustafa
* LOPEZ ARNAU Eliott
* BENSEDDIK Ambrine

---

## ✨ Fonctionnalités principales

Le projet propose un système complet de gestion de contenu avec deux niveaux de privilèges (Utilisateur standard et Administrateur).

### 👤 Côté Utilisateur
* **Authentification fluide :** Système de connexion sécurisé avec création de compte "à la volée" lors de la première connexion.
* **Gestion des Articles :** Consultation de la liste des articles, création de nouveaux articles, et suppression de ses propres publications.
* **Filtrage :** Barre de recherche permettant de filtrer les articles par pseudo d'auteur.
* **Espace Commentaires :** Possibilité de lire, publier et supprimer ses propres commentaires sous chaque article.

### 🛡️ Côté Administrateur
* **CRUD des Catégories :** Interface d'administration dédiée permettant d'ajouter, modifier et supprimer des catégories.
* **Sécurité référentielle :** Blocage de la suppression d'une catégorie si celle-ci est déjà liée à un article existant.
* **Modération :** Droit de suppression sur l'intégralité des articles et des commentaires publiés sur le blog.

---

## 📂 Architecture du Projet

Le code source est organisé selon l'arborescence suivante :

```text
📁 tp_php/
├── 📁 admin/
│   └── crud.php                   # Interface d'administration (CRUD catégories)
├── 📁 css/
│   └── style.css                  # Styles globaux (Header, Footer, variables)
├── 📁 database/
│   ├── blog_php.sql               # Script de création de la base de données
│   └── function_database.php      # Fichier centralisant toutes les requêtes PDO
├── 📁 filesPHP/
│   ├── ajout_commentaire.php      # Traitement de l'ajout des commentaires
│   ├── comm_article.php           # Page d'affichage d'un article et ses commentaires
│   ├── footer.php                 # Pied de page global
│   ├── header.php                 # En-tête global et navigation
│   ├── index.php                  # Page d'accueil (liste des articles et filtres)
│   ├── p_creation.php             # Formulaire de création d'un article
│   └── utils.php                  # Fonctions utilitaires (affichage d'erreurs, etc.)
├── 📁 README/
│   └── README.md                  # Documentation du projet
└── login.php                      # Page d'authentification (Racine du projet)