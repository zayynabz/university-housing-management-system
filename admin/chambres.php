<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Admin']);

$pdo = getDB();
$residences = $pdo->query('SELECT id_residence, nom_residence FROM residence ORDER BY nom_residence')->fetchAll();
$selectedResidence = trim($_GET['residence'] ?? '');

$sql = "SELECT c.id_chambre, c.numero_chambre, c.status_chambre, c.loyer_mensuel,
               ap.numero_appartement, ap.etage, r.nom_residence,
               u.nom AS occupant_nom, u.prenom AS occupant_prenom
        FROM chambre c
        JOIN appartement ap ON ap.id_appartement = c.id_appartement
        JOIN residence r ON r.id_residence = ap.id_residence
        LEFT JOIN occupation o ON o.id_chambre = c.id_chambre AND o.status_occupation = 'En cours'
        LEFT JOIN utilisateur u ON u.id_utilisateur = o.id_resident";
$params = [];
if ($selectedResidence !== '') {
    $sql .= ' WHERE r.id_residence = ?';
    $params[] = $selectedResidence;
}
$sql .= ' ORDER BY r.nom_residence, ap.etage, ap.numero_appartement, c.numero_chambre';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

renderPageStart('Chambres');
renderPrivateHeader('Chambres', 'Statut détaillé des chambres, loyers et occupants actuels.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <section class="table-card">
            <div class="toolbar">
                <h2>Liste des chambres</h2>
                <form method="get" class="inline-actions">
                    <select name="residence">
                        <option value="">Toutes les résidences</option>
                        <?php foreach ($residences as $residence): ?>
                            <option value="<?= (int) $residence['id_residence'] ?>" <?= $selectedResidence == $residence['id_residence'] ? 'selected' : '' ?>><?= e($residence['nom_residence']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit">Filtrer</button>
                </form>
            </div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Chambre</th><th>Statut</th><th>Loyer</th><th>Appartement</th><th>Étage</th><th>Résidence</th><th>Occupant</th></tr></thead>
                    <tbody>
                    <?php if (!$rooms): ?>
                        <tr><td colspan="7" class="muted">Aucune chambre trouvée.</td></tr>
                    <?php else: foreach ($rooms as $room): ?>
                        <tr>
                            <td><?= e($room['numero_chambre']) ?></td>
                            <td><span class="status <?= $room['status_chambre'] === 'Libre' ? 'status-success' : 'status-warning' ?>"><?= e($room['status_chambre']) ?></span></td>
                            <td><?= e(formatMoney($room['loyer_mensuel'])) ?></td>
                            <td><?= e($room['numero_appartement']) ?></td>
                            <td><?= e($room['etage']) ?></td>
                            <td><?= e($room['nom_residence']) ?></td>
                            <td><?= $room['occupant_nom'] ? e($room['occupant_prenom'] . ' ' . $room['occupant_nom']) : '<span class="muted">Aucun</span>' ?></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
