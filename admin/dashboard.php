<?php
// admin/dashboard.php
session_start();
require_once '../config/db.php';

// Protection admin
if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../auth/connexion.php');
    exit;
}

// Statistiques
$nb_produits  = $pdo->query('SELECT COUNT(*) FROM products')->fetchColumn();
$nb_commandes = $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$nb_clients   = $pdo->query('SELECT COUNT(*) FROM users WHERE is_admin = 0')->fetchColumn();
$ca_total     = $pdo->query('SELECT SUM(prix_unitaire) FROM order_details')->fetchColumn();

// 5 dernières commandes
$dernieres = $pdo->query(
    'SELECT o.*, u.nom AS client_nom
     FROM orders o JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC LIMIT 5'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin – SenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="admin-layout">

    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <h2>⚙️ Admin</h2>
        <nav>
            <a href="dashboard.php" class="active">📊 Dashboard</a>
            <a href="produits.php">🍛 Produits</a>
            <a href="commandes.php">📦 Commandes</a>
            <a href="../index.php">🏠 Voir le site</a>
        </nav>
    </aside>

    <!-- Contenu principal -->
    <main class="admin-main">
        <h1>Tableau de bord</h1>
        <p class="admin-bienvenue">Bienvenue, <?= htmlspecialchars($_SESSION['user_nom']) ?> !</p>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card stat-orange">
                <span class="stat-icon">🍛</span>
                <div>
                    <p class="stat-nombre"><?= $nb_produits ?></p>
                    <p class="stat-label">Plats au catalogue</p>
                </div>
            </div>
            <div class="stat-card stat-blue">
                <span class="stat-icon">📦</span>
                <div>
                    <p class="stat-nombre"><?= $nb_commandes ?></p>
                    <p class="stat-label">Commandes total</p>
                </div>
            </div>
            <div class="stat-card stat-green">
                <span class="stat-icon">👥</span>
                <div>
                    <p class="stat-nombre"><?= $nb_clients ?></p>
                    <p class="stat-label">Clients inscrits</p>
                </div>
            </div>
            <div class="stat-card stat-purple">
                <span class="stat-icon">💰</span>
                <div>
                    <p class="stat-nombre"><?= number_format($ca_total ?? 0, 0, ',', ' ') ?></p>
                    <p class="stat-label">Chiffre d'affaires (FCFA)</p>
                </div>
            </div>
        </div>

        <!-- Accès rapides -->
        <div class="acces-rapides">
            <a href="produits.php?action=ajouter" class="btn btn-primary">+ Ajouter un plat</a>
            <a href="commandes.php" class="btn btn-outline">Voir les commandes</a>
        </div>

        <!-- Dernières commandes -->
        <h2 style="margin: 2rem 0 1rem;">Dernières commandes</h2>
        <div class="table-responsive">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Adresse</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dernieres as $cmd): ?>
                    <tr>
                        <td>#<?= $cmd['id'] ?></td>
                        <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
                        <td><?= htmlspecialchars(substr($cmd['adresse'], 0, 40)) ?>...</td>
                        <td>
                            <span class="statut statut-<?= strtolower(str_replace([' ', 'é'], ['-', 'e'], $cmd['statut'])) ?>">
                                <?= htmlspecialchars($cmd['statut']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y', strtotime($cmd['created_at'])) ?></td>
                        <td>
                            <a href="commandes.php?id=<?= $cmd['id'] ?>" class="btn btn-outline btn-small">Gérer</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </main>
</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>