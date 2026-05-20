<?php
// auth/inscription.php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

require_once '../config/db.php';

$erreurs = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom      = trim($_POST['nom']      ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm  = trim($_POST['confirm']  ?? '');

    if (empty($nom)) {
        $erreurs[] = 'Le nom est obligatoire.';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreurs[] = 'Adresse email invalide.';
    }
    if (strlen($password) < 8) {
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
        // Utiliser MD5 pour rester cohérent avec la connexion
        $hash = md5($password);
        $stmt = $pdo->prepare(
            'INSERT INTO users (nom, email, password) VALUES (:nom, :email, :password)'
        );
        $stmt->execute([
            ':nom'      => $nom,
            ':email'    => $email,
            ':password' => $hash
        ]);
        $success = 'Compte créé avec succès ! Vous pouvez vous connecter.';
    }
}

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
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription – SenFood</title>
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
        <h1>Créer un compte</h1>
        <p class="auth-subtitle">Rejoignez SenFood et commandez vos plats préférés</p>

        <?php if (!empty($erreurs)): ?>
            <div class="alert alert-error">
                <?php foreach ($erreurs as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                <p><?= htmlspecialchars($success) ?></p>
                <a href="connexion.php">Se connecter →</a>
            </div>
        <?php endif; ?>

        <form method="POST" action="" id="form-inscription" novalidate>

            <div class="form-group">
                <label for="nom">Nom complet</label>
                <input type="text" id="nom" name="nom"
                       value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>"
                       placeholder="Ex : Moussa Diallo" required>
                <span class="form-error" id="err-nom"></span>
            </div>

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
                           placeholder="8 caractères minimum" required>
                    <button type="button" class="toggle-password"
                            onclick="togglePassword('password', this)"
                            aria-label="Afficher/Masquer le mot de passe">
                        <?php echo oeil_ouvert(); ?>
                    </button>
                </div>
                <span class="form-error" id="err-password"></span>
            </div>

            <div class="form-group">
                <label for="confirm">Confirmer le mot de passe</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm" name="confirm"
                           placeholder="Répétez votre mot de passe" required>
                    <button type="button" class="toggle-password"
                            onclick="togglePassword('confirm', this)"
                            aria-label="Afficher/Masquer la confirmation">
                        <?php echo oeil_ouvert(); ?>
                    </button>
                </div>
                <span class="form-error" id="err-confirm"></span>
            </div>

            <button type="submit" class="btn btn-primary btn-full">
                Créer mon compte
            </button>
        </form>

        <p class="auth-link">
            Déjà un compte ? <a href="connexion.php">Se connecter</a>
        </p>
    </div>
</main>

<?php include '../includes/footer.php'; ?>
<script src="../assets/js/main.js"></script>
<script src="../assets/js/toggle-password.js"></script>
</body>
</html>