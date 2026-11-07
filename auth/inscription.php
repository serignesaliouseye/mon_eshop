<?php
// auth/inscription.php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit;
}

require_once '../config/db.php';

// Jeton CSRF
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

/**
 * Hache le mot de passe. Si la colonne "password" est assez large (60 caractères ou plus),
 * on utilise password_hash() ; sinon on garde md5 pour ne pas tronquer le hachage.
 * La page de connexion accepte les deux formats.
 */
function hacher_mot_de_passe(PDO $pdo, string $mot_de_passe): string {
    try {
        $st = $pdo->query(
            "SELECT CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'password'"
        );
        if ($st && (int) $st->fetchColumn() >= 60) {
            return password_hash($mot_de_passe, PASSWORD_DEFAULT);
        }
    } catch (Throwable $e) { /* on retombe sur md5 */ }
    return md5($mot_de_passe);
}

function icones_oeil(): string {
    return '<svg class="oeil" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>'
         . '<svg class="oeil-barre" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden><path d="M17.94 17.94A10.1 10.1 0 0 1 12 19C5.6 19 2 12 2 12a18 18 0 0 1 4.06-5.06M9.9 5.1A9.7 9.7 0 0 1 12 5c6.4 0 10 7 10 7a18 18 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m2 2 20 20"/></svg>';
}

$erreurs = [];
$success = false;
$nom     = '';
$email   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom      = trim($_POST['nom']      ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $erreurs[] = 'Session expirée, veuillez réessayer.';
    }
    if ($nom === '') {
        $erreurs[] = 'Le nom est obligatoire.';
    } elseif (mb_strlen($nom) > 100) {
        $erreurs[] = 'Le nom est trop long (100 caractères maximum).';
    }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Adresse email invalide.';
    }
    if (mb_strlen($password) < 8) {
        $erreurs[] = 'Le mot de passe doit contenir au moins 8 caractères.';
    }
    if ($password !== $confirm) {
        $erreurs[] = 'Les mots de passe ne correspondent pas.';
    }

    if (empty($erreurs)) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            $erreurs[] = 'Cet email est déjà utilisé.';
        }
    }

    if (empty($erreurs)) {
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (nom, email, password, is_admin) VALUES (:nom, :email, :password, 0)'
            );
            $stmt->execute([
                ':nom'      => $nom,
                ':email'    => $email,
                ':password' => hacher_mot_de_passe($pdo, $password),
            ]);
            $success = true;
        } catch (PDOException $e) {
            // Deux inscriptions simultanées avec le même email (contrainte UNIQUE)
            if ($e->getCode() === '23000') {
                $erreurs[] = 'Cet email est déjà utilisé.';
            } else {
                throw $e;
            }
        }
    }
}

$titre_page = 'Inscription';
include '../includes/header.php';
?>

