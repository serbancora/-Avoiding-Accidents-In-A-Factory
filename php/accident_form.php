<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin','ssm']);
$page_title = "Formular Accident – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";

require_once "accidente_repo.php";

$id_utilizator = $_SESSION['id_utilizator'] ?? null;
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$errors = [];
$accident = [
    'data_accident' => '',
    'ora_accident' => '',
    'locatie' => '',
    'descriere' => '',
    'gravitate' => 'minor'
];

$selected_angajati = [];

if ($id) {
    $db_acc = get_accident_by_id($conn, $id);
    if (!$db_acc) {
        $errors[] = "Accident inexistent.";
    } else {
        // datele pot veni ca DateTime din sqlsrv
        $accident['data_accident'] = ($db_acc['data_accident'] instanceof DateTime) ? $db_acc['data_accident']->format('Y-m-d') : (string)$db_acc['data_accident'];
        $accident['ora_accident']  = ($db_acc['ora_accident'] instanceof DateTime) ? $db_acc['ora_accident']->format('H:i') : substr((string)$db_acc['ora_accident'], 0, 5);
        $accident['locatie'] = (string)$db_acc['locatie'];
        $accident['descriere'] = (string)$db_acc['descriere'];
        $accident['gravitate'] = (string)$db_acc['gravitate'];

        $selected_angajati = get_accident_angajati($conn, $id);
    }
}

$all_angajati = get_angajati($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accident['data_accident'] = $_POST['data_accident'] ?? '';
    $accident['ora_accident'] = $_POST['ora_accident'] ?? '';
    $accident['locatie'] = trim($_POST['locatie'] ?? '');
    $accident['descriere'] = trim($_POST['descriere'] ?? '');
    $accident['gravitate'] = $_POST['gravitate'] ?? 'minor';

    $ids = $_POST['id_angajat'] ?? [];
    $consecinte = $_POST['consecinta'] ?? [];
    $zile = $_POST['concediu_medical_zile'] ?? [];

    $selected_angajati = [];
    for ($i=0; $i<count($ids); $i++) {
        $aid = (int)$ids[$i];
        if ($aid <= 0) continue;

        $c = $consecinte[$i] ?? '';
        $cmz = (int)($zile[$i] ?? 0);

        $selected_angajati[] = [
            'id_angajat' => $aid,
            'consecinta' => $c,
            'concediu_medical_zile' => $cmz
        ];
    }

    if ($accident['data_accident'] === '') $errors[] = "Data accidentului este obligatorie.";
    if ($accident['ora_accident'] === '') $errors[] = "Ora accidentului este obligatorie.";
    if ($accident['locatie'] === '') $errors[] = "Locația este obligatorie.";
    if (!in_array($accident['gravitate'], ['minor','grav','mortal'], true)) $errors[] = "Gravitate invalidă.";
    if (!$id_utilizator) $errors[] = "Sesiune invalidă (id_utilizator lipsă).";
    if (count($selected_angajati) === 0) $errors[] = "Selectează cel puțin un angajat implicat.";

    foreach ($selected_angajati as $sa) {
        if (!in_array($sa['consecinta'], ['ranit usor','ranit grav','decedat'], true)) {
            $errors[] = "Consecință invalidă pentru unul dintre angajați.";
            break;
        }
        if ($sa['concediu_medical_zile'] < 0 || $sa['concediu_medical_zile'] > 255) {
            $errors[] = "Concediu medical (zile) trebuie să fie între 0 și 255.";
            break;
        }
    }

    if (count($errors) === 0) {
        sqlsrv_begin_transaction($conn);
        try {
            if (!$id) {
                // INSERT Accident + luam id-ul corect cu OUTPUT
                $sql = "
                    insert into Accident(data_accident, ora_accident, locatie, descriere, gravitate, id_utilizator)
                    output inserted.id_accident
                    values (?, ?, ?, ?, ?, ?)
                ";

                $params = [
                    $accident['data_accident'],
                    $accident['ora_accident'],
                    $accident['locatie'],
                    $accident['descriere'],
                    $accident['gravitate'],
                    (int)$id_utilizator
                ];

                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) {
                    $errs = sqlsrv_errors();
                    $msg = $errs ? $errs[0]['message'] : 'Unknown SQL error';
                    throw new Exception("Eroare insert Accident: " . $msg);
                }

                $row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC);
                $id = (int)$row['id_accident'];

                if ($id <= 0) {
                    throw new Exception("ID accident invalid după insert.");
                }
            }
            else {
                $sql = "
                    update Accident
                    set data_accident = ?, ora_accident = ?, locatie = ?, descriere = ?, gravitate = ?
                    where id_accident = ?
                ";
                $params = [
                    $accident['data_accident'],
                    $accident['ora_accident'],
                    $accident['locatie'],
                    $accident['descriere'],
                    $accident['gravitate'],
                    $id
                ];
                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) throw new Exception("Eroare update Accident.");

                // sterge legaturi vechi (NU sterge accidentul)
                $stmt = sqlsrv_query($conn, "delete from Angajat_Accident where id_accident = ?", [$id]);
                if ($stmt === false) throw new Exception("Eroare delete legaturi Angajat_Accident.");
            }

            // insereaza legaturi
            $sql = "
                insert into Angajat_Accident(id_angajat, id_accident, consecinta, concediu_medical_zile)
                values (?, ?, ?, ?)
            ";

            foreach ($selected_angajati as $sa) {
                $params = [
                    (int)$sa['id_angajat'],
                    (int)$id,
                    $sa['consecinta'],
                    (int)$sa['concediu_medical_zile']
                ];
                $stmt = sqlsrv_query($conn, $sql, $params);
                if ($stmt === false) {
                    $errs = sqlsrv_errors();
                    $msg = $errs ? $errs[0]['message'] : 'Unknown SQL error';
                    throw new Exception("Eroare insert Angajat_Accident: " . $msg);
                }
            }

            sqlsrv_commit($conn);
            header("Location: accidente.php");
            exit;
        } catch (Exception $e) {
            sqlsrv_rollback($conn);
            $errors[] = "Eroare la salvare: " . $e->getMessage();
        }
    }
}
?>

