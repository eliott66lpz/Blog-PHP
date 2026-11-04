<?php

$msgErreur = null;

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

$article_id = isset($_GET['article_id']) ? (int)$_GET['article_id'] : 1;
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    if (!empty($description)) {
        $requete = $connexion->prepare(
                "INSERT INTO Commentaire (description, article_id, user_id)
             VALUES (:description, :article_id, :user_id)"
        );
        $requete->execute([
                'description' => $description,
                'article_id'  => $article_id,
                'user_id'     => $user_id
        ]);

        header("Location: comm_article.php?article_id=" . $article_id);
        exit();
    } else {
        $msgErreur = "Le commentaire ne peut pas être vide.";
    }
}


$req_article = $connexion->prepare(
        "SELECT Article.*, User.pseudo 
     FROM Article 
     INNER JOIN User ON Article.user_id = User.id 
     WHERE Article.id = :id"
);
$req_article->execute(['id' => $article_id]);
$article = $req_article->fetch();

if (!$article) {
    die("Cet article n'existe pas.");
}

$req_comment = $connexion->prepare(
        "SELECT Commentaire.*, User.pseudo 
     FROM Commentaire 
     INNER JOIN User ON Commentaire.user_id = User.id 
     WHERE Commentaire.article_id = :article_id 
     ORDER BY Commentaire.id DESC"
);
$req_comment->execute(['article_id' => $article_id]);
$commentaires = $req_comment->fetchAll();
?>

<?php require_once ('header.php'); ?>

<a href="index.php">← Retour à la liste des articles</a>
<hr>

<article>
    <h1><?= htmlspecialchars($article['titre'] ?? 'Sans titre') ?></h1>
    <h3>
        Auteur : <?= htmlspecialchars((string)($article['pseudo'] ?? 'Inconnu')) ?> |
        Catégorie : <?= htmlspecialchars((string)($article['categorie'] ?? 'Général')) ?>
    </h3>
    <p><?= nl2br(htmlspecialchars($article['description'] ?? '')) ?></p>
</article>

<hr>

<h2>Ajouter un commentaire</h2>

<?php showMsgErreur($msgErreur); ?>

<form method="POST">
    <label>Votre message : <br>
        <textarea name="description" id="description" rows="4" cols="50" required></textarea></label><br><br>
    <button type="submit">Publier le commentaire</button>
</form>

<hr>

<h2>Espace Commentaires (<?= count($commentaires) ?>)</h2>

<?php if (empty($commentaires)): ?>
    <p>Soyez le premier à commenter cet article !</p>
<?php else: ?>
    <ul>
        <?php foreach ($commentaires as $com): ?>
            <li style="margin-bottom: 15px;">
                <strong><?= htmlspecialchars((string)($com['pseudo'] ?? 'Inconnu')) ?> :</strong><br>
                <?= htmlspecialchars($com['description'] ?? '') ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require_once ('footer.php'); ?>