<?php
session_start();

// 1. Sécurité : Vérifier si c'est bien l'admin
if (!isset($_SESSION['user']) || $_SESSION['user']['admin'] !== 1) {
    header('Location: ../index.php');
    exit();
}

// 2. Connexion à la base de données
$connexion = new PDO('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');

// 3. Ajout d'une catégorie
if (isset($_POST['valider'])) {
    $nom = $_POST['nom'];
    
    if (!empty($nom)) {
        $req = $connexion->prepare("INSERT INTO Catégorie (nom) VALUES (:nom)");
        $req->execute(['nom' => $nom]);
        
        // Recharger la page pour vider le formulaire
        header('Location: crud.php');
        exit();
    }
}

// 4. Suppression d'une catégorie
if (isset($_GET['supprimer'])) {
    $id = $_GET['supprimer'];
    
    $req = $connexion->prepare("DELETE FROM Catégorie WHERE id = :id");
    $req->execute(['id' => $id]);
    
    header('Location: crud.php');
    exit();
}

// 5. Récupérer toutes les catégories pour les afficher
$req = $connexion->query("SELECT * FROM Catégorie");
$categories = $req->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Catégories</title>
</head>
<body>
    <h1>Administration des Catégories</h1>
    
    <p>
        <a href="../index.php">Accueil</a> | 
        <a href="../deconnexion.php">Déconnexion</a>
    </p>

    <hr>

    <!-- Formulaire d'ajout -->
    <h2>Ajouter une catégorie</h2>
    <form method="POST" action="">
        <label>Nom de la catégorie :</label>
        <input type="text" name="nom" required>
        <button type="submit" name="valider">Ajouter</button>
    </form>

    <hr>

    <!-- Liste des catégories -->
    <h2>Liste des catégories</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Action</th>
        </tr>
        <?php foreach ($categories as $cat): ?>
        <tr>
            <td><?= $cat['id'] ?></td>
            <td><?= htmlspecialchars($cat['nom']) ?></td>
            <td>
                <a href="crud.php?supprimer=<?= $cat['id'] ?>" onclick="return confirm('Confirmer la suppression ?');">Supprimer</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>