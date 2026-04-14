<?php
require_once __DIR__ . '/../config/ui.php';

if (isset($_SESSION['user_id'], $_SESSION['user_role'])) {
    redirectTo(dashboardPathForRole($_SESSION['user_role']));
}

$error = '';
$email = trim($_POST['email'] ?? '');

if (isPostRequest()) {
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Veuillez remplir tous les champs.';
    } else {
        $pdo = getDB();
        updateLatePayments($pdo);

        $stmt = $pdo->prepare("SELECT * FROM utilisateur WHERE email = ? AND status_compte = 'Actif'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id_utilisateur'];
            $_SESSION['user_nom'] = trim($user['prenom'] . ' ' . $user['nom']);
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_email'] = $user['email'];
            redirectTo(dashboardPathForRole($user['role']));
        }

        $error = 'Email ou mot de passe incorrect.';
    }
}

renderPageStart('Connexion');
renderPublicHeader();
?>
<main class="main-content">
    <div class="page-shell page-shell-narrow auth-shell">
        <section class="auth-card">
            <h1>Connexion</h1>
            <p class="page-subtitle">Authentification sécurisée selon le rôle utilisateur.</p>
            <?php if ($error !== ''): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" class="stack-form">
                <div class="form-field">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?= e($email) ?>" required>
                </div>
                <div class="form-field">
                    <label for="password">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>
                <div class="form-actions">
                    <button type="submit">Se connecter</button>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/index.php')) ?>">Retour à l'accueil</a>
                </div>
            </form>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
