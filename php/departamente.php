<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin']); // doar admin (dacă vrei și manager/ssm, îl extinzi)

$page_title = "Departamente – SafeFactory";

$sql = "SELECT id_departament, nume_departament, locatie
        FROM Departament
        ORDER BY nume_departament";

$stmt = sqlsrv_query($conn, $sql);
if ($stmt === false) {
    die(print_r(sqlsrv_errors(), true));
}

include "_layout_top.php";
include "_sidebar.php";
?>

<div class="main-content">
    <h1>Departamente</h1>
    <p>Vizualizare departamente (modificările sunt dezactivate).</p>

    <table class="data-table">
        <tr>
            <th>ID</th>
            <th>Denumire departament</th>
            <th>Locație</th>
        </tr>

        <?php while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) { ?>
            <tr>
                <td><?= (int)$row['id_departament'] ?></td>
                <td><?= htmlspecialchars($row['nume_departament']) ?></td>
                <td><?= htmlspecialchars($row['locatie']) ?></td>
            </tr>
        <?php } ?>
    </table>
</div>

<?php include "_layout_bottom.php"; ?>
