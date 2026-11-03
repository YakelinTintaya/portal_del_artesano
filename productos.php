    <div class="contenedor tienda-layout">
        <div class="filtros">
            <h3>Filtros</h3>
            <form action="<?= BASE_URL ?>/productos" method="get">
                <?php if ($q !== ''): ?><input type="hidden" name="q" value="<?= e($q) ?>"><?php endif; ?>
                <label>Categoría</label>
                <select name="cat" onchange="this.form.submit()">
                    <option value="todos">Todos</option>
<?php foreach ($categorias as $slug => $nombre): ?>
                    <option value="<?= e($slug) ?>" <?= $cat === $slug ? 'selected' : '' ?>><?= e($nombre) ?></option>
<?php endforeach; ?>
                </select>
                <noscript><button type="submit" class="btn-comprar">Aplicar</button></noscript>
            </form>
        </div>

        <div class="contenido-tienda">
            <div class="botones-pildora">
                <a class="btn-pildora <?= $cat === 'todos' ? 'active' : '' ?>" href="<?= BASE_URL ?>/productos<?= $q !== '' ? '?q=' . urlencode($q) : '' ?>">Todos</a>
<?php foreach ($categorias as $slug => $nombre): ?>
                <a class="btn-pildora <?= $cat === $slug ? 'active' : '' ?>" href="<?= BASE_URL ?>/productos?cat=<?= e($slug) ?><?= $q !== '' ? '&amp;q=' . urlencode($q) : '' ?>"><?= e($nombre) ?></a>
<?php endforeach; ?>
            </div>

            <div class="grid-productos">
<?php foreach ($productos as $p): ?>
                <div class="producto-caja">
                    <img src="<?= e($p['img']) ?>" alt="<?= e($p['nombre']) ?>">
                    <h4><?= e($p['nombre']) ?></h4>
                    <p><?= money($p['precio']) ?></p>
                    <a class="btn-comprar" href="<?= BASE_URL ?>/producto/<?= (int) $p['id'] ?>">Ver</a>
                </div>
<?php endforeach; ?>
<?php if (!$productos): ?>
                <p>No se encontraron productos.</p>
<?php endif; ?>
            </div>
        </div>
    </div>
