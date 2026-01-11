<?php

function require_login(): void {
    if (!isset($_SESSION['rol'])) {
        header("Location: ../html/login.html");
        exit();
    }
}

function require_roles(array $allowed_roles): void {
    require_login();
    $rol = $_SESSION['rol'];

    if (!in_array($rol, $allowed_roles, true)) {
        header("Location: ../html/login.html");
        exit();
    }
}

function is_admin(): bool   { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin'; }
function is_ssm(): bool     { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'ssm'; }
function is_manager(): bool { return isset($_SESSION['rol']) && $_SESSION['rol'] === 'manager'; }
