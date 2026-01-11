<?php
session_start();
include "../connect.php";
include "_auth.php";

require_roles(['admin','ssm']);
$page_title = "Măsuri – SafeFactory";

include "_layout_top.php";
include "_sidebar.php";
?>

<div class="main-content">
    <h1>Măsuri</h1>

    <div class="info-box">
        <p>Admin/SSM: aici vei gestiona măsurile corective (CRUD ulterior).</p>
    </div>
</div>

<?php include "_layout_bottom.php"; ?>
