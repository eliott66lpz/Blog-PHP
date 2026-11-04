<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'utils.php';

if (empty($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit();
}

$currentUser   = $_SESSION['user'];
$currentUserId = (int)$currentUser['id'];
$isAdmin       = (int)($currentUser['admin'] ?? 0) === 1;

$msgErreur = null;

try {
    $connexion = new PDO('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur PDO : ' . $e->getMessage());
} catch (Exception $e) {
    die('Erreur Générale : ' . $e->getMessage());
}

$article_id = isset($_GET['article_id']) ? (int)$_GET['article_id'] : 0;
if ($article_id <= 0) {
    header('Location: index.php');
    exit();
}

// 1. Traitement de la suppression d'un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comm_id'])) {
    $comm_id_to_delete = (int)$_POST['delete_comm_id'];

    if ($isAdmin) {
        $stmt_del = $connexion->prepare("DELETE FROM Commentaire WHERE id = :id");
        $stmt_del->execute(['id' => $comm_id_to_delete]);
    } else {
        $stmt_del = $connexion->prepare("DELETE FROM Commentaire WHERE id = :id AND user_id = :user_id");
        $stmt_del->execute([
                'id'      => $comm_id_to_delete,
                'user_id' => $currentUserId
        ]);
    }

    header("Location: comm_article.php?article_id=" . $article_id);
    exit();
}

// 2. Traitement de l'ajout d'un commentaire
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_commentaire'])) {
    $description = trim($_POST['description'] ?? '');

    if (!empty($description)) {
        $requete = $connexion->prepare(
                "INSERT INTO Commentaire (description, article_id, user_id)
             VALUES (:description, :article_id, :user_id)"
        );
        $requete->execute([
                'description' => $description,
                'article_id'  => $article_id,
                'user_id'     => $currentUserId
        ]);

        header("Location: comm_article.php?article_id=" . $article_id);
        exit();
    } else {
        $msgErreur = "Le commentaire ne peut pas être vide.";
    }
}

// Récupération de l'article
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

// Récupération des commentaires
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

<?php require_once 'header.php'; ?>

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

    <form method="POST" action="comm_article.php?article_id=<?= $article_id ?>">
        <label for="desc_comm">Votre message :</label><br>
        <textarea name="description" id="desc_comm" rows="4" cols="50" required></textarea><br><br>
        <button type="submit" name="ajouter_commentaire">Publier le commentaire</button>
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
                <?= nl2br(htmlspecialchars($com['description'] ?? '')) ?><br><br>

                <?php if ($isAdmin || $currentUserId === (int)$com['user_id']): ?>
                    <form method="POST">
                        <input type="hidden" name="delete_comm_id" value="<?= (int)$com['id'] ?>">
                        <button type="submit" onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</button>
                    </form>
                    <br>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require_once 'footer.php'; ?>