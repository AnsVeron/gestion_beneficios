<?php
require 'config.php';
pedir_vendedor();

$u = usuario();
$campana = campana_abierta($db);
$mensaje = '';
$error = '';

if ($campana && $_SERVER['REQUEST_METHOD'] == 'POST') {
    $cliente = trim($_POST['cliente']);
    $cantidades = $_POST['cantidad'];
    
    $items = [];
    $total = 0;
    foreach ($cantidades as $pid => $cant) {
        $cant = (int) $cant;
        if ($cant <= 0)
            continue;

        $q = $db->prepare("SELECT * FROM productos WHERE id = ? AND campana_id = ?");
        $q->execute([(int) $pid, $campana['id']]);
        $p = $q->fetch();
        if (!$p)
            continue;

        if ($cant > $p['stock']) {
            $error = 'No hay stock suficiente de ' . $p['nombre'] . ' (disponible: ' . $p['stock'] . ')';
            break;
        }
        $items[] = ['producto' => $p, 'cantidad' => $cant];
        $total += $p['precio'] * $cant;
    }

    if (!$error && !$items) {
        $error = 'Debe cargar al menos un producto';
    }

    if (!$error) {
        $q = $db->prepare("INSERT INTO ventas (campana_id, vendedor_id, cliente, fecha, total) VALUES (?, ?, ?, NOW(), ?)");
        $q->execute([$campana['id'], $u['id'], $cliente, $total]);
        $venta_id = $db->lastInsertId();

        foreach ($items as $it) {
            $q = $db->prepare("INSERT INTO venta_items (venta_id, producto_id, cantidad, precio_unit) VALUES (?, ?, ?, ?)");
            $q->execute([$venta_id, $it['producto']['id'], $it['cantidad'], $it['producto']['precio']]);

            $q = $db->prepare("UPDATE productos SET stock = stock - ? WHERE id = ?");
            $q->execute([$it['cantidad'], $it['producto']['id']]);

            $q = $db->prepare("INSERT INTO movimientos_stock (producto_id, cantidad, motivo, fecha) VALUES (?, ?, ?, NOW())");
            $q->execute([$it['producto']['id'], -$it['cantidad'], 'Venta #' . $venta_id]);
        }
        $mensaje = 'Venta registrada (N° ' . $venta_id . ') por $' . number_format($total, 2);
    }
}

$productos = [];
if ($campana) {
    $q = $db->prepare("SELECT * FROM productos WHERE campana_id = ? ORDER BY nombre");
    $q->execute([$campana['id']]);
    $productos = $q->fetchAll();
}

require 'encabezado.php';
?>

<h3>Registrar venta</h3>

<?php if (!$campana): ?>
    <p>No hay campaña abierta.</p>
<?php else: ?>

    <?php if ($error): ?>
        <p class="error"><?= h($error) ?></p><?php endif; ?>
    <?php if ($mensaje): ?>
        <p class="ok"><?= h($mensaje) ?></p><?php endif; ?>

    <p>Campaña: <b><?= h($campana['nombre']) ?></b></p>

    <form method="post">
        Cliente: <input type="text" name="cliente" required><br><br>
        <table>
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Stock</th>
                <th>Cantidad</th>
            </tr>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td><?= h($p['nombre']) ?></td>
                    <td>$<?= number_format($p['precio'], 2) ?></td>
                    <td><?= $p['stock'] ?></td>
                    <td><input type="number" name="cantidad[<?= $p['id'] ?>]" value="0" min="0"></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <button type="submit">Registrar venta</button>
    </form>

<?php endif; ?>

<?php require 'pie.php'; ?>