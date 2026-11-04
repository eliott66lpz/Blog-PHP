<?php
$msgErreur= null;
require_once ('utils.php');

try {
    $connexion = new PDO('mysql:host=localhost;dbname=blog_php;charset=utf8', 'root', '');
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $connexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Erreur PDO : ' . $e->getMessage());
} catch (Exception $e) {
    die('Erreur Générale : ' . $e->getMessage());
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $mdp = $_POST['mdp'];

    if(empty($email) || empty($mdp)){
        $msgErreur = "Veuillez remplir tout les champs";
    }


    if ($msgErreur === null) {
        $stmt = $connexion->prepare("SELECT * FROM User WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        $stmt->closeCursor();

        if ($user) {
            if (password_verify($mdp, $user['mdp'])) {
                $_SESSION['user'] = [
                    'id'     => (int)$user['id'],
                    'email'  => $user['email'],
                    'pseudo' => $user['pseudo'],
                    'admin'  => (int)$user['admin']
                ];
                header('Location: index.php');
                exit();
            } else {
                $msgErreur = "Mot de passe incorrect.";
            }
        } else {
            $hashMdp = password_hash($mdp, PASSWORD_DEFAULT);
            $pseudoBase = explode('@', $email)[0];
            $isAdmin = ($email === 'admin@localhost.fr' && $mdp === 'admin69IUT') ? 1 : 0;

            $requete = $connexion->prepare(
                "INSERT INTO User (email, mdp, pseudo, admin) VALUES (:email, :mdp, :pseudo, :admin)"
            );
            $requete->execute([
                'email'  => $email,
                'mdp'    => $hashMdp,
                'pseudo' => $pseudoBase,
                'admin'  => $isAdmin
            ]);

            $_SESSION['user'] = [
                'id'     => (int)$connexion->lastInsertId(),
                'email'  => $email,
                'pseudo' => $pseudoBase,
                'admin'  => $isAdmin
            ];

            header('Location: index.php');
            exit();
        }
    }
}
?>

<?php require_once ('header.php'); ?>


    <h2>Connexion</h2>

    <?php showMsgErreur($msgErreur); ?>

    <form method="POST">
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
                required
            >
        </div>

        <br>

        <button type="submit" name="valider">Se connecter</button>
    </form>

<?php require_once ('footer.php'); ?>