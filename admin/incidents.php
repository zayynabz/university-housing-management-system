<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Admin']);

function incidentStatusClass(string $status): string
{
    if ($status === 'Résolu') {
        return 'status-success';
    }
    if ($status === 'En cours') {
        return 'status-warning';
    }
    return 'status-danger';
}

function incidentPriorityClass(string $priority): string
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
$error = '';

if (isPostRequest() && isset($_POST['assign_incident'])) {
    $incidentId = (int) ($_POST['incident_id'] ?? 0);
    $technicienId = (int) ($_POST['technicien_id'] ?? 0);

    if ($incidentId <= 0 || $technicienId <= 0) {
        $error = 'Données invalides.';
    } else {
        try {
            $pdo->beginTransaction();

            $incidentStmt = $pdo->prepare(
                'SELECT id_technicien, status_incident FROM incident WHERE id_incident = ? FOR UPDATE'
            );
            $incidentStmt->execute([$incidentId]);
            $incident = $incidentStmt->fetch();

            if (!$incident) {
                throw new Exception('Incident introuvable.');
            }
            if ($incident['status_incident'] === 'Résolu') {
                throw new Exception('Cet incident est déjà résolu.');
            }
            if (!technicianExists($pdo, $technicienId)) {
                throw new Exception('Technicien introuvable.');
            }

            $previousTechId = empty($incident['id_technicien']) ? 0 : (int) $incident['id_technicien'];

            $updateIncident = $pdo->prepare(
                "UPDATE incident
                 SET id_technicien = ?, status_incident = 'En cours'
                 WHERE id_incident = ?"
            );
            $updateIncident->execute([$technicienId, $incidentId]);

            $updateTech = $pdo->prepare("UPDATE technicien SET disponibilite = 'Occupé' WHERE id_technicien = ?");
            $updateTech->execute([$technicienId]);

            if ($previousTechId > 0 && $previousTechId !== $technicienId) {
                syncTechnicianAvailability($pdo, $previousTechId);
            }

            $pdo->commit();
            setFlash('success', 'Incident assigné avec succès.');
            redirectTo('/admin/incidents.php');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $exception->getMessage();
        }
    }
}

$techniciens = $pdo->query(
    "SELECT t.id_technicien, t.specialite, t.disponibilite, u.nom, u.prenom
     FROM technicien t
     JOIN utilisateur u ON u.id_utilisateur = t.id_technicien
     ORDER BY CASE t.disponibilite WHEN 'Disponible' THEN 0 ELSE 1 END, u.nom, u.prenom"
)->fetchAll();

$incidents = $pdo->query(
    "SELECT i.id_incident, i.titre_incident, i.description_incident, i.status_incident, i.priorite_incident, i.date_creation,
            i.id_technicien, ru.nom AS resident_nom, ru.prenom AS resident_prenom, c.numero_chambre,
            tu.nom AS tech_nom, tu.prenom AS tech_prenom
     FROM incident i
     JOIN utilisateur ru ON ru.id_utilisateur = i.id_resident
     JOIN chambre c ON c.id_chambre = i.id_chambre
     LEFT JOIN technicien tt ON tt.id_technicien = i.id_technicien
     LEFT JOIN utilisateur tu ON tu.id_utilisateur = tt.id_technicien
     ORDER BY FIELD(i.status_incident, 'En attente', 'En cours', 'Résolu'),
              FIELD(i.priorite_incident, 'Haute', 'Moyenne', 'Faible'),
              i.date_creation DESC"
)->fetchAll();

renderPageStart('Incidents');
renderPrivateHeader('Incidents', 'Assignation des incidents aux techniciens et suivi de résolution.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <?php renderFlashMessage(); ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <section class="table-card">
            <div class="toolbar">
                <h2>Liste des incidents</h2>
                <a class="btn btn-secondary" href="<?= e(appUrl('/admin/export_xml.php?type=incidents')) ?>">Exporter XML</a>
            </div>
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Résident</th>
                            <th>Chambre</th>
                            <th>Titre</th>
                            <th>Description</th>
                            <th>Priorité</th>
                            <th>Statut</th>
                            <th>Technicien</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (!$incidents): ?>
                        <tr><td colspan="9" class="muted">Aucun incident trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($incidents as $incident): ?>
                            <tr>
                                <td><?= (int) $incident['id_incident'] ?></td>
                                <td><?= e($incident['resident_prenom'] . ' ' . $incident['resident_nom']) ?></td>
                                <td><?= e($incident['numero_chambre']) ?></td>
                                <td><?= e($incident['titre_incident']) ?></td>
                                <td><?= e(shortenText((string) $incident['description_incident'], 80)) ?></td>
                                <td><span class="status <?= e(incidentPriorityClass($incident['priorite_incident'])) ?>"><?= e($incident['priorite_incident']) ?></span></td>
                                <td><span class="status <?= e(incidentStatusClass($incident['status_incident'])) ?>"><?= e($incident['status_incident']) ?></span></td>
                                <td>
                                    <?php if ($incident['tech_nom']): ?>
                                        <?= e($incident['tech_prenom'] . ' ' . $incident['tech_nom']) ?>
                                    <?php else: ?>
                                        <span class="muted">Non assigné</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($incident['status_incident'] === 'Résolu'): ?>
                                        <span class="muted">Clôturé</span>
                                    <?php else: ?>
                                        <form method="post" class="inline-actions">
                                            <input type="hidden" name="incident_id" value="<?= (int) $incident['id_incident'] ?>">
                                            <select name="technicien_id" required>
                                                <option value="">Choisir</option>
                                                <?php foreach ($techniciens as $technicien): ?>
                                                    <option value="<?= (int) $technicien['id_technicien'] ?>" <?= (int) $technicien['id_technicien'] === (int) $incident['id_technicien'] ? 'selected' : '' ?>>
                                                        <?= e($technicien['prenom'] . ' ' . $technicien['nom'] . ' - ' . $technicien['specialite']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" name="assign_incident">Assigner</button>
                                        </form>
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
