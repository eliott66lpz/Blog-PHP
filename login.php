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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $mdp = $_POST['mdp'];

    if (!empty($email) && !empty($mdp)) {
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
                $erreur = "Mot de passe incorrect.";
            }
        } else {
            $hashMdp = password_hash($mdp, PASSWORD_DEFAULT);
            $pseudoBase = explode('@', $email)[0];
            $isAdmin = ($email === 'admin@localhost.fr') ? 1 : 0;

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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - TP PHP</title>
</head>

<body>

    <h2>Connexion</h2>

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

</body>
</html>