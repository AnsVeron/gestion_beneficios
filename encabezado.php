<?php
$u = usuario();
$actual = basename($_SERVER['PHP_SELF']);

$menu = [];
if ($u && $u['rol'] == 'admin') {
    $menu = [
        'admin_campanas.php' => 'Campañas',
        'admin_productos.php' => 'Productos y Stock',
        'admin_vendedores.php' => 'Vendedores',
        'admin_ventas.php' => 'Ventas y Entregas',
        'admin_rendiciones.php' => 'Rendiciones',
    ];
} elseif ($u) {
    $menu = [
        'vend_ventas.php' => 'Mis ventas',
        'vend_nueva_venta.php' => 'Registrar venta',
        'vend_rendicion.php' => 'Rendición',
    ];
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión de Beneficios</title>
    <link rel="stylesheet" href="estilos.css">
</head>

<body>

    <header class="barra">
        <h2>Gestión de Beneficios</h2>

        <?php if ($u): ?>
            <button type="button" class="menu-boton" aria-expanded="false" aria-controls="menu">
                <span class="menu-icono"></span> Menú
            </button>
            <nav id="menu">
                <?php foreach ($menu as $href => $texto): ?>
                    <a href="<?= $href ?>" <?= $href == $actual ? ' class="activo"' : '' ?>><?= $texto ?></a>
                <?php endforeach; ?>
                <div class="cuenta">
                    <a href="logout.php" class="salir">Cerrar sesión</a>
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