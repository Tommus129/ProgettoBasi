<?php
// =============================================================
// ESG-BALANCE - Dashboard Revisore ESG
// =============================================================
require_once __DIR__ . '/../../includes/auth.php';
requireRole('revisore_esg');

require_once __DIR__ . '/../../config/db_config.php';
$pdo = getDBConnection();
$idRevisore = getIdUtente();

// Bilanci assegnati a questo revisore
$stmtBilanci = $pdo->prepare(
    'SELECT b.id_bilancio, a.ragione_sociale, b.anno_esercizio, b.stato,
            ar.id_assegnazione,
            (SELECT COUNT(*) FROM giudizi_revisione gr WHERE gr.id_assegnazione = ar.id_assegnazione) AS ha_giudicato
     FROM assegnazioni_revisori ar
     JOIN bilanci b ON ar.id_bilancio = b.id_bilancio
     JOIN aziende a ON b.id_azienda = a.id_azienda
     WHERE ar.id_revisore = :id
     ORDER BY b.stato, b.anno_esercizio DESC'
);
$stmtBilanci->execute([':id' => $idRevisore]);
$bilanci = $stmtBilanci->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Revisore - ESG-BALANCE</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-dark bg-info">
    <div class="container-fluid">
        <span class="navbar-brand">ESG-BALANCE - Revisore ESG</span>
        <span class="text-white"><?= htmlspecialchars($_SESSION['nome'] . ' ' . $_SESSION['cognome']) ?></span>
        <a href="../logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
</nav>
<div class="container mt-4">
    <div class="row mb-3">
        <div class="col">
            <a href="gestione_competenze.php" class="btn btn-primary">Gestisci Competenze</a>
        </div>
    </div>
    <h5>Bilanci assegnati</h5>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Azienda</th><th>Anno</th><th>Stato</th><th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($bilanci as $b): ?>
            <tr>
                <td><?= htmlspecialchars($b['ragione_sociale']) ?></td>
                <td><?= $b['anno_esercizio'] ?></td>
                <td><span class="badge bg-secondary"><?= $b['stato'] ?></span></td>
                <td>
                    <a href="revisione_bilancio.php?id_assegnazione=<?= $b['id_assegnazione'] ?>" class="btn btn-sm btn-outline-primary">Rivedi</a>
                    <?php if (!$b['ha_giudicato'] && $b['stato'] === 'in_revisione'): ?>
                        <a href="inserisci_giudizio.php?id_assegnazione=<?= $b['id_assegnazione'] ?>" class="btn btn-sm btn-success">Inserisci Giudizio</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
