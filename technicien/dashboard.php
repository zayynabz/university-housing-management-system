<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Technicien']);

function technicianPriorityClass(string $priority): string
{
    if ($priority === 'Haute') {
        return 'status-danger';
    }
    if ($priority === 'Moyenne') {
        return 'status-warning';
    }
    return 'status-success';
}

$pdo = getDB();
$techId = (int) $_SESSION['user_id'];
$techStmt = $pdo->prepare(
    "SELECT t.specialite, t.disponibilite, u.nom, u.prenom
     FROM technicien t
     JOIN utilisateur u ON u.id_utilisateur = t.id_technicien
     WHERE t.id_technicien = ?"
);
$techStmt->execute([$techId]);
$technicien = $techStmt->fetch();

if (!$technicien) {
    setFlash('error', 'Technicien introuvable.');
    redirectTo('/auth/login.php');
}

$availableIncidents = $pdo->query(
    "SELECT i.id_incident, i.titre_incident, i.description_incident, i.priorite_incident, i.status_incident, i.date_creation,
            u.nom AS resident_nom, u.prenom AS resident_prenom
     FROM incident i
     JOIN utilisateur u ON u.id_utilisateur = i.id_resident
     WHERE i.id_technicien IS NULL
       AND i.status_incident = 'En attente'
     ORDER BY FIELD(i.priorite_incident, 'Haute', 'Moyenne', 'Faible'), i.date_creation DESC"
)->fetchAll();

$myActiveIncidents = activeIncidentCountForTechnician($pdo, $techId);

renderPageStart('Dashboard technicien');
renderPrivateHeader('Dashboard technicien', 'Incidents disponibles et interventions en cours.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <?php renderFlashMessage(); ?>
        <section class="stats-grid">
            <article class="stat-card"><h3>Disponibilité</h3><p><?= e($technicien['disponibilite']) ?></p></article>
            <article class="stat-card"><h3>Spécialité</h3><p style="font-size:1.2rem;"><?= e($technicien['specialite']) ?></p></article>
            <article class="stat-card"><h3>Incidents actifs</h3><p><?= $myActiveIncidents ?></p></article>
            <article class="stat-card"><h3>Incidents disponibles</h3><p><?= count($availableIncidents) ?></p></article>
        </section>
        <section class="table-card">
            <div class="toolbar"><h2>Incidents à prendre en charge</h2><a class="btn btn-secondary" href="<?= e(appUrl('/technicien/view_incidents.php')) ?>">Mes incidents</a></div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>#</th><th>Résident</th><th>Titre</th><th>Description</th><th>Priorité</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (!$availableIncidents): ?>
                        <tr><td colspan="7" class="muted">Aucun incident disponible.</td></tr>
                    <?php else: ?>
                        <?php foreach ($availableIncidents as $incident): ?>
                            <tr>
                                <td><?= (int) $incident['id_incident'] ?></td>
                                <td><?= e($incident['resident_prenom'] . ' ' . $incident['resident_nom']) ?></td>
                                <td><?= e($incident['titre_incident']) ?></td>
                                <td><?= e(shortenText($incident['description_incident'], 100)) ?></td>
                                <td><span class="status <?= e(technicianPriorityClass($incident['priorite_incident'])) ?>"><?= e($incident['priorite_incident']) ?></span></td>
                                <td><?= e($incident['date_creation']) ?></td>
                                <td>
                                    <form method="post" action="<?= e(appUrl('/technicien/assign_incident.php')) ?>">
                                        <input type="hidden" name="id" value="<?= (int) $incident['id_incident'] ?>">
                                        <button type="submit">Prendre en charge</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
