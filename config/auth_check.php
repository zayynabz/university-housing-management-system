<?php
require_once __DIR__ . '/helpers.php';
ensureSessionStarted();

function checkRole(array $allowedRoles): void
{
    if (empty($_SESSION['user_id'])) {
        header('Location: ' . appUrl('/auth/login.php'));
        exit;
    }

    $currentRole = $_SESSION['user_role'] ?? '';
    if (!in_array($currentRole, $allowedRoles, true)) {
        http_response_code(403);
        echo '<!DOCTYPE html><html lang="fr"><head><meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>Accès refusé</title>';
        echo '<link rel="stylesheet" href="' . htmlspecialchars(appUrl('/assets/theme.css'), ENT_QUOTES, 'UTF-8') . '">';
        echo '</head><body>';
        echo '<main class="main-content"><div class="page-shell page-shell-narrow">';
        echo '<section class="card"><h1>Accès refusé</h1>';
        echo '<p>Vous n\'avez pas les droits nécessaires pour ouvrir cette page.</p>';
        echo '<p><a class="btn btn-secondary" href="' . htmlspecialchars(appUrl('/index.php'), ENT_QUOTES, 'UTF-8') . '">Retour à l\'accueil</a></p>';
        echo '</section></div></main></body></html>';
        exit;
    }
}

function logout(): void
{
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    session_destroy();
    header('Location: ' . appUrl('/auth/login.php'));
    exit;
}
