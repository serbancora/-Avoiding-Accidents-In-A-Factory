<?php
session_start();
include "../connect.php";

// verificare rol
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== "ssm") {
    header("Location: ../html/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title>Responsabil SSM – SafeFactory</title>
    <link rel="stylesheet" href="../css/style_dashboard.css">
</head>

<body>
    <div class="sidebar">
        <h2>🦺 SSM</h2>
        <ul>
            <li><a href="#">Înregistrează Accident</a></li>
            <li><a href="#">Cauze</a></li>
            <li><a href="#">Măsuri</a></li>
            <li><a href="#">Toate Accidentele</a></li>
            <li><a href="#">Statistici</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <h1>Bun venit, Responsabil SSM</h1>
        <p>Te rugăm să selectezi o acțiune din meniul din stânga.</p>

        <div class="info-box">
            <h2>Priorități SSM</h2>
            <ul>
                <li>Înregistrează un accident nou imediat după producere</li>
                <li>Actualizează statusul măsurilor corective</li>
                <li>Identifică și adaugă cauzele accidentelor</li>
                <li>Monitorizează implementarea măsurilor în timp</li>
                <li>Generează rapoarte și statistici pentru management</li>
            </ul>
        </div>
    </div>
</body>
</html>
