<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin']);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: admin_dashboard.php?msg=invalid_id");
    exit();
}

$id = (int)$_GET['id'];

if (isset($_SESSION['id_utilizator']) && (int)$_SESSION['id_utilizator'] === $id) {
    header("Location: admin_dashboard.php?msg=cannot_delete_self");
    exit();
}

$sql = "DELETE FROM Utilizator WHERE id_utilizator = ?";
$stmt = sqlsrv_query($conn, $sql, [$id]);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    $is_fk_error = false;

    if (is_array($errors)) {
        foreach ($errors as $e) {
            if (isset($e['code']) && (int)$e['code'] === 547) {
                $is_fk_error = true;
                break;
            }
        }
    }

    if ($is_fk_error) {
        header("Location: admin_dashboard.php?msg=user_has_accidents");
        exit();
    }

    header("Location: admin_dashboard.php?msg=delete_failed");
    exit();
}

header("Location: admin_dashboard.php?msg=delete_success");
exit();
