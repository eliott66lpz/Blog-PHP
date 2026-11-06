<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msgErreur= null;
require_once('filesPHP/utils.php');
require_once 'database/function_database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp   = $_POST['mdp'] ?? '';

    if (empty($email) || empty($mdp)) {
        $msgErreur = "Veuillez remplir tous les champs";
    }

    if ($msgErreur === null) {
        $resultat = authentifierUser($email, $mdp);

        if (is_string($resultat)) {
            $msgErreur = $resultat;
        } elseif ($resultat === false) {
            creerUser($email, $mdp);
        }
    }
}
?>

<?php require_once('filesPHP/header.php'); ?>


    <h2>Connexion</h2>

    <?php showMsgErreur($msgErreur); ?>

    <form method="POST" action="login.php">
        <div>
            <label for="email">Adresse Email :</label>
            <input 
                type="email" 
                id="email" 
                name="email" 
                value="<?= htmlspecialchars($email ?? '') ?>" 
                required
            >

        </div>

        <br>

        <div>
            <label for="mdp">Mot de passe :</label>
            <input 
                type="password" 
                id="mdp" 
                name="mdp"
                value="<?= htmlspecialchars($mdp ?? '') ?>"
                required
            >
        </div>

        <br>

        <button type="submit" name="valider">Se connecter</button>
    </form>

<?php require_once('filesPHP/footer.php'); ?>