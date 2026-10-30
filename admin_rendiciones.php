<?php
require 'config.php';
pedir_admin();

$mensaje = '';

if (isset($_GET['confirmar'])) {
    $q = $db->prepare("UPDATE rendiciones SET confirmada = 1 WHERE id = ?");
    $q->execute([(int) $_GET['confirmar']]);
    $mensaje = 'Rendición confirmada';
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

$q = $db->prepare("SELECT r.*, u.nombre AS vendedor FROM rendiciones r
                   JOIN usuarios u ON u.id = r.vendedor_id
                   WHERE r.campana_id = ? ORDER BY r.id DESC");
$q->execute([$campana_id]);
$rendiciones = $q->fetchAll();

require 'encabezado.php';
?>

<h3>Rendiciones</h3>

<?php if ($mensaje): ?>
    <p class="ok"><?= h($mensaje) ?></p><?php endif; ?>

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
        <th>Monto</th>
        <th>Estado</th>
        <th></th>
    </tr>
    <?php foreach ($rendiciones as $r): ?>
        <tr>
            <td><?= $r['id'] ?></td>
            <td><?= h($r['fecha']) ?></td>
            <td><?= h($r['vendedor']) ?></td>
            <td>$<?= number_format($r['monto'], 2) ?></td>
            <td><?= $r['confirmada'] ? 'Confirmada' : 'Pendiente' ?></td>
            <td>
                <?php if (!$r['confirmada']): ?>
                    <a href="?confirmar=<?= $r['id'] ?>&campana_id=<?= $campana_id ?>">Confirmar</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<p>El detalle de deuda por vendedor está en <a href="admin_vendedores.php">Vendedores</a>.</p>

<?php require 'pie.php'; ?>