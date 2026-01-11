<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin']);
$page_title = "Adaugă utilizator – SafeFactory";

// angajați fără cont
$sql = "SELECT a.id_angajat, a.nume, a.prenume, a.functie
        FROM Angajat a
        LEFT JOIN Utilizator u ON u.Id_angajat = a.id_angajat
        WHERE u.Id_angajat IS NULL
        ORDER BY a.nume, a.prenume";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

include "_layout_top.php";
include "_sidebar.php";
?>

<div class="main-content">
    <h1>Adaugă utilizator</h1>

    <form method="post" action="store_user.php" class="form-card">
        <label>Angajat</label>
        <select name="id_angajat" required>
            <option value="">-- selectează angajat --</option>
            <?php while ($a = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
                <option value="<?= (int)$a['id_angajat'] ?>">
                    <?= htmlspecialchars($a['nume'] . " " . $a['prenume'] . " – " . $a['functie']) ?>
                </option>
            <?php } ?>
        </select>

        <label>Username</label>
        <input type="text" name="username" required>

        <label>Parola</label>
        <input type="password" name="parola" required>

        <label>Rol</label>
        <select name="rol" required>
            <option value="admin">admin</option>
            <option value="ssm">ssm</option>
            <option value="manager">manager</option>
        </select>

        <div class="action-buttons" style="margin-top:14px;">
            <button class="btn btn-edit" type="submit" style="border:none;">💾 Salvează</button>
            <a class="btn btn-delete" href="admin_dashboard.php" style="background:#6c7a89;">↩ Înapoi</a>
        </div>
    </form>
</div>

<?php include "_layout_bottom.php"; ?>
