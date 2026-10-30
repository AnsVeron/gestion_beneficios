<?php
require 'config.php';
pedir_admin();

$mensaje = '';

if (isset($_POST['guardar'])) {
    $id = (int) $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $desc = trim($_POST['descripcion']);
    $inicio = $_POST['fecha_inicio'] ?: null;
    $fin = $_POST['fecha_fin'] ?: null;

    if ($id > 0) {
        $q = $db->prepare("UPDATE campanas SET nombre=?, descripcion=?, fecha_inicio=?, fecha_fin=? WHERE id=?");
        $q->execute([$nombre, $desc, $inicio, $fin, $id]);
        $mensaje = 'Campaña actualizada';
    } else {
        $q = $db->prepare("INSERT INTO campanas (nombre, descripcion, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?)");
        $q->execute([$nombre, $desc, $inicio, $fin]);
        $mensaje = 'Campaña creada';
    }
}

if (isset($_GET['iniciar'])) {
    if (campana_abierta($db)) {
        $mensaje = 'Ya hay una campaña abierta, ciérrela primero.';
    } else {
        $q = $db->prepare("UPDATE campanas SET estado='abierta' WHERE id=? AND estado='borrador'");
        $q->execute([(int) $_GET['iniciar']]);
        $mensaje = 'Campaña iniciada';
    }
}

if (isset($_GET['cerrar'])) {
    $q = $db->prepare("UPDATE campanas SET estado='cerrada' WHERE id=? AND estado='abierta'");
    $q->execute([(int) $_GET['cerrar']]);
    $mensaje = 'Campaña cerrada';
}

$editar = ['id' => 0, 'nombre' => '', 'descripcion' => '', 'fecha_inicio' => '', 'fecha_fin' => ''];
if (isset($_GET['editar'])) {
    $q = $db->prepare("SELECT * FROM campanas WHERE id=?");
    $q->execute([(int) $_GET['editar']]);
    $c = $q->fetch();
    if ($c)
        $editar = $c;
}

$campanas = $db->query("SELECT * FROM campanas ORDER BY id DESC")->fetchAll();

require 'encabezado.php';
?>

<h3>Campañas / Beneficios</h3>

<?php if ($mensaje): ?>
    <p class="ok"><?= h($mensaje) ?></p><?php endif; ?>

<form method="post">
    <input type="hidden" name="id" value="<?= (int) $editar['id'] ?>">
        Nombre: <input type="text" name="nombre" value="<?= h($editar['nombre']) ?>" required><br><br>
        Descripción: <textarea name="descripcion"><?= h($editar['descripcion']) ?></textarea><br><br>
        Fecha inicio: <input type="date" name="fecha_inicio" value="<?= h($editar['fecha_inicio']) ?>">
        Fecha fin: <input type="date" name="fecha_fin" value="<?= h($editar['fecha_fin']) ?>"><br><br>
    <button type="submit" name="guardar">Guardar</button>
    <?php if ($editar['id']): ?><a href="admin_campanas.php">Cancelar</a><?php endif; ?>
</form>

<h4>Listado</h4>
<table>
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Inicio</th>
        <th>Fin</th>
        <th>Estado</th>
        <th>Acciones</th>
    </tr>
    <?php foreach ($campanas as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= h($c['nombre']) ?></td>
            <td><?= h($c['fecha_inicio']) ?></td>
            <td><?= h($c['fecha_fin']) ?></td>
            <td><?= h($c['estado']) ?></td>
            <td>
                <a href="?editar=<?= $c['id'] ?>">Editar</a>
                <?php if ($c['estado'] == 'borrador'): ?>
                    <a href="?iniciar=<?= $c['id'] ?>">Iniciar</a>
                <?php elseif ($c['estado'] == 'abierta'): ?>
                    <a href="?cerrar=<?= $c['id'] ?>" onclick="return confirm('¿Cerrar la campaña?')">Cerrar</a>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<?php require 'pie.php'; ?>