<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Resident']);

$pdo = getDB();
updateLatePayments($pdo);
$residentId = (int) $_SESSION['user_id'];
$occupation = activeOccupationForResident($pdo, $residentId);

$incidentStmt = $pdo->prepare('SELECT COUNT(*) FROM incident WHERE id_resident = ?');
$incidentStmt->execute([$residentId]);
$incidentCount = (int) $incidentStmt->fetchColumn();

$paymentStmt = $pdo->prepare(
    "SELECT COUNT(*) FROM paiement p JOIN occupation o ON o.id_occupation = p.id_occupation WHERE o.id_resident = ? AND p.status_paiement <> 'Payé'"
);
$paymentStmt->execute([$residentId]);
$pendingPayments = (int) $paymentStmt->fetchColumn();

$userStmt = $pdo->prepare('SELECT nom, prenom, email FROM utilisateur WHERE id_utilisateur = ?');
$userStmt->execute([$residentId]);
$user = $userStmt->fetch();

renderPageStart('Dashboard résident');
renderPrivateHeader('Espace résident', 'Tableau de bord personnel : chambre, paiements et incidents.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <?php renderFlashMessage(); ?>
        <section class="stats-grid">
            <article class="stat-card"><h3>Mes incidents</h3><p><?= $incidentCount ?></p></article>
            <article class="stat-card"><h3>Paiements à régler</h3><p><?= $pendingPayments ?></p></article>
            <article class="stat-card"><h3>Ma chambre</h3><p><?= e($occupation['numero_chambre'] ?? '-') ?></p></article>
        </section>
        <section class="split-layout">
            <article class="dashboard-section">
                <h2>Actions rapides</h2>
                <div class="action-row">
                    <a class="btn btn-primary" href="<?= e(appUrl('/resident/room.php')) ?>">Voir ma chambre</a>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/resident/payments.php')) ?>">Consulter mes paiements</a>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/resident/create_incident.php')) ?>">Signaler un incident</a>
                </div>
            </article>
            <article class="dashboard-section">
                <h2>Profil</h2>
                <div class="definition-list">
                    <div class="definition-item"><strong>Nom</strong><span><?= e($user['prenom'] . ' ' . $user['nom']) ?></span></div>
                    <div class="definition-item"><strong>Email</strong><span><?= e($user['email']) ?></span></div>
                    <div class="definition-item"><strong>Résidence</strong><span><?= e($occupation['nom_residence'] ?? 'Aucune affectation') ?></span></div>
                </div>
            </article>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
