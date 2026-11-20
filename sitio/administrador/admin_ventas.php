<?php
require '../../aplicacion/compartido/config.php';
pedir_admin();

$mensaje = '';

if (isset($_POST['entregar'])) {
    $venta_id = (int) $_POST['venta_id'];
    $fecha = $_POST['fecha_entrega'] ?: date('Y-m-d');
    $q = $db->prepare("UPDATE ventas SET entregado = 1, fecha_entrega = ? WHERE id = ?");
    $q->execute([$fecha, $venta_id]);
    $mensaje = 'Entrega registrada';
}

$campanas = $db->query("SELECT * FROM campanas ORDER BY id DESC")->fetchAll();
$campana_id = isset($_REQUEST['campana_id']) ? (int) $_REQUEST['campana_id'] : 0;
if (!$campana_id) {
    $abierta = campana_abierta($db);
    if ($abierta)
        $campana_id = $abierta['id'];
    elseif ($campanas)
        $campana_id = $campanas[0]['id'];
}

$q = $db->prepare("
    SELECT v.*, u.nombre AS vendedor,
      (SELECT IFNULL(SUM(p.monto),0) FROM pagos p WHERE p.venta_id = v.id) AS pagado
    FROM ventas v
    JOIN usuarios u ON u.id = v.vendedor_id
    WHERE v.campana_id = ?
    ORDER BY v.id DESC");
$q->execute([$campana_id]);
$ventas = $q->fetchAll();

require '../../aplicacion/compartido/encabezado.php';
?>

<h3>Ventas y Entregas</h3>

<?php if ($mensaje): ?><p class="ok"><?= h($mensaje) ?></p><?php endif; ?>

<form method="get">
    Campaña:
    <select name="campana_id" onchange="this.form.submit()">
        <?php foreach ($campanas as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id'] == $campana_id ? 'selected' : '' ?>><?= h($c['nombre']) ?></option>
        <?php endforeach; ?>
    </select>
</form>

<table>
    <tr>
        <th>Venta</th>
        <th>Vendedor</th>
        <th>Cliente y detalle</th>
        <th class="num">Total</th>
        <th class="num">Pagado</th>
        <th class="num">Saldo</th>
        <th>Entrega</th>
    </tr>
    <?php foreach ($ventas as $v): ?>
        <?php
        $qi = $db->prepare("SELECT i.*, p.nombre FROM venta_items i JOIN productos p ON p.id = i.producto_id WHERE i.venta_id = ?");
        $qi->execute([$v['id']]);
        $items = $qi->fetchAll();
        ?>
        <tr>
            <td class="sin-corte">
                N° <?= $v['id'] ?>
                <span class="dato-tenue"><?= h(substr($v['fecha'], 0, 16)) ?></span>
            </td>
            <td><?= h($v['vendedor']) ?></td>
            <td>
                <?= h($v['cliente']) ?>
                <?php foreach ($items as $i): ?>
                    <span class="dato-tenue"><?= h($i['nombre']) ?>&nbsp;x<?= $i['cantidad'] ?></span>
                <?php endforeach; ?>
            </td>
            <td class="num">$<?= number_format($v['total'], 2) ?></td>
            <td class="num">$<?= number_format($v['pagado'], 2) ?></td>
            <td class="num">$<?= number_format($v['total'] - $v['pagado'], 2) ?></td>
            <td>
                <?php if ($v['entregado']): ?>
                    <span class="sin-corte">Entregado el <?= h($v['fecha_entrega']) ?></span>
                <?php else: ?>
                    <form method="post" class="cobro">
                        <input type="hidden" name="venta_id" value="<?= $v['id'] ?>">
                        <input type="hidden" name="campana_id" value="<?= (int) $campana_id ?>">
                        <input type="date" name="fecha_entrega" value="<?= date('Y-m-d') ?>">
                        <button type="submit" name="entregar">Entregar</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php require '../../aplicacion/compartido/pie.php'; ?>
