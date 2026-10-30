<?php
require 'config.php';
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

require 'encabezado.php';
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
        <th>ID</th>
        <th>Fecha</th>
        <th>Vendedor</th>
        <th>Cliente</th>
        <th>Detalle</th>
        <th>Total</th>
        <th>Pagado</th>
        <th>Saldo</th>
        <th>Entrega</th>
    </tr>
    <?php foreach ($ventas as $v): ?>
    <?php
    $qi = $db->prepare("SELECT i.*, p.nombre FROM venta_items i JOIN productos p ON p.id = i.producto_id WHERE i.venta_id = ?");
    $qi->execute([$v['id']]);
    $items = $qi->fetchAll();
    ?>
    <tr>
        <td><?= $v['id'] ?></td>
        <td><?= h($v['fecha']) ?></td>
        <td><?= h($v['vendedor']) ?></td>
        <td><?= h($v['cliente']) ?></td>
        <td>
            <?php foreach ($items as $i): ?>
                    <?= h($i['nombre']) ?> x<?= $i['cantidad'] ?><br>
            <?php endforeach; ?>
        </td>
        <td>$<?= number_format($v['total'], 2) ?></td>
        <td>$<?= number_format($v['pagado'], 2) ?></td>
        <td>$<?= number_format($v['total'] - $v['pagado'], 2) ?></td>
        <td>
            <?php if ($v['entregado']): ?>
                    Entregado el <?= h($v['fecha_entrega']) ?>
            <?php else: ?>
                    <form method="post" style="margin:0">
                        <input type="hidden" name="venta_id" value="<?= $v['id'] ?>">
                        <input type="hidden" name="campana_id" value="<?= (int) $campana_id ?>">
                        <input type="date" name="fecha_entrega" value="<?= date('Y-m-d') ?>">
                        <button type="submit" name="entregar">Registrar entrega</button>
                    </form>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</table>

<?php require 'pie.php'; ?>
