<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Technicien']);

function technicianIncidentStatusClass(string $status): string
{
    if ($status === 'Résolu') {
        return 'status-success';
    }
    if ($status === 'En cours') {
        return 'status-warning';
    }
    return 'status-danger';
}

function technicianIncidentPriorityClass(string $priority): string
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
$stmt = $pdo->prepare(
    "SELECT i.id_incident, i.titre_incident, i.description_incident, i.priorite_incident, i.status_incident, i.date_creation,
            u.nom AS resident_nom, u.prenom AS resident_prenom
     FROM incident i
     JOIN utilisateur u ON u.id_utilisateur = i.id_resident
     WHERE i.id_technicien = ?
     ORDER BY FIELD(i.status_incident, 'En cours', 'En attente', 'Résolu'),
              FIELD(i.priorite_incident, 'Haute', 'Moyenne', 'Faible'),
              i.date_creation DESC"
);
$stmt->execute([$techId]);
$incidents = $stmt->fetchAll();

renderPageStart('Mes incidents');
renderPrivateHeader('Mes incidents', 'Liste des incidents assignés au technicien.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <?php renderFlashMessage(); ?>
        <section class="table-card">
            <div class="toolbar"><h2>Incidents assignés</h2></div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>#</th><th>Résident</th><th>Titre</th><th>Description</th><th>Priorité</th><th>Statut</th><th>Date</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (!$incidents): ?>
                        <tr><td colspan="8" class="muted">Aucun incident assigné.</td></tr>
                    <?php else: ?>
                        <?php foreach ($incidents as $incident): ?>
                            <tr>
                                <td><?= (int) $incident['id_incident'] ?></td>
                                <td><?= e($incident['resident_prenom'] . ' ' . $incident['resident_nom']) ?></td>
                                <td><?= e($incident['titre_incident']) ?></td>
                                <td><?= e(shortenText($incident['description_incident'], 100)) ?></td>
                                <td><span class="status <?= e(technicianIncidentPriorityClass($incident['priorite_incident'])) ?>"><?= e($incident['priorite_incident']) ?></span></td>
                                <td><span class="status <?= e(technicianIncidentStatusClass($incident['status_incident'])) ?>"><?= e($incident['status_incident']) ?></span></td>
                                <td><?= e($incident['date_creation']) ?></td>
                                <td>
                                    <?php if ($incident['status_incident'] !== 'Résolu'): ?>
                                        <form method="post" action="<?= e(appUrl('/technicien/resolve_incident.php')) ?>">
                                            <input type="hidden" name="id" value="<?= (int) $incident['id_incident'] ?>">
                                            <button type="submit">Marquer résolu</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="muted">Terminé</span>
                                    <?php endif; ?>
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
