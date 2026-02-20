<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireRole('revisore');

$id_revisore = $_SESSION['id_utente'];
$id_bilancio = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Verifica che il bilancio sia assegnato a questo revisore
$stmt = $pdo->prepare("
    SELECT b.*, a.nome AS nome_azienda, a.settore
    FROM Bilanci b
    JOIN Aziende a ON b.id_azienda = a.id_azienda
    WHERE b.id_bilancio = ? AND b.id_revisore = ?
");
$stmt->execute([$id_bilancio, $id_revisore]);
$bilancio = $stmt->fetch();

if (!$bilancio) {
    header('Location: dashboard_revisore.php');
    exit;
}

// Carica indicatori ESG del bilancio
$stmt = $pdo->prepare("
    SELECT vb.*, i.nome AS nome_indicatore, i.categoria, i.unita_misura
    FROM ValoriBilancio vb
    JOIN IndicatoriESG i ON vb.id_indicatore = i.id_indicatore
    WHERE vb.id_bilancio = ?
    ORDER BY i.categoria, i.nome
");
$stmt->execute([$id_bilancio]);
$valori = $stmt->fetchAll();

// Carica revisioni precedenti
$stmt = $pdo->prepare("SELECT * FROM Revisioni WHERE id_bilancio = ? ORDER BY data_revisione DESC");
$stmt->execute([$id_bilancio]);
$revisioni = $stmt->fetchAll();

// Gestione POST - salva revisione
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $esito = $_POST['esito'];
    $commento = $_POST['commento'];
    $stmt = $pdo->prepare("INSERT INTO Revisioni (id_bilancio, id_revisore, esito, commento) VALUES (?, ?, ?, ?)");
    $stmt->execute([$id_bilancio, $id_revisore, $esito, $commento]);

    // Aggiorna stato bilancio
    $nuovo_stato = ($esito === 'approvato') ? 'approvato' : (($esito === 'rifiutato') ? 'rifiutato' : 'in_revisione');
    $stmt = $pdo->prepare("UPDATE Bilanci SET stato = ? WHERE id_bilancio = ?");
    $stmt->execute([$nuovo_stato, $id_bilancio]);

    $msg = "Revisione salvata con esito: $esito";
    header("Location: revisione_bilanci.php?id=$id_bilancio");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Revisione Bilancio - ESG Balance</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
</head>
<body>
<?php require_once '../../includes/header.php'; ?>
<div class="container">
    <h2>Revisione Bilancio ESG</h2>
    <h3><?= htmlspecialchars($bilancio['nome_azienda']) ?> - Anno <?= $bilancio['anno'] ?></h3>
    <p><strong>Settore:</strong> <?= htmlspecialchars($bilancio['settore']) ?></p>
    <p><strong>Stato attuale:</strong> <span class="badge badge-<?= $bilancio['stato'] ?>"><?= ucfirst($bilancio['stato']) ?></span></p>

    <h4>Valori Indicatori ESG</h4>
    <table class="data-table">
        <thead>
            <tr><th>Categoria</th><th>Indicatore</th><th>Valore</th><th>Unita</th><th>Note</th></tr>
        </thead>
        <tbody>
        <?php foreach ($valori as $v): ?>
            <tr>
                <td><?= htmlspecialchars($v['categoria']) ?></td>
                <td><?= htmlspecialchars($v['nome_indicatore']) ?></td>
                <td><?= htmlspecialchars($v['valore']) ?></td>
                <td><?= htmlspecialchars($v['unita_misura']) ?></td>
                <td><?= htmlspecialchars($v['note'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h4>Inserisci Revisione</h4>
    <form method="POST">
        <label>Esito:</label>
        <select name="esito" required>
            <option value="approvato">Approvato</option>
            <option value="rifiutato">Rifiutato</option>
            <option value="in_revisione">Richiede Modifiche</option>
        </select>
        <label>Commento:</label>
        <textarea name="commento" rows="5" placeholder="Inserisci commento..."></textarea>
        <button type="submit">Salva Revisione</button>
    </form>

    <h4>Storico Revisioni</h4>
    <?php if (empty($revisioni)): ?>
        <p>Nessuna revisione precedente.</p>
    <?php else: ?>
    <table class="data-table">
        <thead><tr><th>Data</th><th>Esito</th><th>Commento</th></tr></thead>
        <tbody>
        <?php foreach ($revisioni as $r): ?>
            <tr>
                <td><?= $r['data_revisione'] ?></td>
                <td><span class="badge badge-<?= $r['esito'] ?>"><?= ucfirst($r['esito']) ?></span></td>
                <td><?= htmlspecialchars($r['commento']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <a href="dashboard_revisore.php">Torna alla Dashboard</a>
</div>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
