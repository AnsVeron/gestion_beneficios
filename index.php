<?php
require 'config.php';

if (usuario()) {
    header('Location: panel.php');
    exit;
}

require 'encabezado.php';
?>

<main class="portada">

    <section class="heroe">
        <p class="heroe-marca">Campañas a beneficio</p>
        <h1>Del primer producto vendido a la <em>última rendición</em>.</h1>
        <p class="heroe-texto">Ordena las campañas a beneficio: el stock, las ventas, los cobros y el dinero que cada vendedor tiene que rendir.</p>
        <div class="heroe-acciones">
            <a class="boton" href="login.php">Iniciar sesión</a>
            <a class="boton boton-linea" href="registro.php">Registrarse como vendedor</a>
        </div>
    </section>

    <section>
        <h2>Cómo avanza una campaña</h2>
        <ol class="recorrido">
            <li>
                <b>Se abre la campaña</b>
                El administrador carga los productos, sus precios y el stock, y aprueba a los vendedores.
            </li>
            <li>
                <b>Se registra la venta</b>
                El vendedor anota el cliente y las cantidades, y el stock se descuenta solo.
            </li>
            <li>
                <b>Se cobra</b>
                Cada venta admite uno o varios pagos, hasta cubrir el saldo.
            </li>
            <li>
                <b>Se rinde el dinero</b>
                El vendedor informa lo que entrega y el administrador lo confirma.
            </li>
            <li>
                <b>Se entrega el producto</b>
                El administrador registra la fecha de entrega de cada venta.
            </li>
        </ol>
    </section>

    <section class="roles">
        <div>
            <h2>Para quien organiza</h2>
            <ul>
                <li>Tener varias campañas abiertas a la vez.</li>
                <li>Aprobar qué vendedores participan en cada campaña.</li>
                <li>Ajustar el stock con un historial de movimientos.</li>
                <li>Ver cuánto vendió, cobró y rindió cada vendedor.</li>
                <li>Confirmar rendiciones y registrar entregas.</li>
            </ul>
        </div>
        <div>
            <h2>Para quien vende</h2>
            <ul>
                <li>Solicitar la aprobación para vender en una campaña.</li>
                <li>Elegir la campaña y registrar cada venta.</li>
                <li>Anotar los cobros, completos o en partes.</li>
                <li>Ver cuánto falta cobrar y cuánto falta rendir.</li>
                <li>Seguir el estado de sus rendiciones y entregas.</li>
            </ul>
        </div>
    </section>

</main>

<?php require 'pie.php'; ?>
