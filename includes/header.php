<?php
// includes/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Nombre d'articles dans le panier
$nb_panier = 0;
if (!empty($_SESSION['panier'])) {
    foreach ($_SESSION['panier'] as $item) {
        $nb_panier += $item['quantite'];
    }
}

// Page courante, pour mettre le lien actif en évidence
$page_courante = $_SERVER['SCRIPT_NAME'] ?? '';
if (!function_exists('lien_actif')) {
    function lien_actif(string $chemin): string {
        global $page_courante;
        return $page_courante === $chemin ? ' aria-current="page"' : '';
    }
}

$connecte  = isset($_SESSION['user_id']);
$est_admin = $connecte && !empty($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1;
$nom_user  = $connecte ? trim($_SESSION['user_nom'] ?? '') : '';
$prenom    = $nom_user !== '' ? explode(' ', $nom_user)[0] : '';
$initiale  = $nom_user !== '' ? mb_strtoupper(mb_substr($nom_user, 0, 1)) : '?';

// La barre d'annonce n'apparaît pas dans l'espace admin
$afficher_annonce = strpos($page_courante, '/admin/') !== 0;

$titre_complet = isset($titre_page)
    ? htmlspecialchars($titre_page) . ' – SenFood'
    : 'SenFood – Plats sénégalais locaux';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $titre_complet ?></title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:wght@500;600;700&family=Figtree:wght@400;500;600&display=swap" rel="stylesheet">
    <?= $extra_head ?? '' ?>
</head>
<body>

<?php if ($afficher_annonce): ?>
<div class="topbar">
    <div class="container topbar-inner">
        <span><strong>Livraison en moins de 45 min</strong> partout à Saint louis</span>
        <span class="topbar-droite">Paiement en ligne ou à la livraison</span>
    </div>
</div>
<?php endif; ?>

<header class="site-header" id="site-header">
    <div class="container header-inner">

        <a href="/index.php" class="logo" aria-label="SenFood, accueil">
            <span class="logo-mark" aria-hidden="true">S</span>
            <span class="logo-text">Sen<span>Food</span></span>
        </a>

        <!-- Navigation principale (devient un panneau sur mobile) -->
        <nav class="nav-main" id="nav-menu" aria-label="Navigation principale">
            <a href="/index.php"<?= lien_actif('/index.php') ?>>Accueil</a>
            <a href="/client/catalogue.php"<?= lien_actif('/client/catalogue.php') ?>>Catalogue</a>

            <?php if ($connecte): ?>
                <a href="/client/mes_commandes.php"<?= lien_actif('/client/mes_commandes.php') ?>>Mes commandes</a>

                <!-- Liens réservés au panneau mobile -->
                <?php if ($est_admin): ?>
                    <a href="/admin/dashboard.php" class="nav-mobile-only">Administration</a>
                <?php endif; ?>
                <span class="nav-sep nav-mobile-only"></span>
                <a href="/auth/logout.php" class="nav-mobile-only nav-deconnexion">Déconnexion (<?= htmlspecialchars($prenom) ?>)</a>
            <?php else: ?>
                <span class="nav-sep nav-mobile-only"></span>
                <a href="/auth/connexion.php" class="btn btn-outline nav-mobile-only">Connexion</a>
                <a href="/auth/inscription.php" class="btn btn-primary nav-mobile-only">S'inscrire</a>
            <?php endif; ?>
        </nav>

        <div class="header-actions">

            <?php if ($connecte): ?>
                <a href="/client/panier.php" class="header-icone"
                   aria-label="Panier<?= $nb_panier > 0 ? ' (' . $nb_panier . ' article' . ($nb_panier > 1 ? 's' : '') . ')' : '' ?>"<?= lien_actif('/client/panier.php') ?>>
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <circle cx="9" cy="20" r="1.5"/><circle cx="18" cy="20" r="1.5"/>
                        <path d="M2 3h3l2.7 12.4a2 2 0 0 0 2 1.6h8.1a2 2 0 0 0 2-1.5L22 7H6"/>
                    </svg>
                    <?php if ($nb_panier > 0): ?>
                        <span class="badge-panier" id="compteur-panier"><?= $nb_panier ?></span>
                    <?php endif; ?>
                </a>

                <!-- Menu du compte (ordinateur) -->
                <div class="compte">
                    <button type="button" class="compte-btn" id="compte-btn" aria-haspopup="true" aria-expanded="false" aria-controls="compte-menu">
                        <span class="avatar" aria-hidden="true"><?= htmlspecialchars($initiale) ?></span>
                        <span class="compte-nom"><?= htmlspecialchars($prenom) ?></span>
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>
                    <div class="compte-menu" id="compte-menu" hidden>
                        <div class="compte-entete">
                            <span>Connecté en tant que</span>
                            <strong><?= htmlspecialchars($nom_user) ?></strong>
                        </div>
                        <a href="/client/mes_commandes.php">Mes commandes</a>
                        <a href="/client/panier.php">Mon panier</a>
                        <?php if ($est_admin): ?>
                            <a href="/admin/dashboard.php">Administration</a>
                        <?php endif; ?>
                        <hr>
                        <a href="/auth/logout.php" class="compte-danger">Déconnexion</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/auth/connexion.php" class="lien-connexion">Connexion</a>
                <a href="/auth/inscription.php" class="btn btn-primary btn-small btn-inscription">S'inscrire</a>
            <?php endif; ?>

            <button type="button" class="hamburger" id="hamburger" aria-label="Ouvrir le menu" aria-controls="nav-menu" aria-expanded="false">
                <span></span>
                <span></span>
                <span></span>
            </button>
        </div>

    </div>
</header>

<script>
// Comportement du header : menu mobile, menu du compte, ombre au défilement
(function () {
    var entete = document.getElementById('site-header');
    var burger = document.getElementById('hamburger');
    var nav    = document.getElementById('nav-menu');
    var cBtn   = document.getElementById('compte-btn');
    var cMenu  = document.getElementById('compte-menu');

    function fermerMobile() {
        if (!nav || !burger) return;
        nav.classList.remove('open');
        burger.classList.remove('active');
        burger.setAttribute('aria-expanded', 'false');
        burger.setAttribute('aria-label', 'Ouvrir le menu');
    }
    function fermerCompte() {
        if (!cMenu || !cBtn) return;
        cMenu.hidden = true;
        cBtn.setAttribute('aria-expanded', 'false');
    }

    if (burger && nav) {
        // stopImmediatePropagation : évite que main.js bascule le menu une seconde fois
        burger.addEventListener('click', function (e) {
            e.stopImmediatePropagation();
            var ouvert = nav.classList.toggle('open');
            burger.classList.toggle('active', ouvert);
            burger.setAttribute('aria-expanded', ouvert ? 'true' : 'false');
            burger.setAttribute('aria-label', ouvert ? 'Fermer le menu' : 'Ouvrir le menu');
        });
    }

    if (cBtn && cMenu) {
        cBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var ouvrir = cMenu.hidden;
            cMenu.hidden = !ouvrir;
            cBtn.setAttribute('aria-expanded', ouvrir ? 'true' : 'false');
        });
    }

    document.addEventListener('click', function (e) {
        if (cMenu && !cMenu.contains(e.target)) fermerCompte();
        if (nav && burger && !nav.contains(e.target) && !burger.contains(e.target)) fermerMobile();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') { fermerCompte(); fermerMobile(); }
    });

    function surDefilement() { entete.classList.toggle('scrolled', window.scrollY > 8); }
    window.addEventListener('scroll', surDefilement, { passive: true });
    surDefilement();
})();
</script>