<?php
// =============================================================
// ESG-BALANCE - Dashboard (redirect per ruolo)
// =============================================================
require_once __DIR__ . '/../includes/auth.php';
requireAuth();

$ruolo = getRuolo();

switch ($ruolo) {
    case 'amministratore':
        header('Location: admin/dashboard_admin.php');
        break;
    case 'revisore_esg':
        header('Location: revisore/dashboard_revisore.php');
        break;
    case 'responsabile_aziendale':
        header('Location: responsabile/dashboard_responsabile.php');
        break;
    default:
        logout();
}
exit;
?>
