<?php
// admin/dashboard.php
session_start();
require_once '../config/db.php';
require_once '../includes/admin_nav.php';

// Protection admin
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../auth/connexion.php');
    exit;
}

// Statistiques
$nb_produits  = (int) $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$nb_commandes = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$nb_clients   = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE is_admin = 0')->fetchColumn();

// Chiffre d'affaires : prix x quantité (si la colonne "quantite" n'existe pas, on retombe sur l'ancien calcul)
try {
    $ca_total = $pdo->query('SELECT SUM(prix_unitaire * quantite) FROM order_details')->fetchColumn();
} catch (PDOException $e) {
    $ca_total = $pdo->query('SELECT SUM(prix_unitaire) FROM order_details')->fetchColumn();
}

// 5 dernières commandes
$dernieres = $pdo->query(
    'SELECT o.*, u.nom AS client_nom
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC LIMIT 5'
)->fetchAll();

$titre_page = 'Dashboard admin';
include '../includes/header.php';
?>

<div class="admin-layout">

    <?php sidebar_admin('dashboard'); ?>

    <!-- Contenu principal -->
    <main class="admin-main">

        <div class="admin-header-row">
            <div>
                <h1>Tableau de bord</h1>
                <p class="admin-bienvenue">Bonjour <?= htmlspecialchars($_SESSION['user_nom']) ?>, voici l'activité de SenFood.</p>
            </div>
            <div class="acces-rapides">
                <a href="produits.php?action=ajouter" class="btn btn-primary btn-small">+ Ajouter un plat</a>
                <a href="commandes.php" class="btn btn-outline btn-small">Voir les commandes</a>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card stat-orange">
                <span class="stat-icon"><?= icone('plat') ?></span>
                <div>
                    <p class="stat-nombre"><?= $nb_produits ?></p>
                    <p class="stat-label">Plats au catalogue</p>
                </div>
            </div>
            <div class="stat-card stat-blue">
                <span class="stat-icon"><?= icone('commande') ?></span>
                <div>
                    <p class="stat-nombre"><?= $nb_commandes ?></p>
                    <p class="stat-label">Commandes au total</p>
                </div>
            </div>
            <div class="stat-card stat-green">
                <span class="stat-icon"><?= icone('clients') ?></span>
                <div>
                    <p class="stat-nombre"><?= $nb_clients ?></p>
                    <p class="stat-label">Clients inscrits</p>
                </div>
            </div>
            <div class="stat-card stat-purple">
                <span class="stat-icon"><?= icone('argent') ?></span>
                <div>
                    <p class="stat-nombre"><?= number_format((float) ($ca_total ?? 0), 0, ',', ' ') ?></p>
                    <p class="stat-label">Chiffre d'affaires (FCFA)</p>
                </div>
            </div>
        </div>

        <!-- Dernières commandes -->
        <div class="admin-section-head">
            <h2>Dernières commandes</h2>
            <a href="commandes.php">Tout voir</a>
        </div>

        <div class="admin-card">
            <?php if (empty($dernieres)): ?>
                <p class="admin-vide">Aucune commande pour le moment.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table-admin">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Client</th>
                            <th>Adresse</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dernieres as $cmd):
                            $adresse = $cmd['adresse'] ?? '';
                            $adresse_courte = mb_strlen($adresse) > 40 ? mb_substr($adresse, 0, 40) . '…' : $adresse;
                            $classe_statut = mb_strtolower(str_replace([' ', 'é'], ['-', 'e'], $cmd['statut']));
                        ?>
                        <tr>
                            <td><strong>#<?= (int) $cmd['id'] ?></strong></td>
                            <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
                            <td class="td-muted"><?= htmlspecialchars($adresse_courte) ?></td>
                            <td>
                                <span class="statut statut-<?= htmlspecialchars($classe_statut) ?>">
                                    <?= htmlspecialchars($cmd['statut']) ?>
                                </span>
                            </td>
                            <td class="td-muted"><?= date('d/m/Y', strtotime($cmd['created_at'])) ?></td>
                            <td class="td-droite">
                                <a href="commandes.php?id=<?= (int) $cmd['id'] ?>" class="btn btn-outline btn-small">Gérer</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

    </main>
</div>

<?php include '../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>