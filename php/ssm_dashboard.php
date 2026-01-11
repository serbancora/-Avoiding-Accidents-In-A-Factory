<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['ssm']);

$page_title = "SSM – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";

require_once "statistici_repo.php";

// SSM vede global (fără scope pe departament)
$rol = $_SESSION['rol'];
$dept_id = null;

// KPI
$kpi = stats_kpi_accidente($conn, $rol, $dept_id);

// date pt sumar “ultimele 3 luni”
$by_month = stats_accidente_pe_luna($conn, $rol, $dept_id);
$last3 = array_slice($by_month, -3);
$last3_txt = [];
foreach ($last3 as $r) {
    $last3_txt[] = (string)$r['luna'] . ": " . (int)$r['nr'];
}

// măsuri status (in curs / finalizat) – global
$ms = stats_masuri_status($conn, $rol, $dept_id);
$ms_map = ['in curs' => 0, 'finalizat' => 0];
foreach ($ms as $row) {
    $ms_map[(string)$row['status']] = (int)$row['nr'];
}
?>

<div class="main-content">
    <h1>Bun venit, SSM</h1>
    <p>Aici gestionezi accidentele, cauzele și măsurile, și poți urmări indicatorii principali.</p>

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

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Măsuri în curs</div>
            <div class="stat-value"><?= (int)$ms_map['in curs'] ?></div>
        </div>
        <div class="stat-card">
            <div class="stat-title">Măsuri finalizate</div>
            <div class="stat-value"><?= (int)$ms_map['finalizat'] ?></div>
        </div>
    </div>

    <div class="info-box" style="margin-top: 14px;">
        <h2>Acces rapid</h2>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
            <a class="btn btn-edit" href="accidente.php">Accidente</a>
            <a class="btn btn-edit" href="cauze.php">Cauze</a>
            <a class="btn btn-edit" href="masuri.php">Măsuri</a>
            <a class="btn btn-edit" href="statistici.php">Statistici</a>
        </div>
        <p style="margin-top:12px; color:#555;">
            Ultimele 3 luni: <b><?= htmlspecialchars(implode(" | ", $last3_txt)) ?></b>
        </p>
    </div>

    <div class="info-box" style="margin-top: 14px;">
        <h2>Ce poți face ca SSM</h2>
        <ul>
            <li>Înregistrezi și actualizezi accidente</li>
            <li>Adaugi/editezi cauze și măsuri corective</li>
            <li>Urmărești progresul măsurilor și indicatorii principali</li>
        </ul>
    </div>
</div>

<?php include "_layout_bottom.php"; ?>
