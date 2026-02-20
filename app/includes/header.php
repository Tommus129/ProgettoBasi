<?php
// header.php - Intestazione comune per tutte le pagine
// ESG-BALANCE Platform

$ruolo = $_SESSION['ruolo'] ?? '';
$nome_utente = ($_SESSION['nome'] ?? '') . ' ' . ($_SESSION['cognome'] ?? '');
?>
<header class="main-header">
    <div class="header-inner">
        <a href="/" class="logo">
            <span class="logo-icon">ESG</span>
            <span class="logo-text">BALANCE</span>
        </a>
        <nav class="main-nav">
            <?php if ($ruolo === 'admin'): ?>
                <a href="/app/pages/admin/dashboard_admin.php">Dashboard</a>
                <a href="/app/pages/admin/gestione_utenti.php">Utenti</a>
                <a href="/app/pages/admin/gestione_aziende.php">Aziende</a>
                <a href="/app/pages/admin/gestione_indicatori.php">Indicatori</a>
            <?php elseif ($ruolo === 'revisore'): ?>
                <a href="/app/pages/revisore/dashboard_revisore.php">Dashboard</a>
                <a href="/app/pages/revisore/revisione_bilanci.php">Revisioni</a>
            <?php elseif ($ruolo === 'azienda'): ?>
                <a href="/app/pages/azienda/dashboard_azienda.php">Dashboard</a>
                <a href="/app/pages/azienda/inserimento_bilancio.php">Nuovo Bilancio</a>
            <?php endif; ?>
        </nav>
        <div class="user-info">
            <span class="user-name"><?= htmlspecialchars(trim($nome_utente)) ?></span>
            <span class="user-role badge badge-<?= $ruolo ?>"><?= ucfirst($ruolo) ?></span>
            <a href="/app/logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
</header>
