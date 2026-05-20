<?php
// client/mes_commandes.php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/connexion.php');
    exit;
}

$stmt = $pdo->prepare(
    'SELECT o.*, 
     GROUP_CONCAT(p.nom SEPARATOR ", ") AS plats,
     SUM(od.prix_unitaire) AS total
     FROM orders o
     JOIN order_details od ON od.order_id = o.id
     JOIN products p ON p.id = od.product_id
     WHERE o.user_id = :user_id
     GROUP BY o.id
     ORDER BY o.created_at DESC'
);
$stmt->execute([':user_id' => $_SESSION['user_id']]);
$commandes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes commandes – SenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container">
    <h1 class="page-titre">Mes commandes</h1>

    <?php if (empty($commandes)): ?>
        <div class="panier-vide">
            <p>Vous n'avez pas encore passé de commande.</p>
            <a href="catalogue.php" class="btn btn-primary">Commander maintenant</a>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Plats</th>
                        <th>Adresse</th>
                        <th>Total</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($commandes as $cmd): ?>
                    <tr>
                        <td>#<?= $cmd['id'] ?></td>
                        <td><?= htmlspecialchars($cmd['plats']) ?></td>
                        <td><?= htmlspecialchars($cmd['adresse']) ?></td>
                        <td><?= number_format($cmd['total'], 0, ',', ' ') ?> FCFA</td>
                        <td>
                            <span class="statut statut-<?= strtolower(str_replace(' ', '-', $cmd['statut'])) ?>">
                                <?= htmlspecialchars($cmd['statut']) ?>
                            </span>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($cmd['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>