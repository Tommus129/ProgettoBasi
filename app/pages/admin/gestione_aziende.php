<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireRole('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $nome = $_POST['nome'];
        $settore = $_POST['settore'];
        $email_referente = $_POST['email_referente'];
        $id_utente = (int)$_POST['id_utente'];
        $stmt = $pdo->prepare("INSERT INTO Aziende (nome, settore, email_referente, id_utente) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $settore, $email_referente, $id_utente]);
        $msg = "Azienda creata.";
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id_azienda'];
        $stmt = $pdo->prepare("DELETE FROM Aziende WHERE id_azienda = ?");
        $stmt->execute([$id]);
        $msg = "Azienda eliminata.";
    }
}

$aziende = $pdo->query("
    SELECT a.*, u.nome AS nome_utente, u.cognome AS cognome_utente
    FROM Aziende a
    LEFT JOIN Utenti u ON a.id_utente = u.id_utente
    ORDER BY a.nome
")->fetchAll();

$utenti_azienda = $pdo->query("SELECT * FROM Utenti WHERE ruolo='azienda' ORDER BY cognome")->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Aziende - ESG Balance</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
</head>
<body>
<?php require_once '../../includes/header.php'; ?>
<div class="container">
    <h2>Gestione Aziende</h2>
    <?php if (isset($msg)) echo "<p class='msg'>$msg</p>"; ?>

    <h3>Aggiungi Azienda</h3>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <input type="text" name="nome" placeholder="Nome Azienda" required>
        <input type="text" name="settore" placeholder="Settore" required>
        <input type="email" name="email_referente" placeholder="Email Referente" required>
        <select name="id_utente" required>
            <option value="">-- Seleziona Utente Azienda --</option>
            <?php foreach ($utenti_azienda as $u): ?>
            <option value="<?= $u['id_utente'] ?>"><?= htmlspecialchars($u['cognome'].' '.$u['nome']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Crea Azienda</button>
    </form>

    <h3>Elenco Aziende</h3>
    <table class="data-table">
        <thead>
            <tr><th>ID</th><th>Nome</th><th>Settore</th><th>Email</th><th>Referente</th><th>Azioni</th></tr>
        </thead>
        <tbody>
        <?php foreach ($aziende as $az): ?>
            <tr>
                <td><?= $az['id_azienda'] ?></td>
                <td><?= htmlspecialchars($az['nome']) ?></td>
                <td><?= htmlspecialchars($az['settore']) ?></td>
                <td><?= htmlspecialchars($az['email_referente']) ?></td>
                <td><?= htmlspecialchars($az['cognome_utente'].' '.$az['nome_utente']) ?></td>
                <td>
                    <form method="POST" onsubmit="return confirm('Eliminare?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_azienda" value="<?= $az['id_azienda'] ?>">
                        <button type="submit" class="btn-danger">Elimina</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php require_once '../../includes/footer.php'; ?>
</body>
</html>
