<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin','ssm','manager']);
$page_title = "Măsuri – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";

require_once "masuri_repo.php";

$rol = $_SESSION['rol'];

$filters = [
    'q' => trim($_GET['q'] ?? ''),
    'status' => $_GET['status'] ?? '',
    'from' => $_GET['from'] ?? '',
    'to' => $_GET['to'] ?? ''
];

// mesaje (redirect after post)
$flash = $_GET['msg'] ?? '';

// DELETE (doar admin/ssm) — rulează înainte de afișare
if (($rol === 'admin' || $rol === 'ssm') && isset($_POST['action']) && $_POST['action'] === 'delete_masura') {
    $id_del = (int)($_POST['id_masura'] ?? 0);

    try {
        $ok = delete_masura_if_unused($conn, $id_del);

        if ($ok) {
            header("Location: masuri.php?msg=" . urlencode("Măsura a fost ștearsă."));
            exit;
        } else {
            header("Location: masuri.php?msg=" . urlencode("Nu se poate șterge: măsura este asociată unor accidente."));
            exit;
        }
    } catch (Exception $e) {
        header("Location: masuri.php?msg=" . urlencode("Eroare: " . $e->getMessage()));
        exit;
    }
}

$masuri = get_masuri_with_counts($conn, $filters);

function fmt_date($v): string {
    if ($v instanceof DateTime) return $v->format('Y-m-d');
    $s = (string)$v;
    return $s === '' ? '' : substr($s, 0, 10);
}
?>

<div class="main-content">
    <h1>Măsuri</h1>

    <?php if ($flash !== '') { ?>
        <div class="alert"><?= htmlspecialchars($flash) ?></div>
    <?php } ?>


    <?php if ($rol === 'admin' || $rol === 'ssm') { ?>
        <div style="margin: 15px 0;">
            <a class="btn btn-edit" href="masura_form.php">➕ Adaugă măsură</a>
        </div>
    <?php } else { ?>
        <div class="alert">Manager: poți doar vizualiza măsurile.</div>
    <?php } ?>

    <div class="filter-card">
        <form method="get" class="filter-form">
            <div class="filter-group">
                <label>Căutare (descriere)</label>
                <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>">
            </div>

            <div class="filter-group">
                <label>Status</label>
                <select name="status">
                    <option value="">— orice —</option>
                    <option value="in curs" <?= $filters['status']==='in curs'?'selected':'' ?>>in curs</option>
                    <option value="finalizat" <?= $filters['status']==='finalizat'?'selected':'' ?>>finalizat</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Termen de la</label>
                <input type="date" name="from" value="<?= htmlspecialchars($filters['from']) ?>">
            </div>

            <div class="filter-group">
                <label>Termen până la</label>
                <input type="date" name="to" value="<?= htmlspecialchars($filters['to']) ?>">
            </div>

            <div class="filter-actions">
                <button class="btn btn-edit" type="submit">Filtrează</button>
                <a class="btn btn-secondary" href="masuri.php">Reset</a>
            </div>
        </form>
    </div>

    <div class="info-box">
        <?php if (count($masuri) === 0) { ?>
            <p>Nu există măsuri pentru filtrele selectate.</p>
        <?php } else { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descriere</th>
                        <th>Termen implementare</th>
                        <th>Status</th>
                        <th>Nr. accidente</th>
                        <?php if ($rol === 'admin' || $rol === 'ssm') { ?>
                            <th>Acțiuni</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($masuri as $m) { ?>
                        <tr>
                            <td><?= (int)$m['id_masura'] ?></td>
                            <td><?= htmlspecialchars((string)$m['descriere_masura']) ?></td>
                            <td><?= htmlspecialchars(fmt_date($m['termen_implementare'])) ?></td>
                            <td><?= htmlspecialchars((string)$m['status']) ?></td>
                            <td><?= (int)$m['nr_accidente'] ?></td>

                            <?php if ($rol === 'admin' || $rol === 'ssm') { ?>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <a class="btn btn-edit" href="masura_form.php?id=<?= (int)$m['id_masura'] ?>">
                                            Editează
                                        </a>

                                        <?php if ((int)$m['nr_accidente'] === 0) { ?>
                                            <form method="post" style="display:inline;"
                                                onsubmit="return confirm('Sigur vrei să ștergi măsura #<?= (int)$m['id_masura'] ?>?');">
                                                <input type="hidden" name="action" value="delete_masura">
                                                <input type="hidden" name="id_masura" value="<?= (int)$m['id_masura'] ?>">
                                                <button class="btn btn-delete" type="submit">
                                                    Șterge
                                                </button>
                                            </form>
                                        <?php } ?>
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
