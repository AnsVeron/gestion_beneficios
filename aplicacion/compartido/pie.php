    </div>

    <footer class="pie">
        <div>
            <b>Gestión de Beneficios</b>
            <p>Stock, ventas, cobros y rendiciones de cada campaña a beneficio.</p>
        </div>
        <div class="pie-enlaces">
            <?php if (usuario()): ?>
                <a href="/proyecto/sitio/panel.php">Inicio</a>
                <a href="/proyecto/sitio/acceso/logout.php">Cerrar sesión</a>
            <?php else: ?>
                <a href="/proyecto/index.php">Inicio</a>
                <a href="/proyecto/sitio/acceso/login.php">Iniciar sesión</a>
                <a href="/proyecto/sitio/acceso/registro.php">Registrarse como vendedor</a>
            <?php endif; ?>
        </div>
        <small>© <?= date('Y') ?> Gestión de Beneficios</small>
    </footer>

</body>
</html>
