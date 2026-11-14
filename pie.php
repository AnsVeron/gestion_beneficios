    </div>

    <footer class="pie">
        <div>
            <b>Gestión de Beneficios</b>
            <p>Stock, ventas, cobros y rendiciones de cada campaña a beneficio.</p>
        </div>
        <div class="pie-enlaces">
            <?php if (usuario()): ?>
                <a href="panel.php">Inicio</a>
                <a href="logout.php">Cerrar sesión</a>
            <?php else: ?>
                <a href="index.php">Inicio</a>
                <a href="login.php">Iniciar sesión</a>
                <a href="registro.php">Registrarse como vendedor</a>
            <?php endif; ?>
        </div>
        <small>© <?= date('Y') ?> Gestión de Beneficios</small>
    </footer>

</body>
</html>
