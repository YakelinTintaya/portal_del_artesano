    <div class="contenedor">
        <h2 style="margin-top: 20px;">Mi Carrito de Compras</h2>
        <div class="caja-blanca">
            <div id="carrito-items">
<?php if (!$items): ?>
                <p style="padding: 15px;">El carrito está vacío.</p>
<?php endif; ?>
<?php foreach ($items as $p): ?>
                <div class="item-carrito">
                    <img src="<?= e($p['img']) ?>" alt="" style="width: 50px; height: 50px; object-fit: cover; border-radius:4px;">
                    <div style="flex:1; margin-left: 15px;">
                        <h4><?= e($p['nombre']) ?></h4>
                        <small><?= money($p['precio']) ?> x <?= (int) $p['cantidad'] ?></small>
                    </div>
                    <strong><?= money($p['subtotal']) ?></strong>
                    <form action="<?= BASE_URL ?>/carrito/eliminar" method="post" style="margin:0">
                        <?= csrf_field() ?>
                        <input type="hidden" name="producto_id" value="<?= (int) $p['id'] ?>">
                        <button type="submit" style="background:red; color:white; border:none; padding:4px 8px; margin-left:15px; cursor:pointer; border-radius:3px;">X</button>
                    </form>
                </div>
<?php endforeach; ?>
            </div>
            <hr style="margin: 15px 0; border: 0; border-top: 1px solid #eee;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3>Total a Pagar: <span id="total-carrito"><?= money($total) ?></span></h3>
<?php if ($items): ?>
                <a href="<?= BASE_URL ?>/checkout" class="btn-comprar"><?= empty($_SESSION['usuario']) ? 'Comprar Producto (Iniciar Sesión)' : 'Continuar al Pago' ?></a>
<?php endif; ?>
            </div>
        </div>
    </div>
