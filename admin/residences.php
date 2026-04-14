<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Admin']);

$pdo = getDB();
$residences = $pdo->query(
    "SELECT r.id_residence, r.nom_residence, r.adresse_residence,
            COUNT(DISTINCT a.id_appartement) AS nb_appartements,
            COUNT(c.id_chambre) AS nb_chambres,
            SUM(CASE WHEN c.status_chambre = 'Libre' THEN 1 ELSE 0 END) AS chambres_libres,
            SUM(CASE WHEN c.status_chambre = 'Occupée' THEN 1 ELSE 0 END) AS chambres_occupees
     FROM residence r
     LEFT JOIN appartement a ON a.id_residence = r.id_residence
     LEFT JOIN chambre c ON c.id_appartement = a.id_appartement
     GROUP BY r.id_residence, r.nom_residence, r.adresse_residence
     ORDER BY r.nom_residence"
)->fetchAll();

renderPageStart('Résidences');
renderPrivateHeader('Résidences', 'Consultation synthétique des bâtiments, appartements et chambres.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <section class="table-card">
            <div class="toolbar"><h2>Liste des résidences</h2><a class="btn btn-secondary" href="<?= e(appUrl('/admin/chambres.php')) ?>">Voir les chambres</a></div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Résidence</th><th>Adresse</th><th>Appartements</th><th>Chambres</th><th>Libres</th><th>Occupées</th></tr></thead>
                    <tbody>
                    <?php if (!$residences): ?>
                        <tr><td colspan="6" class="muted">Aucune résidence trouvée.</td></tr>
                    <?php else: foreach ($residences as $residence): ?>
                        <tr>
                            <td><?= e($residence['nom_residence']) ?></td>
                            <td><?= e($residence['adresse_residence'] ?: '-') ?></td>
                            <td><?= (int) $residence['nb_appartements'] ?></td>
                            <td><?= (int) $residence['nb_chambres'] ?></td>
                            <td><span class="status status-success"><?= (int) $residence['chambres_libres'] ?></span></td>
                            <td><span class="status status-warning"><?= (int) $residence['chambres_occupees'] ?></span></td>
                        </tr>
                    <?php endforeach; endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
