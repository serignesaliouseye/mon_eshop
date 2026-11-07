<?php
// admin/commandes.php – Gestion des commandes
session_start();
require_once '../config/db.php';
require_once '../includes/admin_nav.php';

// ── Protection admin ──
if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: ../auth/connexion.php');
    exit;
}

// ── Jeton CSRF : les actions qui modifient les données passent en POST ──
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['csrf'];

const STATUTS = ['En attente', 'Validée', 'Annulée'];

$action  = $_GET['action'] ?? 'liste';
$id      = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$message = '';
$erreur  = '';

// ── Traitement des formulaires (POST) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_action = $_POST['action'] ?? '';
    $post_id     = (int) ($_POST['id'] ?? 0);

    if (!hash_equals($csrf, $_POST['csrf'] ?? '')) {
        $erreur = 'Session expirée, veuillez réessayer.';
    } elseif ($post_id > 0) {

        if ($post_action === 'valider' || $post_action === 'annuler') {
            $statut = $post_action === 'valider' ? 'Validée' : 'Annulée';
            $pdo->prepare('UPDATE orders SET statut = :statut WHERE id = :id')
                ->execute([':statut' => $statut, ':id' => $post_id]);
            header('Location: commandes.php?msg=' . $post_action);
            exit;
        }

        if ($post_action === 'supprimer') {
            $pdo->beginTransaction();
            try {
                $pdo->prepare('DELETE FROM order_details WHERE order_id = :id')->execute([':id' => $post_id]);
                $pdo->prepare('DELETE FROM orders WHERE id = :id')->execute([':id' => $post_id]);
                $pdo->commit();
            } catch (Throwable $e) {
                $pdo->rollBack();
                throw $e;
            }
            header('Location: commandes.php?msg=supprime');
            exit;
        }

        if ($post_action === 'modifier') {
            $adresse = trim($_POST['adresse'] ?? '');
            $statut  = $_POST['statut'] ?? '';
            if ($adresse === '') {
                $erreur = "L'adresse de livraison est obligatoire.";
            } elseif (!in_array($statut, STATUTS, true)) {
                $erreur = 'Statut invalide.';
            } else {
                $pdo->prepare('UPDATE orders SET adresse = :adresse, statut = :statut WHERE id = :id')
                    ->execute([':adresse' => $adresse, ':statut' => $statut, ':id' => $post_id]);
                header('Location: commandes.php?msg=modifie');
                exit;
            }
        }
    }
}

// ── Message de confirmation ──
$msgs = [
    'valider'  => 'Commande validée.',
    'annuler'  => 'Commande annulée.',
    'supprime' => 'Commande supprimée.',
    'modifie'  => 'Commande modifiée.',
];
if (isset($_GET['msg'])) {
    $message = $msgs[$_GET['msg']] ?? '';
}

// ── Commande à modifier ──
$commande_edit = null;
$details_edit  = [];
if ($action === 'modifier' && $id > 0) {
    $stmt = $pdo->prepare('SELECT o.*, u.nom AS client_nom FROM orders o JOIN users u ON u.id = o.user_id WHERE o.id = :id');
    $stmt->execute([':id' => $id]);
    $commande_edit = $stmt->fetch();

    if ($commande_edit) {
        $stmt2 = $pdo->prepare(
            'SELECT od.*, p.nom AS plat_nom
             FROM order_details od JOIN products p ON p.id = od.product_id
             WHERE od.order_id = :id'
        );
        $stmt2->execute([':id' => $id]);
        $details_edit = $stmt2->fetchAll();
    } else {
        $erreur = 'Commande introuvable.';
    }
}

