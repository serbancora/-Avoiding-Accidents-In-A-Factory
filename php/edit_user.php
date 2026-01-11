<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin']);
$page_title = "Editare utilizator – SafeFactory";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php?msg=invalid_id");
    exit();
}

$id = (int)$_GET['id'];

if (isset($_SESSION['id_utilizator']) && (int)$_SESSION['id_utilizator'] === $id) {
    header("Location: admin_dashboard.php?msg=cannot_edit_self");
    exit();
}

$sql = "SELECT u.id_utilizator, u.username, u.rol, a.nume, a.prenume
        FROM Utilizator u
        JOIN Angajat a ON u.Id_angajat = a.id_angajat
        WHERE u.id_utilizator = ?";

$stmt = sqlsrv_query($conn, $sql, [$id]);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

$user = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
if (!$user) {
    header("Location: admin_dashboard.php?msg=invalid_id");
    exit();
}

include "_layout_top.php";
include "_sidebar.php";
?>

<div class="main-content">
    <h1>Editare utilizator</h1>
    <p><b><?= htmlspecialchars($user['nume'] . " " . $user['prenume']) ?></b></p>

    <form method="post" action="update_user.php" class="form-card">
        <input type="hidden" name="id_utilizator" value="<?= (int)$user['id_utilizator'] ?>">

        <label>Username</label>
        <input type="text" name="username" required value="<?= htmlspecialchars($user['username']) ?>">

        <label>Rol</label>
        <select name="rol" required>
            <option value="admin" <?= $user['rol'] === 'admin' ? 'selected' : '' ?>>admin</option>
            <option value="ssm" <?= $user['rol'] === 'ssm' ? 'selected' : '' ?>>ssm</option>
            <option value="manager" <?= $user['rol'] === 'manager' ? 'selected' : '' ?>>manager</option>
        </select>

        <div class="action-buttons" style="margin-top:14px;">
            <button class="btn btn-edit" type="submit" style="border:none;">💾 Salvează</button>
            <a class="btn btn-delete" href="admin_dashboard.php" style="background:#6c7a89;">↩ Înapoi</a>
        </div>
    </form>
</div>

<?php include "_layout_bottom.php"; ?>
