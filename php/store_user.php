<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: admin_dashboard.php");
    exit();
}

$id_angajat = isset($_POST['id_angajat']) ? (int)$_POST['id_angajat'] : 0;
$username   = isset($_POST['username']) ? trim($_POST['username']) : "";
$parola     = isset($_POST['parola']) ? trim($_POST['parola']) : "";
$rol        = isset($_POST['rol']) ? trim($_POST['rol']) : "";

if ($id_angajat <= 0 || $username === "" || $parola === "" || !in_array($rol, ['admin','ssm','manager'], true)) {
    header("Location: admin_dashboard.php?msg=create_failed");
    exit();
}

$sql = "INSERT INTO Utilizator (Id_angajat, username, parola, rol)
        VALUES (?, ?, ?, ?)";

$stmt = sqlsrv_query($conn, $sql, [$id_angajat, $username, $parola, $rol]);

if ($stmt === false) {
    $errors = sqlsrv_errors();
    if (is_array($errors)) {
        foreach ($errors as $e) {
            if (isset($e['code']) && ((int)$e['code'] === 2601 || (int)$e['code'] === 2627)) {
                header("Location: admin_dashboard.php?msg=username_taken");
                exit();
            }
        }
    }
    header("Location: admin_dashboard.php?msg=create_failed");
    exit();
}

header("Location: admin_dashboard.php?msg=create_success");
exit();
