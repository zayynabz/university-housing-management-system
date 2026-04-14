<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Admin']);

function paymentBadgeClass(string $status): string
{
    if ($status === 'Payé') {
        return 'status-success';
    }
    if ($status === 'En retard') {
        return 'status-danger';
    }
    return 'status-warning';
}

$pdo = getDB();
updateLatePayments($pdo);
$error = '';

if (isPostRequest()) {
    $paymentId = (int) ($_POST['payment_id'] ?? 0);
    $action = $_POST['payment_action'] ?? '';

    if ($paymentId <= 0) {
        $error = 'Paiement invalide.';
    } else {
        try {
            if ($action === 'mark_paid') {
                $stmt = $pdo->prepare(
                    "UPDATE paiement
                     SET status_paiement = 'Payé', date_paiement = CURDATE()
                     WHERE id_paiement = ? AND status_paiement <> 'Payé'"
                );
                $stmt->execute([$paymentId]);

                if ($stmt->rowCount() === 0) {
                    throw new Exception('Aucun paiement à valider.');
                }

                setFlash('success', 'Paiement validé.');
                redirectTo('/admin/paiements.php');
            }

            if ($action === 'reset_waiting') {
                $stmt = $pdo->prepare(
                    "UPDATE paiement
                     SET status_paiement = 'En attente', date_paiement = NULL
                     WHERE id_paiement = ? AND status_paiement = 'Payé'"
                );
                $stmt->execute([$paymentId]);

                if ($stmt->rowCount() === 0) {
                    throw new Exception('Aucun paiement payé à remettre en attente.');
                }

                updateLatePayments($pdo);
                setFlash('success', 'Paiement remis en attente.');
                redirectTo('/admin/paiements.php');
            }

            throw new Exception('Action non reconnue.');
        } catch (Throwable $exception) {
            $error = $exception->getMessage();
        }
    }
}

$payments = $pdo->query(
    "SELECT p.*, u.nom, u.prenom, c.numero_chambre
     FROM paiement p
     JOIN occupation o ON o.id_occupation = p.id_occupation
     JOIN resident r ON r.id_resident = o.id_resident
     JOIN utilisateur u ON u.id_utilisateur = r.id_resident
     LEFT JOIN chambre c ON c.id_chambre = o.id_chambre
     ORDER BY p.mois_concerne DESC, p.date_echeance DESC"
)->fetchAll();

renderPageStart('Paiements');
renderPrivateHeader('Paiements', 'Validation administrative des paiements et suivi des retards.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <?php renderFlashMessage(); ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <section class="table-card">
            <div class="toolbar">
                <h2>Liste des paiements</h2>
                <a class="btn btn-secondary" href="<?= e(appUrl('/admin/export_xml.php?type=paiements')) ?>">Exporter XML</a>
            </div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Résident</th><th>Chambre</th><th>Mois</th><th>Montant</th><th>Échéance</th><th>Date paiement</th><th>Statut</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (!$payments): ?>
                        <tr><td colspan="8" class="muted">Aucun paiement enregistré.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= e($payment['prenom'] . ' ' . $payment['nom']) ?></td>
                                <td><?= e($payment['numero_chambre'] ?: '-') ?></td>
                                <td><?= e($payment['mois_concerne']) ?></td>
                                <td><?= e(formatMoney($payment['montant_a_payer'])) ?></td>
                                <td><?= e($payment['date_echeance'] ?: '-') ?></td>
                                <td><?= e($payment['date_paiement'] ?: '-') ?></td>
                                <td><span class="status <?= e(paymentBadgeClass($payment['status_paiement'])) ?>"><?= e($payment['status_paiement']) ?></span></td>
                                <td>
                                    <div class="inline-actions">
                                        <?php if ($payment['status_paiement'] !== 'Payé'): ?>
                                            <form method="post">
                                                <input type="hidden" name="payment_id" value="<?= (int) $payment['id_paiement'] ?>">
                                                <input type="hidden" name="payment_action" value="mark_paid">
                                                <button type="submit">Valider</button>
                                            </form>
                                        <?php endif; ?>
                                        <?php if ($payment['status_paiement'] === 'Payé'): ?>
                                            <form method="post">
                                                <input type="hidden" name="payment_id" value="<?= (int) $payment['id_paiement'] ?>">
                                                <input type="hidden" name="payment_action" value="reset_waiting">
                                                <button type="submit" class="btn btn-secondary">Remettre en attente</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
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
