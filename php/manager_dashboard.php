<?php
session_start();
include "../connect.php";

// verificare rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "manager") {
    header("Location: ../html/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Manager – SafeFactory</title>
    <link rel="stylesheet" href="../css/style_dashboard.css">
</head>

<body>
    <div class="sidebar">
        <h2>📊 Manager</h2>
        <ul>
            <li><a href="#">Accidente Departament</a></li>
            <li><a href="#">Status Măsuri</a></li>
            <li><a href="#">Statistici</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Bun venit, Manager</h1>
        <p>Aici vei putea vizualiza accidentele din departamentul tău.</p>

        <div class="info-box">
            <h2>Informații utile</h2>
            <ul>
                <li>Monitorizează accidentele din departament</li>
                <li>Verifică implementarea măsurilor corective</li>
                <li>Analizează statisticile specifice departamentului</li>
                <li>Comunică cu SSM pentru actualizări</li>
            </ul>
        </div>
    </div>
</body>
</html>
