<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $parola   = $_POST['parola'];

    // interogare SQL Server cu parametri
    $sql = "SELECT id_utilizator, username, parola, rol 
            FROM Utilizator
            WHERE username = ?";

    $params = [$username];

    $stmt = sqlsrv_query($conn, $sql, $params);

    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    // dacă găsim user
    if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {

        if ($row['parola'] === $parola) {

            $_SESSION['id_utilizator'] = $row['id_utilizator'];
            $_SESSION['rol']           = $row['rol'];

            // redirecționare în funcție de rol
            if ($row['rol'] === 'admin') {
                header("Location: php/admin_dashboard.php");
                exit();
            }
            if ($row['rol'] === 'ssm') {
                header("Location: php/ssm_dashboard.php");
                exit();
            }
            if ($row['rol'] === 'manager') {
                header("Location: php/manager_dashboard.php");
                exit();
            }

        } else {
            echo "Parolă greșită!";
        }

    } else {
        echo "Username invalid!";
    }
}
?>
