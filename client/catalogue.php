<?php
// client/catalogue.php
session_start();
require_once '../config/db.php';

$titre_page = 'Catalogue';

// Récupérer toutes les catégories
$categories = $pdo->query('SELECT * FROM categories')->fetchAll();

// Filtres
$categorie_id = isset($_GET['categorie']) ? (int)$_GET['categorie'] : 0;
$recherche    = isset($_GET['q']) ? trim($_GET['q']) : '';
$prix_max     = isset($_GET['prix_max']) ? (int)$_GET['prix_max'] : 0;

// Construction de la requête dynamique
$sql    = 'SELECT p.*, c.nom AS categorie_nom FROM products p LEFT JOIN categories c ON p.categorie_id = c.id WHERE 1=1';
$params = [];

if ($categorie_id > 0) {
    $sql .= ' AND p.categorie_id = :categorie_id';
    $params[':categorie_id'] = $categorie_id;
}
if (!empty($recherche)) {
    $sql .= ' AND (p.nom LIKE :q OR p.description LIKE :q2)';
    $params[':q']  = '%' . $recherche . '%';
    $params[':q2'] = '%' . $recherche . '%';
}
if ($prix_max > 0) {
    $sql .= ' AND p.prix <= :prix_max';
    $params[':prix_max'] = $prix_max;
}

$sql .= ' ORDER BY p.created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produits = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catalogue – SenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="catalogue-page">
    <div class="container">

        <h1 class="page-titre">Nos plats sénégalais</h1>

        <!-- Barre de recherche et filtres -->
        <div class="filtres-bar">
            <input type="text" id="recherche-input" placeholder="🔍 Rechercher un plat..."
                   value="<?= htmlspecialchars($recherche) ?>">

            <select id="filtre-categorie">
                <option value="0">Toutes les catégories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>"
                        <?= $categorie_id == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="filtre-prix">
                <option value="0">Tous les prix</option>
                <option value="1000" <?= $prix_max == 1000 ? 'selected' : '' ?>>Moins de 1 000 FCFA</option>
                <option value="2000" <?= $prix_max == 2000 ? 'selected' : '' ?>>Moins de 2 000 FCFA</option>
                <option value="3000" <?= $prix_max == 3000 ? 'selected' : '' ?>>Moins de 3 000 FCFA</option>
                <option value="5000" <?= $prix_max == 5000 ? 'selected' : '' ?>>Moins de 5 000 FCFA</option>
            </select>
        </div>

        <!-- Grille des produits -->
        <div class="produits-grid" id="catalogue-grid">
            <?php if (empty($produits)): ?>
                <p class="aucun-resultat">Aucun plat trouvé. Essayez un autre filtre.</p>
            <?php else: ?>
                <?php foreach ($produits as $produit): ?>
                <div class="produit-card"
                     data-nom="<?= strtolower(htmlspecialchars($produit['nom'])) ?>"
                     data-categorie="<?= $produit['categorie_id'] ?>"
                     data-prix="<?= $produit['prix'] ?>">
                    <div class="produit-image">
                        <img src="../<?= htmlspecialchars($produit['image']) ?>"
                             alt="<?= htmlspecialchars($produit['nom']) ?>"
                             onerror="this.src='../assets/images/default.jpg'">
                        <span class="produit-badge"><?= htmlspecialchars($produit['categorie_nom']) ?></span>
                    </div>
                    <div class="produit-info">
                        <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                        <p class="produit-description">
                            <?= htmlspecialchars(substr($produit['description'], 0, 80)) ?>...
                        </p>
                        <div class="produit-footer">
                            <span class="produit-prix">
                                <?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA
                            </span>
                            <div class="produit-actions">
                                <a href="produit.php?id=<?= $produit['id'] ?>"
                                   class="btn btn-outline btn-small">Détails</a>
                                <a href="panier.php?action=ajouter&id=<?= $produit['id'] ?>"
                                   class="btn btn-primary btn-small">+ Panier</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </div>
</main>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
<script src="../assets/js/catalogue.js"></script>
</body>
</html>