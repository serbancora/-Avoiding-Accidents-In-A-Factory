<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin','ssm','manager']);
$page_title = "Statistici – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";
?>

<div class="main-content">
    <h1>Statistici</h1>

    <div class="info-box">
        <p>Aici vor veni grafice/indicatori: accidente pe departament, pe lună etc.</p>
    </div>
</div>

<?php include "_layout_bottom.php"; ?>
