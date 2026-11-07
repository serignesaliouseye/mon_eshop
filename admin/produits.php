<?php
// admin/produits.php – CRUD des plats
session_start();
require_once '../config/db.php';
require_once '../includes/admin_nav.php';

// ── Protection admin ──
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../auth/connexion.php');
    exit;
}

// ── Jeton CSRF ──
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

const IMAGE_TAILLE_MAX = 3 * 1024 * 1024; // 3 Mo
const IMAGE_TYPES = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

$action  = $_GET['action'] ?? 'liste';
$id      = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = '';
$erreur  = '';

$categories    = $pdo->query('SELECT * FROM categories ORDER BY nom')->fetchAll();
$ids_categorie = array_map('intval', array_column($categories, 'id'));

// ── Plat à modifier (chargé avant le traitement du formulaire) ──
$produit_edit = null;
if ($action === 'modifier' && $id > 0) {
    $stmt = $pdo->prepare('SELECT * FROM products WHERE id = :id');
    $stmt->execute([':id' => $id]);
    $produit_edit = $stmt->fetch() ?: null;
    if (!$produit_edit) {
        $erreur = 'Plat introuvable.';
        $action = 'liste';
    }
}

// ── Traitement des formulaires (POST) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        $erreur = 'Session expirée, veuillez réessayer.';

    // Suppression
    } elseif (($_POST['action'] ?? '') === 'supprimer') {
        $del_id = (int) ($_POST['id'] ?? 0);
        try {
            $ok = $pdo->prepare('DELETE FROM products WHERE id = :id')->execute([':id' => $del_id]);
        } catch (PDOException $e) {
            $ok = false; // plat déjà utilisé dans des commandes
        }
        if ($ok) {
            header('Location: produits.php?msg=supprime');
            exit;
        }
        $erreur = 'Ce plat est lié à des commandes et ne peut pas être supprimé.';

    // Ajout / modification
    } elseif ($action === 'ajouter' || $action === 'modifier') {
        $nom         = trim($_POST['nom'] ?? '');
        $prix        = (float) ($_POST['prix'] ?? 0);
        $description = trim($_POST['description'] ?? '');
        $cat_id      = (int) ($_POST['categorie_id'] ?? 0);
        $cat_id      = in_array($cat_id, $ids_categorie, true) ? $cat_id : null;
        $image_path  = $produit_edit['image'] ?? '';

        if ($nom === '' || $prix <= 0) {
            $erreur = 'Le nom et le prix sont obligatoires.';
        }

        // Image : taille, vrai type MIME, nom aléatoire
        if ($erreur === '' && !empty($_FILES['image']['name'])) {
            $f = $_FILES['image'];
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $erreur = "Le téléversement de l'image a échoué.";
            } elseif ($f['size'] > IMAGE_TAILLE_MAX) {
                $erreur = 'Image trop lourde (3 Mo maximum).';
            } else {
                $info = @getimagesize($f['tmp_name']);
                $ext  = $info ? (IMAGE_TYPES[$info['mime']] ?? null) : null;
                if (!$ext) {
                    $erreur = 'Format image non accepté (jpg, png, webp uniquement).';
                } else {
                    $nom_fichier = 'plat_' . bin2hex(random_bytes(6)) . '.' . $ext;
                    if (move_uploaded_file($f['tmp_name'], dirname(__DIR__) . '/assets/images/' . $nom_fichier)) {
                        $image_path = 'assets/images/' . $nom_fichier;
                    } else {
                        $erreur = "Impossible d'enregistrer l'image.";
                    }
                }
            }
        }

        if ($erreur === '') {
            $params = [':nom' => $nom, ':prix' => $prix, ':desc' => $description, ':cat' => $cat_id, ':img' => $image_path];
            if ($action === 'modifier' && $produit_edit) {
                $pdo->prepare('UPDATE products SET nom=:nom, prix=:prix, description=:desc, categorie_id=:cat, image=:img WHERE id=:id')
                    ->execute($params + [':id' => $id]);
                header('Location: produits.php?msg=modifie');
            } else {
                $pdo->prepare('INSERT INTO products (nom, prix, description, categorie_id, image) VALUES (:nom,:prix,:desc,:cat,:img)')
                    ->execute($params);
                header('Location: produits.php?msg=ajoute');
            }
            exit;
        }
    }
}

