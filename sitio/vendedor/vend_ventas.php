<?php
require '../../aplicacion/compartido/config.php';
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
    SELECT v.*, c.nombre AS campana, c.estado AS campana_estado,
      (SELECT IFNULL(SUM(p.monto),0) FROM pagos p WHERE p.venta_id = v.id) AS pagado
    FROM ventas v
    JOIN campanas c ON c.id = v.campana_id
    WHERE v.vendedor_id = ?
    ORDER BY c.estado = 'abierta' DESC, c.id DESC, v.id DESC");
$q->execute([$u['id']]);

$campanas = [];
foreach ($q->fetchAll() as $v) {
    $campanas[$v['campana_id']]['nombre'] = $v['campana'];
    $campanas[$v['campana_id']]['estado'] = $v['campana_estado'];
    $campanas[$v['campana_id']]['ventas'][] = $v;
}

require '../../aplicacion/compartido/encabezado.php';
?>

<h3>Mis ventas</h3>

<?php if ($error): ?>
    <p class="error"><?= h($error) ?></p><?php endif; ?>
<?php if ($mensaje): ?>
    <p class="ok"><?= h($mensaje) ?></p><?php endif; ?>

<?php if (!$campanas): ?>
    <p>Todavía no registró ventas.</p>
<?php endif; ?>

<?php foreach ($campanas as $campana): ?>
    <h4><?= h($campana['nombre']) ?><?= $campana['estado'] == 'cerrada' ? ' (cerrada)' : '' ?></h4>
    <table>
        <tr>
            <th>Venta</th>
            <th>Cliente y detalle</th>
            <th class="num">Total</th>
            <th class="num">Cobrado</th>
            <th class="num">Pendiente</th>
            <th>Entrega</th>
            <th>Registrar cobro</th>
        </tr>
        <?php foreach ($campana['ventas'] as $v): ?>
            <?php
            $qi = $db->prepare("SELECT i.*, p.nombre FROM venta_items i JOIN productos p ON p.id = i.producto_id WHERE i.venta_id = ?");
            $qi->execute([$v['id']]);
            $items = $qi->fetchAll();
            $saldo = $v['total'] - $v['pagado'];
            ?>
            <tr>
                <td class="sin-corte">
                    N° <?= $v['id'] ?>
                    <span class="dato-tenue"><?= h(substr($v['fecha'], 0, 16)) ?></span>
                </td>
                <td>
                    <?= h($v['cliente']) ?>
                    <?php foreach ($items as $i): ?>
                        <span class="dato-tenue"><?= h($i['nombre']) ?>&nbsp;x<?= $i['cantidad'] ?></span>
                    <?php endforeach; ?>
                </td>
                <td class="num">$<?= number_format($v['total'], 2) ?></td>
                <td class="num">$<?= number_format($v['pagado'], 2) ?></td>
                <td class="num">$<?= number_format($saldo, 2) ?></td>
                <td class="sin-corte"><?= $v['entregado'] ? h($v['fecha_entrega']) : 'Pendiente' ?></td>
                <td>
                    <?php if ($saldo > 0): ?>
                        <form method="post" class="cobro">
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
<?php endforeach; ?>

<?php require '../../aplicacion/compartido/pie.php'; ?>