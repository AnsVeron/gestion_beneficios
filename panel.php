<?php
require 'config.php';
pedir_login();

$u = usuario();
$mensaje = '';
$error = '';

if ($u['rol'] == 'vendedor' && isset($_POST['solicitar'])) {
    $q = $db->prepare("SELECT id FROM campanas WHERE id = ? AND estado = 'abierta'");
    $q->execute([(int) $_POST['campana_id']]);

    if (!$q->fetch()) {
        $error = 'Esa campaña ya no está abierta.';
    } else {
        $q = $db->prepare("INSERT INTO solicitudes (campana_id, vendedor_id, estado, fecha) VALUES (?, ?, 'pendiente', NOW())
                           ON DUPLICATE KEY UPDATE
                             fecha = IF(estado = 'rechazada', NOW(), fecha),
                             estado = IF(estado = 'rechazada', 'pendiente', estado)");
        $q->execute([(int) $_POST['campana_id'], $u['id']]);
        $mensaje = 'Solicitud enviada. El administrador tiene que aprobarla para que pueda vender.';
    }
}

$abiertas = campanas_abiertas($db);
$aprobadas = [];
$otras = [];

if ($u['rol'] == 'vendedor') {
    $aprobadas = campanas_aprobadas($db, $u['id']);

    $q = $db->prepare("SELECT c.*, s.estado AS solicitud FROM campanas c
                       LEFT JOIN solicitudes s ON s.campana_id = c.id AND s.vendedor_id = ?
                       WHERE c.estado = 'abierta' AND (s.estado IS NULL OR s.estado <> 'aprobada')
                       ORDER BY c.id DESC");
    $q->execute([$u['id']]);
    $otras = $q->fetchAll();
}

require 'encabezado.php';
?>

<h3>Inicio</h3>

<p>Bienvenido/a, <?= h($u['nombre']) ?>.</p>

<?php if ($error): ?>
    <p class="error"><?= h($error) ?></p><?php endif; ?>
<?php if ($mensaje): ?>
    <p class="ok"><?= h($mensaje) ?></p><?php endif; ?>

<?php if (!$abiertas): ?>
    <p>No hay ninguna campaña abierta en este momento.</p>
<?php elseif ($u['rol'] == 'vendedor'): ?>
    <h4>Mis campañas aprobadas</h4>
    <?php if (!$aprobadas): ?>
        <p>Todavía no tiene campañas aprobadas. Solicite la aprobación en una de las campañas abiertas.</p>
    <?php else: ?>
        <table>
            <tr>
                <th>Campaña</th>
                <th>Vendido</th>
                <th>Cobrado</th>
                <th>Pend. de cobro</th>
                <th>Rendido</th>
                <th>Deuda (a rendir)</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($aprobadas as $c): ?>
                <?php
                $q = $db->prepare("SELECT IFNULL(SUM(total),0) FROM ventas WHERE vendedor_id = ? AND campana_id = ?");
                $q->execute([$u['id'], $c['id']]);
                $vendido = $q->fetchColumn();

                $q = $db->prepare("SELECT IFNULL(SUM(p.monto),0) FROM pagos p
                                   JOIN ventas v ON v.id = p.venta_id
                                   WHERE v.vendedor_id = ? AND v.campana_id = ?");
                $q->execute([$u['id'], $c['id']]);
                $cobrado = $q->fetchColumn();

                $q = $db->prepare("SELECT IFNULL(SUM(monto),0) FROM rendiciones WHERE vendedor_id = ? AND campana_id = ?");
                $q->execute([$u['id'], $c['id']]);
                $rendido = $q->fetchColumn();
                ?>
                <tr>
                    <td><?= h($c['nombre']) ?></td>
                    <td>$<?= number_format($vendido, 2) ?></td>
                    <td>$<?= number_format($cobrado, 2) ?></td>
                    <td>$<?= number_format($vendido - $cobrado, 2) ?></td>
                    <td>$<?= number_format($rendido, 2) ?></td>
                    <td><b>$<?= number_format($cobrado - $rendido, 2) ?></b></td>
                    <td>
                        <a href="vend_nueva_venta.php?campana_id=<?= $c['id'] ?>">Registrar venta</a>
                        <a href="vend_rendicion.php?campana_id=<?= $c['id'] ?>">Rendir</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>

    <?php if ($otras): ?>
        <h4>Otras campañas abiertas</h4>
        <table>
            <tr>
                <th>Campaña</th>
                <th>Inicio</th>
                <th>Fin</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($otras as $c): ?>
                <tr>
                    <td><?= h($c['nombre']) ?></td>
                    <td><?= h($c['fecha_inicio']) ?></td>
                    <td><?= h($c['fecha_fin']) ?></td>
                    <td>
                        <?php if ($c['solicitud'] == 'pendiente'): ?>
                            Pendiente de aprobación
                        <?php elseif ($c['solicitud'] == 'rechazada'): ?>
                            Rechazada
                        <?php else: ?>
                            Sin solicitar
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($c['solicitud'] != 'pendiente'): ?>
                            <form method="post" style="margin:0">
                                <input type="hidden" name="campana_id" value="<?= $c['id'] ?>">
                                <button type="submit" name="solicitar"><?= $c['solicitud'] == 'rechazada' ? 'Solicitar de nuevo' : 'Solicitar aprobación' ?></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php else: ?>
    <h4>Campañas abiertas</h4>
    <table>
        <tr>
            <th>Campaña</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Acciones</th>
        </tr>
        <?php foreach ($abiertas as $c): ?>
            <tr>
                <td><?= h($c['nombre']) ?></td>
                <td><?= h($c['fecha_inicio']) ?></td>
                <td><?= h($c['fecha_fin']) ?></td>
                <td>
                    <a href="admin_ventas.php?campana_id=<?= $c['id'] ?>">Ver ventas</a>
                    <a href="admin_rendiciones.php?campana_id=<?= $c['id'] ?>">Ver rendiciones</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?>

<?php require 'pie.php'; ?>
