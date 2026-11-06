<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'utils.php';
require_once '../database/function_database.php';

if (empty($_SESSION['user']['id'])) {
    header('Location: login.php');
    exit();
}

$currentUser   = $_SESSION['user'];
$currentUserId = (int)$currentUser['id'];
$isAdmin       = (int)($currentUser['admin'] ?? 0) === 1;

$msgErreur = null;


$article_id = isset($_GET['article_id']) ? (int)$_GET['article_id'] : 0;
if ($article_id <= 0) {
    header('Location: index.php');
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comm_id'])) {
    $comm_id_to_delete = (int)$_POST['delete_comm_id'];
    suppComm($comm_id_to_delete, $currentUserId, $isAdmin, $article_id);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_commentaire'])) {
    $description = trim($_POST['description'] ?? '');

    if (!empty($description)) {
        ajoutComm($description, $article_id, $currentUserId);
    } else {
        $msgErreur = "Le commentaire ne peut pas être vide.";
    }
}

$article = recupArticle($article_id);
    if (!$article) {
        die("Cet article n'existe pas.");
    }

    $commentaires = recupCommentaires($article_id);

?>

<?php require_once 'header.php'; ?>

    <style>
        /* Lien retour à l'accueil */
        a[href="index.php"] {
            text-decoration: none;
            font-style: italic;
            color: black;
            font-size: 90%;
        }

        a[href="index.php"]:hover {
            text-decoration: underline;
        }

        /* Titre et contenu de l'article */
        .card-article h1 {
            font-size: 30px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 20px 0 10px 0;
            text-align: left;
            letter-spacing: -0.5px;
        }

        .article-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #555;
            margin-bottom: 16px;
        }

        .meta-separator {
            color: #888;
        }

        .badge-cat {
            background-color: #e5e5e5;
            color: #333;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }

        .article-body {
            font-size: 15px;
            line-height: 1.65;
            color: #2b2b2b;
            margin: 0 0 10px 0;
            text-align: justify;
        }

        /* Champs de saisie */
        textarea {
            border: none;
            border-radius: 5px;
            padding: 8px;
            font-family: inherit;
        }

        /* Bouton principal (Publier) */
        form button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 200px;
            white-space: nowrap;
            border: none;
            border-radius: 20px;
            box-shadow: 3px 3px gray;
            padding: 8px 20px;
            margin: 5px 0;
            background-color: white;
            color: black;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
        }

        form button:hover {
            background-color: rgb(48, 48, 48);
            margin-left: 2px;
            margin-top: 2px;
            box-shadow: none;
            color: white;
        }

        /* Bouton Supprimer (compact et rouge) */
        button.btn-supprimer {
            min-width: 0;
            width: auto;
            padding: 4px 14px;
            font-size: 12px;
            border-radius: 12px;
            background-color: #ffd6d6;
            color: #a80000;
            box-shadow: 2px 2px #b08d8d;
        }

        button.btn-supprimer:hover {
            background-color: #b81414;
            color: white;
            box-shadow: none;
        }

        /* Lignes de séparation */
        hr {
            border: none;
            border-top: 1px solid #c4c0c0;
            margin: 25px 0;
        }
    </style>

    <a href="index.php">← Retour à la liste des articles</a>
    <hr>

    <article class="card-article">
        <h1><?= htmlspecialchars($article['titre'] ?? 'Sans titre') ?></h1>

        <div class="article-meta">
            <span>Par <strong><?= htmlspecialchars((string)($article['pseudo'] ?? 'Inconnu')) ?></strong></span>
            <span class="meta-separator">•</span>
            <span class="badge-cat"><?= htmlspecialchars((string)($article['categorie'] ?? 'Général')) ?></span>
        </div>

        <p class="article-body">
            <?= nl2br(htmlspecialchars($article['description'] ?? '')) ?>
        </p>
    </article>

    <hr class="article-separator">

    <hr>

    <h2>Ajouter un commentaire</h2>

<?php showMsgErreur($msgErreur); ?>

    <form method="POST" action="comm_article.php?article_id=<?= $article_id ?>">
        <label for="desc_comm">Votre message :</label><br>
        <textarea name="description" id="desc_comm" rows="4" cols="50" required></textarea><br><br>
        <button type="submit" name="ajouter_commentaire">Publier le commentaire</button>
    </form>

    <hr>

    <h2>Espace Commentaires (<?= count($commentaires) ?>)</h2>

<?php if (empty($commentaires)): ?>
    <p>Soyez le premier à commenter cet article !</p>
<?php else: ?>
    <ul>
        <?php foreach ($commentaires as $com): ?>
            <li style="margin-bottom: 15px;">
                <strong><?= htmlspecialchars((string)($com['pseudo'] ?? 'Inconnu')) ?> :</strong><br><br>
                <?= nl2br(htmlspecialchars($com['description'] ?? '')) ?><br><br>

                <?php if ($isAdmin || $currentUserId === (int)$com['user_id']): ?>
                    <form method="POST">
                        <input type="hidden" name="delete_comm_id" value="<?= (int)$com['id'] ?>">
                        <button type="submit" class="btn-supprimer" onclick="return confirm('Supprimer ce commentaire ?')">Supprimer</button>
                    </form>
                    <br>
                <?php endif; ?>
                <hr>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require_once 'footer.php'; ?>