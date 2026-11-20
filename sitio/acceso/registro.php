<?php
require '../../aplicacion/compartido/config.php';

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    if ($nombre == '' || $email == '' || $pass == '') {
        $error = 'Complete todos los campos';
    } else {
        $q = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
        $q->execute([$email]);
        if ($q->fetch()) {
            $error = 'Ese email ya está registrado';
        } else {
            $q = $db->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, 'vendedor')");
            $q->execute([$nombre, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            $ok = 'Registrado correctamente. Ya puede iniciar sesión.';
        }
    }
}

require '../../aplicacion/compartido/encabezado.php';
?>

<main class="acceso">
    <div class="acceso-caja">
        <h3>Registro de vendedor</h3>

        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p><?php endif; ?>
        <?php if ($ok): ?>
            <p class="ok"><?= h($ok) ?></p><?php endif; ?>

        <form method="post">
            <label>Nombre <input type="text" name="nombre" required></label>
            <label>Email <input type="email" name="email" required></label>
            <label>Contraseña <input type="password" name="password" required></label>
            <button type="submit">Registrarse</button>
        </form>

        <p><a href="/proyecto/sitio/acceso/login.php">Volver al inicio de sesión</a></p>
    </div>
</main>

<?php require '../../aplicacion/compartido/pie.php'; ?>