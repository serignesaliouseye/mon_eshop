<?php
// client/panier.php
session_start();
require_once '../config/db.php';

// Initialiser le panier si vide
if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$action = $_GET['action'] ?? '';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$qte    = isset($_GET['quantite']) ? max(1, (int)$_GET['quantite']) : 1;

// ── Ajouter au panier ──
if ($action === 'ajouter' && $id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $produit = $stmt->fetch();

    if ($produit) {
        if (isset($_SESSION['panier'][$id])) {
            $_SESSION['panier'][$id]['quantite'] += $qte;
        } else {
            $_SESSION['panier'][$id] = [
                'id'       => $produit['id'],
                'nom'      => $produit['nom'],
                'prix'     => $produit['prix'],
                'image'    => $produit['image'],
                'quantite' => $qte
            ];
        }
    }
    header('Location: panier.php');
    exit;
}

// ── Supprimer un article ──
if ($action === 'supprimer' && $id > 0) {
    unset($_SESSION['panier'][$id]);
    header('Location: panier.php');
    exit;
}

// ── Vider le panier ──
if ($action === 'vider') {
    $_SESSION['panier'] = [];
    header('Location: panier.php');
    exit;
}

// ── Modifier la quantité ──
if ($action === 'modifier' && $id > 0) {
    $nouvelle_qte = isset($_POST['quantite']) ? (int)$_POST['quantite'] : 1;
    if ($nouvelle_qte > 0 && isset($_SESSION['panier'][$id])) {
        $_SESSION['panier'][$id]['quantite'] = $nouvelle_qte;
    }
    header('Location: panier.php');
    exit;
}

// Calcul du total
$total = 0;
foreach ($_SESSION['panier'] as $item) {
    $total += $item['prix'] * $item['quantite'];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Panier – SenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container panier-page">
    <h1 class="page-titre">🛒 Mon Panier</h1>

    <?php if (empty($_SESSION['panier'])): ?>
        <div class="panier-vide">
            <p>Votre panier est vide.</p>
            <a href="catalogue.php" class="btn btn-primary">Voir le catalogue</a>
        </div>
    <?php else: ?>

        <div class="panier-layout">

            <!-- Liste des articles -->
            <div class="panier-articles">
                <?php foreach ($_SESSION['panier'] as $item): ?>
                <div class="panier-item">
                    <img src="../<?= htmlspecialchars($item['image']) ?>"
                         alt="<?= htmlspecialchars($item['nom']) ?>"
                         onerror="this.src='../assets/images/default.jpg'">
                    <div class="panier-item-info">
                        <h3><?= htmlspecialchars($item['nom']) ?></h3>
                        <p class="panier-item-prix">
                            <?= number_format($item['prix'], 0, ',', ' ') ?> FCFA
                        </p>
                    </div>
                    <div class="panier-item-qte">
                        <form method="POST" action="panier.php?action=modifier&id=<?= $item['id'] ?>">
                            <input type="number" name="quantite"
                                   value="<?= $item['quantite'] ?>" min="1" max="10"
                                   onchange="this.form.submit()">
                        </form>
                    </div>
                    <div class="panier-item-total">
                        <?= number_format($item['prix'] * $item['quantite'], 0, ',', ' ') ?> FCFA
                    </div>
                    <a href="panier.php?action=supprimer&id=<?= $item['id'] ?>"
                       class="btn-supprimer"
                       onclick="return confirmerSuppressionPanier(event, this.href)">✕</a>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Récapitulatif -->
            <div class="panier-recap">
                <h2>Récapitulatif</h2>
                <div class="recap-ligne">
                    <span>Sous-total</span>
                    <span><?= number_format($total, 0, ',', ' ') ?> FCFA</span>
                </div>
                <div class="recap-ligne">
                    <span>Livraison</span>
                    <span>Gratuite</span>
                </div>
                <div class="recap-ligne recap-total">
                    <span>Total</span>
                    <span><?= number_format($total, 0, ',', ' ') ?> FCFA</span>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="commande.php" class="btn btn-primary btn-full">
                        Valider la commande →
                    </a>
                <?php else: ?>
                    <a href="../auth/connexion.php" class="btn btn-primary btn-full">
                        Se connecter pour commander
                    </a>
                <?php endif; ?>

                <a href="panier.php?action=vider" class="btn btn-outline btn-full"
                   style="margin-top:0.8rem"
                   onclick="return confirmerVidePanier(event, this.href)">
                    Vider le panier
                </a>
            </div>

        </div>

    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
<script>
function confirmerSuppressionPanier(e, href) {
    e.preventDefault();
    confirmerSuppression('Retirer ce plat du panier ?', () => {
        window.location.href = href;
    });
    return false;
}
function confirmerVidePanier(e, href) {
    e.preventDefault();
    confirmerSuppression('Vider complètement votre panier ?', () => {
        window.location.href = href;
    });
    return false;
}
</script>
</body>
</html>