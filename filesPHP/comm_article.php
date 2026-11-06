<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'utils.php';
require_once '../database/function_database.php';

if (empty($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit();
}

$currentUser   = $_SESSION['user'];
$currentUserId = (int)$currentUser['id'];
$isAdmin       = (int)($currentUser['admin'] ?? 0) === 1;

$msgErreur = null;


$article_id = isset($_GET['article_id']) ? (int)$_GET['article_id'] : 0;
if ($article_id <= 0) {
    header('Location: index.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comm_id'])) {
    $comm_id_to_delete = (int)$_POST['delete_comm_id'];
    suppComm($comm_id_to_delete, $currentUserId, $isAdmin, $article_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_commentaire'])) {
    $description = trim($_POST['description'] ?? '');

    if (!empty($description)) {
        ajoutComm($description, $article_id, $currentUserId);
    } else {
        $msgErreur = "Le commentaire ne peut pas être vide.";
    }
}

$article = recupArticle($article_id);
    if (!$article) {
        die("Cet article n'existe pas.");
    }

    $commentaires = recupCommentaires($article_id);

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