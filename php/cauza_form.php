<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin','ssm']);
$page_title = "Cauză – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";

require_once "cauze_repo.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

$errors = [];
$cauza = ['descriere_cauza' => ''];

if ($id) {
    $db = get_cauza_by_id($conn, $id);
    if (!$db) {
        $errors[] = "Cauză inexistentă.";
    } else {
        $cauza['descriere_cauza'] = (string)$db['descriere_cauza'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cauza['descriere_cauza'] = trim($_POST['descriere_cauza'] ?? '');

    if ($cauza['descriere_cauza'] === '') {
        $errors[] = "Descrierea cauzei este obligatorie.";
    }
    if (strlen($cauza['descriere_cauza']) > 200) {
        $errors[] = "Descrierea cauzei trebuie să aibă max 200 caractere.";
    }

    if (count($errors) === 0) {
        try {
            if (!$id) {
                insert_cauza($conn, $cauza['descriere_cauza']);
            } else {
                update_cauza($conn, $id, $cauza['descriere_cauza']);
            }
            header("Location: cauze.php");
            exit;
        } catch (Exception $e) {
            $errors[] = "Eroare la salvare: " . $e->getMessage();
        }
    }
}
?>

<div class="main-content">
    <h1><?= $id ? "Editează cauză #".$id : "Adaugă cauză" ?></h1>

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
            <div class="filter-group" style="max-width:700px;">
                <label>Descriere cauză *</label>
                <textarea name="descriere_cauza" rows="4" style="width:100%;"><?= htmlspecialchars($cauza['descriere_cauza']) ?></textarea>
            </div>

            <div style="margin-top:16px;">
                <button class="btn btn-edit" type="submit">Salvează</button>
                <a class="btn btn-secondary" href="cauze.php" style="margin-left:8px;">Renunță</a>
            </div>
        </form>
    </div>
</div>

<?php include "_layout_bottom.php"; ?>
