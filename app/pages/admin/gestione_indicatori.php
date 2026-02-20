<?php
// =============================================================
// ESG-BALANCE - Admin: Gestione Indicatori ESG
// =============================================================
require_once __DIR__ . '/../../includes/auth.php';
requireRole(['amministratore']);
require_once __DIR__ . '/../../includes/db.php';
;

$pdo     = getDBConnection();
$errore  = '';
$success = '';

// Inserimento nuovo indicatore
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['azione']) && $_POST['azione'] === 'inserisci') {
    $nome       = trim($_POST['nome_indicatore'] ?? '');
    $descr      = trim($_POST['descrizione'] ?? '');
    $rilevanza  = (int)($_POST['rilevanza'] ?? 5);
    $categoria  = $_POST['categoria'] ?? '';
    $codNorm    = trim($_POST['codice_normativa'] ?? '');
    $ambito     = trim($_POST['ambito'] ?? '');
    $frequenza  = trim($_POST['frequenza'] ?? '');

    if (empty($nome) || empty($categoria)) {
        $errore = 'Nome e categoria sono obbligatori.';
    } else {
        try {
            $stmt = $pdo->prepare('CALL sp_inserisci_indicatore_esg(:nome, :descr, NULL, :rilevanza, :categoria)');
            $stmt->execute([':nome' => $nome, ':descr' => $descr, ':rilevanza' => $rilevanza, ':categoria' => $categoria]);
            $idNew = $pdo->lastInsertId();

            if ($categoria === 'ambientale' && !empty($codNorm)) {
                $pdo->prepare('INSERT INTO indicatori_ambientali (id_indicatore, codice_normativa) VALUES (?, ?)')
                    ->execute([$idNew, $codNorm]);
            } elseif ($categoria === 'sociale') {
                $pdo->prepare('INSERT INTO indicatori_sociali (id_indicatore, ambito, frequenza_rilevazione) VALUES (?, ?, ?)')
                    ->execute([$idNew, $ambito, $frequenza]);
            }

            require_once __DIR__ . '/../../../mongodb/log_events.php';
            logEvento('crea_indicatore', "Admin ha creato indicatore ESG: $nome", ['id_indicatore' => $idNew, 'categoria' => $categoria]);
            $success = "Indicatore '$nome' aggiunto con successo.";
        } catch (PDOException $e) {
            $errore = 'Errore: ' . $e->getMessage();
        }
    }
}

// Lista indicatori
$indicatori = $pdo->query(
    'SELECT ie.id_indicatore, ie.nome_indicatore, ie.rilevanza, ie.categoria,
            ia.codice_normativa, is2.ambito, is2.frequenza_rilevazione
     FROM indicatori_esg ie
     LEFT JOIN indicatori_ambientali ia ON ie.id_indicatore = ia.id_indicatore
     LEFT JOIN indicatori_sociali is2 ON ie.id_indicatore = is2.id_indicatore
     ORDER BY ie.categoria, ie.nome_indicatore'
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Indicatori ESG - Admin</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body>
<nav class="navbar navbar-dark bg-success">
    <div class="container-fluid">
        <span class="navbar-brand">ESG-BALANCE - Gestione Indicatori ESG</span>
        <a href="dashboard_admin.php" class="btn btn-outline-light btn-sm">Dashboard</a>
    </div>
</nav>
<div class="container mt-4">
    <?php if ($errore): ?><div class="alert alert-danger"><?= htmlspecialchars($errore) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>

    <div class="row">
        <!-- Form aggiunta indicatore -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-success text-white">Nuovo Indicatore ESG</div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="azione" value="inserisci">
                        <div class="mb-2">
                            <label class="form-label">Nome *</label>
                            <input type="text" class="form-control" name="nome_indicatore" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Descrizione</label>
                            <textarea class="form-control" name="descrizione" rows="2"></textarea>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Rilevanza (0-10)</label>
                            <input type="number" class="form-control" name="rilevanza" min="0" max="10" value="5">
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Categoria *</label>
                            <select class="form-select" name="categoria" id="sel-categoria" required onchange="toggleCampiSpecifici()">
                                <option value="">-- Seleziona --</option>
                                <option value="ambientale">Ambientale</option>
                                <option value="sociale">Sociale</option>
                                <option value="altro">Altro</option>
                            </select>
                        </div>
                        <div id="campo-ambientale" style="display:none" class="mb-2">
                            <label class="form-label">Codice Normativa</label>
                            <input type="text" class="form-control" name="codice_normativa">
                        </div>
                        <div id="campo-sociale" style="display:none">
                            <div class="mb-2">
                                <label class="form-label">Ambito</label>
                                <input type="text" class="form-control" name="ambito">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Frequenza Rilevazione</label>
                                <input type="text" class="form-control" name="frequenza">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mt-2">Aggiungi Indicatore</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lista indicatori -->
        <div class="col-md-8">
            <h5>Indicatori ESG registrati (<?= count($indicatori) ?>)</h5>
            <table class="table table-sm table-bordered">
                <thead class="table-success">
                    <tr><th>Nome</th><th>Categoria</th><th>Rilevanza</th><th>Extra</th></tr>
                </thead>
                <tbody>
                <?php foreach ($indicatori as $ind): ?>
                    <tr>
                        <td><?= htmlspecialchars($ind['nome_indicatore']) ?></td>
                        <td><span class="badge bg-secondary"><?= $ind['categoria'] ?></span></td>
                        <td><?= $ind['rilevanza'] ?>/10</td>
                        <td>
                            <?php if ($ind['codice_normativa']): ?>Norma: <?= htmlspecialchars($ind['codice_normativa']) ?><?php endif; ?>
                            <?php if ($ind['ambito']): ?>Ambito: <?= htmlspecialchars($ind['ambito']) ?><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
function toggleCampiSpecifici() {
    const cat = document.getElementById('sel-categoria').value;
    document.getElementById('campo-ambientale').style.display = (cat === 'ambientale') ? 'block' : 'none';
    document.getElementById('campo-sociale').style.display = (cat === 'sociale') ? 'block' : 'none';
}
</script>
</body>
</html>
