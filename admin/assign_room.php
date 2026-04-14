<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Admin']);

$pdo = getDB();
$error = '';
$defaultDate = $_POST['start_date'] ?? date('Y-m-d');

if (isPostRequest()) {
    $residentId = (int) ($_POST['resident_id'] ?? 0);
    $roomId = (int) ($_POST['room_id'] ?? 0);
    $startDate = trim($_POST['start_date'] ?? '');
    $parsedDate = DateTime::createFromFormat('Y-m-d', $startDate);

    if ($residentId <= 0 || $roomId <= 0 || $startDate === '') {
        $error = 'Tous les champs sont obligatoires.';
    } elseif (!$parsedDate || $parsedDate->format('Y-m-d') !== $startDate) {
        $error = 'La date de début est invalide.';
    } else {
        try {
            $pdo->beginTransaction();

            if (!residentExists($pdo, $residentId)) {
                throw new Exception('Résident introuvable.');
            }
            if (activeOccupationForResident($pdo, $residentId)) {
                throw new Exception('Ce résident possède déjà une occupation active.');
            }

            $roomStmt = $pdo->prepare(
                "SELECT c.id_chambre, c.status_chambre, c.loyer_mensuel
                 FROM chambre c
                 WHERE c.id_chambre = ?
                 FOR UPDATE"
            );
            $roomStmt->execute([$roomId]);
            $room = $roomStmt->fetch();

            if (!$room) {
                throw new Exception('Chambre introuvable.');
            }
            if (activeOccupationForRoom($pdo, $roomId)) {
                throw new Exception('La chambre est déjà occupée.');
            }
            if ($room['status_chambre'] !== 'Libre') {
                throw new Exception('Cette chambre n\'est pas libre.');
            }

            $insertOccupation = $pdo->prepare(
                'INSERT INTO occupation (id_resident, id_chambre, date_debut, status_occupation) VALUES (?, ?, ?, "En cours")'
            );
            $insertOccupation->execute([$residentId, $roomId, $startDate]);
            $occupationId = (int) $pdo->lastInsertId();

            $updateRoom = $pdo->prepare("UPDATE chambre SET status_chambre = 'Occupée' WHERE id_chambre = ?");
            $updateRoom->execute([$roomId]);

            createFirstPaymentForOccupation($pdo, $occupationId, $startDate, (float) $room['loyer_mensuel']);

            $pdo->commit();
            setFlash('success', 'Affectation enregistrée. Le premier paiement a été créé.');
            redirectTo('/admin/dashboard.php');
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $error = $exception->getMessage();
        }
    }
}

$residents = $pdo->query(
    "SELECT res.id_resident, u.nom, u.prenom, res.numero_etudiant
     FROM resident res
     JOIN utilisateur u ON u.id_utilisateur = res.id_resident
     WHERE res.id_resident NOT IN (
         SELECT id_resident FROM occupation WHERE status_occupation = 'En cours'
     )
     ORDER BY u.nom, u.prenom"
)->fetchAll();

$rooms = $pdo->query(
    "SELECT c.id_chambre, c.numero_chambre, c.loyer_mensuel, r.nom_residence
     FROM chambre c
     JOIN appartement a ON a.id_appartement = c.id_appartement
     JOIN residence r ON r.id_residence = a.id_residence
     WHERE c.status_chambre = 'Libre'
     ORDER BY r.nom_residence, c.numero_chambre"
)->fetchAll();

renderPageStart('Affecter une chambre');
renderPrivateHeader('Affecter une chambre', 'Formulaire simple avec retour direct au dashboard après enregistrement.');
?>
<main class="main-content">
    <div class="page-shell page-shell-narrow panel-stack">
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <section class="card">
            <div class="toolbar">
                <h2>Nouvelle affectation</h2>
                <div class="action-row">
                    <a class="btn btn-secondary" href="<?= e(appUrl('/admin/dashboard.php')) ?>">Dashboard</a>
                    <a class="btn btn-secondary" href="<?= e(appUrl('/admin/residents.php')) ?>">Résidents</a>
                </div>
            </div>
            <?php if (!$residents): ?>
                <div class="empty-state">Tous les résidents ont déjà une chambre active.</div>
            <?php elseif (!$rooms): ?>
                <div class="empty-state">Aucune chambre libre n'est disponible.</div>
            <?php else: ?>
                <form method="post" class="stack-form">
                    <div class="form-field"><label for="resident_id">Résident</label><select id="resident_id" name="resident_id" required><option value="">Choisir</option><?php foreach ($residents as $resident): ?><option value="<?= (int) $resident['id_resident'] ?>"><?= e($resident['prenom'] . ' ' . $resident['nom'] . ' - ' . ($resident['numero_etudiant'] ?: 'Sans numéro')) ?></option><?php endforeach; ?></select></div>
                    <div class="form-field"><label for="room_id">Chambre libre</label><select id="room_id" name="room_id" required><option value="">Choisir</option><?php foreach ($rooms as $room): ?><option value="<?= (int) $room['id_chambre'] ?>"><?= e($room['numero_chambre'] . ' - ' . $room['nom_residence'] . ' - ' . formatMoney($room['loyer_mensuel'])) ?></option><?php endforeach; ?></select></div>
                    <div class="form-field"><label for="start_date">Date de début</label><input type="date" id="start_date" name="start_date" value="<?= e($defaultDate) ?>" required></div>
                    <div class="form-actions"><button type="submit">Enregistrer</button></div>
                </form>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
