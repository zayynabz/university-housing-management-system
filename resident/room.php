<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Resident']);

$pdo = getDB();
$occupation = activeOccupationForResident($pdo, (int) $_SESSION['user_id']);
renderPageStart('Ma chambre');
renderPrivateHeader('Ma chambre', 'Résumé de l\'occupation en cours et des informations liées au logement.');
?>
<main class="main-content">
    <div class="page-shell page-shell-narrow panel-stack">
        <section class="card">
            <?php if (!$occupation): ?>
                <div class="empty-state">Aucune chambre active n'est encore affectée à votre compte.</div>
            <?php else: ?>
                <div class="definition-list">
                    <div class="definition-item"><strong>Résidence</strong><span><?= e($occupation['nom_residence']) ?></span></div>
                    <div class="definition-item"><strong>Adresse</strong><span><?= e($occupation['adresse_residence'] ?: '-') ?></span></div>
                    <div class="definition-item"><strong>Appartement</strong><span><?= e($occupation['numero_appartement']) ?> - Étage <?= e($occupation['etage']) ?></span></div>
                    <div class="definition-item"><strong>Chambre</strong><span><?= e($occupation['numero_chambre']) ?></span></div>
                    <div class="definition-item"><strong>Loyer mensuel</strong><span><?= e(formatMoney($occupation['loyer_mensuel'])) ?></span></div>
                    <div class="definition-item"><strong>Date de début</strong><span><?= e($occupation['date_debut']) ?></span></div>
                    <div class="definition-item"><strong>Statut du contrat</strong><span><?= e($occupation['status_occupation']) ?></span></div>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
