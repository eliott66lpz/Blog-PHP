<?php
declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

try{
    $connexion = new PDO ('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    die('Erreur PDO : ' . $e->getMessage());
}catch(Exception $e){
    die('Erreur Générale : ' . $e->getMessage());
}

if($_SERVER['REQUEST_METHOD'] == 'POST'){

    $titre = $_POST['titre'];
    $description = $_POST['description'];
    $categorie = $_POST['categorie'];
    $user_id = $_POST['user']['id'];


if (!empty($titre) && !empty($description) && !empty($categorie)) {
        $stmt = $connexion->prepare("INSERT INTO Commentaire (description, article_id, user_id) VALUES (:description, :article_id, :user_id)");
        $stmt->execute([
            "description" => $description,
            "article_id"   => $article_id,
            "user_id"=> $user_id
        ]);

        header('Location: index.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
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
                id="descritption"
                name="description"
                rows="6"
                cols="40"
                placeholder="Écrivez votre texte ici..."
                required>
            </textarea>
        </div>

        <div>
            <label for="categorie">Catégorie : </label>
            <select id="categorie" name="categorie" required>
                <option value="">-- Choisir une catégorie --</option>
                <option value="Science-Fiction">Science-Fiction</option>
                <option value="Aventure">Aventure</option>
                <option value="Policier">Policier</option>
                <option value="Fantastique">Fantastique</option>
            </select>
        </div>
        <button type="submit" name="valider">Valider</button>
        </form>
</body>
</html>