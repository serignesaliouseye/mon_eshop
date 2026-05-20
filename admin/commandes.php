<?php
// admin/commandes.php – Gestion des commandes
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../auth/connexion.php');
    exit;
}

$action  = $_GET['action'] ?? 'liste';
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';

// ── Changer le statut ──
if (in_array($action, ['valider', 'annuler']) && $id > 0) {
    $statut = $action === 'valider' ? 'Validée' : 'Annulée';
    $pdo->prepare('UPDATE orders SET statut = :statut WHERE id = :id')
        ->execute([':statut' => $statut, ':id' => $id]);
    header('Location: commandes.php?msg=' . $action);
    exit;
}

// ── Supprimer ──
if ($action === 'supprimer' && $id > 0) {
    $pdo->prepare('DELETE FROM order_details WHERE order_id = :id')->execute([':id' => $id]);
    $pdo->prepare('DELETE FROM orders WHERE id = :id')->execute([':id' => $id]);
    header('Location: commandes.php?msg=supprime');
    exit;
}

// ── Modifier adresse ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'modifier' && $id > 0) {
    $adresse = trim($_POST['adresse'] ?? '');
    $statut  = $_POST['statut'] ?? 'En attente';
    if (!empty($adresse)) {
        $pdo->prepare('UPDATE orders SET adresse = :adresse, statut = :statut WHERE id = :id')
            ->execute([':adresse' => $adresse, ':statut' => $statut, ':id' => $id]);
        header('Location: commandes.php?msg=modifie');
        exit;
    }
}

// Messages
if (isset($_GET['msg'])) {
    $msgs = [
        'valider'  => '✅ Commande validée.',
        'annuler'  => '❌ Commande annulée.',
        'supprime' => '🗑️ Commande supprimée.',
        'modifie'  => '✏️ Commande modifiée.'
    ];
    $message = $msgs[$_GET['msg']] ?? '';
}

// Charger une commande pour modification
$commande_edit = null;
if ($action === 'modifier' && $id > 0) {
    $stmt = $pdo->prepare('SELECT o.*, u.nom AS client_nom FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = :id');
    $stmt->execute([':id' => $id]);
    $commande_edit = $stmt->fetch();

    // Détails de la commande
    $stmt2 = $pdo->prepare(
        'SELECT od.*, p.nom AS plat_nom FROM order_details od JOIN products p ON p.id = od.product_id WHERE od.order_id = :id'
    );
    $stmt2->execute([':id' => $id]);
    $details_edit = $stmt2->fetchAll();
}

// Liste toutes les commandes
$commandes = $pdo->query(
    'SELECT o.*, u.nom AS client_nom,
     COUNT(od.id) AS nb_articles,
     SUM(od.prix_unitaire) AS total
     FROM orders o
     JOIN users u ON u.id = o.user_id
     LEFT JOIN order_details od ON od.order_id = o.id
     GROUP BY o.id
     ORDER BY o.created_at DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des commandes – SenFood Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <h2>⚙️ Admin</h2>
        <nav>
            <a href="dashboard.php">📊 Dashboard</a>
            <a href="produits.php">🍛 Produits</a>
            <a href="commandes.php" class="active">📦 Commandes</a>
            <a href="../index.php">🏠 Voir le site</a>
        </nav>
    </aside>

    <main class="admin-main">

        <?php if ($message): ?>
            <div class="alert alert-success"><p><?= htmlspecialchars($message) ?></p></div>
        <?php endif; ?>

        <?php if ($action === 'modifier' && $commande_edit): ?>

        <!-- Formulaire modification -->
        <h1>Modifier la commande #<?= $commande_edit['id'] ?></h1>
        <p><strong>Client :</strong> <?= htmlspecialchars($commande_edit['client_nom']) ?></p>

        <h3 style="margin:1.5rem 0 0.8rem;">Détail des plats</h3>
        <ul style="margin-bottom:1.5rem;">
            <?php foreach ($details_edit as $d): ?>
            <li><?= htmlspecialchars($d['plat_nom']) ?> — <?= number_format($d['prix_unitaire'], 0, ',', ' ') ?> FCFA</li>
            <?php endforeach; ?>
        </ul>

        <form method="POST" action="commandes.php?action=modifier&id=<?= $commande_edit['id'] ?>" class="form-admin">
            <div class="form-group">
                <label>Adresse de livraison</label>
                <textarea name="adresse" rows="3" required><?= htmlspecialchars($commande_edit['adresse']) ?></textarea>
            </div>
            <div class="form-group">
                <label>Statut</label>
                <select name="statut">
                    <option value="En attente" <?= $commande_edit['statut'] === 'En attente' ? 'selected' : '' ?>>En attente</option>
                    <option value="Validée"    <?= $commande_edit['statut'] === 'Validée'    ? 'selected' : '' ?>>Validée</option>
                    <option value="Annulée"    <?= $commande_edit['statut'] === 'Annulée'    ? 'selected' : '' ?>>Annulée</option>
                </select>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Enregistrer</button>
                <a href="commandes.php" class="btn btn-outline">Annuler</a>
            </div>
        </form>

        <?php else: ?>

        <!-- Liste des commandes -->
        <div class="admin-header-row">
            <h1>Commandes (<?= count($commandes) ?>)</h1>
        </div>

        <div class="table-responsive">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Client</th>
                        <th>Articles</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $cmd): ?>
                    <tr>
                        <td>#<?= $cmd['id'] ?></td>
                        <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
                        <td><?= $cmd['nb_articles'] ?> article(s)</td>
                        <td><?= number_format($cmd['total'] ?? 0, 0, ',', ' ') ?> FCFA</td>
                        <td>
                            <span class="statut statut-<?= strtolower(str_replace([' ', 'é'], ['-', 'e'], $cmd['statut'])) ?>">
                                <?= htmlspecialchars($cmd['statut']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($cmd['created_at'])) ?></td>
                        <td class="td-actions">
                            <?php if ($cmd['statut'] === 'En attente'): ?>
                                <a href="commandes.php?action=valider&id=<?= $cmd['id'] ?>"
                                   class="btn btn-primary btn-small">✅ Valider</a>
                                <a href="commandes.php?action=annuler&id=<?= $cmd['id'] ?>"
                                   class="btn btn-outline btn-small">❌ Annuler</a>
                            <?php endif; ?>
                            <a href="commandes.php?action=modifier&id=<?= $cmd['id'] ?>"
                               class="btn btn-outline btn-small">✏️ Modifier</a>
                            <button class="btn btn-danger btn-small"
                                    onclick="confirmerSuppression('Supprimer la commande #<?= $cmd['id'] ?> ?', () => { window.location.href=\'commandes.php?action=supprimer&id=<?= $cmd['id'] ?>\' })">
                                🗑️
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php endif; ?>
    </main>
</div>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>