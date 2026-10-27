<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

try {
    $connexion = new PDO('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur PDO : ' . $e->getMessage());
} catch (Exception $e) {
    die('Erreur Générale : ' . $e->getMessage());
}

// RÉCUPÉRATION DES ARTICLES
$requete = $connexion->query("SELECT * FROM Article ORDER BY id DESC");
$articles = $requete->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil - TP PHP</title>
</head>
<body>
    <h1>Liste des Articles</h1>

    <?php if(empty($articles)): ?>
        <p>Aucun article disponible</p>

    <?php else : ?>
        <?php foreach ($articles as $article): ?>
            <h2><?= htmlspecialchars($article['titre'] ?? 'Sans titre') ?></h2>

            <h3>
                Auteur : <?= htmlspecialchars((string)($article['user_id'] ?? 'Inconnu')) ?> |
                Catégorie : <?= htmlspecialchars((string)($article['categorie'] ?? 'Général')) ?>
            </h3>

            <p><?= nl2br(htmlspecialchars($article['description'] ?? '')) ?></p>

            <a href="comm_article.php?article_id=<?= (int)$article['id'] ?>">Voir les commentaires de cet article</a>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>