<div class="main-content">
    <h1><?= $id ? "Editează accident #".$id : "Înregistrează accident" ?></h1>

    <?php if (count($errors) > 0) { ?>
        <div class="alert">
            <ul>
                <?php foreach ($errors as $err) { ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php } ?>
            </ul>
        </div>
    <?php } ?>

    <div class="info-box">
        <form method="post">
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
                <div>
                    <label>Data *</label><br>
                    <input type="date" name="data_accident" value="<?= htmlspecialchars($accident['data_accident']) ?>" required>
                </div>

                <div>
                    <label>Ora *</label><br>
                    <input type="time" name="ora_accident" value="<?= htmlspecialchars($accident['ora_accident']) ?>" required>
                </div>

                <div style="min-width:220px;">
                    <label>Gravitate *</label><br>
                    <select name="gravitate" required>
                        <option value="minor"  <?= $accident['gravitate']==='minor'?'selected':'' ?>>minor</option>
                        <option value="grav"   <?= $accident['gravitate']==='grav'?'selected':'' ?>>grav</option>
                        <option value="mortal" <?= $accident['gravitate']==='mortal'?'selected':'' ?>>mortal</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:10px;">
                <label>Locație *</label><br>
                <input type="text" name="locatie" value="<?= htmlspecialchars($accident['locatie']) ?>" required style="width:100%;">
            </div>

            <div style="margin-top:10px;">
                <label>Descriere</label><br>
                <textarea name="descriere" rows="4" style="width:100%;"><?= htmlspecialchars($accident['descriere']) ?></textarea>
            </div>

            <hr style="margin:16px 0;">

            <h3>Angajați implicați *</h3>

            <div id="angajati-container" style="display:flex; flex-direction:column; gap:10px;">
                <?php
                if (count($selected_angajati) === 0) {
                    $selected_angajati[] = ['id_angajat'=>0,'consecinta'=>'ranit usor','concediu_medical_zile'=>0];
                }

                foreach ($selected_angajati as $row) {
                    $rid = (int)($row['id_angajat'] ?? 0);
                    $rcon = (string)($row['consecinta'] ?? 'ranit usor');
                    $rz = (int)($row['concediu_medical_zile'] ?? 0);
                ?>
                    <div class="info-box" style="padding:10px;">
                        <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:end;">
                            <div style="min-width:260px;">
                                <label>Angajat</label><br>
                                <select name="id_angajat[]" required>
                                    <option value="0">— selectează —</option>
                                    <?php foreach ($all_angajati as $a) { ?>
                                        <option value="<?= (int)$a['id_angajat'] ?>"
                                            <?= ($rid === (int)$a['id_angajat'])?'selected':'' ?>>
                                            <?= htmlspecialchars($a['nume_complet']." (".$a['nume_departament'].")") ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div>
                                <label>Consecință</label><br>
                                <select name="consecinta[]" required>
                                    <option value="ranit usor" <?= ($rcon==='ranit usor')?'selected':'' ?>>rănit ușor</option>
                                    <option value="ranit grav" <?= ($rcon==='ranit grav')?'selected':'' ?>>rănit grav</option>
                                    <option value="decedat" <?= ($rcon==='decedat')?'selected':'' ?>>decedat</option>
                                </select>
                            </div>

                            <div>
                                <label>Concediu medical (zile)</label><br>
                                <input type="number" name="concediu_medical_zile[]" min="0" max="255" value="<?= htmlspecialchars((string)$rz) ?>">
                            </div>

                            <div>
                                <button type="button" class="btn btn-delete" onclick="removeRow(this)">Șterge rând</button>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>

            <button type="button" class="btn btn-edit" style="margin-top:10px;" onclick="addRow()">+ Adaugă angajat</button>

            <div style="margin-top:16px;">
                <button class="btn btn-edit" type="submit">Salvează</button>
                <a class="btn" href="accidente.php" style="margin-left:6px;">Renunță</a>
            </div>
        </form>
    </div>
</div>

<script>
function removeRow(btn){
  const box = btn.closest('.info-box');
  if (!box) return;
  box.remove();
}
function addRow(){
  const container = document.getElementById('angajati-container');
  const template = container.querySelector('.info-box');
  if (!template) return;
  const clone = template.cloneNode(true);

  clone.querySelectorAll('select').forEach(sel => {
    if (sel.name === 'id_angajat[]') sel.value = '0';
    if (sel.name === 'consecinta[]') sel.value = 'ranit usor';
  });
  clone.querySelectorAll('input').forEach(inp => {
    if (inp.name === 'concediu_medical_zile[]') inp.value = '0';
  });

  container.appendChild(clone);
}
</script>

<?php include "_layout_bottom.php"; ?>
