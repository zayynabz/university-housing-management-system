<?php
require_once __DIR__ . '/helpers.php';
ensureSessionStarted();
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function currentPath(): string
{
    return str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
}

function currentRole(): ?string
{
    return $_SESSION['user_role'] ?? null;
}

function currentUserName(): string
{
    return trim((string) ($_SESSION['user_nom'] ?? 'Utilisateur'));
}

function dashboardPathForRole(?string $role): string
{
    switch ($role) {
        case 'Admin':
            return '/admin/dashboard.php';
        case 'Resident':
            return '/resident/dashboard.php';
        case 'Technicien':
            return '/technicien/dashboard.php';
        default:
            return '/index.php';
    }
}

function menuLinksForRole(?string $role): array
{
    switch ($role) {
        case 'Admin':
            return [
                ['/admin/dashboard.php', 'Dashboard'],
                ['/admin/residents.php', 'Résidents'],
                ['/admin/residences.php', 'Résidences'],
                ['/admin/chambres.php', 'Chambres'],
                ['/admin/assign_room.php', 'Affectations'],
                ['/admin/paiements.php', 'Paiements'],
                ['/admin/incidents.php', 'Incidents'],
                ['/profile.php', 'Profil'],
            ];
        case 'Resident':
            return [
                ['/resident/dashboard.php', 'Dashboard'],
                ['/resident/room.php', 'Ma chambre'],
                ['/resident/payments.php', 'Paiements'],
                ['/resident/create_incident.php', 'Incident'],
                ['/resident/view_incidents.php', 'Suivi'],
                ['/profile.php', 'Profil'],
            ];
        case 'Technicien':
            return [
                ['/technicien/dashboard.php', 'Dashboard'],
                ['/technicien/view_incidents.php', 'Mes incidents'],
                ['/profile.php', 'Profil'],
            ];
        default:
            return [
                ['/index.php', 'Accueil'],
                ['/auth/login.php', 'Se connecter'],
            ];
    }
}

function isCurrentRoute(string $path): bool
{
    return currentPath() === appUrl($path);
}

function setFlash(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return is_array($flash) ? $flash : null;
}

function redirectTo(string $path): void
{
    header('Location: ' . appUrl($path));
    exit;
}

function renderPageStart(string $title): void
{
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');

    echo '<!DOCTYPE html><html lang="fr"><head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
    echo '<title>' . e($title) . '</title>';
    echo '<link rel="stylesheet" href="' . e(appUrl('/assets/theme.css')) . '">';
    echo '</head><body>';
}

function renderPageEnd(): void
{
    echo '<script src="' . e(appUrl('/assets/app.js')) . '"></script>';
    echo '</body></html>';
}

function renderFlashMessage(): void
{
    $flash = getFlash();
    if (!$flash) {
        return;
    }

    $class = $flash['type'] === 'error' ? 'alert alert-error' : 'alert alert-success';
    echo '<div class="' . e($class) . '">' . e($flash['message']) . '</div>';
}

function renderPublicHeader(): void
{
    echo '<header class="site-header">';
    echo '<div class="page-shell header-row">';
    echo '<a class="brand" href="' . e(appUrl('/index.php')) . '"><span class="brand-mark">UDE</span><span class="brand-text">Logement universitaire</span></a>';
    echo '<a class="btn btn-primary" href="' . e(appUrl('/auth/login.php')) . '">Se connecter</a>';
    echo '</div></header>';
}

function renderPrivateHeader(string $title, string $subtitle = ''): void
{
    $role = currentRole();
    $links = menuLinksForRole($role);

    echo '<header class="site-header">';
    echo '<div class="page-shell">';
    echo '<div class="header-row">';
    echo '<a class="brand" href="' . e(appUrl(dashboardPathForRole($role))) . '"><span class="brand-mark">UDE</span><span class="brand-text">Logement universitaire</span></a>';
    echo '<div class="header-user">';
    echo '<span class="user-chip">' . e(currentUserName()) . ' · ' . e($role ?? 'Invité') . '</span>';
    echo '<form method="post" action="' . e(appUrl('/auth/logout.php')) . '"><button type="submit" class="btn btn-secondary">Déconnexion</button></form>';
    echo '</div></div>';

    echo '<div class="page-title-block">';
    echo '<h1>' . e($title) . '</h1>';
    if ($subtitle !== '') {
        echo '<p class="page-subtitle">' . e($subtitle) . '</p>';
    }
    echo '</div>';

    echo '<nav class="section-tabs">';
    foreach ($links as $link) {
        $path = $link[0];
        $label = $link[1];
        $class = isCurrentRoute($path) ? 'section-tab is-active' : 'section-tab';
        echo '<a class="' . e($class) . '" href="' . e(appUrl($path)) . '">' . e($label) . '</a>';
    }
    echo '</nav></div></header>';
}
