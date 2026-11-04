<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

if (empty($_SESSION['user']['admin']) || (int)$_SESSION['user']['admin'] !== 1) {
    header('Location: index.php');
    exit();
}

try {
    $connexion = new PDO('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur PDO : ' . $e->getMessage());
} catch (Exception $e) {
    die('Erreur Générale : ' . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter'])) {
    $nom = $_POST['nom'] ?? '';
    if (!empty($nom)) {
        $req = $connexion->prepare("INSERT INTO Categorie (nom) VALUES (:nom)");
        $req->execute(['nom' => $nom]);
        header('Location: crud.php');
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier'])) {
    $id = (int)($_POST['id'] ?? 0);
    $nom = $_POST['nom'] ?? '';
    if ($id > 0 && !empty($nom)) {
        $req = $connexion->prepare("UPDATE Categorie SET nom = :nom WHERE id = :id");
        $req->execute(['nom' => $nom, 'id' => $id]);
        header('Location: crud.php');
        exit();
    }
}

if (isset($_GET['supprimer'])) {
    $id = (int)$_GET['supprimer'];
    if ($id > 0) {
        $req = $connexion->prepare("DELETE FROM Categorie WHERE id = :id");
        $req->execute(['id' => $id]);
    }
    header('Location: crud.php');
    exit();
}

$categorie_a_modifier = null;
if (isset($_GET['editer'])) {
    $id = (int)$_GET['editer'];
    $req = $connexion->prepare("SELECT * FROM Categorie WHERE id = :id");
    $req->execute(['id' => $id]);
    $categorie_a_modifier = $req->fetch();
}

$categories = $connexion->query("SELECT * FROM Categorie ORDER BY id ASC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Catégories - Admin</title>
</head>
<body>
<a href="index.php">← Retour à l'accueil</a>
<hr>

<h1>Administration des Catégories</h1>

<?php if ($categorie_a_modifier): ?>
    <h2>Modifier la catégorie : <?= htmlspecialchars($categorie_a_modifier['nom']) ?></h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?= (int)$categorie_a_modifier['id'] ?>">
        <label for="nom">Nouveau nom :
        <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($categorie_a_modifier['nom']) ?>" required></label>
        <button type="submit" name="modifier">Enregistrer</button>
        <a href="crud.php" style="margin-left: 10px;">Annuler</a>
    </form>
<?php else: ?>
    <h2>Ajouter une catégorie</h2>
    <form method="POST">
        <label>Nom de la catégorie :
        <input type="text" name="nom" required></label>
        <button type="submit" name="ajouter">Ajouter</button>
    </form>
<?php endif; ?>

<hr>

<h2>Liste des catégories (<?= count($categories) ?>)</h2>

<?php if (empty($categories)): ?>
    <p>Aucune catégorie enregistrée.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
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
                    <a href="crud.php?editer=<?= (int)$cat['id'] ?>">Modifier</a> |
                    <a href="crud.php?supprimer=<?= (int)$cat['id'] ?>" >Supprimer</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>
</body>
</html>