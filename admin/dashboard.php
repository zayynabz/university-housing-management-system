<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Admin']);

$pdo = getDB();
updateLatePayments($pdo);
$stats = [
    'residents' => (int) $pdo->query('SELECT COUNT(*) FROM resident')->fetchColumn(),
    'residences' => (int) $pdo->query('SELECT COUNT(*) FROM residence')->fetchColumn(),
    'chambres_libres' => (int) $pdo->query("SELECT COUNT(*) FROM chambre WHERE status_chambre = 'Libre'")->fetchColumn(),
    'paiements_retard' => (int) $pdo->query("SELECT COUNT(*) FROM paiement WHERE status_paiement = 'En retard'")->fetchColumn(),
    'incidents_attente' => (int) $pdo->query("SELECT COUNT(*) FROM incident WHERE status_incident = 'En attente'")->fetchColumn(),
];

$recentResidents = $pdo->query(
    "SELECT u.nom, u.prenom, res.numero_etudiant, res.filiere
     FROM resident res JOIN utilisateur u ON u.id_utilisateur = res.id_resident
     ORDER BY u.date_creation DESC LIMIT 5"
)->fetchAll();

renderPageStart('Dashboard administrateur');
renderPrivateHeader('Dashboard administrateur', 'Vue d\'ensemble de la résidence, des paiements et des incidents.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <?php renderFlashMessage(); ?>
        <section class="stats-grid">
            <article class="stat-card"><h3>Résidents</h3><p><?= $stats['residents'] ?></p></article>
            <article class="stat-card"><h3>Résidences</h3><p><?= $stats['residences'] ?></p></article>
            <article class="stat-card"><h3>Chambres libres</h3><p><?= $stats['chambres_libres'] ?></p></article>
            <article class="stat-card"><h3>Paiements en retard</h3><p><?= $stats['paiements_retard'] ?></p></article>
            <article class="stat-card"><h3>Incidents en attente</h3><p><?= $stats['incidents_attente'] ?></p></article>
        </section>

        <section class="dashboard-grid">
            <article class="dashboard-section">
                <h2>Gestion principale</h2>
                <p class="page-subtitle">Accès rapide aux opérations les plus importantes du système.</p>
                <div class="action-row">
                    <a class="btn btn-primary" href="<?= e(appUrl('/admin/add_resident.php')) ?>">Ajouter un résident</a>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/admin/assign_room.php')) ?>">Affecter une chambre</a>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/admin/paiements.php')) ?>">Suivre les paiements</a>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/admin/incidents.php')) ?>">Gérer les incidents</a>
                </div>
            </article>
            <article class="dashboard-section">
                <h2>Exports XML</h2>
                <p class="page-subtitle">Ajouté pour exploiter les notions XML du cours.</p>
                <div class="export-links">
                    <a class="btn btn-secondary" href="<?= e(appUrl('/admin/export_xml.php?type=residents')) ?>">Résidents XML</a>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/admin/export_xml.php?type=paiements')) ?>">Paiements XML</a>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/admin/export_xml.php?type=incidents')) ?>">Incidents XML</a>
                </div>
            </article>
        </section>

        <section class="table-card">
            <div class="toolbar"><h2>Derniers résidents ajoutés</h2><a class="btn btn-secondary" href="<?= e(appUrl('/admin/residents.php')) ?>">Voir tous les résidents</a></div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Nom</th><th>Numéro étudiant</th><th>Filière</th></tr></thead>
                    <tbody>
                    <?php if (!$recentResidents): ?>
                        <tr><td colspan="3" class="muted">Aucun résident enregistré.</td></tr>
                    <?php else: foreach ($recentResidents as $resident): ?>
                        <tr>
                            <td><?= e($resident['prenom'] . ' ' . $resident['nom']) ?></td>
                            <td><?= e($resident['numero_etudiant'] ?: '-') ?></td>
                            <td><?= e($resident['filiere'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
