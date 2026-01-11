<?php
// presupune că ai deja session_start() + include "_auth.php" înainte să incluzi sidebar-ul
?>
<div class="sidebar">
    <h2>
        <?php
            if ($_SESSION['rol'] === 'admin')   echo "👑 Admin";
            if ($_SESSION['rol'] === 'ssm')     echo "🦺 SSM";
            if ($_SESSION['rol'] === 'manager') echo "📊 Manager";
        ?>
    </h2>

    <ul>
        <?php if (is_admin()) { ?>
            <li><a href="admin_dashboard.php">Utilizatori</a></li>
            <li><a href="departamente.php">Departamente</a></li>
        <?php } ?>

        <li><a href="accidente.php">Accidente</a></li>
        <li><a href="statistici.php">Statistici</a></li>

        <?php if (is_admin() || is_ssm()) { ?>
            <li><a href="cauze.php">Cauze</a></li>
            <li><a href="masuri.php">Măsuri</a></li>
        <?php } ?>

        <li><a href="logout.php">Logout</a></li>
    </ul>
</div>
