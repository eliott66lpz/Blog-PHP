<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../database/function_database.php';

// Protection admin avec redirection correcte
if (empty($_SESSION['user']['admin']) || (int)$_SESSION['user']['admin'] !== 1) {
    header('Location: ../filesPHP/index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom = trim($_POST['nom'] ?? '');
    if (ajouterCategorie($nom)) {
        header('Location: crud.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier'])) {
    $id = (int)($_POST['id'] ?? 0);
    $nom = trim($_POST['nom'] ?? '');
    if (modifierCategorie($id, $nom)) {
        header('Location: crud.php');
        exit();
    }
}

if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    try {
        supprimerCategorie($id);
    } catch (PDOException $e) {
        $_SESSION['erreur_crud'] = "Impossible de supprimer : cette catégorie est liée à des articles existants.";
    }
    header('Location: crud.php');
    exit();
}

$categorie_a_modifier = null;
if (isset($_GET['editer'])) {
    $categorie_a_modifier = recupCategorieParId((int)$_GET['editer']);
}

$categories = recupCategories();

require_once '../filesPHP/header.php';
?>

    <style>
        h1 {
            margin-bottom: 20px;
        }

        h2 {
            text-align: center;
            margin-top: 30px;
            margin-bottom: 15px;
        }

        .retour-accueil {
            display: inline-block;
            margin-bottom: 15px;
            color: #000;
            text-decoration: none;
            font-style: italic;
            font-size: 90%;
        }

        .retour-accueil:hover {
            text-decoration: underline;
        }

        form {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 25px;
        }

        form label {
            display: flex;
            flex-direction: column;
            align-items: center;
            font-weight: bold;
            gap: 5px;
        }

        form input[type="text"] {
            border: none;
            border-radius: 5px;
            height: 28px;
            padding: 2px 10px;
            width: 250px;
        }

        button {
            width: 200px;
            border: none;
            border-radius: 20px;
            box-shadow: 3px 3px gray;
            padding: 6px 15px;
            background-color: white;
            color: black;
            text-align: center;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
        }

        button:hover {
            background-color: rgb(48, 48, 48);
            margin-left: 2px;
            margin-top: 2px;
            box-shadow: none;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.1);
            margin: 15px 0 30px 0;
        }

        th, td {
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        th {
            background-color: rgb(48, 48, 48);
            color: white;
        }

        .action-link {
            color: #000;
            text-decoration: none;
            font-weight: bold;
            margin: 0 5px;
        }

        .action-link:hover {
            text-decoration: underline;
        }

        .action-supprimer {
            color: #a80000;
        }

        .erreur-crud {
            background-color: #ffd6d6;
            color: #a80000;
            padding: 10px 15px;
            border-radius: 5px;
            text-align: center;
            margin: 15px auto;
            width: fit-content;
        }

        hr {
            border: none;
            border-top: 1px solid #c4c0c0;
            margin: 25px 0;
        }
    </style>

    <a href="../filesPHP/index.php" class="retour-accueil">← Retour à l'accueil</a>
    <hr>

    <h1>Administration des Catégories</h1>

<?php if (!empty($_SESSION['erreur_crud'])): ?>
    <div class="erreur-crud">
        <?= htmlspecialchars($_SESSION['erreur_crud']) ?>
    </div>
    <?php unset($_SESSION['erreur_crud']); ?>
<?php endif; ?>

<?php if ($categorie_a_modifier): ?>
    <h2>Modifier la catégorie : <?= htmlspecialchars($categorie_a_modifier['nom']) ?></h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?= (int)$categorie_a_modifier['id'] ?>">
        <label for="nom">Nouveau nom :
            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($categorie_a_modifier['nom']) ?>" required>
        </label>
        <button type="submit" name="modifier">Enregistrer</button>
        <a href="crud.php" class="action-link" style="margin-top: 5px;">Annuler</a>
    </form>
<?php else: ?>
    <h2>Ajouter une catégorie</h2>
    <form method="POST">
        <label for="nom_cat">Nom de la catégorie :
            <input type="text" id="nom_cat" name="nom" required>
        </label>
        <button type="submit" name="ajouter">Ajouter</button>
    </form>
<?php endif; ?>

    <hr>

    <h2>Liste des catégories (<?= count($categories) ?>)</h2>

<?php if (empty($categories)): ?>
    <p style="text-align: center;">Aucune catégorie enregistrée.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= (int)$cat['id'] ?></td>
                <td><?= htmlspecialchars($cat['nom'] ?? '') ?></td>
                <td>
                    <a href="crud.php?editer=<?= (int)$cat['id'] ?>" class="action-link">Modifier</a> |
                    <a href="crud.php?supprimer=<?= (int)$cat['id'] ?>" class="action-link action-supprimer" onclick="return confirm('Supprimer cette catégorie ?')">Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php require_once '../filesPHP/footer.php'; ?>