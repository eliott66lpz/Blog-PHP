<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'utils.php';

// Protection de la page
if (empty($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit();
}

// Connexion à la base de données
try {
    $connexion = new PDO('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur PDO : ' . $e->getMessage());
} catch (Exception $e) {
    die('Erreur Générale : ' . $e->getMessage());
}

$currentUser = $_SESSION['user'];
$currentUserId = (int)$currentUser['id'];
$isAdmin = (int)($currentUser['admin'] ?? 0) === 1;

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article_id'])) {
    $article_id_to_delete = (int)$_POST['delete_article_id'];

    try {
        // 1. Vérifier si l'utilisateur a le droit de supprimer cet article
        if (!$isAdmin) {
            $stmt_verif = $connexion->prepare("SELECT id FROM Article WHERE id = :id AND user_id = :user_id");
            $stmt_verif->execute([
                    'id'      => $article_id_to_delete,
                    'user_id' => $currentUserId
            ]);
            if (!$stmt_verif->fetch()) {
                die("Action non autorisée : vous n'êtes pas l'auteur de cet article.");
            }
        }

        // 2. Supprimer d'abord les commentaires rattachés à cet article
        $stmt_del_comms = $connexion->prepare("DELETE FROM Commentaire WHERE article_id = :article_id");
        $stmt_del_comms->execute(['article_id' => $article_id_to_delete]);

        // 3. Supprimer l'article
        $stmt_del_art = $connexion->prepare("DELETE FROM Article WHERE id = :id");
        $stmt_del_art->execute(['id' => $article_id_to_delete]);

        header('Location: index.php');
        exit();

    } catch (PDOException $e) {
        die('Erreur lors de la suppression : ' . $e->getMessage());
    }
}

// Filtre par pseudo
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

<?php require_once 'header.php'; ?>

    <h1>Liste des Articles</h1>

    <a href="p_creation.php">Ajouter un article</a><br><br>

<?php if ($isAdmin): ?>
    <a href="admin/crud.php">Accéder au CRUD</a><br><br>
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