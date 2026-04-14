<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Resident']);

$pdo = getDB();
$residentId = (int) $_SESSION['user_id'];
$error = '';
$form = [
    'titre' => trim($_POST['titre'] ?? ''),
    'description' => trim($_POST['description'] ?? ''),
    'priorite' => $_POST['priorite'] ?? 'Moyenne',
];
$currentRoom = activeOccupationForResident($pdo, $residentId);

if (isPostRequest()) {
    if (!$currentRoom) {
        $error = 'Aucune chambre active n\'est assignée à votre compte.';
    } elseif ($form['titre'] === '' || $form['description'] === '') {
        $error = 'Veuillez remplir tous les champs.';
    } elseif (!in_array($form['priorite'], ['Faible', 'Moyenne', 'Haute'], true)) {
        $error = 'Priorité invalide.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO incident (id_resident, id_chambre, titre_incident, description_incident, status_incident, priorite_incident)
             VALUES (?, ?, ?, ?, 'En attente', ?)"
        );
        $stmt->execute([$residentId, $currentRoom['id_chambre'], $form['titre'], $form['description'], $form['priorite']]);
        setFlash('success', 'Incident enregistré avec succès.');
        redirectTo('/resident/dashboard.php');
    }
}

renderPageStart('Nouvel incident');
renderPrivateHeader('Signaler un incident', 'Après enregistrement, retour direct au dashboard.');
?>
<main class="main-content">
    <div class="page-shell page-shell-narrow panel-stack">
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <section class="card">
            <div class="toolbar">
                <h2>Nouveau signalement</h2>
                <div class="action-row">
                    <a class="btn btn-secondary" href="<?= e(appUrl('/resident/dashboard.php')) ?>">Dashboard</a>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/resident/view_incidents.php')) ?>">Mes incidents</a>
                </div>
            </div>
            <?php if (!$currentRoom): ?>
                <div class="empty-state">Vous devez avoir une chambre active pour créer un incident.</div>
            <?php else: ?>
                <p class="small-note">Chambre concernée : <strong><?= e($currentRoom['numero_chambre']) ?></strong></p>
                <form method="post" class="stack-form">
                    <div class="form-field"><label for="titre">Titre</label><input type="text" id="titre" name="titre" value="<?= e($form['titre']) ?>" required></div>
                    <div class="form-field"><label for="description">Description</label><textarea id="description" name="description" required><?= e($form['description']) ?></textarea></div>
                    <div class="form-field"><label for="priorite">Priorité</label><select id="priorite" name="priorite"><?php foreach (['Faible', 'Moyenne', 'Haute'] as $priority): ?><option value="<?= e($priority) ?>" <?= $form['priorite'] === $priority ? 'selected' : '' ?>><?= e($priority) ?></option><?php endforeach; ?></select></div>
                    <div class="form-actions"><button type="submit">Envoyer</button></div>
                </form>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
