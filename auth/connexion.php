<?php
// auth/connexion.php
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

const MAX_TENTATIVES = 5;
const DUREE_BLOCAGE  = 300; // secondes

// Accepte les mots de passe hachés avec password_hash() et les anciens hachages md5
function mot_de_passe_valide(string $saisi, string $stocke): bool {
    if (!empty(password_get_info($stocke)['algo'])) {
        return password_verify($saisi, $stocke);
    }
    return hash_equals($stocke, md5($saisi));
}

$erreur = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $erreur = 'Session expirée, veuillez réessayer.';

    } elseif (($_SESSION['blocage_jusqua'] ?? 0) > time()) {
        $minutes = (int) ceil(($_SESSION['blocage_jusqua'] - time()) / 60);
        $erreur  = "Trop de tentatives. Réessayez dans $minutes minute(s).";

    } elseif ($email === '' || $password === '') {
        $erreur = 'Veuillez remplir tous les champs.';

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = "Cette adresse email n'est pas valide.";

    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && mot_de_passe_valide($password, (string) $user['password'])) {
            session_regenerate_id(true); // protège contre le vol de session
            unset($_SESSION['tentatives'], $_SESSION['blocage_jusqua']);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['is_admin'] = (int) $user['is_admin'];

            header('Location: ' . ($user['is_admin'] == 1 ? '/admin/dashboard.php' : '/index.php'));
            exit;
        }

        // Échec : on compte les tentatives
        $_SESSION['tentatives'] = ($_SESSION['tentatives'] ?? 0) + 1;
        if ($_SESSION['tentatives'] >= MAX_TENTATIVES) {
            $_SESSION['blocage_jusqua'] = time() + DUREE_BLOCAGE;
            $_SESSION['tentatives']     = 0;
        }
        $erreur = 'Email ou mot de passe incorrect.';
    }
}

$titre_page = 'Connexion';
include '../includes/header.php';
?>

<main class="auth-page">

    <!-- Volet visuel (masqué sur mobile) -->
    <section class="auth-visual" aria-hidden="true">
        <img src="/assets/images/food.jpg" alt=""
             onerror="this.onerror=null;this.src='/assets/images/thiep.jpg'">
        <div class="auth-visual-contenu">
            <h2>Le goût du Sénégal, à votre porte.</h2>
            <ul>
                <li>Plats préparés le jour même</li>
                <li>Livraison en moins de 45 minutes</li>
                <li>Paiement en ligne ou à la livraison</li>
            </ul>
        </div>
    </section>

    <!-- Formulaire -->
    <section class="auth-panel">
        <div class="auth-form-wrap">
            <h1>Se connecter</h1>
            <p class="auth-subtitle">Heureux de vous revoir. Connectez-vous pour commander.</p>

            <?php if ($erreur): ?>
                <div class="alert alert-error" role="alert">
                    <p><?= htmlspecialchars($erreur) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" action="" id="form-connexion" novalidate>
                <input type="hidden" name="csrf" value="<?= $_SESSION['csrf'] ?>">

                <div class="form-group">
                    <label for="email">Adresse email</label>
                    <input type="email" id="email" name="email"
                           value="<?= htmlspecialchars($email) ?>"
                           placeholder="exemple@email.sn"
                           autocomplete="email" autofocus required>
                    <span class="form-error" id="err-email"></span>
                </div>

                <div class="form-group">
                    <label for="password">Mot de passe</label>
                    <div class="password-wrapper">
                        <input type="password" id="password" name="password"
                               placeholder="Votre mot de passe"
                               autocomplete="current-password" required>
                        <button type="button" class="toggle-password" id="toggle-password"
                                aria-label="Afficher le mot de passe" aria-pressed="false">
                            <svg class="oeil" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg class="oeil-barre" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" hidden>
                                <path d="M17.94 17.94A10.1 10.1 0 0 1 12 19C5.6 19 2 12 2 12a18 18 0 0 1 4.06-5.06M9.9 5.1A9.7 9.7 0 0 1 12 5c6.4 0 10 7 10 7a18 18 0 0 1-2.16 3.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="m2 2 20 20"/>
                            </svg>
                        </button>
                    </div>
                    <span class="form-error" id="err-password"></span>
                </div>

                <button type="submit" class="btn btn-primary btn-large btn-full">Se connecter</button>
            </form>

            <p class="auth-link">
                Pas encore de compte ? <a href="/auth/inscription.php">Créer un compte</a>
            </p>
        </div>
    </section>

</main>

<?php include '../includes/footer.php'; ?>
<script src="/assets/js/main.js"></script>
<script>
    // Afficher / masquer le mot de passe
    (function () {
        var bouton = document.getElementById('toggle-password');
        var champ  = document.getElementById('password');
        if (!bouton || !champ) return;
        bouton.addEventListener('click', function () {
            var visible = champ.type === 'text';
            champ.type = visible ? 'password' : 'text';
            bouton.setAttribute('aria-pressed', visible ? 'false' : 'true');
            bouton.setAttribute('aria-label', visible ? 'Afficher le mot de passe' : 'Masquer le mot de passe');
            bouton.querySelector('.oeil').hidden = !visible;
            bouton.querySelector('.oeil-barre').hidden = visible;
        });
    })();
</script>
</body>
</html>