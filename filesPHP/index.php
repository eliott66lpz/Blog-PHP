<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'utils.php';
require_once '../database/function_database.php';

if (empty($_SESSION['user']['id'])) {
    header('Location: ../login.php');
    exit();
}

$currentUser = $_SESSION['user'];
$currentUserId = (int)$currentUser['id'];
$isAdmin = (int)($currentUser['admin'] ?? 0) === 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_article_id'])) {
    $article_id_to_delete = (int)$_POST['delete_article_id'];
    suppArticle($article_id_to_delete, $currentUserId, $isAdmin);
}

$pseudo_filtre = trim($_GET['pseudo_f'] ?? '');
    $articles = filtrePseudo($pseudo_filtre);
?>

<?php require_once 'header.php'; ?>

    <style>
        /* Grands boutons d'action (Ajouter un article & Accéder au CRUD) */
        h1+a,
        .btn-grand {
            background-color: white;
            padding: 12px;
            margin: 10px 32%;
            font-style: italic;
            color: black;
            text-decoration: none;
            border-radius: 15px;
            display: flex;
            justify-content: center;
            font-size: 130%;
            box-sizing: border-box;
        }

        h1+a:hover,
        .btn-grand:hover {
            color: green;
            font-weight: bold;
            text-decoration: underline;
        }

        /* Barre de recherche et filtrage */
        .form-filtre {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin: 25px auto;
            width: 100%;
        }

        .form-filtre label {
            font-weight: 600;
            font-size: 15px;
            color: #222;
        }

        .form-filtre input[type="text"] {
            border: none;
            border-radius: 5px;
            height: 28px;
            padding: 2px 10px;
        }

        .form-filtre a {
            font-size: 13px;
            color: #555;
            text-decoration: underline;
        }

        .form-filtre a:hover {
            color: #000;
        }

        /* Bouton Valider du filtre et lien des commentaires */
        .form-filtre button,
        a.btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            white-space: nowrap;
            border: none;
            border-radius: 20px;
            box-shadow: 3px 3px gray;
            padding: 6px 18px;
            background-color: white;
            color: black;
            text-decoration: none;
            box-sizing: border-box;
            cursor: pointer;
            font-family: inherit;
            font-size: 14px;
        }

        .form-filtre button:hover,
        a.btn-action:hover {
            background-color: rgb(48, 48, 48);
            margin-left: 2px;
            margin-top: 2px;
            box-shadow: none;
            color: white;
        }

        /* Bouton Supprimer (format compact) */
        button.btn-supprimer {
            border: none;
            border-radius: 12px;
            padding: 4px 14px;
            font-size: 12px;
            background-color: #ffd6d6;
            color: #a80000;
            box-shadow: 2px 2px #b08d8d;
            cursor: pointer;
            font-family: inherit;
        }

        button.btn-supprimer:hover {
            background-color: #b81414;
            color: white;
            box-shadow: none;
            margin-left: 2px;
            margin-top: 2px;
        }

        /* Typographie et cartes d'articles */
        .card-article h2 {
            font-size: 26px;
            font-weight: 700;
            color: #1a1a1a;
            margin: 15px 0 8px 0;
            text-align: left;
            letter-spacing: -0.3px;
        }

        .article-meta {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            color: #555;
            margin-bottom: 14px;
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
            margin: 0 0 18px 0;
            text-align: justify;
        }

        .article-actions {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 12px;
            margin-bottom: 10px;
        }

        /* Lignes de séparation */
        hr,
        hr.article-separator {
            border: none;
            border-top: 1px solid #c4c0c0;
            margin: 25px 0;
        }
    </style>

    <h1>Liste des Articles</h1>

    <a href="p_creation.php">Ajouter un article</a>

<?php if ($isAdmin): ?>
    <a href="../admin/crud.php" class="btn-grand">Accéder au CRUD</a>
<?php endif; ?>

    <form method="GET" action="index.php" class="form-filtre">
            <label for="pseudo_f">Filtrer par pseudo :</label>
            <input type="text" id="pseudo_f" name="pseudo_f" value="<?= htmlspecialchars($pseudo_filtre) ?>">
            <button type="submit">Valider</button>
            <?php if (!empty($pseudo_filtre)): ?>
                <a href="index.php">Réinitialiser</a>
            <?php endif; ?>
    </form>

    <hr>

<?php if (empty($articles)): ?>
    <p>Aucun article disponible</p>
<?php else: ?>
    <?php foreach ($articles as $article): ?>
        <article class="card-article">
            <h2><?= htmlspecialchars($article['titre'] ?? 'Sans titre') ?></h2>

            <div class="article-meta">
                <span>Par <strong><?= htmlspecialchars((string)($article['pseudo'] ?? 'Inconnu')) ?></strong></span>
                <span class="meta-separator"> | </span>
                <span class="badge-cat"><?= htmlspecialchars((string)($article['categorie'] ?? 'Général')) ?></span>
            </div>

            <p class="article-body">
                <?= nl2br(htmlspecialchars($article['description'] ?? '')) ?>
            </p>

            <div class="article-actions">
                <a href="comm_article.php?article_id=<?= (int)$article['id'] ?>" class="btn-action">
                    Voir les commentaires de cet article
                </a>

                <?php if ($isAdmin || $currentUserId === (int)$article['user_id']): ?>
                    <form method="POST" action="index.php" style="display:inline;">
                        <input type="hidden" name="delete_article_id" value="<?= (int)$article['id'] ?>">
                        <button type="submit" class="btn-supprimer" onclick="return confirm('Supprimer cet article ?')">Supprimer</button>
                    </form>
                <?php endif; ?>
            </div>
        </article>
        <hr class="article-separator">
    <?php endforeach; ?>
<?php endif; ?>

<?php require_once 'footer.php'; ?>