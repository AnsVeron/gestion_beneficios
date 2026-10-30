<?php
require 'config.php';
pedir_admin();

$mensaje = '';

$campanas = $db->query("SELECT * FROM campanas ORDER BY id DESC")->fetchAll();
$campana_id = isset($_REQUEST['campana_id']) ? (int) $_REQUEST['campana_id'] : 0;
if (!$campana_id) {
    $abierta = campana_abierta($db);
    if ($abierta)
        $campana_id = $abierta['id'];
    elseif ($campanas)
        $campana_id = $campanas[0]['id'];
}

if (isset($_POST['guardar'])) {
    $id = (int) $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $precio = (float) $_POST['precio'];
    $stock = (int) $_POST['stock'];

    if ($id > 0) {
        $q = $db->prepare("UPDATE productos SET nombre=?, precio=? WHERE id=?");
        $q->execute([$nombre, $precio, $id]);
        $mensaje = 'producto actualizado';
    } else {
        $q = $db->prepare("INSERT INTO productos (campana_id, nombre, precio, stock) VALUES (?, ?, ?, ?)");
        $q->execute([$campana_id, $nombre, $precio, $stock]);
        $pid = $db->lastInsertId();
        if ($stock != 0) {
            $q = $db->prepare("INSERT INTO movimientos_stock (producto_id, cantidad, motivo, fecha) VALUES (?, ?, 'stock inicial', NOW())");
            $q->execute([$pid, $stock]);
        }
        $mensaje = 'producto creado';
    }
}

if (isset($_POST['ajustar'])) {
    $pid = (int) $_POST['producto_id'];
    $cantidad = (int) $_POST['cantidad'];
    $motivo = trim($_POST['motivo']);

    $q = $db->prepare("UPDATE productos SET stock = stock + ? WHERE id = ?");
    $q->execute([$cantidad, $pid]);
    $q = $db->prepare("INSERT INTO movimientos_stock (producto_id, cantidad, motivo, fecha) VALUES (?, ?, ?, NOW())");
    $q->execute([$pid, $cantidad, $motivo]);
    $mensaje = 'stock ajustado';
}

$editar = ['id' => 0, 'nombre' => '', 'precio' => '', 'stock' => 0];
if (isset($_GET['editar'])) {
    $q = $db->prepare("SELECT * FROM productos WHERE id=?");
    $q->execute([(int) $_GET['editar']]);
    $p = $q->fetch();
    if ($p) {
        $editar = $p;
        $campana_id = $p['campana_id'];
    }
}

$q = $db->prepare("SELECT * FROM productos WHERE campana_id = ? ORDER BY nombre");
$q->execute([$campana_id]);
$productos = $q->fetchAll();

$movs = $db->query("SELECT m.*, p.nombre FROM movimientos_stock m
                    JOIN productos p ON p.id = m.producto_id
                    WHERE p.campana_id = " . (int) $campana_id . "
                    ORDER BY m.id DESC LIMIT 20")->fetchAll();

require 'encabezado.php';
?>

<h3>Productos y Stock</h3>

<?php if ($mensaje): ?>
    <p class="ok"><?= h($mensaje) ?></p><?php endif; ?>

<form method="get">
    Campaña:
    <select name="campana_id" onchange="this.form.submit()">
        <?php foreach ($campanas as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id'] == $campana_id ? 'selected' : '' ?>>
                <?= h($c['nombre']) ?> (<?= h($c['estado']) ?>)
            </option>
        <?php endforeach; ?>
    </select>
</form>

<h4><?= $editar['id'] ? 'Editar producto' : 'Nuevo producto' ?></h4>
<form method="post">
    <input type="hidden" name="id" value="<?= (int) $editar['id'] ?>">
    <input type="hidden" name="campana_id" value="<?= (int) $campana_id ?>">
    Nombre: <input type="text" name="nombre" value="<?= h($editar['nombre']) ?>" required>
    Precio: <input type="number" step="0.01" name="precio" value="<?= h($editar['precio']) ?>" required>
    <?php if (!$editar['id']): ?>
        Stock inicial: <input type="number" name="stock" value="0">
    <?php endif; ?>
    <button type="submit" name="guardar">Guardar</button>
    <?php if ($editar['id']): ?><a href="admin_productos.php?campana_id=<?= $campana_id ?>">Cancelar</a><?php endif; ?>
</form>

<h4>Listado</h4>
<table>
    <tr>
        <th>ID</th>
        <th>Producto</th>
        <th>Precio</th>
        <th>Stock</th>
        <th>Ajustar stock</th>
    </tr>
    <?php foreach ($productos as $p): ?>
        <tr>
            <td><?= $p['id'] ?></td>
            <td><?= h($p['nombre']) ?> <a href="?editar=<?= $p['id'] ?>">(editar)</a></td>
            <td>$<?= number_format($p['precio'], 2) ?></td>
            <td><?= $p['stock'] ?></td>
            <td>
                <form method="post" style="margin:0">
                    <input type="hidden" name="producto_id" value="<?= $p['id'] ?>">
                    <input type="hidden" name="campana_id" value="<?= (int) $campana_id ?>">
                    <input type="number" name="cantidad" value="0" size="4" required>
                    <input type="text" name="motivo" placeholder="Motivo" required>
                    <button type="submit" name="ajustar">Aplicar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<h4>Últimos movimientos de stock</h4>
<table>
    <tr>
        <th>Fecha</th>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Motivo</th>
    </tr>
    <?php foreach ($movs as $m): ?>
        <tr>
            <td><?= h($m['fecha']) ?></td>
            <td><?= h($m['nombre']) ?></td>
            <td><?= $m['cantidad'] ?></td>
            <td><?= h($m['motivo']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<?php require 'pie.php'; ?>