<main class="auth-page">

    <!-- Volet visuel (masqué sur mobile) -->
    <section class="auth-visual" aria-hidden="true">
        <img src="/assets/images/food.jpg" alt=""
             onerror="this.onerror=null;this.src='/assets/images/thiep.jpg'">
        <div class="auth-visual-contenu">
            <h2>Rejoignez SenFood, régalez-vous en quelques clics.</h2>
            <ul>
                <li>Commandez en moins de deux minutes</li>
                <li>Suivez vos commandes en un coup d'œil</li>
                <li>Retrouvez les plats que vous aimez</li>
            </ul>
        </div>
    </section>

    <section class="auth-panel">
        <div class="auth-form-wrap">

        <?php if ($success): ?>

            <!-- Compte créé -->
            <div class="auth-succes">
                <span class="auth-succes-icone" aria-hidden="true">
                    <svg viewBox="0 0 24 24" width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <h1>Compte créé</h1>
                <p class="auth-subtitle">Bienvenue <?= htmlspecialchars($nom) ?> ! Vous pouvez maintenant vous connecter et commander vos plats préférés.</p>
                <a href="/auth/connexion.php" class="btn btn-primary btn-large btn-full">Se connecter</a>
            </div>

        <?php else: ?>

            <h1>Créer un compte</h1>
            <p class="auth-subtitle">Rejoignez SenFood et commandez vos plats préférés.</p>

            <?php if (!empty($erreurs)): ?>
                <div class="alert alert-error" role="alert">
                    <?php foreach ($erreurs as $e): ?>
                        <p><?= htmlspecialchars($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="form-inscription" novalidate>
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                <div class="form-group">
                    <label for="nom">Nom complet</label>
                    <input type="text" id="nom" name="nom" maxlength="100"
                           value="<?= htmlspecialchars($nom) ?>"
                           placeholder="Ex : Moussa Diallo"
                           autocomplete="name" autofocus required>
                    <span class="form-error" id="err-nom"></span>
                </div>

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($email) ?>"
                           placeholder="exemple@email.sn"
                           autocomplete="email" required>
                    <span class="form-error" id="err-email"></span>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                               placeholder="8 caractères minimum"
                               autocomplete="new-password" required>
                        <button type="button" class="toggle-password" data-cible="password"
                                aria-label="Afficher le mot de passe" aria-pressed="false"><?= icones_oeil() ?></button>
                    </div>
                    <div class="force" id="force" data-niveau="0" aria-hidden="true">
                        <span></span><span></span><span></span><span></span>
                    </div>
                    <span class="force-texte" id="force-texte" aria-live="polite"></span>
                    <span class="form-error" id="err-password"></span>
                </div>

                <div class="form-group">
                    <label for="confirm">Confirmer le mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm" name="confirm"
                               placeholder="Répétez votre mot de passe"
                               autocomplete="new-password" required>
                        <button type="button" class="toggle-password" data-cible="confirm"
                                aria-label="Afficher la confirmation" aria-pressed="false"><?= icones_oeil() ?></button>
                    </div>
                    <span class="form-error" id="err-confirm"></span>
                </div>

                <button type="submit" class="btn btn-primary btn-large btn-full">Créer mon compte</button>
            </form>

            <p class="auth-link">
                Déjà un compte ? <a href="/auth/connexion.php">Se connecter</a>
            </p>

        <?php endif; ?>

        </div>
    </section>

</main>

<?php include '../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
<script>
    (function () {
        // Afficher / masquer les mots de passe
        document.querySelectorAll('.toggle-password[data-cible]').forEach(function (bouton) {
            var champ = document.getElementById(bouton.dataset.cible);
            if (!champ) return;
            bouton.addEventListener('click', function () {
                var visible = champ.type === 'text';
                champ.type = visible ? 'password' : 'text';
                bouton.setAttribute('aria-pressed', visible ? 'false' : 'true');
                bouton.querySelector('.oeil').hidden = !visible;
                bouton.querySelector('.oeil-barre').hidden = visible;
            });
        });

        var mdp     = document.getElementById('password');
        var confirm = document.getElementById('confirm');
        if (!mdp || !confirm) return;

        // Indicateur de robustesse
        var barre  = document.getElementById('force');
        var texte  = document.getElementById('force-texte');
        var libelles = ['', 'Faible', 'Moyen', 'Bon', 'Excellent'];
        mdp.addEventListener('input', function () {
            var v = mdp.value, s = 0;
            if (v.length >= 8) s++;
            if (v.length >= 12) s++;
            if (/[a-z]/.test(v) && /[A-Z]/.test(v)) s++;
            if (/\d/.test(v)) s++;
            if (/[^A-Za-z0-9]/.test(v)) s++;
            var niveau = v ? Math.max(1, Math.min(s, 4)) : 0;
            if (v.length < 8) niveau = Math.min(niveau, 1);
            barre.dataset.niveau = niveau;
            texte.textContent = libelles[niveau];
            verifierConfirmation();
        });

        // Vérification en direct de la confirmation
        var erreurConfirm = document.getElementById('err-confirm');
        function verifierConfirmation() {
            if (!confirm.value) { erreurConfirm.textContent = ''; return; }
            erreurConfirm.textContent = confirm.value === mdp.value ? '' : 'Les mots de passe ne correspondent pas.';
        }
        confirm.addEventListener('input', verifierConfirmation);
    })();
</script>
</body>
</html>