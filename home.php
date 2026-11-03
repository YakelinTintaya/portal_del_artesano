    <div class="hero">
        <div class="hero-imagen">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcT0ADhhjZtRQ0HUMsb4yxVHFOqtuzJP8-HAgMPHi1Ko3w&s=10" alt="Textiles">
        </div>
        <div class="hero-texto">
            <h1>Arte y Cultura de Cusco</h1>
            <p>Conecta con artesanos locales. Compra directamente productos hechos a mano con precios justos.</p>
            <a href="<?= BASE_URL ?>/productos" class="btn-comprar">Comprar ahora</a>
        </div>
    </div>

    <div class="contenedor">
        <div class="cajas-categorias">
            <a class="caja-item" href="<?= BASE_URL ?>/productos?cat=ceramica">
                <img src="https://images.unsplash.com/photo-1578749556568-bc2c40e68b61?auto=format&fit=crop&w=400&q=80" alt="Cerámica">
            </a>
            <a class="caja-item" href="<?= BASE_URL ?>/productos?cat=plateria">
                <img src="https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?auto=format&fit=crop&w=400&q=80" alt="Platería">
            </a>
            <a class="caja-item" href="<?= BASE_URL ?>/productos?cat=arte">
                <img src="https://images.unsplash.com/photo-1513519245088-0e12902e5a38?auto=format&fit=crop&w=400&q=80" alt="Arte">
            </a>
        </div>
    </div>

    <div class="seccion-marron">
        <h2>Productos Destacados</h2>
        <div class="contenedor">
            <div class="grid-destacados">
<?php foreach ($destacados as $p): ?>
                <div class="tarjeta">
                    <div class="tarjeta-img">
                        <img src="<?= e($p['img']) ?>" alt="<?= e($p['nombre']) ?>">
                    </div>
                    <h3><?= e($p['nombre']) ?></h3>
                    <p><?= e($p['resumen']) ?></p>
                    <a class="btn-comprar" href="<?= BASE_URL ?>/producto/<?= (int) $p['id'] ?>" style="margin-top: 10px;">Ver más</a>
                </div>
<?php endforeach; ?>
            </div>
        </div>
    </div>
