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

$user_id = (int)$_SESSION['user']['id'];
$msgErreur = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $selected_cats = $_POST['categories'] ?? [];

    if (!empty($titre) && !empty($description) && !empty($selected_cats) && is_array($selected_cats)) {
        ajouterArticle($titre, $description, $selected_cats, $user_id);
    } else {
        $msgErreur = "Veuillez remplir tous les champs pour publier l'article.";
    }
}

$categories = recupCategories();
?>

<?php require_once 'header.php'; ?>

    <style>
        h1 {
            margin-top: 30px;
            margin-bottom: 20px;
        }

        h2 {
            text-align: center;
            margin: 0 0 20px 0;
        }

        a[href="index.php"] {
            text-decoration: none;
            font-style: italic;
            color: black;
            font-size: 90%;
            display: inline-block;
            margin-bottom: 10px;
        }

        a[href="index.php"]:hover {
            text-decoration: underline;
        }

        hr {
            border: none;
            border-top: 1px solid #c4c0c0;
            margin: 15px 0 25px 0;
        }

        form {
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            width: 50%;
            gap: 15px;
        }

        form div {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        label {
            font-weight: 600;
            font-size: 14px;
            color: #222;
        }

        input[type="text"],
        textarea,
        select {
            border: none;
            border-radius: 5px;
            padding: 8px 10px;
            font-family: inherit;
            font-size: 14px;
            background-color: white;
            box-sizing: border-box;
        }

        input[type="text"] {
            height: 36px;
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        form div label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: normal;
            cursor: pointer;
        }

        button {
            align-self: center;
            width: 200px;
            border: none;
            border-radius: 20px;
            box-shadow: 3px 3px gray;
            padding: 8px 15px;
            margin-top: 10px;
            background-color: white;
            color: black;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
            transition: background-color 0.2s ease;
        }

        button:hover {
            background-color: rgb(48, 48, 48);
            margin-left: 2px;
            margin-top: 12px;
            box-shadow: none;
            color: white;
        }
    </style>

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