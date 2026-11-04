<?php

$_SESSION['user_id'] = 4;
require_once ('utils.php');

try {
    $connexion = new PDO('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur PDO : ' . $e->getMessage());
} catch (Exception $e) {
    die('Erreur Générale : ' . $e->getMessage());
}

$pseudo_filtre = trim($_GET['pseudo_f'] ?? '');

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
?>


<?php require_once('header.php'); ?>


    <h1>Liste des Articles</h1>

    <a href="p_creation.php" type="button">Ajouter un article</a><br><br>

    <?php if (!empty($_SESSION['user']['admin']) && (int)$_SESSION['user']['admin'] === 1): ?>
        <a href="crud.php" type="button">Accèder au CRUD</a>
    <?php endif; ?><br><br>

    <form method="GET">
        <div>
            <label>Filtrer par pseudo :
            <input type="text" name="pseudo_f" value="<?= htmlspecialchars($pseudo_filtre ?? '') ?>" >
            </label>
            <button type="submit">Valider</button>
        </div>
    </form>

    <?php if(empty($articles)): ?>
        <p>Aucun article disponible</p>

    <?php else : ?>
        <?php foreach ($articles as $article): ?>
            <h2><?= htmlspecialchars($article['titre'] ?? 'Sans titre') ?></h2>

            <h3>
                Auteur : <?= htmlspecialchars((string)($article['pseudo'] ?? 'Inconnu')) ?> |
                Catégorie : <?= htmlspecialchars((string)($article['categorie'] ?? 'Général')) ?>
            </h3>

            <p><?= nl2br(htmlspecialchars($article['description'] ?? '')) ?></p>

            <a href="comm_article.php?article_id=<?= (int)$article['id'] ?>">Voir les commentaires de cet article</a>
            <br><br>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] === (int)$article['user_id']): ?>
                <form method="POST">
                    <button type="submit" onclick="return confirm('Supprimer cet article ?')">Supprimer</button>
                </form>

            <?php endif; ?>
                <hr>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require_once ('footer.php'); ?>