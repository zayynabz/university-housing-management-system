<?php
require_once __DIR__ . '/db.php';
//regroupe fct reutilisables partout
function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isPostRequest(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'; //si un user envoie un formulaire: POST sinon GET
}

function updateLatePayments(PDO $pdo): void
{
    $sql = "UPDATE paiement
            SET status_paiement = 'En retard'
            WHERE status_paiement <> 'Payé'
              AND date_echeance IS NOT NULL
              AND date_echeance < CURDATE()";
    $pdo->exec($sql);
}

function isStrongPassword(string $password): bool
{
    return (bool) preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z\d]).{8,}$/', $password);
}

function activeOccupationForResident(PDO $pdo, int $residentId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT o.*, c.numero_chambre, c.loyer_mensuel, c.id_chambre,
                a.numero_appartement, a.etage,
                r.nom_residence, r.adresse_residence
         FROM occupation o
         JOIN chambre c ON c.id_chambre = o.id_chambre
         JOIN appartement a ON a.id_appartement = c.id_appartement
         JOIN residence r ON r.id_residence = a.id_residence
         WHERE o.id_resident = ?
           AND o.status_occupation = 'En cours'
         LIMIT 1"
    );
    $stmt->execute([$residentId]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function activeOccupationForRoom(PDO $pdo, int $roomId): ?array
{
    $stmt = $pdo->prepare(
        "SELECT o.id_occupation, o.id_resident, u.nom, u.prenom, c.numero_chambre
         FROM occupation o
         JOIN resident r ON r.id_resident = o.id_resident
         JOIN utilisateur u ON u.id_utilisateur = r.id_resident
         JOIN chambre c ON c.id_chambre = o.id_chambre
         WHERE o.id_chambre = ?
           AND o.status_occupation = 'En cours'
         LIMIT 1"
    );
    $stmt->execute([$roomId]);
    $row = $stmt->fetch();

    return $row ?: null;
}

function residentFullName(PDO $pdo, int $residentId): string
{
    $stmt = $pdo->prepare('SELECT CONCAT(prenom, " ", nom) FROM utilisateur WHERE id_utilisateur = ?');
    $stmt->execute([$residentId]);

    return (string) ($stmt->fetchColumn() ?: 'Résident');
}

function residentExists(PDO $pdo, int $residentId): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM resident WHERE id_resident = ? LIMIT 1');
    $stmt->execute([$residentId]);

    return (bool) $stmt->fetchColumn();
}

function technicianExists(PDO $pdo, int $technicianId): bool
{
    $stmt = $pdo->prepare('SELECT 1 FROM technicien WHERE id_technicien = ? LIMIT 1');
    $stmt->execute([$technicianId]);

    return (bool) $stmt->fetchColumn();
}

function formatMoney($amount): string
{
    return number_format((float) $amount, 2, ',', ' ') . ' MAD';
}

function shortenText(string $text, int $width = 80): string
{
    $text = trim($text);
    if ($text === '') {
        return '';
    }

    if (function_exists('mb_strimwidth')) {
        return mb_strimwidth($text, 0, $width, '...');
    }

    if (strlen($text) <= $width) {
        return $text;
    }

    return rtrim(substr($text, 0, max(0, $width - 3))) . '...';
}

function activeIncidentCountForTechnician(PDO $pdo, int $technicianId): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM incident WHERE id_technicien = ? AND status_incident <> 'Résolu'");
    $stmt->execute([$technicianId]);

    return (int) $stmt->fetchColumn();
}

function syncTechnicianAvailability(PDO $pdo, int $technicianId): void
{
    $count = activeIncidentCountForTechnician($pdo, $technicianId);
    $availability = $count > 0 ? 'Occupé' : 'Disponible';
    $stmt = $pdo->prepare('UPDATE technicien SET disponibilite = ? WHERE id_technicien = ?');
    $stmt->execute([$availability, $technicianId]);
}

function createFirstPaymentForOccupation(PDO $pdo, int $occupationId, string $startDate, float $amount): void
{
    $date = DateTime::createFromFormat('Y-m-d', $startDate);
    if (!$date) {
        $date = new DateTime();
    }

    $month = $date->format('Y-m');
    $dueDate = $date->format('Y-m-d');

    $stmt = $pdo->prepare(
        "INSERT INTO paiement (id_occupation, montant_a_payer, mois_concerne, date_echeance, status_paiement)
         VALUES (?, ?, ?, ?, 'En attente')
         ON DUPLICATE KEY UPDATE id_paiement = id_paiement"
    );
    $stmt->execute([$occupationId, $amount, $month, $dueDate]);
}

function xmlResponse(string $filename, DOMDocument $dom): void
{
    header('Content-Type: application/xml; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    echo $dom->saveXML();
    exit;
}
