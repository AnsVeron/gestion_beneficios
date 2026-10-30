<?php
require 'config.php';
pedir_vendedor();

$u = usuario();
$mensaje = '';
$error = '';

if (isset($_POST['pagar'])) {
    $venta_id = (int) $_POST['venta_id'];
    $monto = (float) $_POST['monto'];

    $q = $db->prepare("SELECT v.total, IFNULL((SELECT SUM(p.monto) FROM pagos p WHERE p.venta_id = v.id),0) AS pagado
                       FROM ventas v WHERE v.id = ? AND v.vendedor_id = ?");
    $q->execute([$venta_id, $u['id']]);
    $v = $q->fetch();

    if (!$v) {
        $error = 'venta no encontrada';
    } elseif ($monto <= 0) {
        $error = 'el monto debe ser mayor a cero';
    } elseif ($monto > $v['total'] - $v['pagado']) {
        $error = 'el monto supera el saldo pendiente';
    } else {
        $q = $db->prepare("INSERT INTO pagos (venta_id, monto, fecha) VALUES (?, ?, NOW())");
        $q->execute([$venta_id, $monto]);
        $mensaje = 'pago registrado';
    }
}

$q = $db->prepare("
    SELECT v.*, c.nombre AS campana,
      (SELECT IFNULL(SUM(p.monto),0) FROM pagos p WHERE p.venta_id = v.id) AS pagado
    FROM ventas v
    JOIN campanas c ON c.id = v.campana_id
    WHERE v.vendedor_id = ?
    ORDER BY v.id DESC");
$q->execute([$u['id']]);
$ventas = $q->fetchAll();

require 'encabezado.php';
?>

<h3>Mis ventas</h3>

<?php if ($error): ?>
    <p class="error"><?= h($error) ?></p><?php endif; ?>
<?php if ($mensaje): ?>
    <p class="ok"><?= h($mensaje) ?></p><?php endif; ?>

<table>
    <tr>
        <th>ID</th>
        <th>Fecha</th>
        <th>Campaña</th>
        <th>Cliente</th>
        <th>Detalle</th>
        <th>Total</th>
        <th>Cobrado</th>
        <th>Pendiente</th>
        <th>Entrega</th>
        <th>Registrar cobro</th>
    </tr>
    <?php foreach ($ventas as $v): ?>
        <?php
        $qi = $db->prepare("SELECT i.*, p.nombre FROM venta_items i JOIN productos p ON p.id = i.producto_id WHERE i.venta_id = ?");
        $qi->execute([$v['id']]);
        $items = $qi->fetchAll();
        $saldo = $v['total'] - $v['pagado'];
        ?>
        <tr>
            <td><?= $v['id'] ?></td>
            <td><?= h($v['fecha']) ?></td>
            <td><?= h($v['campana']) ?></td>
            <td><?= h($v['cliente']) ?></td>
            <td><?php foreach ($items as $i): ?><?= h($i['nombre']) ?> x<?= $i['cantidad'] ?><br><?php endforeach; ?></td>
            <td>$<?= number_format($v['total'], 2) ?></td>
            <td>$<?= number_format($v['pagado'], 2) ?></td>
            <td>$<?= number_format($saldo, 2) ?></td>
            <td><?= $v['entregado'] ? h($v['fecha_entrega']) : 'Pendiente' ?></td>
            <td>
                <?php if ($saldo > 0): ?>
                    <form method="post" style="margin:0">
                        <input type="hidden" name="venta_id" value="<?= $v['id'] ?>">
                        <input type="number" step="0.01" name="monto" value="<?= $saldo ?>" required>
                        <button type="submit" name="pagar">Cobrar</button>
                    </form>
                <?php else: ?>
                    Cancelada
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php require 'pie.php'; ?>