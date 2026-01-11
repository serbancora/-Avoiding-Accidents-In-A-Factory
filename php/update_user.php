<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_dashboard.php");
    exit();
}

$id       = isset($_POST['id_utilizator']) ? (int)$_POST['id_utilizator'] : 0;
$username = isset($_POST['username']) ? trim($_POST['username']) : "";
$rol      = isset($_POST['rol']) ? trim($_POST['rol']) : "";

if ($id <= 0 || $username === "" || !in_array($rol, ['admin','ssm','manager'], true)) {
    header("Location: admin_dashboard.php?msg=update_failed");
    exit();
}

if (isset($_SESSION['id_utilizator']) && (int)$_SESSION['id_utilizator'] === $id) {
    header("Location: admin_dashboard.php?msg=cannot_edit_self");
    exit();
}

$sql = "UPDATE Utilizator
        SET username = ?, rol = ?
        WHERE id_utilizator = ?";

$stmt = sqlsrv_query($conn, $sql, [$username, $rol, $id]);
if ($stmt === false) {
    header("Location: admin_dashboard.php?msg=update_failed");
    exit();
}

header("Location: admin_dashboard.php?msg=update_success");
exit();
