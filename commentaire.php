<?php

declare(strict_types=1);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

session_start();

try {
    $connexion = new PDO('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur PDO : ' . $e->getMessage());
} catch (Exception $e) {
    die('Erreur Générale : ' . $e->getMessage());
}

    $requete = $connexion->prepare(
                    "SELECT * FROM Commentaire"
                );
                $requete->execute([
                    'id'  => $id,
                    'description'    => $description,
                    'article_id' => $article_id,
                    'user_id'  => $user_id
                ]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - TP PHP</title>
</head>
<body>
    <h1>Page Commentaire</h1>
    <a href="ajout_commentaire.php" type="button">Ajouter un Commentaire</button>
</body>
</html>