<?php
// admin/produits.php – CRUD complet des plats
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../auth/connexion.php');
    exit;
}

$action  = $_GET['action'] ?? 'liste';
$id      = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$message = '';
$erreur  = '';

$categories = $pdo->query('SELECT * FROM categories')->fetchAll();

// ── SUPPRIMER ──
if ($action === 'supprimer' && $id > 0) {
    $pdo->prepare('DELETE FROM products WHERE id = :id')->execute([':id' => $id]);
    header('Location: produits.php?msg=supprime');
    exit;
}

// ── AJOUTER / MODIFIER ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom         = trim($_POST['nom']         ?? '');
    $prix        = (float)($_POST['prix']     ?? 0);
    $description = trim($_POST['description'] ?? '');
    $cat_id      = (int)($_POST['categorie_id'] ?? 0);
    $image_path  = trim($_POST['image_actuelle'] ?? '');

    if (empty($nom) || $prix <= 0) {
        $erreur = 'Le nom et le prix sont obligatoires.';
    } else {
        // Upload image
        if (!empty($_FILES['image']['name'])) {
            $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $exts_ok   = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($ext, $exts_ok)) {
                $nom_fichier = 'plat_' . time() . '.' . $ext;
                $dest        = '../assets/images/' . $nom_fichier;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                    $image_path = 'assets/images/' . $nom_fichier;
                }
            } else {
                $erreur = 'Format image non accepté (jpg, png, webp uniquement).';
            }
        }

        if (empty($erreur)) {
            if ($action === 'modifier' && $id > 0) {
                $stmt = $pdo->prepare(
                    'UPDATE products SET nom=:nom, prix=:prix, description=:desc, categorie_id=:cat, image=:img WHERE id=:id'
                );
                $stmt->execute([':nom'=>$nom,':prix'=>$prix,':desc'=>$description,':cat'=>$cat_id,':img'=>$image_path,':id'=>$id]);
                header('Location: produits.php?msg=modifie');
            } else {
                $stmt = $pdo->prepare(
                    'INSERT INTO products (nom, prix, description, categorie_id, image) VALUES (:nom,:prix,:desc,:cat,:img)'
                );
                $stmt->execute([':nom'=>$nom,':prix'=>$prix,':desc'=>$description,':cat'=>$cat_id,':img'=>$image_path]);
                header('Location: produits.php?msg=ajoute');
            }
            exit;
        }
    }
}

// Messages
if (isset($_GET['msg'])) {
    $msgs = ['ajoute'=>'Plat ajouté avec succès !', 'modifie'=>'Plat modifié avec succès !', 'supprime'=>'Plat supprimé.'];
    $message = $msgs[$_GET['msg']] ?? '';
}

// Charger le produit pour modification
$produit_edit = null;
if ($action === 'modifier' && $id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $produit_edit = $stmt->fetch();
}

// Liste des produits
$produits = $pdo->query(
    'SELECT p.*, c.nom AS categorie_nom FROM products p LEFT JOIN categories c ON p.categorie_id = c.id ORDER BY p.created_at DESC'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des plats – SenFood Admin</title>
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
            <a href="produits.php" class="active">🍛 Produits</a>
            <a href="commandes.php">📦 Commandes</a>
            <a href="../index.php">🏠 Voir le site</a>
        </nav>
    </aside>

    <main class="admin-main">

        <?php if ($message): ?>
            <div class="alert alert-success"><p><?= htmlspecialchars($message) ?></p></div>
        <?php endif; ?>
        <?php if ($erreur): ?>
            <div class="alert alert-error"><p><?= htmlspecialchars($erreur) ?></p></div>
        <?php endif; ?>

        <!-- Formulaire ajout / modification -->
        <?php if ($action === 'ajouter' || $action === 'modifier'): ?>
        <h1><?= $action === 'modifier' ? 'Modifier le plat' : 'Ajouter un plat' ?></h1>

        <form method="POST" action="produits.php?action=<?= $action ?><?= $id ? '&id='.$id : '' ?>"
              enctype="multipart/form-data" class="form-admin">

            <input type="hidden" name="image_actuelle"
                   value="<?= htmlspecialchars($produit_edit['image'] ?? '') ?>">

            <div class="form-group">
                <label>Nom du plat</label>
                <input type="text" name="nom" required
                       value="<?= htmlspecialchars($produit_edit['nom'] ?? $_POST['nom'] ?? '') ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Prix (FCFA)</label>
                    <input type="number" name="prix" min="0" step="50" required
                           value="<?= $produit_edit['prix'] ?? $_POST['prix'] ?? '' ?>">
                </div>
                <div class="form-group">
                    <label>Catégorie</label>
                    <select name="categorie_id">
                        <option value="">-- Choisir --</option>
                        <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= ($produit_edit['categorie_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nom']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4"><?= htmlspecialchars($produit_edit['description'] ?? '') ?></textarea>
            </div>
            <div class="form-group">
                <label>Image du plat</label>
                <?php if (!empty($produit_edit['image'])): ?>
                    <img src="../<?= htmlspecialchars($produit_edit['image']) ?>"
                         alt="Image actuelle" style="max-height:100px;margin-bottom:0.5rem;border-radius:8px;">
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">
            </div>
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">
                    <?= $action === 'modifier' ? 'Enregistrer les modifications' : 'Ajouter le plat' ?>
                </button>
                <a href="produits.php" class="btn btn-outline">Annuler</a>
            </div>
        </form>

        <?php else: ?>

        <!-- Liste des produits -->
        <div class="admin-header-row">
            <h1>Gestion des plats (<?= count($produits) ?>)</h1>
            <a href="produits.php?action=ajouter" class="btn btn-primary">+ Ajouter un plat</a>
        </div>

        <div class="table-responsive">
            <table class="table-admin">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Nom</th>
                        <th>Catégorie</th>
                        <th>Prix</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($produits as $p): ?>
                    <tr>
                        <td>
                            <img src="../<?= htmlspecialchars($p['image']) ?>"
                                 alt="<?= htmlspecialchars($p['nom']) ?>"
                                 style="width:60px;height:50px;object-fit:cover;border-radius:6px;"
                                 onerror="this.src='../assets/images/default.jpg'">
                        </td>
                        <td><?= htmlspecialchars($p['nom']) ?></td>
                        <td><?= htmlspecialchars($p['categorie_nom'] ?? '—') ?></td>
                        <td><?= number_format($p['prix'], 0, ',', ' ') ?> FCFA</td>
                        <td class="td-actions">
                            <a href="produits.php?action=modifier&id=<?= $p['id'] ?>"
                               class="btn btn-outline btn-small">✏️ Modifier</a>
                            <button class="btn btn-danger btn-small"
                                    onclick="confirmerSuppression('Supprimer le plat &quot;<?= htmlspecialchars($p['nom']) ?>&quot; ?', () => { window.location.href=\'produits.php?action=supprimer&id=<?= $p['id'] ?>\' })">
                                🗑️ Supprimer
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