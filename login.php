    <div class="contenedor">
        <h2 style="margin-top: 30px; text-align: center;">Iniciar Sesión</h2>
        <div class="caja-blanca" style="max-width: 400px; margin: 20px auto;">
            <form class="formulario-pago" action="<?= BASE_URL ?>/login" method="post">
                <?= csrf_field() ?>
                <label>Correo Electrónico:</label>
                <input type="email" name="email" placeholder="cliente@correo.com" required>

                <label>Contraseña:</label>
                <input type="password" name="password" placeholder="********" required>

                <button type="submit" class="btn-comprar" style="margin-top: 15px; width: 100%;">Ingresar y Continuar Compra</button>
            </form>

            <p style="font-size: 13px; margin-top: 20px; text-align: center; color: #666;">
                ¿No tienes cuenta? <a href="#" style="color: #333; font-weight: bold;">Regístrate como Cliente</a>
            </p>
        </div>
    </div>
