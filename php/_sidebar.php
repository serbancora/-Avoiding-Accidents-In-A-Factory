<?php
// presupune că ai deja session_start() + include "_auth.php" înainte să incluzi sidebar-ul
?>
<div class="sidebar">
    <h2 class="sidebar-title">
        <?php if ($_SESSION['rol'] === 'admin') { ?>
            <a href="admin_dashboard.php">👑 Admin</a>
        <?php } ?>

        <?php if ($_SESSION['rol'] === 'ssm') { ?>
            <a href="ssm_dashboard.php">🦺 SSM</a>
        <?php } ?>

        <?php if ($_SESSION['rol'] === 'manager') { ?>
            <a href="manager_dashboard.php">📊 Manager</a>
        <?php } ?>
    </h2>

    <ul>
        <?php if (is_admin()) { ?>
            <li><a href="utilizatori.php">Utilizatori</a></li>
            <li><a href="departamente.php">Departamente</a></li>
        <?php } ?>

        <li><a href="accidente.php">Accidente</a></li>

        <?php if (is_admin() || is_ssm() || $_SESSION['rol'] === 'manager') { ?>
            <li><a href="masuri.php">Măsuri</a></li>
        <?php } ?>

        <li><a href="statistici.php">Statistici</a></li>

        <?php if (is_admin() || is_ssm()) { ?>
            <li><a href="cauze.php">Cauze</a></li>
        <?php } ?>

        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
