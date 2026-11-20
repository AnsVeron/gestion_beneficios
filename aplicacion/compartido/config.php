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
        header('Location: /proyecto/sitio/acceso/login.php');
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
    $q = $db->query("SELECT * FROM campanas WHERE estado = 'abierta' ORDER BY id DESC LIMIT 1");
    $c = $q->fetch();
    return $c ? $c : null;
}

function campanas_abiertas($db) {
    return $db->query("SELECT * FROM campanas WHERE estado = 'abierta' ORDER BY id DESC")->fetchAll();
}

function campanas_aprobadas($db, $vendedor_id) {
    $q = $db->prepare("SELECT c.* FROM campanas c
                       JOIN solicitudes s ON s.campana_id = c.id
                       WHERE c.estado = 'abierta' AND s.vendedor_id = ? AND s.estado = 'aprobada'
                       ORDER BY c.id DESC");
    $q->execute([$vendedor_id]);
    return $q->fetchAll();
}

function campanas_para_rendir($db, $vendedor_id) {
    $q = $db->prepare("SELECT c.* FROM campanas c
                       WHERE c.estado = 'abierta' AND (
                         EXISTS (SELECT 1 FROM solicitudes s WHERE s.campana_id = c.id AND s.vendedor_id = ? AND s.estado = 'aprobada')
                         OR EXISTS (SELECT 1 FROM ventas v WHERE v.campana_id = c.id AND v.vendedor_id = ?)
                       )
                       ORDER BY c.id DESC");
    $q->execute([$vendedor_id, $vendedor_id]);
    return $q->fetchAll();
}

function campana_elegida($disponibles) {
    $id = 0;
    if (isset($_REQUEST['campana_id'])) {
        $id = (int) $_REQUEST['campana_id'];
    } elseif (isset($_SESSION['campana_id'])) {
        $id = (int) $_SESSION['campana_id'];
    }

    foreach ($disponibles as $c) {
        if ($c['id'] == $id) {
            $_SESSION['campana_id'] = $c['id'];
            return $c;
        }
    }
    return $disponibles ? $disponibles[0] : null;
}
