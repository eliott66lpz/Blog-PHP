<?php
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
    <button type="button">Ajouter un Commentaire</button>
</body>
</html>