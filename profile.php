<?php
require_once __DIR__ . '/config/auth_check.php';
require_once __DIR__ . '/config/ui.php';
checkRole(['Admin', 'Resident', 'Technicien']);

$pdo = getDB();
$userId = (int) $_SESSION['user_id'];
$error = '';
$success = '';

$stmt = $pdo->prepare('SELECT nom, prenom, email, telephone, role FROM utilisateur WHERE id_utilisateur = ?');
$stmt->execute([$userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlash('error', 'Utilisateur introuvable.');
    redirectTo(dashboardPathForRole(currentRole()));
}

if (isPostRequest()) {
    $telephone = trim($_POST['telephone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    try {
        $pdo->beginTransaction();
        $pdo->prepare('UPDATE utilisateur SET telephone = ? WHERE id_utilisateur = ?')->execute([$telephone !== '' ? $telephone : null, $userId]);

        if ($password !== '' || $confirm !== '') {
            if ($password !== $confirm) {
                throw new Exception('La confirmation du mot de passe est incorrecte.');
            }
            if (!isStrongPassword($password)) {
                throw new Exception('Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.');
            }
            $pdo->prepare('UPDATE utilisateur SET mot_de_passe = ? WHERE id_utilisateur = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
        }

        $pdo->commit();
        $_SESSION['user_nom'] = trim($user['prenom'] . ' ' . $user['nom']);
        $success = 'Profil mis à jour avec succès.';
        $stmt->execute([$userId]);
        $user = $stmt->fetch();
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $error = $exception->getMessage();
    }
}

renderPageStart('Profil');
renderPrivateHeader('Profil utilisateur', 'Coordonnées et mot de passe.');
?>
<main class="main-content">
    <div class="page-shell page-shell-narrow panel-stack">
        <?php if ($success !== ''): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
        <section class="card">
            <div class="toolbar">
                <h2>Informations</h2>
                <a class="btn btn-secondary" href="<?= e(appUrl(dashboardPathForRole(currentRole()))) ?>">Dashboard</a>
            </div>
            <div class="definition-list">
                <div class="definition-item"><strong>Nom complet</strong><span><?= e($user['prenom'] . ' ' . $user['nom']) ?></span></div>
                <div class="definition-item"><strong>Email</strong><span><?= e($user['email']) ?></span></div>
                <div class="definition-item"><strong>Rôle</strong><span><?= e($user['role']) ?></span></div>
            </div>
        </section>
        <section class="card">
            <h2>Mettre à jour</h2>
            <form method="post" class="stack-form" data-password-check>
                <div class="form-field">
                    <label for="telephone">Téléphone</label>
                    <input type="text" id="telephone" name="telephone" value="<?= e($user['telephone'] ?? '') ?>">
                </div>
                <div class="form-field">
                    <label for="password">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-field">
                    <label for="confirm_password">Confirmer le mot de passe</label>
                    <input type="password" id="confirm_password" name="confirm_password">
                </div>
                <div class="form-actions"><button type="submit">Enregistrer</button></div>
            </form>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
