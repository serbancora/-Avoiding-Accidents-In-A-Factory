<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin']);

$page_title = "Administrator – SafeFactory";

// interogare: lista utilizatorilor
$sql = "SELECT 
            u.id_utilizator, 
            u.username, 
            u.rol, 
            a.nume, 
            a.prenume,
            a.functie
        FROM Utilizator u
        JOIN Angajat a ON u.Id_angajat = a.id_angajat
        ORDER BY u.id_utilizator";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$my_id = isset($_SESSION['id_utilizator']) ? (int)$_SESSION['id_utilizator'] : 0;

function msg_text($msg) {
    if ($msg === 'create_success') return "✅ Utilizatorul a fost creat cu succes.";
    if ($msg === 'create_failed') return "❗ Crearea utilizatorului a eșuat.";
    if ($msg === 'username_taken') return "❗ Username deja existent. Alege alt username.";

    if ($msg === 'user_has_accidents') return "❗ Nu poți șterge acest utilizator deoarece are accidente asociate în sistem.";
    if ($msg === 'cannot_delete_self') return "❗ Nu îți poți șterge propriul cont.";
    if ($msg === 'cannot_edit_self') return "❗ Nu îți poți edita propriul cont din panoul de administrare.";

    if ($msg === 'delete_success') return "✅ Utilizatorul a fost șters cu succes.";
    if ($msg === 'delete_failed') return "❗ Ștergerea a eșuat. Încearcă din nou.";
    if ($msg === 'invalid_id') return "❗ ID invalid.";

    if ($msg === 'update_success') return "✅ Utilizatorul a fost actualizat cu succes.";
    if ($msg === 'update_failed') return "❗ Actualizarea a eșuat.";
    return "";
}

include "_layout_top.php";
include "_sidebar.php";
?>

<div class="main-content">
    <h1>Utilizatori</h1>
    <p>Listă completă a utilizatorilor înregistrați în sistem.</p>

    <div style="margin: 15px 0;">
        <a class="btn btn-edit" href="create_user.php">➕ Adaugă utilizator</a>
    </div>

    <?php if (isset($_GET['msg']) && msg_text($_GET['msg']) !== ""): ?>
        <div class="alert">
            <?= htmlspecialchars(msg_text($_GET['msg'])) ?>
        </div>
    <?php endif; ?>

    <table class="data-table">
        <tr>
            <th>ID</th>
            <th>Nume Angajat</th>
            <th>Funcție</th>
            <th>Username</th>
            <th>Rol</th>
            <th>Acțiuni</th>
        </tr>

        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
            $row_id = (int)$row['id_utilizator'];
            $is_me = ($my_id > 0 && $row_id === $my_id);
        ?>
            <tr>
                <td><?= $row_id ?></td>
                <td><?= htmlspecialchars($row['nume'] . ' ' . $row['prenume']) ?></td>
                <td><?= htmlspecialchars($row['functie']) ?></td>
                <td><?= htmlspecialchars($row['username']) ?></td>
                <td><?= htmlspecialchars($row['rol']) ?></td>

                <td class="action-cell">
                    <?php if ($is_me) { ?>
                        <span class="muted">—</span>
                    <?php } else { ?>
                        <div class="action-buttons">
                            <a class="btn btn-edit" href="edit_user.php?id=<?= $row_id ?>">✏️ Editare</a>
                            <a class="btn btn-delete"
                               href="delete_user.php?id=<?= $row_id ?>"
                               onclick="return confirm('Sigur vrei să ștergi acest utilizator?');">🗑️ Ștergere</a>
                        </div>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<?php include "_layout_bottom.php"; ?>
