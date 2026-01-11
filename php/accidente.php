<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin','ssm','manager']);
$page_title = "Accidente – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";

require_once "accidente_repo.php";

$rol = $_SESSION['rol'];
$dept_id = $_SESSION['id_departament'] ?? null;

$filters = [
    'q' => trim($_GET['q'] ?? ''),
    'from' => $_GET['from'] ?? '',
    'to' => $_GET['to'] ?? '',
    'gravitate' => $_GET['gravitate'] ?? '',
    'departament' => $_GET['departament'] ?? ''
];

$departamente = [];
if ($rol === 'admin' || $rol === 'ssm') {
    $departamente = get_departamente($conn);
}

$accidente = get_accidente($conn, $rol, $dept_id, $filters);
?>

<div class="main-content">
    <h1>Accidente</h1>

    <?php if ($rol === 'admin' || $rol === 'ssm') { ?>
        <div style="margin: 15px 0;">
            <a class="btn btn-edit" href="accident_form.php">➕ Înregistrează accident</a>
        </div>
    <?php } else { ?>
        <div class="alert">Manager: poți doar vizualiza accidentele care implică angajați din departamentul tău.</div>
    <?php } ?>

    <div class="filter-card">
        <form method="get" class="filter-form">

            <div class="filter-group">
            <label>Căutare (locație / descriere)</label>
            <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>">
            </div>

            <div class="filter-group">
            <label>De la</label>
            <input type="date" name="from" value="<?= htmlspecialchars($filters['from']) ?>">
            </div>

            <div class="filter-group">
            <label>Până la</label>
            <input type="date" name="to" value="<?= htmlspecialchars($filters['to']) ?>">
            </div>

            <div class="filter-group">
            <label>Gravitate</label>
            <select name="gravitate">
                <option value="">— orice —</option>
                <option value="minor" <?= $filters['gravitate']==='minor'?'selected':'' ?>>minor</option>
                <option value="grav" <?= $filters['gravitate']==='grav'?'selected':'' ?>>grav</option>
                <option value="mortal" <?= $filters['gravitate']==='mortal'?'selected':'' ?>>mortal</option>
            </select>
            </div>

            <?php if ($rol === 'admin' || $rol === 'ssm') { ?>
            <div class="filter-group">
                <label>Departament</label>
                <select name="departament">
                <option value="">— toate —</option>
                <?php foreach ($departamente as $d) { ?>
                    <option value="<?= (int)$d['id_departament'] ?>"
                    <?= ((string)$filters['departament'] === (string)$d['id_departament'])?'selected':'' ?>>
                    <?= htmlspecialchars($d['nume_departament']) ?>
                    </option>
                <?php } ?>
                </select>
            </div>
            <?php } ?>

            <div class="filter-actions">
            <button class="btn btn-edit" type="submit">Filtrează</button>
            <a class="btn btn-secondary" href="accidente.php">Reset</a>
            </div>

        </form>
        </div>

    <div class="info-box">
        <?php if (count($accidente) === 0) { ?>
            <p>Nu există accidente pentru filtrele selectate.</p>
        <?php } else { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Ora</th>
                        <th>Locație</th>
                        <th>Gravitate</th>
                        <th>Raportat de</th>
                        <th>Angajați implicați</th>
                        <th>Departamente implicate</th>
                        <?php if ($rol === 'admin' || $rol === 'ssm') { ?>
                            <th>Acțiuni</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($accidente as $a) { ?>
                        <tr>
                            <td><?= htmlspecialchars($a['data_accident'] instanceof DateTime ? $a['data_accident']->format('Y-m-d') : (string)$a['data_accident']) ?></td>
                            <td><?= htmlspecialchars($a['ora_accident'] instanceof DateTime ? $a['ora_accident']->format('H:i:s') : (string)$a['ora_accident']) ?></td>
                            <td><?= htmlspecialchars((string)$a['locatie']) ?></td>
                            <td><?= htmlspecialchars((string)$a['gravitate']) ?></td>
                            <td><?= htmlspecialchars((string)$a['raportat_de']) ?></td>
                            <td><?= htmlspecialchars((string)($a['angajati_implicati'] ?? '')) ?></td>
                            <td><?= htmlspecialchars((string)($a['departamente_implicate'] ?? '')) ?></td>

                            <?php if ($rol === 'admin' || $rol === 'ssm') { ?>
                                <td class="action-cell">
                                <div class="action-buttons">
                                    <a class="btn btn-edit" href="accident_form.php?id=<?= (int)$a['id_accident'] ?>">Editează</a>
                                </div>
                                </td>
                            <?php } ?>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
</div>

<?php include "_layout_bottom.php"; ?>
