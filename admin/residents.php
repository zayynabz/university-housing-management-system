<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Admin']);

$pdo = getDB();
updateLatePayments($pdo);
$search = trim($_GET['q'] ?? '');

$sql = "SELECT u.id_utilisateur, u.nom, u.prenom, u.email, u.telephone, u.date_creation,
               res.numero_etudiant, res.filiere,
               c.numero_chambre, a.numero_appartement, r.nom_residence,
               o.id_occupation, o.date_debut, o.date_fin, o.status_occupation
        FROM utilisateur u
        JOIN resident res ON u.id_utilisateur = res.id_resident
        LEFT JOIN occupation o ON o.id_resident = res.id_resident AND o.status_occupation = 'En cours'
        LEFT JOIN chambre c ON c.id_chambre = o.id_chambre
        LEFT JOIN appartement a ON a.id_appartement = c.id_appartement
        LEFT JOIN residence r ON r.id_residence = a.id_residence
        WHERE u.role = 'Resident'";
$params = [];
if ($search !== '') {
    $sql .= ' AND (u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? OR res.filiere LIKE ?)';
    $term = '%' . $search . '%';
    $params = [$term, $term, $term, $term];
}
$sql .= ' ORDER BY u.nom, u.prenom';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$residents = $stmt->fetchAll();

renderPageStart('Résidents');
renderPrivateHeader('Résidents', 'Liste complète des résidents et de leurs affectations actives.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <?php renderFlashMessage(); ?>
        <section class="table-card">
            <div class="toolbar">
                <h2>Liste des résidents</h2>
                <div class="action-row">
                    <input class="search-input" type="search" value="<?= e($search) ?>" placeholder="Rechercher" data-table-filter="residents-table">
                    <a class="btn btn-primary" href="<?= e(appUrl('/admin/add_resident.php')) ?>">Ajouter</a>
                </div>
            </div>
            <div class="table-scroll">
                <table id="residents-table">
                    <thead><tr><th>Nom</th><th>Email</th><th>Téléphone</th><th>Numéro étudiant</th><th>Filière</th><th>Chambre</th><th>Résidence</th><th>Contrat</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (!$residents): ?>
                        <tr><td colspan="9" class="muted">Aucun résident trouvé.</td></tr>
                    <?php else: foreach ($residents as $resident): ?>
                        <tr>
                            <td><?= e($resident['prenom'] . ' ' . $resident['nom']) ?></td>
                            <td><?= e($resident['email']) ?></td>
                            <td><?= e($resident['telephone'] ?: '-') ?></td>
                            <td><?= e($resident['numero_etudiant'] ?: '-') ?></td>
                            <td><?= e($resident['filiere'] ?: '-') ?></td>
                            <td><?= $resident['numero_chambre'] ? e($resident['numero_chambre'] . ' / App. ' . $resident['numero_appartement']) : '<span class="muted">Aucune</span>' ?></td>
                            <td><?= e($resident['nom_residence'] ?: '-') ?></td>
                            <td><?= $resident['date_debut'] ? e('Début : ' . $resident['date_debut']) : '-' ?></td>
                            <td>
                                <?php if (!empty($resident['id_occupation'])): ?>
                                    <form method="post" action="<?= e(appUrl('/admin/end_occupation.php')) ?>">
                                        <input type="hidden" name="occupation_id" value="<?= (int) $resident['id_occupation'] ?>">
                                        <button type="submit" data-confirm="Confirmer la résiliation du contrat et la libération de la chambre ?">Résilier</button>
                                    </form>
                                <?php else: ?>
                                    <span class="muted">Aucune action</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
