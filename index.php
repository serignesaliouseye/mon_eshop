<?php
// index.php – Page d'accueil SenFood
session_start();
require_once 'config/db.php';

$titre_page = 'Accueil';

// 6 derniers plats ajoutés
$stmt = $pdo->prepare('SELECT * FROM products ORDER BY created_at DESC LIMIT 6');
$stmt->execute();
$produits_vedettes = $stmt->fetchAll();

// header.php ouvre <html>, <head> et <body> : on ne les écrit pas ici
include 'includes/header.php';
?>

<main>

    <!-- ===== HÉRO ===== -->
    <section class="sf-hero">
        <div class="sf-container sf-hero-inner">
            <div class="sf-hero-texte">
                <h1>La cuisine sénégalaise, livrée chez vous à Dakar</h1>
                <p class="sf-lead">
                    Thiéboudienne, yassa, mafé : commandez des plats préparés
                    par des cuisiniers locaux, avec des ingrédients frais du jour.
                </p>
                <div class="sf-hero-actions">
                    <a href="client/catalogue.php" class="btn btn-primary btn-large">Voir le catalogue</a>
                    <a href="#populaires" class="btn btn-ghost btn-large">Plats populaires</a>
                </div>
            </div>

            <div class="sf-hero-visual">
                <img src="assets/images/food.jpg" alt="Plats sénégalais SenFood">
            </div>
        </div>

        <div class="sf-container">
            <div class="sf-facts">
                <p><strong>Moins de 45 minutes</strong>de livraison partout à Dakar</p>
                <p><strong>Ingrédients frais</strong>sélectionnés chaque jour</p>
                <p><strong>Paiement flexible</strong>en ligne ou à la livraison</p>
            </div>
        </div>
    </section>

    <!-- ===== PLATS POPULAIRES ===== -->
    <section class="sf-section" id="populaires">
        <div class="sf-container">
            <div class="sf-section-head">
                <div>
                    <h2>Nos plats populaires</h2>
                    <p>Les plats préférés de nos clients cette semaine.</p>
                </div>
                <a href="client/catalogue.php" class="btn btn-outline">Voir tout le catalogue</a>
            </div>

            <?php if (empty($produits_vedettes)): ?>
                <p class="sf-vide">Aucun plat disponible pour le moment. Revenez bientôt.</p>
            <?php else: ?>
            <div class="sf-grid">
                <?php foreach ($produits_vedettes as $produit): ?>
                <article class="sf-card">
                    <a href="client/produit.php?id=<?= (int) $produit['id'] ?>" class="sf-card-image">
                        <img src="<?= htmlspecialchars($produit['image']) ?>"
                             alt="<?= htmlspecialchars($produit['nom']) ?>"
                             loading="lazy"
                             onerror="this.onerror=null;this.src='assets/images/thiep.jpg'">
                    </a>
                    <div class="sf-card-body">
                        <h3><?= htmlspecialchars($produit['nom']) ?></h3>
                        <?php
                            $desc = $produit['description'] ?? '';
                            $court = mb_strlen($desc) > 80 ? mb_substr($desc, 0, 80) . '…' : $desc;
                        ?>
                        <p class="sf-card-desc"><?= htmlspecialchars($court) ?></p>
                        <div class="sf-card-footer">
                            <span class="sf-prix"><?= number_format($produit['prix'], 0, ',', ' ') ?> FCFA</span>
                            <a href="client/produit.php?id=<?= (int) $produit['id'] ?>"
                               class="btn btn-outline btn-small">Voir le plat</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===== À PROPOS ===== -->
    <section class="sf-section sf-section--tint">
        <div class="sf-container sf-apropos">
            <div class="sf-apropos-image">
                <img src="assets/images/thiep.jpg" alt="Cuisine sénégalaise SenFood" loading="lazy">
            </div>
            <div class="sf-apropos-texte">
                <h2>À propos de SenFood</h2>
                <p>SenFood est une plateforme de commande en ligne dédiée aux plats sénégalais traditionnels. Notre mission : relier les amoureux de la cuisine locale aux meilleurs cuisiniers du Sénégal.</p>
                <p>Du thiéboudienne au yassa, en passant par le mafé, chaque plat est préparé avec des ingrédients frais et locaux.</p>
                <ul class="sf-list">
                    <li>Plats préparés avec des ingrédients frais</li>
                    <li>Livraison rapide à domicile</li>
                    <li>Commande en ligne simple et sécurisée</li>
                    <li>Large choix de plats sénégalais</li>
                </ul>
            </div>
        </div>
    </section>

    <!-- ===== POURQUOI SENFOOD ===== -->
    <section class="sf-section">
        <div class="sf-container">
            <div class="sf-section-head">
                <div>
                    <h2>Pourquoi commander sur SenFood</h2>
                </div>
            </div>
            <div class="sf-why">
                <div>
                    <h3>Recettes authentiques</h3>
                    <p>Des recettes traditionnelles préparées par des cuisiniers locaux.</p>
                </div>
                <div>
                    <h3>Livraison rapide</h3>
                    <p>Votre commande chez vous en moins de 45 minutes, partout à Dakar.</p>
                </div>
                <div>
                    <h3>Paiement sécurisé</h3>
                    <p>Payez en ligne en toute sécurité, ou à la livraison selon votre préférence.</p>
                </div>
                <div>
                    <h3>Qualité contrôlée</h3>
                    <p>Chaque plat est préparé le jour même avec des ingrédients frais.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== APPEL À L'ACTION ===== -->
    <section class="sf-cta">
        <div class="sf-container sf-cta-inner">
            <h2>Une envie de thiéboudienne ? Commandez en quelques minutes.</h2>
            <a href="client/catalogue.php" class="btn btn-light btn-large">Commander maintenant</a>
        </div>
    </section>

</main>

<?php include 'includes/footer.php'; ?>
<script src="assets/js/main.js"></script>
</body>
</html>