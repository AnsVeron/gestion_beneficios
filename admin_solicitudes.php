<?php
require 'config.php';
pedir_admin();

$mensaje = '';

if (isset($_POST['aprobar']) || isset($_POST['rechazar'])) {
    $estado = isset($_POST['aprobar']) ? 'aprobada' : 'rechazada';
    $q = $db->prepare("UPDATE solicitudes SET estado = ? WHERE id = ?");
    $q->execute([$estado, (int) $_POST['solicitud_id']]);
    $mensaje = isset($_POST['aprobar']) ? 'Solicitud aprobada' : 'Solicitud rechazada';
}

$q = $db->query("SELECT s.*, c.nombre AS campana, u.nombre AS vendedor, u.email
                 FROM solicitudes s
                 JOIN campanas c ON c.id = s.campana_id
                 JOIN usuarios u ON u.id = s.vendedor_id
                 WHERE c.estado = 'abierta'
                 ORDER BY c.id DESC, s.fecha");

$grupos = ['pendiente' => [], 'aprobada' => [], 'rechazada' => []];
foreach ($q->fetchAll() as $s) {
    $grupos[$s['estado']][] = $s;
}
$titulos = ['pendiente' => 'Pendientes', 'aprobada' => 'Aprobadas', 'rechazada' => 'Rechazadas'];

require 'encabezado.php';
?>

<h3>Solicitudes de vendedores</h3>

<?php if ($mensaje): ?>
    <p class="ok"><?= h($mensaje) ?></p><?php endif; ?>

<?php if (!$grupos['pendiente']): ?>
    <p>No hay solicitudes pendientes.</p>
<?php endif; ?>

<?php foreach ($grupos as $estado => $solicitudes): ?>
    <?php if ($solicitudes): ?>
        <h4><?= $titulos[$estado] ?></h4>
        <table>
            <tr>
                <th>Campaña</th>
                <th>Vendedor</th>
                <th>Email</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($solicitudes as $s): ?>
                <tr>
                    <td><?= h($s['campana']) ?></td>
                    <td><?= h($s['vendedor']) ?></td>
                    <td><?= h($s['email']) ?></td>
                    <td><?= h($s['fecha']) ?></td>
                    <td>
                        <form method="post" style="margin:0">
                            <input type="hidden" name="solicitud_id" value="<?= $s['id'] ?>">
                            <?php if ($estado != 'aprobada'): ?>
                                <button type="submit" name="aprobar">Aprobar</button>
                            <?php endif; ?>
                            <?php if ($estado != 'rechazada'): ?>
                                <button type="submit" name="rechazar"><?= $estado == 'aprobada' ? 'Quitar aprobación' : 'Rechazar' ?></button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
<?php endforeach; ?>

<?php require 'pie.php'; ?>
