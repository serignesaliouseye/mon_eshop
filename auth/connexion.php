<?php
// auth/connexion.php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/db.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $erreur = 'Veuillez remplir tous les champs.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email');
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && $user['password'] === md5($password)) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['is_admin'] = $user['is_admin'];

            if ($user['is_admin'] == 1) {
                header('Location: ../admin/dashboard.php');
            } else {
                header('Location: ../index.php');
            }
            exit;
        } else {
            $erreur = 'Email ou mot de passe incorrect.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion – SenFood</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <style>
        .password-wrapper {
            position: relative;
        }
        .password-wrapper input {
            padding-right: 3rem;
        }
        .toggle-password {
            position: absolute;
            right: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            color: var(--text-light);
            display: flex;
            align-items: center;
            transition: color 0.2s ease;
        }
        .toggle-password:hover { color: var(--primary-color); }
        .toggle-password svg   { width: 20px; height: 20px; }
    </style>
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="auth-container">
    <div class="auth-box">
        <h1>Se connecter</h1>
        <p class="auth-subtitle">Bon retour sur SenFood !</p>

        <?php if ($erreur): ?>
            <div class="alert alert-error">
                <p><?= htmlspecialchars($erreur) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="form-connexion" novalidate>

            <div class="form-group">
                <label for="email">Adresse email</label>
                <input type="email" id="email" name="email"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                       placeholder="exemple@email.sn" required>
                <span class="form-error" id="err-email"></span>
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password"
                           placeholder="Votre mot de passe" required>
                    <button type="button" class="toggle-password"
                            onclick="togglePassword('password', this)"
                            aria-label="Afficher/Masquer le mot de passe">
                        <?php echo oeil_ouvert(); ?>
                    </button>
                </div>
                <span class="form-error" id="err-password"></span>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                Se connecter
            </button>
        </form>

        <p class="auth-link">
            Pas encore de compte ? <a href="inscription.php">S'inscrire</a>
        </p>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
<script src="../assets/js/toggle-password.js"></script>
</body>
</html>

<?php
function oeil_ouvert() {
    return '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                 stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0
                         8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542
                         7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>';
}
?>