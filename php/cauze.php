<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin','ssm','manager']);
$page_title = "Cauze – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";

require_once "cauze_repo.php";

$rol = $_SESSION['rol'];

$filters = [
    'q' => trim($_GET['q'] ?? '')
];

// mesaje (redirect after post)
$flash = $_GET['msg'] ?? '';

// DELETE (doar admin/ssm) — rulează înainte de afișare
if (($rol === 'admin' || $rol === 'ssm') && isset($_POST['action']) && $_POST['action'] === 'delete_cauza') {
    $id_del = (int)($_POST['id_cauza'] ?? 0);

    try {
        $ok = delete_cauza_if_unused($conn, $id_del);

        if ($ok) {
            header("Location: cauze.php?msg=" . urlencode("Cauza a fost ștearsă."));
            exit;
        } else {
            header("Location: cauze.php?msg=" . urlencode("Nu se poate șterge: cauza este asociată unor accidente."));
            exit;
        }
    } catch (Exception $e) {
        header("Location: cauze.php?msg=" . urlencode("Eroare: " . $e->getMessage()));
        exit;
    }
}

$cauze = get_cauze_with_counts($conn, $filters);
?>

<div class="main-content">
    <h1>Cauze</h1>

    <?php if ($flash !== '') { ?>
        <div class="alert"><?= htmlspecialchars($flash) ?></div>
    <?php } ?>

    <?php if ($rol === 'admin' || $rol === 'ssm') { ?>
        <div style="margin: 15px 0;">
            <a class="btn btn-edit" href="cauza_form.php">➕ Adaugă cauză</a>
        </div>
    <?php } else { ?>
        <div class="alert">Manager: poți doar vizualiza cauzele.</div>
    <?php } ?>

    <div class="filter-card">
        <form method="get" class="filter-form">
            <div class="filter-group">
                <label>Căutare (descriere)</label>
                <input type="text" name="q" value="<?= htmlspecialchars($filters['q']) ?>">
            </div>

            <div class="filter-actions">
                <button class="btn btn-edit" type="submit">Filtrează</button>
                <a class="btn btn-secondary" href="cauze.php">Reset</a>
            </div>
        </form>
    </div>

    <div class="info-box">
        <?php if (count($cauze) === 0) { ?>
            <p>Nu există cauze pentru filtrele selectate.</p>
        <?php } else { ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Descriere</th>
                        <th>Nr. accidente</th>
                        <?php if ($rol === 'admin' || $rol === 'ssm') { ?>
                            <th>Acțiuni</th>
                        <?php } ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($cauze as $c) { ?>
                        <tr>
                            <td><?= (int)$c['id_cauza'] ?></td>
                            <td><?= htmlspecialchars((string)$c['descriere_cauza']) ?></td>
                            <td><?= (int)$c['nr_accidente'] ?></td>

                            <?php if ($rol === 'admin' || $rol === 'ssm') { ?>
                                <td class="action-cell">
                                    <div class="action-buttons">
                                        <a class="btn btn-edit" href="cauza_form.php?id=<?= (int)$c['id_cauza'] ?>">Editează</a>

                                        <?php if ((int)$c['nr_accidente'] === 0) { ?>
                                            <form method="post" style="display:inline;"
                                                onsubmit="return confirm('Sigur vrei să ștergi cauza #<?= (int)$c['id_cauza'] ?>?');">
                                                <input type="hidden" name="action" value="delete_cauza">
                                                <input type="hidden" name="id_cauza" value="<?= (int)$c['id_cauza'] ?>">
                                                <button class="btn btn-delete" type="submit">Șterge</button>
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
