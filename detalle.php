    <div class="contenedor detalle-box">
        <div class="imagen-grande">
            <img src="<?= e($producto['img']) ?>" alt="<?= e($producto['nombre']) ?>">
        </div>
        <div class="info-producto">
            <h1><?= e($producto['nombre']) ?></h1>
            <p><?= e($producto['desc']) ?></p>
            <div class="precio"><?= money($producto['precio']) ?></div>

            <form action="<?= BASE_URL ?>/carrito/agregar" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="producto_id" value="<?= (int) $producto['id'] ?>">

                <div style="display:flex; align-items:center; gap: 10px; margin-bottom: 20px;">
                    <button type="button" data-cantidad="-1" style="padding: 5px 12px; cursor:pointer;">-</button>
                    <input type="number" id="cantidad" name="cantidad" value="1" min="1" max="99"
                           style="width: 60px; text-align:center; font-weight:bold; font-size: 18px;">
                    <button type="button" data-cantidad="1" style="padding: 5px 12px; cursor:pointer;">+</button>
                </div>

                <button type="submit" class="btn-comprar">Añadir al Carrito</button>
            </form>
        </div>
    </div>
