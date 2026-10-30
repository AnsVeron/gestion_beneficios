<?php
require 'config.php';
pedir_login();

$u = usuario();
$campana = campana_abierta($db);

require 'encabezado.php';
?>

<h3>Inicio</h3>

<p>Bienvenido/a, <?= h($u['nombre']) ?>.</p>

<?php if ($campana): ?>
    <p>Campaña activa: <b><?= h($campana['nombre']) ?></b></p>
<?php else: ?>
    <p>No hay ninguna campaña abierta en este momento.</p>
<?php endif; ?>

<?php if ($u['rol'] == 'vendedor' && $campana): ?>
    <?php
    $q = $db->prepare("SELECT IFNULL(SUM(total),0) FROM ventas WHERE vendedor_id = ? AND campana_id = ?");
    $q->execute([$u['id'], $campana['id']]);
    $vendido = $q->fetchColumn();

    $q = $db->prepare("SELECT IFNULL(SUM(p.monto),0) FROM pagos p
                       JOIN ventas v ON v.id = p.venta_id
                       WHERE v.vendedor_id = ? AND v.campana_id = ?");
    $q->execute([$u['id'], $campana['id']]);
    $cobrado = $q->fetchColumn();

    $q = $db->prepare("SELECT IFNULL(SUM(monto),0) FROM rendiciones WHERE vendedor_id = ? AND campana_id = ?");
    $q->execute([$u['id'], $campana['id']]);
    $rendido = $q->fetchColumn();
    ?>
    <table>
        <tr>
            <td>Total vendido</td>
            <td>$<?= number_format($vendido, 2) ?></td>
        </tr>
        <tr>
            <td>Total cobrado</td>
            <td>$<?= number_format($cobrado, 2) ?></td>
        </tr>
        <tr>
            <td>Pendiente de cobro</td>
            <td>$<?= number_format($vendido - $cobrado, 2) ?></td>
        </tr>
        <tr>
            <td>Total rendido</td>
            <td>$<?= number_format($rendido, 2) ?></td>
        </tr>
        <tr>
            <td><b>Deuda (a rendir)</b></td>
            <td><b>$<?= number_format($cobrado - $rendido, 2) ?></b></td>
        </tr>
    </table>
<?php endif; ?>

<?php require 'pie.php'; ?>