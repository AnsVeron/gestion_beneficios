<?php
$u = usuario();
$actual = basename($_SERVER['PHP_SELF']);

$menu = [];
if ($u && $u['rol'] == 'admin') {
    $pendientes = $db->query("SELECT COUNT(*) FROM solicitudes s
                              JOIN campanas c ON c.id = s.campana_id
                              WHERE s.estado = 'pendiente' AND c.estado = 'abierta'")->fetchColumn();
    $menu = [
        '/proyecto/sitio/panel.php' => 'Inicio',
        '/proyecto/sitio/administrador/admin_campanas.php' => 'Campañas',
        '/proyecto/sitio/administrador/admin_productos.php' => 'Productos y Stock',
        '/proyecto/sitio/administrador/admin_vendedores.php' => 'Vendedores',
        '/proyecto/sitio/administrador/admin_solicitudes.php' => $pendientes ? 'Solicitudes (' . $pendientes . ')' : 'Solicitudes',
        '/proyecto/sitio/administrador/admin_ventas.php' => 'Ventas y Entregas',
        '/proyecto/sitio/administrador/admin_rendiciones.php' => 'Rendiciones',
    ];
} elseif ($u) {
    $menu = [
        '/proyecto/sitio/panel.php' => 'Inicio',
        '/proyecto/sitio/vendedor/vend_ventas.php' => 'Mis ventas',
        '/proyecto/sitio/vendedor/vend_nueva_venta.php' => 'Registrar venta',
        '/proyecto/sitio/vendedor/vend_rendicion.php' => 'Rendición',
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Beneficios</title>
    <link rel="stylesheet" href="/proyecto/sitio/recursos/estilos/estilos.css">
</head>

<body>

    <header class="barra">
        <h2><a href="<?= $u ? '/proyecto/sitio/panel.php' : '/proyecto/index.php' ?>">Gestión de Beneficios</a></h2>

        <?php if (!$u): ?>
            <nav id="menu" class="invitado">
                <a href="/proyecto/index.php" <?= $actual == 'index.php' ? ' class="activo"' : '' ?>>Inicio</a>
                <div class="cuenta">
                    <a href="/proyecto/sitio/acceso/login.php" <?= $actual == 'login.php' ? ' class="activo"' : '' ?>>Iniciar sesión</a>
                </div>
            </nav>
        <?php else: ?>
            <button type="button" class="menu-boton" aria-expanded="false" aria-controls="menu">
                <span class="menu-icono"></span> Menú
            </button>
            <nav id="menu">
                <?php foreach ($menu as $href => $texto): ?>
                    <a href="<?= h($href) ?>" <?= basename($href) == $actual ? ' class="activo"' : '' ?>><?= $texto ?></a>
                <?php endforeach; ?>
                <div class="cuenta">
                    <a href="/proyecto/sitio/acceso/logout.php" class="salir">Cerrar sesión</a>
                </div>
            </nav>
            <script>
                var barra = document.querySelector('.barra');
                var boton = barra.querySelector('.menu-boton');
                barra.classList.add('con-js');
                boton.onclick = function () {
                    var abierto = barra.classList.toggle('abierto');
                    boton.setAttribute('aria-expanded', abierto);
                };
            </script>
        <?php endif; ?>
    </header>

    <div class="contenido">