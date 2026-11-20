<?php
require '../../aplicacion/compartido/config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $q = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
    $q->execute([$email]);
    $u = $q->fetch();

    if ($u && password_verify($pass, $u['password'])) {
        $_SESSION['usuario'] = $u;
        header('Location: /proyecto/sitio/panel.php');
        exit;
    }
    $error = 'Email o contraseña incorrectos';
}

require '../../aplicacion/compartido/encabezado.php';
?>

<main class="acceso">
    <div class="acceso-caja">
        <h3>Iniciar sesión</h3>

        <?php if ($error): ?>
            <p class="error"><?= h($error) ?></p><?php endif; ?>

        <form method="post">
            <label>Email <input type="email" name="email" required></label>
            <label>Contraseña <input type="password" name="password" required></label>
            <button type="submit">Entrar</button>
        </form>

        <p><a href="/proyecto/sitio/acceso/registro.php">Registrarse como vendedor</a></p>
    </div>
</main>

<?php require '../../aplicacion/compartido/pie.php'; ?>