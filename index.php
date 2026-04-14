<?php
require_once __DIR__ . '/config/ui.php'; //appeler l ui et ses fcts
renderPageStart('Logement universitaire');
renderPublicHeader();
?>
<main class="main-content">
    <div class="page-shell page-shell-narrow hero-simple panel-stack">
        <section class="hero-card">
            <h1>Univeristé Des Ingénieurs</h1>
            <div class="hero-actions">
                <a class="role-button" href="<?= e(appUrl('/auth/login.php')) ?>">Se connecter</a>
            </div>
        </section>
    </div>
</main>
<?php renderPageEnd(); ?>