// ── Liste des commandes (total = prix x quantité ; repli si la colonne "quantite" n'existe pas) ──
function charger_commandes(PDO $pdo): array {
    $modele = 'SELECT o.*, u.nom AS client_nom, %s AS nb_articles, %s AS total
               FROM orders o
               JOIN users u ON u.id = o.user_id
               LEFT JOIN order_details od ON od.order_id = o.id
               GROUP BY o.id
               ORDER BY o.created_at DESC';
    try {
        $r = $pdo->query(sprintf($modele, 'COALESCE(SUM(od.quantite), 0)', 'COALESCE(SUM(od.prix_unitaire * od.quantite), 0)'));
        if ($r !== false) return $r->fetchAll();
    } catch (PDOException $e) { /* on essaie sans la quantité */ }
    return $pdo->query(sprintf($modele, 'COUNT(od.id)', 'COALESCE(SUM(od.prix_unitaire), 0)'))->fetchAll();
}
$commandes = charger_commandes($pdo);

// ── Filtre par statut ──
$onglets = [
    'tous'       => ['Toutes',     null],
    'en-attente' => ['En attente', 'En attente'],
    'validee'    => ['Validées',   'Validée'],
    'annulee'    => ['Annulées',   'Annulée'],
];
$filtre = $_GET['statut'] ?? 'tous';
if (!isset($onglets[$filtre])) $filtre = 'tous';

$compteurs = ['tous' => count($commandes)];
foreach ($onglets as $cle => [, $valeur]) {
    if ($valeur !== null) {
        $compteurs[$cle] = count(array_filter($commandes, fn($c) => $c['statut'] === $valeur));
    }
}
$affichees = $onglets[$filtre][1] === null
    ? $commandes
    : array_values(array_filter($commandes, fn($c) => $c['statut'] === $onglets[$filtre][1]));

function classe_statut(string $statut): string {
    return mb_strtolower(str_replace([' ', 'é'], ['-', 'e'], $statut));
}

$titre_page = 'Commandes';
include '../includes/header.php';
?>

