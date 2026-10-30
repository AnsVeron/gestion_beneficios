<?php
require 'config.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $pass = $_POST['password'];

    $q = $db->prepare("SELECT * FROM usuarios WHERE email = ?");
    $q->execute([$email]);
    $u = $q->fetch();

    if ($u && password_verify($pass, $u['password'])) {
        $_SESSION['usuario'] = $u;
        header('Location: index.php');
        exit;
    }
    $error = 'Email o contraseña incorrectos';
}

require 'encabezado.php';
?>

<h3>Iniciar sesión</h3>

<?php if ($error): ?>
    <p class="error"><?= h($error) ?></p><?php endif; ?>

<form method="post">
    Email: <input type="email" name="email" required><br><br>
    Contraseña: <input type="password" name="password" required><br><br>
    <button type="submit">Entrar</button>
</form>

<p><a href="registro.php">Registrarse como vendedor</a></p>

<?php require 'pie.php'; ?>