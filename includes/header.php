<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calcul du nombre d'articles dans le panier
$nb_panier = 0;
if (isset($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $nb_panier += $item['quantite'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($titre_page) ? $titre_page . ' – SenFood' : 'SenFood – Plats sénégalais locaux' ?></title>
    <link rel="stylesheet" href="/senfood/assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<header class="site-header">
    <div class="container header-inner">

        <!-- Logo -->
        <a href="/senfood/index.php" class="logo">
            🍛 <span>Sen</span>Food
        </a>

        <!-- Navigation desktop -->
        <nav class="nav-desktop" id="nav-menu">
            <a href="/senfood/index.php">Accueil</a>
            <a href="/senfood/client/catalogue.php">Catalogue</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="/senfood/client/panier.php">
                    🛒 Panier
                    <?php if ($nb_panier > 0): ?>
                        <span class="badge-panier" id="compteur-panier"><?= $nb_panier ?></span>
                    <?php endif; ?>
                </a>
                <a href="/senfood/client/mes_commandes.php">Mes commandes</a>
                <?php if ($_SESSION['is_admin'] == 1): ?>
                    <a href="/senfood/admin/dashboard.php" class="btn-admin">Admin</a>
                <?php endif; ?>
                <a href="/senfood/auth/logout.php" class="btn btn-outline">
                    Déconnexion (<?= htmlspecialchars($_SESSION['user_nom']) ?>)
                </a>
            <?php else: ?>
                <a href="/senfood/auth/connexion.php" class="btn btn-outline">Connexion</a>
                <a href="/senfood/auth/inscription.php" class="btn btn-primary">S'inscrire</a>
            <?php endif; ?>
        </nav>

        <!-- Bouton hamburger mobile -->
        <button class="hamburger" id="hamburger" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>
</header>