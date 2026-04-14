<?php
require_once __DIR__ . '/../config/auth_check.php';
require_once __DIR__ . '/../config/ui.php';
checkRole(['Admin']);

$pdo = getDB();
$type = $_GET['type'] ?? 'residents';
$dom = new DOMDocument('1.0', 'UTF-8');
$dom->formatOutput = true;

switch ($type) {
    case 'residents':
        $root = $dom->createElement('residents');
        $stmt = $pdo->query("SELECT u.nom, u.prenom, u.email, r.numero_etudiant, r.filiere FROM resident r JOIN utilisateur u ON u.id_utilisateur = r.id_resident ORDER BY u.nom, u.prenom");
        foreach ($stmt as $row) {
            $resident = $dom->createElement('resident');
            $resident->setAttribute('email', (string) $row['email']);
            foreach (['nom', 'prenom', 'numero_etudiant', 'filiere'] as $field) {
                $resident->appendChild($dom->createElement($field, (string) ($row[$field] ?? '')));
            }
            $root->appendChild($resident);
        }
        $dom->appendChild($root);
        xmlResponse('residents.xml', $dom);
    case 'paiements':
        $root = $dom->createElement('paiements');
        $stmt = $pdo->query("SELECT id_paiement, montant_a_payer, mois_concerne, status_paiement, date_echeance, date_paiement FROM paiement ORDER BY mois_concerne DESC");
        foreach ($stmt as $row) {
            $node = $dom->createElement('paiement');
            $node->setAttribute('id', (string) $row['id_paiement']);
            foreach (['montant_a_payer', 'mois_concerne', 'status_paiement', 'date_echeance', 'date_paiement'] as $field) {
                $node->appendChild($dom->createElement($field, (string) ($row[$field] ?? '')));
            }
            $root->appendChild($node);
        }
        $dom->appendChild($root);
        xmlResponse('paiements.xml', $dom);
    case 'incidents':
        $root = $dom->createElement('incidents');
        $stmt = $pdo->query("SELECT id_incident, titre_incident, status_incident, priorite_incident, date_creation FROM incident ORDER BY date_creation DESC");
        foreach ($stmt as $row) {
            $node = $dom->createElement('incident');
            $node->setAttribute('id', (string) $row['id_incident']);
            foreach (['titre_incident', 'status_incident', 'priorite_incident', 'date_creation'] as $field) {
                $node->appendChild($dom->createElement($field, (string) ($row[$field] ?? '')));
            }
            $root->appendChild($node);
        }
        $dom->appendChild($root);
        xmlResponse('incidents.xml', $dom);
    default:
        setFlash('error', 'Type d\'export XML inconnu.');
        redirectTo('/admin/dashboard.php');
}
