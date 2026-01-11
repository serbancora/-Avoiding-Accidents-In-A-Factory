<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <title><?= isset($page_title) ? htmlspecialchars($page_title) : "SafeFactory" ?></title>
    <link rel="stylesheet" href="../css/style_dashboard.css">
    <link rel="stylesheet" href="../css/style_filters.css">
    <link rel="stylesheet" href="../css/style_statistics.css">
    <link rel="stylesheet" href="../css/style_sidebar.css">
</head>
<body>
