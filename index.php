<?php
// index.php – Page d'accueil SenFood
session_start();
require_once 'config/db.php';

$titre_page = 'Accueil';

// Récupérer 6 produits vedettes
$stmt = $pdo->prepare('SELECT * FROM products ORDER BY created_at DESC LIMIT 6');
$stmt->execute();
$produits_vedettes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SenFood – Plats sénégalais locaux</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include 'includes/header.php'; ?>

<main>

    <!-- ===== SECTION HÉRO ===== -->
    <section class="hero">
        <div class="hero-content">
            <h1 class="hero-titre">Savourez le meilleur de la cuisine sénégalaise</h1>
            <p class="hero-sous-titre">Thiéboudienne, Yassa, Mafé… Commandez vos plats préférés, préparés avec amour et livrés chez vous.</p>
            <a href="client/catalogue.php" class="btn btn-primary btn-large">
                Voir le catalogue 🍽️
            </a>
        </div>
        <div class="hero-image">
            <img src="assets/images/food.jpg" alt="Plats sénégalais SenFood">
        </div>
    </section>

    <!-- ===== PRODUITS VEDETTES ===== -->
    <section class="section-vedettes">
        <div class="container">
            <h2 class="section-titre">Nos plats populaires</h2>
            <p class="section-sous-titre">Découvrez nos plats les plus appréciés par nos clients</p>

            <div class="produits-grid" id="produits-grid">
                <?php foreach ($produits_vedettes as $produit): ?>
                <div class="produit-card" data-aos>
                    <div class="produit-image">
                        <img src="<?= htmlspecialchars($produit['image']) ?>"
                             alt="<?= htmlspecialchars($produit['nom']) ?>"
                             onerror="this.src='assets/images/thiep.jpg'">
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
                            <a href="client/produit.php?id=<?= $produit['id'] ?>"
                               class="btn btn-outline btn-small">Voir le plat</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="section-cta">
                <a href="client/catalogue.php" class="btn btn-primary">
                    Voir tous les plats →
                </a>
            </div>
        </div>
    </section>

    <!-- ===== SECTION À PROPOS ===== -->
    <section class="section-apropos">
        <div class="container apropos-inner">
            <div class="apropos-texte" data-aos>
                <h2>À propos de SenFood</h2>
                <p>SenFood est une plateforme de commande en ligne dédiée aux plats sénégalais traditionnels. Notre mission est de connecter les amoureux de la cuisine locale avec les meilleurs cuisiniers du Sénégal.</p>
                <p>De la Thiéboudienne au Yassa en passant par le Mafé, nous proposons des plats authentiques préparés avec des ingrédients frais et locaux.</p>
                <ul class="apropos-liste">
                    <li>✅ Plats préparés avec des ingrédients frais</li>
                    <li>✅ Livraison rapide à domicile</li>
                    <li>✅ Commande en ligne facile et sécurisée</li>
                    <li>✅ Large choix de plats sénégalais</li>
                </ul>
            </div>
            <div class="apropos-image" data-aos>
                <img src="assets/images/thiep.jpg" alt="Cuisine sénégalaise SenFood">
            </div>
        </div>
    </section>

    <!-- ===== SECTION POURQUOI NOUS ===== -->
    <section class="section-avantages">
        <div class="container">
            <h2 class="section-titre">Pourquoi choisir SenFood ?</h2>
            <div class="avantages-grid">
                <div class="avantage-card" data-aos>
                    <span class="avantage-icon">🍛</span>
                    <h3>Plats authentiques</h3>
                    <p>Des recettes traditionnelles sénégalaises préparées par des cuisiniers locaux.</p>
                </div>
                <div class="avantage-card" data-aos>
                    <span class="avantage-icon">🚀</span>
                    <h3>Livraison rapide</h3>
                    <p>Votre commande livrée en moins de 45 minutes partout à Dakar.</p>
                </div>
                <div class="avantage-card" data-aos>
                    <span class="avantage-icon">💳</span>
                    <h3>Paiement sécurisé</h3>
                    <p>Paiement en ligne sécurisé ou à la livraison selon votre préférence.</p>
                </div>
                <div class="avantage-card" data-aos>
                    <span class="avantage-icon">⭐</span>
                    <h3>Qualité garantie</h3>
                    <p>Tous nos plats sont contrôlés et préparés avec des ingrédients frais du jour.</p>
                </div>
            </div>
        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>