<?php
session_start();
require_once '../../includes/db.php';
require_once '../../includes/auth.php';
requireRole('admin');

// Gestione azioni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $nome = $_POST['nome'];
        $cognome = $_POST['cognome'];
        $email = $_POST['email'];
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $ruolo = $_POST['ruolo'];

        $stmt = $pdo->prepare("INSERT INTO Utenti (nome, cognome, email, password, ruolo) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $cognome, $email, $password, $ruolo]);
        $msg = "Utente creato con successo.";
    } elseif ($action === 'delete') {
        $id = (int)$_POST['id_utente'];
        $stmt = $pdo->prepare("DELETE FROM Utenti WHERE id_utente = ?");
        $stmt->execute([$id]);
        $msg = "Utente eliminato.";
    } elseif ($action === 'toggle_active') {
        $id = (int)$_POST['id_utente'];
        $active = (int)$_POST['active'];
        $stmt = $pdo->prepare("UPDATE Utenti SET attivo = ? WHERE id_utente = ?");
        $stmt->execute([$active, $id]);
        $msg = "Stato utente aggiornato.";
    }
}

$utenti = $pdo->query("SELECT * FROM Utenti ORDER BY ruolo, cognome")->fetchAll();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Gestione Utenti - ESG Balance</title>
    <link rel="stylesheet" href="../../../public/css/style.css">
</head>
<body>
<?php require_once '../../includes/header.php'; ?>
<div class="container">
    <h2>Gestione Utenti</h2>
    <?php if (isset($msg)) echo "<p class='msg'>$msg</p>"; ?>

    <h3>Aggiungi Utente</h3>
    <form method="POST">
        <input type="hidden" name="action" value="create">
        <input type="text" name="nome" placeholder="Nome" required>
        <input type="text" name="cognome" placeholder="Cognome" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <select name="ruolo" required>
            <option value="admin">Admin</option>
            <option value="revisore">Revisore ESG</option>
            <option value="azienda">Azienda</option>
        </select>
        <button type="submit">Crea Utente</button>
    </form>

    <h3>Elenco Utenti</h3>
    <table class="data-table">
        <thead>
            <tr>
                <th>ID</th><th>Nome</th><th>Cognome</th><th>Email</th><th>Ruolo</th><th>Attivo</th><th>Azioni</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($utenti as $u): ?>
            <tr>
                <td><?= $u['id_utente'] ?></td>
                <td><?= htmlspecialchars($u['nome']) ?></td>
                <td><?= htmlspecialchars($u['cognome']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= $u['ruolo'] ?></td>
                <td><?= $u['attivo'] ? 'Si' : 'No' ?></td>
                <td>
                    <form method="POST" style="display:inline">
                        <input type="hidden" name="action" value="toggle_active">
                        <input type="hidden" name="id_utente" value="<?= $u['id_utente'] ?>">
                        <input type="hidden" name="active" value="<?= $u['attivo'] ? 0 : 1 ?>">
                        <button type="submit"><?= $u['attivo'] ? 'Disattiva' : 'Attiva' ?></button>
                    </form>
                    <form method="POST" style="display:inline" onsubmit="return confirm('Eliminare?')">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_utente" value="<?= $u['id_utente'] ?>">
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