// ── Message de confirmation ──
$msgs = ['ajoute' => 'Plat ajouté avec succès.', 'modifie' => 'Plat modifié avec succès.', 'supprime' => 'Plat supprimé.'];
if (isset($_GET['msg'])) {
    $message = $msgs[$_GET['msg']] ?? '';
}

// ── Valeurs du formulaire (ce que l'admin a tapé prime en cas d'erreur) ──
$form = [
    'nom'          => $_POST['nom']          ?? $produit_edit['nom']          ?? '',
    'prix'         => $_POST['prix']         ?? $produit_edit['prix']         ?? '',
    'description'  => $_POST['description']  ?? $produit_edit['description']  ?? '',
    'categorie_id' => $_POST['categorie_id'] ?? $produit_edit['categorie_id'] ?? '',
];
$image_actuelle = $produit_edit['image'] ?? '';

// ── Liste des plats + recherche ──
$produits = $pdo->query(
    'SELECT p.*, c.nom AS categorie_nom
     FROM products p LEFT JOIN categories c ON p.categorie_id = c.id
     ORDER BY p.created_at DESC'
)->fetchAll();

$q = trim($_GET['q'] ?? '');
$total_plats = count($produits);
if ($q !== '') {
    $produits = array_values(array_filter($produits, fn($p) =>
        mb_stripos($p['nom'], $q) !== false || mb_stripos($p['categorie_nom'] ?? '', $q) !== false
    ));
}

function url_image(?string $chemin): string {
    return '/' . ltrim((string) $chemin, '/');
}

$mode_formulaire = ($action === 'ajouter') || ($action === 'modifier' && $produit_edit);
$titre_page = 'Plats';
include '../includes/header.php';
?>

