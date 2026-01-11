<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin','ssm','manager']);
$page_title = "Statistici – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";

require_once "statistici_repo.php";

$rol = $_SESSION['rol'];
$dept_id = $_SESSION['id_departament'] ?? null;

// KPI
$kpi = stats_kpi_accidente($conn, $rol, $dept_id);

// grafice
$by_month = stats_accidente_pe_luna($conn, $rol, $dept_id);
$by_dept  = stats_accidente_pe_departament($conn, $rol, $dept_id);
$top_cauze = stats_top_cauze($conn, $rol, $dept_id);
$masuri_status = stats_masuri_status($conn, $rol, $dept_id);

// pregătim arrays pentru JS
$months_labels = array_map(fn($r) => (string)$r['luna'], $by_month);
$months_values = array_map(fn($r) => (int)$r['nr'], $by_month);

$dept_labels = array_map(fn($r) => (string)$r['departament'], $by_dept);
$dept_values = array_map(fn($r) => (int)$r['nr'], $by_dept);

$cauze_labels = array_map(fn($r) => (string)$r['cauza'], $top_cauze);
$cauze_values = array_map(fn($r) => (int)$r['nr'], $top_cauze);

$ms_labels = array_map(fn($r) => (string)$r['status'], $masuri_status);
$ms_values = array_map(fn($r) => (int)$r['nr'], $masuri_status);
?>

<div class="main-content">
  <h1>Statistici</h1>

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

  <div class="charts-grid">
    <div class="chart-card">
      <h3>Accidente pe lună (ultimele 12 luni)</h3>
      <canvas id="chartMonths"></canvas>
    </div>

    <div class="chart-card">
      <h3>Distribuție gravitate</h3>
      <canvas id="chartSeverity"></canvas>
    </div>

    <div class="chart-card">
      <h3>Accidente pe departament</h3>
      <canvas id="chartDept"></canvas>
    </div>

    <div class="chart-card">
      <h3>Top cauze</h3>
      <canvas id="chartCauses"></canvas>
    </div>

    <div class="chart-card">
      <h3>Măsuri: status</h3>
      <canvas id="chartMasuri"></canvas>
    </div>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const monthsLabels = <?= json_encode($months_labels) ?>;
const monthsValues = <?= json_encode($months_values) ?>;

const deptLabels = <?= json_encode($dept_labels) ?>;
const deptValues = <?= json_encode($dept_values) ?>;

const causesLabels = <?= json_encode($cauze_labels) ?>;
const causesValues = <?= json_encode($cauze_values) ?>;

const msLabels = <?= json_encode($ms_labels) ?>;
const msValues = <?= json_encode($ms_values) ?>;

const severityLabels = ["minor","grav","mortal"];
const severityValues = [<?= (int)$kpi['minor'] ?>, <?= (int)$kpi['grav'] ?>, <?= (int)$kpi['mortal'] ?>];

// Line: accidente pe lună
new Chart(document.getElementById('chartMonths'), {
  type: 'line',
  data: {
    labels: monthsLabels,
    datasets: [{
      label: 'Accidente',
      data: monthsValues,
      tension: 0.3,
      fill: false
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: true } }
  }
});

// Doughnut: gravitate
new Chart(document.getElementById('chartSeverity'), {
  type: 'doughnut',
  data: {
    labels: severityLabels,
    datasets: [{
      data: severityValues
    }]
  },
  options: { responsive: true }
});

// Bar: departamente
new Chart(document.getElementById('chartDept'), {
  type: 'bar',
  data: {
    labels: deptLabels,
    datasets: [{
      label: 'Accidente',
      data: deptValues
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } }
  }
});

// Bar: top cauze
new Chart(document.getElementById('chartCauses'), {
  type: 'bar',
  data: {
    labels: causesLabels,
    datasets: [{
      label: 'Accidente',
      data: causesValues
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } }
  }
});

// Bar: masuri status
new Chart(document.getElementById('chartMasuri'), {
  type: 'bar',
  data: {
    labels: msLabels,
    datasets: [{
      label: 'Măsuri',
      data: msValues
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false } }
  }
});
</script>

<?php include "_layout_bottom.php"; ?>
