<?php
require 'config.php';
pedir_vendedor();

$u = usuario();
$campana = campana_abierta($db);
$mensaje = '';
$error = '';

function totales_vendedor($db, $vendedor_id, $campana_id)
{
    $q = $db->prepare("SELECT IFNULL(SUM(p.monto),0) FROM pagos p
                       JOIN ventas v ON v.id = p.venta_id
                       WHERE v.vendedor_id = ? AND v.campana_id = ?");
    $q->execute([$vendedor_id, $campana_id]);
    $cobrado = $q->fetchColumn();

    $q = $db->prepare("SELECT IFNULL(SUM(monto),0) FROM rendiciones WHERE vendedor_id = ? AND campana_id = ?");
    $q->execute([$vendedor_id, $campana_id]);
    $rendido = $q->fetchColumn();

    return [$cobrado, $rendido];
}

if ($campana && isset($_POST['rendir'])) {
    $monto = (float) $_POST['monto'];
    list($cobrado, $rendido) = totales_vendedor($db, $u['id'], $campana['id']);
    $deuda = $cobrado - $rendido;

    if ($monto <= 0) {
        $error = 'el monto debe ser mayor a cero';
    } elseif ($monto > $deuda) {
        $error = 'el monto supera lo pendiente de rendición ($' . number_format($deuda, 2) . ')';
    } else {
        $q = $db->prepare("INSERT INTO rendiciones (campana_id, vendedor_id, monto, fecha) VALUES (?, ?, ?, NOW())");
        $q->execute([$campana['id'], $u['id'], $monto]);
        $mensaje = 'rendición registrada. queda pendiente de confirmación por el administrador.';
    }
}

$deuda = 0;
$rendiciones = [];
if ($campana) {
    list($cobrado, $rendido) = totales_vendedor($db, $u['id'], $campana['id']);
    $deuda = $cobrado - $rendido;

    $q = $db->prepare("SELECT * FROM rendiciones WHERE vendedor_id = ? AND campana_id = ? ORDER BY id DESC");
    $q->execute([$u['id'], $campana['id']]);
    $rendiciones = $q->fetchAll();
}

require 'encabezado.php';
?>

<h3>Rendición de fondos</h3>

<?php if (!$campana): ?>
    <p>No hay campaña abierta.</p>
<?php else: ?>

    <?php if ($error): ?>
        <p class="error"><?= h($error) ?></p><?php endif; ?>
    <?php if ($mensaje): ?>
        <p class="ok"><?= h($mensaje) ?></p><?php endif; ?>

    <p>Campaña: <b><?= h($campana['nombre']) ?></b></p>
    <p>Cobrado: $<?= number_format($cobrado, 2) ?> — Rendido: $<?= number_format($rendido, 2) ?></p>
    <p><b>Pendiente de rendir: $<?= number_format($deuda, 2) ?></b></p>

    <?php if ($deuda > 0): ?>
        <form method="post">
            Monto a rendir: <input type="number" step="0.01" name="monto" value="<?= $deuda ?>" required>
            <button type="submit" name="rendir">Rendir</button>
        </form>
    <?php endif; ?>

    <h4>Mis rendiciones</h4>
    <table>
        <tr>
            <th>Fecha</th>
            <th>Monto</th>
            <th>Estado</th>
        </tr>
        <?php foreach ($rendiciones as $r): ?>
            <tr>
                <td><?= h($r['fecha']) ?></td>
                <td>$<?= number_format($r['monto'], 2) ?></td>
                <td><?= $r['confirmada'] ? 'Confirmada' : 'Pendiente' ?></td>
            </tr>
        <?php endforeach; ?>
    </table>

<?php endif; ?>

<?php require 'pie.php'; ?>