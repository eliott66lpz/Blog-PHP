<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'utils.php';
require_once '../database/function_database.php';

if (empty($_SESSION['user']['id'])) {
    header('Location: ../login.php');
    exit();
}

$currentUser = $_SESSION['user'];
$currentUserId = (int)$currentUser['id'];
$isAdmin = (int)($currentUser['admin'] ?? 0) === 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article_id'])) {
    $article_id_to_delete = (int)$_POST['delete_article_id'];
    suppArticle($article_id_to_delete, $currentUserId, $isAdmin);
}

$pseudo_filtre = trim($_GET['pseudo_f'] ?? '');
    $articles = filtrePseudo($pseudo_filtre);
?>

<?php require_once 'header.php'; ?>

    <h1>Liste des Articles</h1>

    <a href="p_creation.php">Ajouter un article</a><br><br>

<?php if ($isAdmin): ?>
    <a href="../admin/crud.php">Accéder au CRUD</a><br><br>
<?php endif; ?>

    <form method="GET" action="index.php">
        <div>
            <label for="pseudo_f">Filtrer par pseudo :</label>
            <input type="text" id="pseudo_f" name="pseudo_f" value="<?= htmlspecialchars($pseudo_filtre) ?>">
            <button type="submit">Valider</button>
            <?php if (!empty($pseudo_filtre)): ?>
                <a href="index.php">Réinitialiser</a>
            <?php endif; ?>
        </div>
    </form>

    <hr>

<?php if (empty($articles)): ?>
    <p>Aucun article disponible</p>
<?php else: ?>
    <?php foreach ($articles as $article): ?>
        <h2><?= htmlspecialchars($article['titre'] ?? 'Sans titre') ?></h2>

        <h3>
            Auteur : <?= htmlspecialchars((string)($article['pseudo'] ?? 'Inconnu')) ?> |
            Catégorie : <?= htmlspecialchars((string)($article['categorie'] ?? 'Général')) ?>
        </h3>

        <p><?= nl2br(htmlspecialchars($article['description'] ?? '')) ?></p>

        <a href="comm_article.php?article_id=<?= (int)$article['id'] ?>">Voir les commentaires de cet article</a>
        <br><br>

        <?php if ($isAdmin || $currentUserId === (int)$article['user_id']): ?>
            <form method="POST" action="index.php">
                <input type="hidden" name="delete_article_id" value="<?= (int)$article['id'] ?>">
                <button type="submit" onclick="return confirm('Supprimer cet article ?')">Supprimer</button>
            </form>
            <br>
        <?php endif; ?>
        <hr>
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once 'footer.php'; ?>