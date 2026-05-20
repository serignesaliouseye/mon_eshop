<?php
// client/commande.php
session_start();
require_once '../config/db.php';

// Protection : connecté uniquement
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/connexion.php');
    exit;
}

// Panier vide → retour catalogue
if (empty($_SESSION['panier'])) {
    header('Location: catalogue.php');
    exit;
}

$erreur  = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $adresse = trim($_POST['adresse'] ?? '');

    if (empty($adresse)) {
        $erreur = 'Veuillez saisir votre adresse de livraison.';
    } else {
        try {
            $pdo->beginTransaction();

            // Créer la commande
            $stmt = $pdo->prepare(
                'INSERT INTO orders (user_id, adresse, statut) VALUES (:user_id, :adresse, "En attente")'
            );
            $stmt->execute([
                ':user_id' => $_SESSION['user_id'],
                ':adresse' => $adresse
            ]);
            $commande_id = $pdo->lastInsertId();

            // Insérer les détails
            $stmt2 = $pdo->prepare(
                'INSERT INTO order_details (order_id, product_id, prix_unitaire) VALUES (:order_id, :product_id, :prix)'
            );
            foreach ($_SESSION['panier'] as $item) {
                for ($i = 0; $i < $item['quantite']; $i++) {
                    $stmt2->execute([
                        ':order_id'   => $commande_id,
                        ':product_id' => $item['id'],
                        ':prix'       => $item['prix']
                    ]);
                }
            }

            $pdo->commit();

            // Vider le panier
            $_SESSION['panier'] = [];
            $success = true;

        } catch (Exception $e) {
            $pdo->rollBack();
            $erreur = 'Une erreur est survenue. Veuillez réessayer.';
        }
    }
}

// Calcul total panier
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
    <title>Validation commande – SenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="container commande-page">
    <h1 class="page-titre">Valider ma commande</h1>

    <?php if ($success): ?>
        <div class="commande-success">
            <span class="success-icon">✅</span>
            <h2>Commande confirmée !</h2>
            <p>Votre commande a bien été enregistrée. Nous la préparerons dans les plus brefs délais.</p>
            <a href="mes_commandes.php" class="btn btn-primary">Voir mes commandes</a>
            <a href="catalogue.php" class="btn btn-outline">Continuer les achats</a>
        </div>
    <?php else: ?>

        <?php if ($erreur): ?>
            <div class="alert alert-error"><p><?= htmlspecialchars($erreur) ?></p></div>
        <?php endif; ?>

        <div class="commande-layout">

            <!-- Résumé de la commande -->
            <div class="commande-resume">
                <h2>Récapitulatif</h2>
                <?php foreach ($_SESSION['panier'] as $item): ?>
                <div class="resume-ligne">
                    <span><?= htmlspecialchars($item['nom']) ?> × <?= $item['quantite'] ?></span>
                    <span><?= number_format($item['prix'] * $item['quantite'], 0, ',', ' ') ?> FCFA</span>
                </div>
                <?php endforeach; ?>
                <div class="resume-total">
                    <span>Total</span>
                    <span><?= number_format($total, 0, ',', ' ') ?> FCFA</span>
                </div>
            </div>

            <!-- Formulaire de livraison -->
            <div class="commande-form">
                <h2>Adresse de livraison</h2>
                <form method="POST" action="" id="form-commande">
                    <div class="form-group">
                        <label for="adresse">Adresse complète</label>
                        <textarea id="adresse" name="adresse" rows="4"
                                  placeholder="Ex : Quartier Médina, Rue 12, Villa 3, Dakar"
                                  required><?= htmlspecialchars($_POST['adresse'] ?? '') ?></textarea>
                        <span class="form-error" id="err-adresse"></span>
                    </div>
                    <button type="submit" class="btn btn-primary btn-full">
                        Confirmer la commande ✅
                    </button>
                </form>
            </div>

        </div>

    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
</body>
</html>