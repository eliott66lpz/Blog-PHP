<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'utils.php';
require_once '../database/function_database.php';


// Protection de la page
if (empty($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit();
}

$user_id = (int)$_SESSION['user']['id'];
$msgErreur = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $selected_cats = $_POST['categories'] ?? []; // Récupère le tableau des cases cochées

    if (!empty($titre) && !empty($description) && !empty($selected_cats) && is_array($selected_cats)) {
        if (!empty($titre) && !empty($description) && !empty($selected_cats) && is_array($selected_cats)) {
            ajouterArticle($titre, $description, $selected_cats, $user_id);
        } else {
            $msgErreur = "Veuillez remplir tous les champs pour publier l'article.";
        }
    }

    $categories = getCategories();
}
?>

<?php require_once 'header.php'; ?>

    <a href="index.php">← Retour à l'accueil</a>
    <hr>

    <h1>Page Création Articles</h1>
    <h2>Créer un Nouvel Article</h2>

<?php showMsgErreur($msgErreur); ?>

    <form method="POST" action="p_creation.php">
        <div>
            <label for="titre">Titre :</label><br>
            <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($_POST['titre'] ?? '') ?>" required>
        </div>

        <br>

        <div>
            <label for="description">Description :</label><br>
            <textarea id="description" name="description" rows="6" cols="40" required><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
        </div>

        <br>

        <div>
            <label>Catégories :</label><br>
            <?php foreach ($categories as $cat): ?>
                <label>
                    <input
                            type="checkbox"
                            name="categories[]"
                            value="<?= htmlspecialchars($cat['nom']) ?>"
                            <?= (isset($_POST['categories']) && in_array($cat['nom'], $_POST['categories'])) ? 'checked' : '' ?>
                    >
                    <?= htmlspecialchars($cat['nom']) ?>
                </label><br>
            <?php endforeach; ?>
        </div>

        <br>
        <button type="submit" name="valider">Valider</button>
    </form>

<?php require_once 'footer.php'; ?>