<?php
session_start();

$db = new PDO('mysql:host=localhost;dbname=beneficios;charset=utf8mb4', 'root', '');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

function usuario() {
    return isset($_SESSION['usuario']) ? $_SESSION['usuario'] : null;
}

function pedir_login() {
    if (!usuario()) {
        header('Location: login.php');
        exit;
    }
}

function pedir_admin() {
    pedir_login();
    if ($_SESSION['usuario']['rol'] != 'admin') {
        die('Acceso bloqueado');
    }
}

function pedir_vendedor() {
    pedir_login();
    if ($_SESSION['usuario']['rol'] != 'vendedor') {
        die('Acceso bloqueado');
    }
}

function h($texto) {
    return htmlspecialchars($texto, ENT_QUOTES, 'UTF-8');
}

function campana_abierta($db) {
    $q = $db->query("SELECT * FROM campanas WHERE estado = 'abierta' LIMIT 1");
    $c = $q->fetch();
    return $c ? $c : null;
}