<div class="admin-layout">
    <?php sidebar_admin('commandes'); ?>

    <main class="admin-main">

        <?php if ($message): ?>
            <div class="alert alert-success"><p><?= htmlspecialchars($message) ?></p></div>
        <?php endif; ?>
        <?php if ($erreur): ?>
            <div class="alert alert-error"><p><?= htmlspecialchars($erreur) ?></p></div>
        <?php endif; ?>

        <?php if ($commande_edit): ?>

        <!-- ===== MODIFICATION D'UNE COMMANDE ===== -->
        <a href="commandes.php" class="btn-retour"><?= icone('retour', 16) ?> Retour aux commandes</a>

        <div class="admin-header-row">
            <div>
                <h1>Commande #<?= (int) $commande_edit['id'] ?></h1>
                <p class="admin-bienvenue">Passée le <?= date('d/m/Y à H:i', strtotime($commande_edit['created_at'])) ?></p>
            </div>
            <span class="statut statut-<?= htmlspecialchars(classe_statut($commande_edit['statut'])) ?>">
                <?= htmlspecialchars($commande_edit['statut']) ?>
            </span>
        </div>

        <div class="admin-edit-grid">

            <form method="POST" action="commandes.php?action=modifier&id=<?= (int) $commande_edit['id'] ?>" class="admin-card admin-card-pad">
                <input type="hidden" name="csrf" value="<?= $csrf ?>">
                <input type="hidden" name="action" value="modifier">
                <input type="hidden" name="id" value="<?= (int) $commande_edit['id'] ?>">

                <h2 class="admin-card-titre">Informations de livraison</h2>

                <div class="form-group">
                    <label for="adresse">Adresse de livraison</label>
                    <textarea id="adresse" name="adresse" rows="4" required><?= htmlspecialchars($_POST['adresse'] ?? $commande_edit['adresse']) ?></textarea>
                </div>
                <div class="form-group">
                    <label for="statut">Statut</label>
                    <select id="statut" name="statut">
                        <?php foreach (STATUTS as $s): ?>
                            <option value="<?= $s ?>" <?= $commande_edit['statut'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                    <a href="commandes.php" class="btn btn-outline">Annuler</a>
                </div>
            </form>

            <aside class="admin-card admin-card-pad">
                <h2 class="admin-card-titre">Récapitulatif</h2>
                <p class="recap-client"><span>Client</span><strong><?= htmlspecialchars($commande_edit['client_nom']) ?></strong></p>

                <?php $total_edit = 0; foreach ($details_edit as $d):
                    $qte   = (int) ($d['quantite'] ?? 1);
                    $ligne = $d['prix_unitaire'] * $qte;
                    $total_edit += $ligne;
                ?>
                <div class="detail-ligne">
                    <span><?= htmlspecialchars($d['plat_nom']) ?> <em>× <?= $qte ?></em></span>
                    <strong><?= number_format($ligne, 0, ',', ' ') ?> FCFA</strong>
                </div>
                <?php endforeach; ?>

                <div class="detail-total">
                    <span>Total</span>
                    <strong><?= number_format($total_edit, 0, ',', ' ') ?> FCFA</strong>
                </div>
            </aside>

        </div>

        <?php else: ?>

        <!-- ===== LISTE DES COMMANDES ===== -->
        <div class="admin-header-row">
            <div>
                <h1>Commandes</h1>
                <p class="admin-bienvenue"><?= $compteurs['tous'] ?> commande(s) au total</p>
            </div>
        </div>

        <div class="filtres-statut" role="tablist">
            <?php foreach ($onglets as $cle => [$libelle]): ?>
                <a href="commandes.php?statut=<?= $cle ?>"
                   class="<?= $filtre === $cle ? 'actif' : '' ?>"><?= $libelle ?> <span><?= $compteurs[$cle] ?></span></a>
            <?php endforeach; ?>
        </div>

        <div class="admin-card">
            <?php if (empty($affichees)): ?>
                <p class="admin-vide">Aucune commande dans cette catégorie.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table-admin">
                    <thead>
                        <tr>
                            <th>N°</th>
                            <th>Client</th>
                            <th>Articles</th>
                            <th>Total</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="td-droite">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($affichees as $cmd): $cid = (int) $cmd['id']; ?>
                        <tr>
                            <td><strong>#<?= $cid ?></strong></td>
                            <td><?= htmlspecialchars($cmd['client_nom']) ?></td>
                            <td class="td-muted"><?= (int) $cmd['nb_articles'] ?></td>
                            <td><strong><?= number_format((float) $cmd['total'], 0, ',', ' ') ?></strong> <span class="td-muted">FCFA</span></td>
                            <td>
                                <span class="statut statut-<?= htmlspecialchars(classe_statut($cmd['statut'])) ?>">
                                    <?= htmlspecialchars($cmd['statut']) ?>
                                </span>
                            </td>
                            <td class="td-muted"><?= date('d/m/Y H:i', strtotime($cmd['created_at'])) ?></td>
                            <td>
                                <div class="td-actions">
                                    <?php if ($cmd['statut'] === 'En attente'): ?>
                                        <form method="POST" class="form-inline">
                                            <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                            <input type="hidden" name="id" value="<?= $cid ?>">
                                            <button name="action" value="valider" class="btn-icon btn-icon--ok" title="Valider" aria-label="Valider la commande #<?= $cid ?>"><?= icone('check', 16) ?></button>
                                            <button name="action" value="annuler" class="btn-icon btn-icon--no" title="Annuler" aria-label="Annuler la commande #<?= $cid ?>"><?= icone('x', 16) ?></button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="commandes.php?action=modifier&id=<?= $cid ?>" class="btn-icon btn-icon--edit" title="Modifier" aria-label="Modifier la commande #<?= $cid ?>"><?= icone('edit', 16) ?></a>
                                    <form method="POST" class="form-inline"
                                          onsubmit="return confirm('Supprimer la commande #<?= $cid ?> ? Cette action est définitive.');">
                                        <input type="hidden" name="csrf" value="<?= $csrf ?>">
                                        <input type="hidden" name="id" value="<?= $cid ?>">
                                        <button name="action" value="supprimer" class="btn-icon btn-icon--no" title="Supprimer" aria-label="Supprimer la commande #<?= $cid ?>"><?= icone('trash', 16) ?></button>
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