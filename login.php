<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$msgErreur = null;
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

    <style>
        /* Zone centrée verticalement et horizontalement */
        .login-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 150px
            width: 100%;
        }

        .login-wrapper h2 {
            text-align: center;
            margin: 0 0 20px 0;
            font-size: 28px;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
            width: 100%;
            max-width: 320px;
        }

        .champ-ligne {
            display: flex;
            flex-direction: column;
            width: 100%;
            gap: 5px;
        }

        .champ-ligne label {
            font-weight: 600;
            font-size: 14px;
        }

        .champ-ligne input {
            border: none;
            border-radius: 8px;
            height: 36px;
            padding: 0 12px;
            font-size: 14px;
            width: 100%;
            box-sizing: border-box;
        }

        .login-form button {
            width: 100%;
            height: 38px;
            border: none;
            border-radius: 20px;
            box-shadow: 3px 3px gray;
            background-color: white;
            color: black;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
            margin-top: 10px;
        }

        .login-form button:hover {
            background-color: rgb(48, 48, 48);
            color: white;
            box-shadow: none;
            margin-left: 2px;
            margin-top: 12px;
        }
    </style>

    <div class="login-wrapper">
        <h2>Connexion</h2>

        <!-- L'erreur est maintenant affichée proprement au centre -->
        <?php showMsgErreur($msgErreur); ?>

        <form method="POST" action="login.php" class="login-form">
            <div class="champ-ligne">
                <label for="email">Adresse Email :</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="champ-ligne">
                <label for="mdp">Mot de passe :</label>
                <input type="password" id="mdp" name="mdp" required>
            </div>

            <button type="submit">Se connecter</button>
        </form>
    </div>

<?php require_once('filesPHP/footer.php'); ?>