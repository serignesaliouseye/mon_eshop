<?php
// client/produit.php
session_start();
require_once '../config/db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: catalogue.php');
    exit;
}

$stmt = $pdo->prepare('SELECT p.*, c.nom AS categorie_nom FROM products p LEFT JOIN categories c ON p.categorie_id = c.id WHERE p.id = :id');
$stmt->execute([':id' => $id]);
$produit = $stmt->fetch();

if (!$produit) {
    header('Location: catalogue.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($produit['nom']) ?> – SenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container produit-detail-page">

    <a href="catalogue.php" class="btn-retour">← Retour au catalogue</a>

    <div class="produit-detail">
        <div class="produit-detail-image">
            <img src="../<?= htmlspecialchars($produit['image']) ?>"
                 alt="<?= htmlspecialchars($produit['nom']) ?>"
                 onerror="this.src='../assets/images/default.jpg'">
        </div>
        <div class="produit-detail-info">
            <span class="produit-badge"><?= htmlspecialchars($produit['categorie_nom']) ?></span>
            <h1><?= htmlspecialchars($produit['nom']) ?></h1>
            <p class="produit-detail-prix">
                <?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA
            </p>
            <p class="produit-detail-desc"><?= htmlspecialchars($produit['description']) ?></p>

            <div class="produit-detail-actions">
                <div class="quantite-selector">
                    <label for="quantite">Quantité :</label>
                    <div class="quantite-controls">
                        <button type="button" id="btn-moins">−</button>
                        <input type="number" id="quantite" value="1" min="1" max="10">
                        <button type="button" id="btn-plus">+</button>
                    </div>
                </div>
                <a href="panier.php?action=ajouter&id=<?= $produit['id'] ?>"
                   class="btn btn-primary btn-large" id="btn-ajouter-panier">
                    🛒 Ajouter au panier
                </a>
            </div>
        </div>
    </div>

</main>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
<script>
    // Gestion quantité
    const input   = document.getElementById('quantite');
    const btnPlus = document.getElementById('btn-plus');
    const btnMoins= document.getElementById('btn-moins');
    const btnAjout= document.getElementById('btn-ajouter-panier');

    btnPlus.addEventListener('click', () => {
        if (input.value < 10) input.value++;
        majLien();
    });
    btnMoins.addEventListener('click', () => {
        if (input.value > 1) input.value--;
        majLien();
    });
    function majLien() {
        btnAjout.href = `panier.php?action=ajouter&id=<?= $produit['id'] ?>&quantite=${input.value}`;
    }
</script>
</body>
</html>