<div class="admin-layout">
    <?php sidebar_admin('produits'); ?>

    <main class="admin-main">

        <?php if ($message): ?>
            <div class="alert alert-success"><p><?= htmlspecialchars($message) ?></p></div>
        <?php endif; ?>
        <?php if ($erreur): ?>
            <div class="alert alert-error"><p><?= htmlspecialchars($erreur) ?></p></div>
        <?php endif; ?>

        <?php if ($mode_formulaire): ?>

        <!-- ===== AJOUT / MODIFICATION ===== -->
        <a href="produits.php" class="btn-retour"><?= icone('retour', 16) ?> Retour aux plats</a>

        <div class="admin-header-row">
            <div>
                <h1><?= $action === 'modifier' ? 'Modifier le plat' : 'Ajouter un plat' ?></h1>
                <p class="admin-bienvenue">
                    <?= $action === 'modifier' ? 'Mettez à jour les informations de ce plat.' : 'Il apparaîtra immédiatement dans le catalogue.' ?>
                </p>
            </div>
        </div>

        <form method="POST"
              action="produits.php?action=<?= $action ?><?= $id ? '&id=' . $id : '' ?>"
              enctype="multipart/form-data" class="admin-edit-grid">
            <input type="hidden" name="csrf" value="<?= $csrf ?>">

            <div class="admin-card admin-card-pad">
                <h2 class="admin-card-titre">Informations du plat</h2>

                <div class="form-group">
                    <label for="nom">Nom du plat</label>
                    <input type="text" id="nom" name="nom" required maxlength="120"
                           value="<?= htmlspecialchars($form['nom']) ?>" placeholder="Ex. Thiéboudienne">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="prix">Prix (FCFA)</label>
                        <input type="number" id="prix" name="prix" min="0" step="50" required
                               value="<?= htmlspecialchars((string) $form['prix']) ?>" placeholder="3500">
                    </div>
                    <div class="form-group">
                        <label for="categorie_id">Catégorie</label>
                        <select id="categorie_id" name="categorie_id">
                            <option value="">Aucune</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= (int) $cat['id'] ?>"
                                    <?= (string) $form['categorie_id'] === (string) $cat['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="5"
                              placeholder="Ingrédients, accompagnements, portion…"><?= htmlspecialchars($form['description']) ?></textarea>
                </div>
            </div>

            <div class="admin-card admin-card-pad">
                <h2 class="admin-card-titre">Photo</h2>

                <div class="upload-preview" id="apercu">
                    <?php if ($image_actuelle): ?>
                        <img src="<?= htmlspecialchars(url_image($image_actuelle)) ?>" alt="Image actuelle" id="apercu-img"
                             onerror="this.onerror=null;this.src='/assets/images/thiep.jpg'">
                    <?php else: ?>
                        <div class="upload-vide" id="apercu-vide"><?= icone('image', 32) ?><span>Aucune image</span></div>
                        <img src="" alt="Aperçu" id="apercu-img" hidden>
                    <?php endif; ?>
                </div>

                <label for="image" class="btn btn-outline btn-small btn-full">
                    <?= $image_actuelle ? "Changer l'image" : 'Choisir une image' ?>
                </label>
                <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/webp" class="input-cache">
                <p class="aide-champ">JPG, PNG ou WebP, 3 Mo maximum.</p>
            </div>

            <div class="form-actions form-actions--pleine">
                <button type="submit" class="btn btn-primary">
                    <?= $action === 'modifier' ? 'Enregistrer les modifications' : 'Ajouter le plat' ?>
                </button>
                <a href="produits.php" class="btn btn-outline">Annuler</a>
            </div>
        </form>

        <script>
            // Aperçu de l'image choisie avant l'envoi
            document.getElementById('image').addEventListener('change', function () {
                var fichier = this.files[0];
                if (!fichier) return;
                var img = document.getElementById('apercu-img');
                var vide = document.getElementById('apercu-vide');
                img.src = URL.createObjectURL(fichier);
                img.hidden = false;
                if (vide) vide.hidden = true;
            });
        </script>

        <?php else: ?>

        <!-- ===== LISTE DES PLATS ===== -->
        <div class="admin-header-row">
            <div>
                <h1>Plats</h1>
                <p class="admin-bienvenue"><?= $total_plats ?> plat(s) au catalogue</p>
            </div>
            <a href="produits.php?action=ajouter" class="btn btn-primary btn-small"><?= icone('plus', 16) ?> Ajouter un plat</a>
        </div>

        <form method="GET" class="admin-recherche" role="search">
            <?= icone('search', 18) ?>
            <input type="search" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Rechercher un plat ou une catégorie">
        </form>

        <div class="admin-card">
            <?php if (empty($produits)): ?>
                <p class="admin-vide">
                    <?= $q !== '' ? 'Aucun plat ne correspond à votre recherche.' : "Aucun plat pour l'instant. Ajoutez le premier !" ?>
                </p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table-admin">
                    <thead>
                        <tr>
                            <th>Plat</th>
                            <th>Catégorie</th>
                            <th>Prix</th>
                            <th class="td-droite">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($produits as $p): $pid = (int) $p['id']; ?>
                        <tr>
                            <td>
                                <div class="cellule-plat">
                                    <img src="<?= htmlspecialchars(url_image($p['image'])) ?>"
                                         alt="" class="produit-miniature"
                                         onerror="this.onerror=null;this.src='/assets/images/thiep.jpg'">
                                    <strong><?= htmlspecialchars($p['nom']) ?></strong>
                                </div>
                            </td>
                            <td>
                                <?php if (!empty($p['categorie_nom'])): ?>
                                    <span class="badge-categorie"><?= htmlspecialchars($p['categorie_nom']) ?></span>
                                <?php else: ?>
                                    <span class="td-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td><strong><?= number_format((float) $p['prix'], 0, ',', ' ') ?></strong> <span class="td-muted">FCFA</span></td>
                            <td>
                                <div class="td-actions">
                                    <a href="produits.php?action=modifier&id=<?= $pid ?>" class="btn-icon btn-icon--edit"
                                       title="Modifier" aria-label="Modifier <?= htmlspecialchars($p['nom']) ?>"><?= icone('edit', 16) ?></a>
                                    <form method="POST" class="form-inline"
                                          onsubmit="return confirm(<?= htmlspecialchars(json_encode('Supprimer le plat « ' . $p['nom'] . ' » ?', JSON_UNESCAPED_UNICODE), ENT_QUOTES) ?>);">
                                        <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                        <input type="hidden" name="id" value="<?= $pid ?>">
                                        <button name="action" value="supprimer" class="btn-icon btn-icon--no"
                                                title="Supprimer" aria-label="Supprimer <?= htmlspecialchars($p['nom']) ?>"><?= icone('trash', 16) ?></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <?php endif; ?>

    </main>
</div>

<?php include '../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
</body>
</html>