<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Resident']);

function residentPaymentBadgeClass(string $status): string
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
$residentId = (int) $_SESSION['user_id'];
$error = '';

if (isPostRequest()) {
    $paymentId = (int) ($_POST['payment_id'] ?? 0);

    if ($paymentId <= 0) {
        $error = 'Paiement invalide.';
    } else {
        $stmt = $pdo->prepare(
            "UPDATE paiement p
             JOIN occupation o ON o.id_occupation = p.id_occupation
             SET p.status_paiement = 'Payé', p.date_paiement = CURDATE()
             WHERE p.id_paiement = ?
               AND o.id_resident = ?
               AND p.status_paiement <> 'Payé'"
        );
        $stmt->execute([$paymentId, $residentId]);

        if ($stmt->rowCount() === 0) {
            $error = 'Ce paiement est introuvable ou déjà payé.';
        } else {
            setFlash('success', 'Paiement enregistré avec succès.');
            redirectTo('/resident/payments.php');
        }
    }
}

$stmt = $pdo->prepare(
    "SELECT p.*, o.id_occupation
     FROM paiement p
     JOIN occupation o ON o.id_occupation = p.id_occupation
     WHERE o.id_resident = ?
     ORDER BY p.mois_concerne DESC, p.date_echeance DESC"
);
$stmt->execute([$residentId]);
$payments = $stmt->fetchAll();

renderPageStart('Mes paiements');
renderPrivateHeader('Mes paiements', 'Visualisation des échéances et paiement du loyer.');
?>
<main class="main-content">
    <div class="page-shell panel-stack">
        <?php renderFlashMessage(); ?>
        <?php if ($error !== ''): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>
        <section class="table-card">
            <div class="toolbar"><h2>Historique des paiements</h2></div>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Mois</th><th>Montant</th><th>Échéance</th><th>Date paiement</th><th>Statut</th><th>Action</th></tr></thead>
                    <tbody>
                    <?php if (!$payments): ?>
                        <tr><td colspan="6" class="muted">Aucun paiement trouvé.</td></tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><?= e($payment['mois_concerne']) ?></td>
                                <td><?= e(formatMoney($payment['montant_a_payer'])) ?></td>
                                <td><?= e($payment['date_echeance'] ?: '-') ?></td>
                                <td><?= e($payment['date_paiement'] ?: '-') ?></td>
                                <td><span class="status <?= e(residentPaymentBadgeClass($payment['status_paiement'])) ?>"><?= e($payment['status_paiement']) ?></span></td>
                                <td>
                                    <?php if ($payment['status_paiement'] !== 'Payé'): ?>
                                        <form method="post">
                                            <input type="hidden" name="payment_id" value="<?= (int) $payment['id_paiement'] ?>">
                                            <button type="submit">Payer maintenant</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="muted">Reçu disponible à l'écran</span>
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
