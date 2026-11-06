<?php
declare(strict_types=1);

function Connexion(): PDO {
    try {
        $connexion = new PDO('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');
        $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $connexion;
    } catch (PDOException $e) {
        die('Erreur PDO : ' . $e->getMessage());
    } catch (Exception $e) {
        die('Erreur Générale : ' . $e->getMessage());
    }
}


function filtrePseudo(string $pseudo_filtre): array {
    $connexion = Connexion();

    if (empty($pseudo_filtre)) {
        $sql = "SELECT Article.*, User.pseudo 
                    FROM Article 
                    INNER JOIN User ON Article.user_id = User.id 
                    ORDER BY Article.id DESC";
        $requete = $connexion->query($sql);
        $articles = $requete->fetchAll();
    } else {
        $sql = "SELECT Article.*, User.pseudo 
                    FROM Article 
                    INNER JOIN User ON Article.user_id = User.id 
                    WHERE User.pseudo = :pseudo 
                    ORDER BY Article.id DESC";
        $requete = $connexion->prepare($sql);
        $requete->execute(['pseudo' => $pseudo_filtre]);
        $articles = $requete->fetchAll();
    }

    return $articles;
}

function suppArticle(int $articleId, int $userId, bool $isAdmin): void{
    $connexion = Connexion();

    try {
        if (!$isAdmin) {
            $stmt_verif = $connexion->prepare("SELECT id FROM Article WHERE id = :id AND user_id = :user_id");
            $stmt_verif->execute([
                'id'      => $articleId,
                'user_id' => $userId
            ]);
            if (!$stmt_verif->fetch()) {
                die("Action non autorisée : vous n'êtes pas l'auteur de cet article.");
            }
        }

        $stmt_del_comms = $connexion->prepare("DELETE FROM Commentaire WHERE article_id = :article_id");
        $stmt_del_comms->execute(['article_id' => $articleId]);

        $stmt_del_art = $connexion->prepare("DELETE FROM Article WHERE id = :id");
        $stmt_del_art->execute(['id' => $articleId]);

        header('Location: index.php');
        exit();

    } catch (PDOException $e) {
        die('Erreur lors de la suppression : ' . $e->getMessage());
    }
}

function suppComm(int $commId, int $userId, bool $isAdmin, int $articleId): void{
    $connexion = Connexion();

    if ($isAdmin) {
        $stmt_del = $connexion->prepare("DELETE FROM Commentaire WHERE id = :id");
        $stmt_del->execute(['id' => $commId]);
    } else {
        $stmt_del = $connexion->prepare("DELETE FROM Commentaire WHERE id = :id AND user_id = :user_id");
        $stmt_del->execute([
            'id'      => $commId,
            'user_id' => $userId
        ]);
    }

    header("Location: comm_article.php?article_id=" . $articleId);
    exit();
}

function ajoutComm(string $description, int $articleId, int $userId): void{
    $connexion = Connexion();

    $requete = $connexion->prepare(
        "INSERT INTO Commentaire (description, article_id, user_id)
             VALUES (:description, :article_id, :user_id)"
    );
    $requete->execute([
        'description' => $description,
        'article_id'  => $articleId,
        'user_id'     => $userId
    ]);

    header("Location: comm_article.php?article_id=" . $articleId);
    exit();
}


function recupArticle(int $articleId) {
    $connexion = Connexion();

    $stmt = $connexion->prepare(
        "SELECT Article.*, User.pseudo 
             FROM Article 
             INNER JOIN User ON Article.user_id = User.id 
             WHERE Article.id = :id"
    );
    $stmt->execute(['id' => $articleId]);

    return $stmt->fetch();
}


function recupCommentaires(int $articleId): array {
    $connexion = Connexion();

    $stmt = $connexion->prepare(
        "SELECT Commentaire.*, User.pseudo 
             FROM Commentaire 
             INNER JOIN User ON Commentaire.user_id = User.id 
             WHERE Commentaire.article_id = :article_id 
             ORDER BY Commentaire.id DESC"
    );
    $stmt->execute(['article_id' => $articleId]);

    return $stmt->fetchAll();
}

function authentifierUser(string $email, string $mdp): ?string{
    $connexion = Connexion();

    $stmt = $connexion->prepare("SELECT * FROM User WHERE email = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();
    $stmt->closeCursor();

    if ($user) {
        if (password_verify($mdp, $user['mdp'])) {
            $_SESSION['user'] = [
                'id'     => (int)$user['id'],
                'email'  => $user['email'],
                'pseudo' => $user['pseudo'],
                'admin'  => (int)$user['admin']
            ];
            header('Location: filesPHP/index.php');
            exit();
        }
        return "Mot de passe incorrect.";
    }
    creerUser($email, $mdp);
    return null;
}

function creerUser(string $email, string $mdp): void
{
    $connexion = Connexion();

    $hashMdp    = password_hash($mdp, PASSWORD_DEFAULT);
    $pseudoBase = explode('@', $email)[0];
    $isAdmin    = ($email === 'admin@localhost.fr' && $mdp === 'admin69IUT') ? 1 : 0;

    $requete = $connexion->prepare(
        "INSERT INTO User (email, mdp, pseudo, admin) VALUES (:email, :mdp, :pseudo, :admin)"
    );
    $requete->execute([
        'email'  => $email,
        'mdp'    => $hashMdp,
        'pseudo' => $pseudoBase,
        'admin'  => $isAdmin
    ]);

    $_SESSION['user'] = [
        'id'     => (int)$connexion->lastInsertId(),
        'email'  => $email,
        'pseudo' => $pseudoBase,
        'admin'  => $isAdmin
    ];

    header('Location: filesPHP/index.php');
    exit();
    }

    function recupCategories(): array {
        $connexion = Connexion();
        $requete = $connexion->query("SELECT * FROM Categorie ORDER BY nom ASC");
        return $requete->fetchAll();
    }

    function recupCategorieParId(int $id): ?array
    {
        $connexion = Connexion();
        $req = $connexion->prepare("SELECT * FROM Categorie WHERE id = :id");
        $req->execute(['id' => $id]);
        $resultat = $req->fetch();

        return $resultat ?: null;
    }

    function ajouterArticle(string $titre, string $description, array $categories, int $userId): void {
        $connexion = Connexion();

        $categories_str = implode(', ', array_map('trim', $categories));

        $stmt = $connexion->prepare(
            "INSERT INTO Article (titre, description, categorie, user_id) 
             VALUES (:titre, :description, :categorie, :user_id)"
        );
        $stmt->execute([
            'titre'       => $titre,
            'description' => $description,
            'categorie'   => $categories_str,
            'user_id'     => $userId
        ]);

        header('Location: index.php');
        exit();
    }

    function ajouterCategorie(string $nom): bool
    {
        $nom = trim($nom);
        if ($nom === '') {
            return false;
        }

        $connexion = Connexion();
        $req = $connexion->prepare("INSERT INTO Categorie (nom) VALUES (:nom)");
        return $req->execute(['nom' => $nom]);
    }

    function modifierCategorie(int $id, string $nom): bool
    {
        $nom = trim($nom);
        if ($id <= 0 || $nom === '') {
            return false;
        }

        $connexion = Connexion();
        $req = $connexion->prepare("UPDATE Categorie SET nom = :nom WHERE id = :id");
        return $req->execute(['nom' => $nom, 'id' => $id]);
    }

    function supprimerCategorie(int $id): bool
    {
        if ($id <= 0) {
            return false;
        }

        $connexion = Connexion();
        $req = $connexion->prepare("DELETE FROM Categorie WHERE id = :id");
        return $req->execute(['id' => $id]);
    }

?>