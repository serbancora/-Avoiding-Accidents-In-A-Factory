<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin']);

$page_title = "Admin – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";

require_once "statistici_repo.php";

// Admin vede global
$rol = $_SESSION['rol'];
$dept_id = null;

$kpi = stats_kpi_accidente($conn, $rol, $dept_id);
$by_month = stats_accidente_pe_luna($conn, $rol, $dept_id);

// ultimele 3 luni
$last3 = array_slice($by_month, -3);
$last3_txt = [];
foreach ($last3 as $r) {
    $last3_txt[] = (string)$r['luna'] . ": " . (int)$r['nr'];
}
?>

<div class="main-content">
    <h1>Bun venit, Admin</h1>
    <p>Panou de administrare general al aplicației SafeFactory.</p>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Total accidente</div>
            <div class="stat-value"><?= (int)$kpi['total'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Minor</div>
            <div class="stat-value"><?= (int)$kpi['minor'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Grav</div>
            <div class="stat-value"><?= (int)$kpi['grav'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Mortal</div>
            <div class="stat-value"><?= (int)$kpi['mortal'] ?></div>
        </div>
    </div>

    <div class="info-box">
        <h2>Acces rapid</h2>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
            <a class="btn btn-edit" href="utilizatori.php">Utilizatori</a>
            <a class="btn btn-edit" href="departamente.php">Departamente</a>
            <a class="btn btn-edit" href="accidente.php">Accidente</a>
            <a class="btn btn-edit" href="cauze.php">Cauze</a>
            <a class="btn btn-edit" href="masuri.php">Măsuri</a>
            <a class="btn btn-edit" href="statistici.php">Statistici</a>
        </div>

        <p style="margin-top:12px; color:#555;">
            Ultimele 3 luni: <b><?= htmlspecialchars(implode(" | ", $last3_txt)) ?></b>
        </p>
    </div>

    <div class="info-box" style="margin-top:14px;">
        <h2>Ce poți face ca Admin</h2>
        <ul>
            <li>Gestionezi utilizatori și departamente</li>
            <li>Ai acces complet la accidente, cauze și măsuri</li>
            <li>Vizualizezi statistici globale</li>
        </ul>
    </div>
</div>

<?php include "_layout_bottom.php"; ?>
