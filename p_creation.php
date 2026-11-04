<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$msgErreur = null;
require_once ('utils.php');


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

    $titre = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $selectCategories = $_POST['categorie'] ?? [];
    $user_id = $_SESSION['user_id'] ?? $_SESSION['user']['id'] ?? 1;

    if (!empty($titre) && !empty($description) && !empty($selectCategories)) {

        $categorie_liste = implode(', ', array_map('trim', $selectCategories));

        $stmt = $connexion->prepare("INSERT INTO Article (titre, description, categorie, user_id) VALUES (:titre, :description, :categorie, :user_id)");
        $stmt->execute([
                "titre"       => $titre,
                "description" => $description,
                "categorie"   => $selectCategories,
                "user_id"     => $user_id
        ]);

        header('Location: index.php');
        exit();

    }else{
        $msgErreur = "Veuillez remplir tous les champs pour publier l'article";
    }
}

$req_categories = $connexion->query("SELECT * FROM Categorie ORDER BY nom ASC");
$categories = $req_categories->fetchAll();
?>

<?php require_once ('header.php'); ?>

<a href="index.php">← Retour à l'accueil</a>
<hr>

<h1>Page Création Articles</h1>

<h2>Créer un Nouvel Article</h2>

<?php showMsgErreur($msgErreur); ?>

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
        <label>Catégories :</label><br>
        <?php foreach ($categories as $cat): ?>
            <label>
                <input
                        type="checkbox"
                        name="categories[]"
                        value="<?= htmlspecialchars($cat['nom']) ?>"
                >
                <?= htmlspecialchars($cat['nom']) ?>
            </label><br>
        <?php endforeach; ?>
    </div>

    <br>
    <button type="submit" name="valider">Valider</button>
</form>

<?php require_once('footer.php'); ?>