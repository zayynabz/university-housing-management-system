<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Technicien']);

if (!isPostRequest()) {
    redirectTo('/technicien/dashboard.php');
}

$incidentId = (int) ($_POST['id'] ?? 0);
$techId = (int) $_SESSION['user_id'];

if ($incidentId <= 0) {
    setFlash('error', 'Incident invalide.');
    redirectTo('/technicien/dashboard.php');
}

$pdo = getDB();

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id_technicien, status_incident FROM incident WHERE id_incident = ? FOR UPDATE');
    $stmt->execute([$incidentId]);
    $incident = $stmt->fetch();

    if (!$incident) {
        throw new Exception('Incident introuvable.');
    }
    if (!empty($incident['id_technicien'])) {
        throw new Exception('Cet incident est déjà assigné.');
    }
    if ($incident['status_incident'] === 'Résolu') {
        throw new Exception('Incident déjà résolu.');
    }

    $updateIncident = $pdo->prepare("UPDATE incident SET id_technicien = ?, status_incident = 'En cours' WHERE id_incident = ?");
    $updateIncident->execute([$techId, $incidentId]);

    $updateTech = $pdo->prepare("UPDATE technicien SET disponibilite = 'Occupé' WHERE id_technicien = ?");
    $updateTech->execute([$techId]);

    $pdo->commit();
    setFlash('success', 'Incident pris en charge.');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlash('error', $exception->getMessage());
}

redirectTo('/technicien/dashboard.php');
