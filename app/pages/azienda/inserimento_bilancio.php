<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireRole('azienda');

$id_utente = $_SESSION['id_utente'];
$stmt = $pdo->prepare("SELECT * FROM Aziende WHERE id_utente = ?");
$stmt->execute([$id_utente]);
$azienda = $stmt->fetch();
$id_azienda = $azienda['id_azienda'];

// Carica indicatori disponibili
$indicatori = $pdo->query("SELECT * FROM IndicatoriESG WHERE attivo = 1 ORDER BY categoria, nome")->fetchAll();

// Bilancio esistente (modifica)
$bilancio = null;
$valori_esistenti = [];
$id_bilancio = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_bilancio > 0) {
    $stmt = $pdo->prepare("SELECT * FROM Bilanci WHERE id_bilancio = ? AND id_azienda = ?");
    $stmt->execute([$id_bilancio, $id_azienda]);
    $bilancio = $stmt->fetch();
    if ($bilancio) {
        $stmt = $pdo->prepare("SELECT * FROM ValoriBilancio WHERE id_bilancio = ?");
        $stmt->execute([$id_bilancio]);
        foreach ($stmt->fetchAll() as $v) {
            $valori_esistenti[$v['id_indicatore']] = $v;
        }
        // Invio per revisione
        if (isset($_GET['action']) && $_GET['action'] === 'submit' && $bilancio['stato'] === 'bozza') {
            $pdo->prepare("UPDATE Bilanci SET stato = 'in_revisione', data_invio = NOW() WHERE id_bilancio = ?")
                ->execute([$id_bilancio]);
            header('Location: dashboard_azienda.php');
            exit;
        }
    }
}

// Gestione POST - salva bilancio
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $anno = (int)$_POST['anno'];
    $action = $_POST['submit_action'] ?? 'bozza';
    $stato = ($action === 'invia') ? 'in_revisione' : 'bozza';
    $data_invio = ($action === 'invia') ? date('Y-m-d H:i:s') : null;

    if ($id_bilancio > 0 && $bilancio) {
        $stmt = $pdo->prepare("UPDATE Bilanci SET anno = ?, stato = ?, data_invio = ? WHERE id_bilancio = ?");
        $stmt->execute([$anno, $stato, $data_invio, $id_bilancio]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO Bilanci (id_azienda, anno, stato, data_invio) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_azienda, $anno, $stato, $data_invio]);
        $id_bilancio = $pdo->lastInsertId();
    }

    // Salva valori indicatori
    foreach ($_POST['indicatori'] ?? [] as $id_ind => $val) {
        $valore = $val['valore'] ?? null;
        $note = $val['note'] ?? null;
        if ($valore === null || $valore === '') continue;
        $stmt = $pdo->prepare("INSERT INTO ValoriBilancio (id_bilancio, id_indicatore, valore, note)
            VALUES (?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE valore = VALUES(valore), note = VALUES(note)");
        $stmt->execute([$id_bilancio, $id_ind, $valore, $note]);
    }

    header('Location: dashboard_azienda.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Inserimento Bilancio ESG - ESG Balance</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
</head>
<body>
<?php require_once '../../includes/header.php'; ?>
<div class="container">
    <h2><?= $bilancio ? 'Modifica' : 'Nuovo' ?> Bilancio ESG - <?= htmlspecialchars($azienda['nome']) ?></h2>

    <form method="POST">
        <label>Anno di riferimento:</label>
        <input type="number" name="anno" min="2000" max="2099"
               value="<?= $bilancio ? $bilancio['anno'] : date('Y') ?>" required>

        <h3>Indicatori ESG</h3>
        <?php
        $categoria_corrente = '';
        foreach ($indicatori as $ind):
            if ($ind['categoria'] !== $categoria_corrente):
                if ($categoria_corrente !== '') echo '</div>';
                $categoria_corrente = $ind['categoria'];
                echo "<h4>" . htmlspecialchars($categoria_corrente) . "</h4><div class='indicatori-group'>";
            endif;
            $val_esistente = $valori_esistenti[$ind['id_indicatore']] ?? null;
        ?>
        <div class="indicatore-row">
            <label><?= htmlspecialchars($ind['nome']) ?> (<?= htmlspecialchars($ind['unita_misura']) ?>):</label>
            <input type="number" step="0.01"
                   name="indicatori[<?= $ind['id_indicatore'] ?>][valore]"
                   value="<?= $val_esistente ? $val_esistente['valore'] : '' ?>"
                   placeholder="Valore">
            <input type="text"
                   name="indicatori[<?= $ind['id_indicatore'] ?>][note]"
                   value="<?= $val_esistente ? htmlspecialchars($val_esistente['note'] ?? '') : '' ?>"
                   placeholder="Note opzionali">
        </div>
        <?php endforeach; ?>
        <?php if ($categoria_corrente !== '') echo '</div>'; ?>

        <div class="form-actions">
            <button type="submit" name="submit_action" value="bozza">Salva Bozza</button>
            <button type="submit" name="submit_action" value="invia" onclick="return confirm('Inviare per revisione?')">Invia per Revisione</button>
        </div>
    </form>
    <a href="dashboard_azienda.php">Torna alla Dashboard</a>
</div>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
