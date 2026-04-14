<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Resident']);

function residentIncidentStatusClass(string $status): string
{
    if ($status === 'Résolu') {
        return 'status-success';
    }
    if ($status === 'En cours') {
        return 'status-warning';
    }
    return 'status-danger';
}

function residentIncidentPriorityClass(string $priority): string
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
$residentId = (int) $_SESSION['user_id'];
$stmt = $pdo->prepare(
    "SELECT i.*, c.numero_chambre, tu.nom AS tech_nom, tu.prenom AS tech_prenom
     FROM incident i
     JOIN chambre c ON c.id_chambre = i.id_chambre
     LEFT JOIN technicien t ON t.id_technicien = i.id_technicien
     LEFT JOIN utilisateur tu ON tu.id_utilisateur = t.id_technicien
     WHERE i.id_resident = ?
     ORDER BY i.date_creation DESC"
);
$stmt->execute([$residentId]);
$incidents = $stmt->fetchAll();

renderPageStart('Mes incidents');
renderPrivateHeader('Mes incidents', 'Suivi des réclamations déclarées par le résident.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <?php renderFlashMessage(); ?>
        <section class="table-card">
            <div class="toolbar"><h2>Historique des incidents</h2><a class="btn btn-secondary" href="<?= e(appUrl('/resident/create_incident.php')) ?>">Nouvel incident</a></div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Titre</th><th>Chambre</th><th>Description</th><th>Priorité</th><th>Statut</th><th>Technicien</th><th>Date</th></tr></thead>
                    <tbody>
                    <?php if (!$incidents): ?>
                        <tr><td colspan="7" class="muted">Aucun incident trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($incidents as $incident): ?>
                            <tr>
                                <td><?= e($incident['titre_incident']) ?></td>
                                <td><?= e($incident['numero_chambre']) ?></td>
                                <td><?= e($incident['description_incident']) ?></td>
                                <td><span class="status <?= e(residentIncidentPriorityClass($incident['priorite_incident'])) ?>"><?= e($incident['priorite_incident']) ?></span></td>
                                <td><span class="status <?= e(residentIncidentStatusClass($incident['status_incident'])) ?>"><?= e($incident['status_incident']) ?></span></td>
                                <td>
                                    <?php if ($incident['tech_nom']): ?>
                                        <?= e($incident['tech_prenom'] . ' ' . $incident['tech_nom']) ?>
                                    <?php else: ?>
                                        <span class="muted">Non assigné</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($incident['date_creation']) ?></td>
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
