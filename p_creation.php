<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

try{
    $connexion = new PDO ('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
}catch(PDOException $e){
    die('Erreur PDO : ' . $e->getMessage());
}catch(Exception $e){
    die('Erreur Générale : ' . $e->getMessage());
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $categorie = $_POST['categorie'];
    $user_id = $_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 1;

    if (!empty($titre) && !empty($description) && !empty($categorie)) {
        $stmt = $connexion->prepare("INSERT INTO Article (titre, description, categorie, user_id) VALUES (:titre, :description, :categorie, :user_id)");
        $stmt->execute([
                "titre"       => $titre,
                "description" => $description,
                "categorie"   => $categorie,
                "user_id"     => $user_id
        ]);

        header('Location: index.php');
        exit();
    }
}

$req_categories = $connexion->query("SELECT * FROM Categorie ORDER BY nom ASC");
$categories = $req_categories->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un Article</title>
</head>
<body>
<a href="index.php">← Retour à l'accueil</a>
<hr>

<h1>Page Création Articles</h1>

<h2>Créer un Nouvel Article</h2>

<form method="POST">
    <div>
        <label for="titre">Titre : </label>
        <input
                type="text"
                id="titre"
                name="titre"
                required
        >
    </div>

    <div>
        <label for="description">Description : </label>
        <textarea
                id="description"
                name="description"
                rows="6"
                cols="40"
                required></textarea>
    </div>

    <div>
        <label for="categorie">Catégorie : </label>
        <select id="categorie" name="categorie" required>
            <option value="">-- Choisir une catégorie --</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat['nom']) ?>">
                    <?= htmlspecialchars($cat['nom']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <br>
    <button type="submit" name="valider">Valider</button>
</form>
</body>
</html>