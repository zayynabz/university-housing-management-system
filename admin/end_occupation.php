<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Admin']);

if (!isPostRequest()) {
    redirectTo('/admin/residents.php');
}

$occupationId = (int) ($_POST['occupation_id'] ?? 0);
if ($occupationId <= 0) {
    setFlash('error', 'Occupation invalide.');
    redirectTo('/admin/residents.php');
}

$pdo = getDB();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id_chambre, status_occupation FROM occupation WHERE id_occupation = ? FOR UPDATE');
    $stmt->execute([$occupationId]);
    $occupation = $stmt->fetch();

    if (!$occupation) {
        throw new Exception('Occupation introuvable.');
    }
    if ($occupation['status_occupation'] !== 'En cours') {
        throw new Exception('Le contrat est déjà terminé.');
    }

    $updateOccupation = $pdo->prepare(
        "UPDATE occupation SET date_fin = CURDATE(), status_occupation = 'Terminée' WHERE id_occupation = ?"
    );
    $updateOccupation->execute([$occupationId]);

    $updateRoom = $pdo->prepare("UPDATE chambre SET status_chambre = 'Libre' WHERE id_chambre = ?");
    $updateRoom->execute([(int) $occupation['id_chambre']]);

    $pdo->commit();
    setFlash('success', 'Contrat résilié et chambre libérée.');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlash('error', $exception->getMessage());
}

redirectTo('/admin/residents.php');
