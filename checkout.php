    <div class="contenedor">
        <h2 style="margin-top:20px;">Detalles de Pago y Envío</h2>
        <div class="caja-blanca" style="max-width: 500px; margin: 20px auto;">
            <p style="margin-bottom: 15px;">Total a pagar: <strong><?= money($total) ?></strong></p>
            <form class="formulario-pago" action="<?= BASE_URL ?>/checkout" method="post">
                <?= csrf_field() ?>
                <label>Nombre Completo:</label>
                <input type="text" name="nombre" placeholder="Ej. Juan Pérez" required>

                <label>Dirección de Envío en Cusco / Perú:</label>
                <input type="text" name="direccion" placeholder="Av. El Sol 123" required>

                <label>Método de Pago:</label>
                <select name="metodo" style="padding: 8px;">
<?php foreach ($metodos as $m): ?>
                    <option><?= e($m) ?></option>
<?php endforeach; ?>
                </select>
                <br>
                <button type="submit" class="btn-comprar">Finalizar y Confirmar Pago</button>
            </form>
        </div>
    </div>
