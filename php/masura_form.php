<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin','ssm']);
$page_title = "Măsură – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";

require_once "masuri_repo.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$errors = [];
$masura = [
    'descriere_masura' => '',
    'termen_implementare' => '',
    'status' => 'in curs'
];

if ($id) {
    $db = get_masura_by_id($conn, $id);
    if (!$db) {
        $errors[] = "Măsură inexistentă.";
    } else {
        $masura['descriere_masura'] = (string)$db['descriere_masura'];
        $masura['status'] = (string)$db['status'];

        if ($db['termen_implementare'] instanceof DateTime) {
            $masura['termen_implementare'] = $db['termen_implementare']->format('Y-m-d');
        } else {
            $masura['termen_implementare'] = $db['termen_implementare'] ? substr((string)$db['termen_implementare'], 0, 10) : '';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $masura['descriere_masura'] = trim($_POST['descriere_masura'] ?? '');
    $masura['termen_implementare'] = $_POST['termen_implementare'] ?? '';
    $masura['status'] = $_POST['status'] ?? 'in curs';

    if ($masura['descriere_masura'] === '') {
        $errors[] = "Descrierea măsurii este obligatorie.";
    }
    if (strlen($masura['descriere_masura']) > 200) {
        $errors[] = "Descrierea măsurii trebuie să aibă max 200 caractere.";
    }

    if (!in_array($masura['status'], ['in curs','finalizat'], true)) {
        $errors[] = "Status invalid.";
    }

    // termen_implementare poate fi gol; dacă e completat trebuie să fie dată validă (basic)
    if ($masura['termen_implementare'] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $masura['termen_implementare'])) {
        $errors[] = "Termen implementare invalid (format așteptat: YYYY-MM-DD).";
    }

    if (count($errors) === 0) {
        try {
            if (!$id) {
                insert_masura($conn, $masura['descriere_masura'], $masura['termen_implementare'], $masura['status']);
            } else {
                update_masura($conn, $id, $masura['descriere_masura'], $masura['termen_implementare'], $masura['status']);
            }
            header("Location: masuri.php");
            exit;
        } catch (Exception $e) {
            $errors[] = "Eroare la salvare: " . $e->getMessage();
        }
    }
}
?>

<div class="main-content">
    <h1><?= $id ? "Editează măsură #".$id : "Adaugă măsură" ?></h1>

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
            <div class="filter-group" style="max-width:800px;">
                <label>Descriere măsură *</label>
                <textarea name="descriere_masura" rows="4" style="width:100%;"><?= htmlspecialchars($masura['descriere_masura']) ?></textarea>
            </div>

            <div style="display:flex; gap:14px; flex-wrap:wrap; margin-top:12px;">
                <div class="filter-group">
                    <label>Termen implementare</label>
                    <input type="date" name="termen_implementare" value="<?= htmlspecialchars($masura['termen_implementare']) ?>">
                </div>

                <div class="filter-group" style="min-width:220px;">
                    <label>Status</label>
                    <select name="status">
                        <option value="in curs" <?= $masura['status']==='in curs'?'selected':'' ?>>in curs</option>
                        <option value="finalizat" <?= $masura['status']==='finalizat'?'selected':'' ?>>finalizat</option>
                    </select>
                </div>
            </div>

            <div style="margin-top:16px;">
                <button class="btn btn-edit" type="submit">Salvează</button>
                <a class="btn btn-secondary" href="masuri.php" style="margin-left:8px;">Renunță</a>
            </div>
        </form>
    </div>
</div>

<?php include "_layout_bottom.php"; ?>
