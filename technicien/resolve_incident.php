<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Technicien']);

if (!isPostRequest()) {
    redirectTo('/technicien/view_incidents.php');
}

$incidentId = (int) ($_POST['id'] ?? 0);
$techId = (int) $_SESSION['user_id'];

if ($incidentId <= 0) {
    setFlash('error', 'Incident invalide.');
    redirectTo('/technicien/view_incidents.php');
}

$pdo = getDB();

try {
    $pdo->beginTransaction();

    $check = $pdo->prepare(
        'SELECT id_incident, status_incident FROM incident WHERE id_incident = ? AND id_technicien = ? FOR UPDATE'
    );
    $check->execute([$incidentId, $techId]);
    $incident = $check->fetch();

    if (!$incident) {
        throw new Exception('Incident introuvable ou non autorisé.');
    }
    if ($incident['status_incident'] === 'Résolu') {
        throw new Exception('Incident déjà résolu.');
    }

    $updateIncident = $pdo->prepare(
        "UPDATE incident SET status_incident = 'Résolu', date_resolution = CURDATE() WHERE id_incident = ?"
    );
    $updateIncident->execute([$incidentId]);

    syncTechnicianAvailability($pdo, $techId);

    $pdo->commit();
    setFlash('success', 'Incident marqué comme résolu.');
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    setFlash('error', $exception->getMessage());
}

redirectTo('/technicien/view_incidents.php');
