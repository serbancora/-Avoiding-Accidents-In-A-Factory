<?php
session_start();
include "../connect.php";
include "_auth.php";

// Asigură dept_id pentru manager (dacă nu e în sesiune)
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'manager' && empty($_SESSION['id_departament'])) {

    if (!empty($_SESSION['id_utilizator'])) {
        $stmt = sqlsrv_query(
            $conn,
            "select u.id_angajat, a.id_departament
             from Utilizator u
             join Angajat a on a.id_angajat = u.id_angajat
             where u.id_utilizator = ?",
            [(int)$_SESSION['id_utilizator']]
        );

        if ($stmt === false) {
            // ca să vezi rapid dacă e problemă de query/conn
            // echo "<pre>"; print_r(sqlsrv_errors()); echo "</pre>";
        } else {
            $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
            if ($row) {
                $_SESSION['id_angajat'] = (int)$row['id_angajat'];
                $_SESSION['id_departament'] = (int)$row['id_departament'];
            }
        }
    }
}

// doar manager
require_roles(['manager']);

$page_title = "Manager – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";

// KPI-uri + mini sumar (scope pe departamentul managerului)
require_once "statistici_repo.php";

$rol = $_SESSION['rol'];
$dept_id = $_SESSION['id_departament'] ?? null;

$kpi = stats_kpi_accidente($conn, $rol, $dept_id);
$by_month = stats_accidente_pe_luna($conn, $rol, $dept_id);

// ultimele 3 luni (pentru un mic sumar)
$last3 = array_slice($by_month, -3);
$last3_txt = [];
foreach ($last3 as $r) {
    $last3_txt[] = (string)$r['luna'] . ": " . (int)$r['nr'];
}
?>

<div class="main-content">
    <h1>Bun venit, Manager</h1>
    <p>Aici vezi rapid situația accidentelor din departamentul tău și ai link direct către rapoarte.</p>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-title">Total accidente (dept.)</div>
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

    <div class="info-box" style="margin-top: 14px;">
        <h2>Acces rapid</h2>
        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:10px;">
            <a class="btn btn-edit" href="accidente.php">Accidente departament</a>
            <a class="btn btn-edit" href="masuri.php">Măsuri (status)</a>
            <a class="btn btn-edit" href="statistici.php">Statistici</a>
        </div>
        <p style="margin-top:12px; color:#555;">
            Ultimele 3 luni: <b><?= htmlspecialchars(implode(" | ", $last3_txt)) ?></b>
        </p>
    </div>

    <div class="info-box" style="margin-top: 14px;">
        <h2>Ce poți face ca Manager</h2>
        <ul>
            <li>Vizualizezi doar accidentele asociate departamentului tău</li>
            <li>Verifici statusul măsurilor și termenele de implementare</li>
            <li>Analizezi statisticile specifice departamentului</li>
        </ul>
    </div>
</div>

<?php include "_layout_bottom.php"; ?>
