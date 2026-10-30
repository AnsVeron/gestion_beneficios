<?php
require 'config.php';
pedir_admin();

$mensaje = '';

if (isset($_POST['guardar'])) {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $q = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
    $q->execute([$email]);
    if ($q->fetch()) {
        $mensaje = 'Ese email ya existe';
    } else {
        $q = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'vendedor')");
        $q->execute([$nombre, $email, password_hash($pass, PASSWORD_DEFAULT)]);
        $mensaje = 'Vendedor agregado';
    }
}

$campanas = $db->query("SELECT * FROM campanas ORDER BY id DESC")->fetchAll();
$campana_id = isset($_GET['campana_id']) ? (int) $_GET['campana_id'] : 0;
if (!$campana_id) {
    $abierta = campana_abierta($db);
    if ($abierta)
        $campana_id = $abierta['id'];
    elseif ($campanas)
        $campana_id = $campanas[0]['id'];
}

$q = $db->prepare("
    SELECT u.id, u.nombre, u.email,
      (SELECT IFNULL(SUM(v.total),0) FROM ventas v WHERE v.vendedor_id = u.id AND v.campana_id = ?) AS vendido,
      (SELECT IFNULL(SUM(p.monto),0) FROM pagos p JOIN ventas v ON v.id = p.venta_id
         WHERE v.vendedor_id = u.id AND v.campana_id = ?) AS cobrado,
      (SELECT IFNULL(SUM(r.monto),0) FROM rendiciones r WHERE r.vendedor_id = u.id AND r.campana_id = ?) AS rendido
    FROM usuarios u
    WHERE u.rol = 'vendedor'
    ORDER BY u.nombre");
$q->execute([$campana_id, $campana_id, $campana_id]);
$vendedores = $q->fetchAll();

require 'encabezado.php';
?>

<h3>Vendedores</h3>

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
        <th>Nombre</th>
        <th>Email</th>
        <th>Vendido</th>
        <th>Cobrado</th>
        <th>Pend. de cobro</th>
        <th>Rendido</th>
        <th>Deuda</th>
    </tr>
    <?php foreach ($vendedores as $v): ?>
        <tr>
            <td><?= $v['id'] ?></td>
            <td><?= h($v['nombre']) ?></td>
            <td><?= h($v['email']) ?></td>
            <td>$<?= number_format($v['vendido'], 2) ?></td>
            <td>$<?= number_format($v['cobrado'], 2) ?></td>
            <td>$<?= number_format($v['vendido'] - $v['cobrado'], 2) ?></td>
            <td>$<?= number_format($v['rendido'], 2) ?></td>
            <td>$<?= number_format($v['cobrado'] - $v['rendido'], 2) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<h4>Agregar vendedor</h4>
<form method="post">
    Nombre: <input type="text" name="nombre" required>
    Email: <input type="email" name="email" required>
    Contraseña: <input type="password" name="password" required>
    <button type="submit" name="guardar">Agregar</button>
</form>

<?php require 'pie.php'; ?>