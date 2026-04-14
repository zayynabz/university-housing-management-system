<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Admin']);

$pdo = getDB();
$error = '';
$form = [
    'nom' => trim($_POST['nom'] ?? ''),
    'prenom' => trim($_POST['prenom'] ?? ''),
    'email' => trim($_POST['email'] ?? ''),
    'telephone' => trim($_POST['telephone'] ?? ''),
    'numero_etudiant' => trim($_POST['numero_etudiant'] ?? ''),
    'date_naissance' => trim($_POST['date_naissance'] ?? ''),
    'filiere' => trim($_POST['filiere'] ?? ''),
];

if (isPostRequest()) {
    $password = $_POST['password'] ?? '';

    if ($form['nom'] === '' || $form['prenom'] === '' || $form['email'] === '' || $password === '') {
        $error = 'Les champs nom, prénom, email et mot de passe sont obligatoires.';
    } elseif (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Adresse email invalide.';
    } elseif (!isStrongPassword($password)) {
        $error = 'Le mot de passe doit contenir au moins 8 caractères, une majuscule, un chiffre et un caractère spécial.';
    } else {
        try {
            $pdo->beginTransaction();

            $stmtUser = $pdo->prepare(
                "INSERT INTO utilisateur (nom, prenom, email, mot_de_passe, telephone, role)
                 VALUES (?, ?, ?, ?, ?, 'Resident')"
            );
            $stmtUser->execute([
                $form['nom'],
                $form['prenom'],
                $form['email'],
                password_hash($password, PASSWORD_DEFAULT),
                $form['telephone'] !== '' ? $form['telephone'] : null,
            ]);

            $userId = (int) $pdo->lastInsertId();

            $stmtResident = $pdo->prepare(
                'INSERT INTO resident (id_resident, numero_etudiant, date_naissance, filiere) VALUES (?, ?, ?, ?)'
            );
            $stmtResident->execute([
                $userId,
                $form['numero_etudiant'] !== '' ? $form['numero_etudiant'] : null,
                $form['date_naissance'] !== '' ? $form['date_naissance'] : null,
                $form['filiere'] !== '' ? $form['filiere'] : null,
            ]);

            $pdo->commit();
            setFlash('success', 'Le résident a été ajouté avec succès.');
            redirectTo('/admin/dashboard.php');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = 'Impossible d\'ajouter le résident : ' . $exception->getMessage();
        }
    }
}

renderPageStart('Ajouter un résident');
renderPrivateHeader('Ajouter un résident', 'Créer un compte résident puis revenir directement au dashboard.');
?>
<main class="main-content">
    <div class="page-shell page-shell-narrow panel-stack">
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <section class="card">
            <div class="toolbar">
                <h2>Nouveau résident</h2>
                <div class="action-row">
                    <a class="btn btn-secondary" href="<?= e(appUrl('/admin/dashboard.php')) ?>">Dashboard</a>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/admin/residents.php')) ?>">Résidents</a>
                </div>
            </div>
            <form method="post" class="stack-form" data-password-check>
                <div class="form-grid">
                    <div class="form-field"><label for="nom">Nom</label><input type="text" id="nom" name="nom" value="<?= e($form['nom']) ?>" required></div>
                    <div class="form-field"><label for="prenom">Prénom</label><input type="text" id="prenom" name="prenom" value="<?= e($form['prenom']) ?>" required></div>
                    <div class="form-field"><label for="email">Email</label><input type="email" id="email" name="email" value="<?= e($form['email']) ?>" required></div>
                    <div class="form-field"><label for="telephone">Téléphone</label><input type="text" id="telephone" name="telephone" value="<?= e($form['telephone']) ?>"></div>
                    <div class="form-field"><label for="password">Mot de passe</label><input type="password" id="password" name="password" required></div>
                    <div class="form-field"><label for="numero_etudiant">Numéro étudiant</label><input type="number" id="numero_etudiant" name="numero_etudiant" value="<?= e($form['numero_etudiant']) ?>"></div>
                    <div class="form-field"><label for="date_naissance">Date de naissance</label><input type="date" id="date_naissance" name="date_naissance" value="<?= e($form['date_naissance']) ?>"></div>
                    <div class="form-field"><label for="filiere">Filière</label><input type="text" id="filiere" name="filiere" value="<?= e($form['filiere']) ?>"></div>
                </div>
                <div class="form-actions"><button type="submit">Enregistrer</button></div>
            </form>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
