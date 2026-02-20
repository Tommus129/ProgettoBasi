<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireRole('azienda');

$id_utente = $_SESSION['id_utente'];

// Recupera dati azienda
$stmt = $pdo->prepare("SELECT * FROM Aziende WHERE id_utente = ?");
$stmt->execute([$id_utente]);
$azienda = $stmt->fetch();

if (!$azienda) {
    die('Nessuna azienda associata al tuo account. Contatta l\'amministratore.');
}

$id_azienda = $azienda['id_azienda'];

// Bilanci azienda
$stmt = $pdo->prepare("
    SELECT b.*,
           u.nome AS nome_revisore, u.cognome AS cognome_revisore,
           (SELECT COUNT(*) FROM Revisioni r WHERE r.id_bilancio = b.id_bilancio) AS num_revisioni
    FROM Bilanci b
    LEFT JOIN Utenti u ON b.id_revisore = u.id_utente
    WHERE b.id_azienda = ?
    ORDER BY b.anno DESC
");
$stmt->execute([$id_azienda]);
$bilanci = $stmt->fetchAll();

$tot = count($bilanci);
$approvati = count(array_filter($bilanci, fn($b) => $b['stato'] === 'approvato'));
$in_revisione = count(array_filter($bilanci, fn($b) => $b['stato'] === 'in_revisione'));
$bozze = count(array_filter($bilanci, fn($b) => $b['stato'] === 'bozza'));
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Azienda - ESG Balance</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
</head>
<body>
<?php require_once '../../includes/header.php'; ?>
<div class="container">
    <h2>Dashboard Azienda: <?= htmlspecialchars($azienda['nome']) ?></h2>
    <p><strong>Settore:</strong> <?= htmlspecialchars($azienda['settore']) ?></p>

    <div class="stats-grid">
        <div class="stat-card"><h3><?= $tot ?></h3><p>Bilanci Totali</p></div>
        <div class="stat-card"><h3><?= $bozze ?></h3><p>Bozze</p></div>
        <div class="stat-card"><h3><?= $in_revisione ?></h3><p>In Revisione</p></div>
        <div class="stat-card"><h3><?= $approvati ?></h3><p>Approvati</p></div>
    </div>

    <div class="actions">
        <a href="inserimento_bilancio.php" class="btn">+ Nuovo Bilancio ESG</a>
    </div>

    <h3>I Tuoi Bilanci ESG</h3>
    <table class="data-table">
        <thead>
            <tr><th>Anno</th><th>Data Invio</th><th>Stato</th><th>Revisore</th><th>Revisioni</th><th>Azioni</th></tr>
        </thead>
        <tbody>
        <?php foreach ($bilanci as $b): ?>
            <tr>
                <td><?= $b['anno'] ?></td>
                <td><?= $b['data_invio'] ?? '-' ?></td>
                <td><span class="badge badge-<?= $b['stato'] ?>"><?= ucfirst($b['stato']) ?></span></td>
                <td><?= $b['nome_revisore'] ? htmlspecialchars($b['cognome_revisore'].' '.$b['nome_revisore']) : 'Non assegnato' ?></td>
                <td><?= $b['num_revisioni'] ?></td>
                <td>
                    <a href="inserimento_bilancio.php?id=<?= $b['id_bilancio'] ?>">Visualizza</a>
                    <?php if ($b['stato'] === 'bozza'): ?>
                    | <a href="inserimento_bilancio.php?id=<?= $b['id_bilancio'] ?>&action=submit" onclick="return confirm('Inviare per revisione?')">Invia</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
